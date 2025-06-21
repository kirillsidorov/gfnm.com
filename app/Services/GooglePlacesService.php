<?php

namespace App\Services;

use App\Models\RestaurantModel;
use App\Models\CityModel;

class GooglePlacesService
{
    private $apiKey;
    private $restaurantModel;
    private $cityModel;
    
    public function __construct()
    {
        $this->apiKey = env('GOOGLE_PLACES_API_KEY'); // Добавить в .env
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
    }
    
    /**
     * Поиск грузинских ресторанов в городе
     */
    public function searchGeorgianRestaurants($cityName, $radius = 50000)
    {
        try {
            // 1. Получаем координаты города
            $cityCoords = $this->getCityCoordinates($cityName);
            if (!$cityCoords) {
                return ['error' => "Не удалось найти координаты для города: {$cityName}"];
            }
            
            // 2. Ищем грузинские рестораны
            $restaurants = $this->findRestaurants($cityCoords, $radius);
            
            // 3. Сохраняем в базу
            $saved = $this->saveRestaurants($restaurants, $cityName);
            
            return [
                'success' => true,
                'city' => $cityName,
                'found' => count($restaurants),
                'saved' => $saved,
                'restaurants' => $restaurants
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Google Places API Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Получение координат города
     */
    private function getCityCoordinates($cityName)
    {
        $url = "https://maps.googleapis.com/maps/api/geocode/json";
        $params = [
            'address' => $cityName,
            'key' => $this->apiKey
        ];
        
        $response = $this->makeApiRequest($url, $params);
        
        if (isset($response['results'][0]['geometry']['location'])) {
            return $response['results'][0]['geometry']['location'];
        }
        
        return null;
    }
    
    /**
     * Поиск ресторанов через Places API
     */
    private function findRestaurants($coordinates, $radius)
    {
        $restaurants = [];
        
        // Различные поисковые запросы для грузинской кухни
        $searchQueries = [
            'Georgian restaurant',
            'Georgian food',
            'Khachapuri',
            'Georgian cuisine',
            'Tbilisi restaurant'
        ];
        
        foreach ($searchQueries as $query) {
            $results = $this->searchPlaces($coordinates, $query, $radius);
            $restaurants = array_merge($restaurants, $results);
        }
        
        // Убираем дубликаты по place_id
        $unique = [];
        foreach ($restaurants as $restaurant) {
            $unique[$restaurant['place_id']] = $restaurant;
        }
        
        return array_values($unique);
    }
    
    /**
     * Поиск мест через Places API
     */
    private function searchPlaces($coordinates, $query, $radius)
    {
        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json";
        $params = [
            'query' => $query,
            'location' => $coordinates['lat'] . ',' . $coordinates['lng'],
            'radius' => $radius,
            'type' => 'restaurant',
            'key' => $this->apiKey
        ];
        
        $response = $this->makeApiRequest($url, $params);
        $places = [];
        
        if (isset($response['results'])) {
            foreach ($response['results'] as $place) {
                // Проверяем что это действительно грузинский ресторан
                if ($this->isGeorgianRestaurant($place)) {
                    $details = $this->getPlaceDetails($place['place_id']);
                    if ($details) {
                        $places[] = array_merge($place, $details);
                    }
                }
            }
        }
        
        return $places;
    }
    
    /**
     * Получение детальной информации о месте
     */
    private function getPlaceDetails($placeId)
    {
        $url = "https://maps.googleapis.com/maps/api/place/details/json";
        $params = [
            'place_id' => $placeId,
            'fields' => 'name,rating,price_level,formatted_address,formatted_phone_number,website,opening_hours,reviews',
            'key' => $this->apiKey
        ];
        
        $response = $this->makeApiRequest($url, $params);
        
        return $response['result'] ?? null;
    }
    
    /**
     * Проверка что это грузинский ресторан
     */
    private function isGeorgianRestaurant($place)
    {
        $georgianKeywords = [
            'georgian', 'georgia', 'tbilisi', 'khachapuri', 'khinkali', 
            'mtsvadi', 'lobio', 'caucasian', 'საქართველო'
        ];
        
        $text = strtolower($place['name'] . ' ' . ($place['types'] ?? ''));
        
        foreach ($georgianKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Сохранение ресторанов в базу
     */
    private function saveRestaurants($restaurants, $cityName)
    {
        $saved = 0;
        
        // Находим или создаем город
        $city = $this->cityModel->where('name', $cityName)->first();
        if (!$city) {
            $cityData = [
                'name' => $cityName,
                'slug' => url_title($cityName, '-', true),
                'country' => 'USA' // По умолчанию, можно определять автоматически
            ];
            $cityId = $this->cityModel->insert($cityData);
        } else {
            $cityId = $city['id'];
        }
        
        foreach ($restaurants as $restaurant) {
            // Проверяем что ресторан еще не существует
            $existing = $this->restaurantModel
                ->where('google_place_id', $restaurant['place_id'])
                ->first();
                
            if (!$existing) {
                $data = [
                    'name' => $restaurant['name'],
                    'slug' => url_title($restaurant['name'], '-', true),
                    'city_id' => $cityId,
                    'address' => $restaurant['formatted_address'] ?? '',
                    'phone' => $restaurant['formatted_phone_number'] ?? '',
                    'website' => $restaurant['website'] ?? '',
                    'google_place_id' => $restaurant['place_id'],
                    'rating' => $restaurant['rating'] ?? 0,
                    'price_level' => $restaurant['price_level'] ?? 0,
                    'description' => $this->generateDescription($restaurant),
                    'is_active' => 1
                ];
                
                // Генерируем SEO URL
                $data['seo_url'] = $data['slug'] . '-restaurant-' . url_title($cityName, '-', true);
                
                if ($this->restaurantModel->insert($data)) {
                    $saved++;
                }
            }
        }
        
        return $saved;
    }
    
    /**
     * Генерация описания ресторана
     */
    private function generateDescription($restaurant)
    {
        $name = $restaurant['name'];
        $address = $restaurant['formatted_address'] ?? '';
        
        // Базовое описание
        $description = "Authentic Georgian restaurant {$name}";
        
        if (!empty($address)) {
            $description .= " located at {$address}";
        }
        
        $description .= ". Serving traditional Georgian dishes like khachapuri, khinkali, and other delicious Georgian cuisine.";
        
        // Добавляем информацию из отзывов если есть
        if (isset($restaurant['reviews']) && !empty($restaurant['reviews'])) {
            $review = $restaurant['reviews'][0]['text'] ?? '';
            if (strlen($review) > 50) {
                $description .= " " . substr($review, 0, 200) . "...";
            }
        }
        
        return $description;
    }
    
    /**
     * Выполнение API запроса
     */
    private function makeApiRequest($url, $params)
    {
        $fullUrl = $url . '?' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("API request failed with code: {$httpCode}");
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['status']) && $data['status'] !== 'OK') {
            throw new Exception("API error: " . $data['status']);
        }
        
        return $data;
    }
}