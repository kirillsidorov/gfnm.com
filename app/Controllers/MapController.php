<?php
// Файл: app/Controllers/MapController.php - ИСПРАВЛЕННАЯ ВЕРСИЯ

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\CityModel;

class MapController extends BaseController
{
    protected $restaurantModel;
    protected $cityModel;

    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
    }

    /**
     * Georgian Restaurant Near Me - с геолокацией и поиском
     */
    public function nearMe()
    {
        // Получаем параметры из URL
        $request = $this->request;
        $searchQuery = $request->getGet('q');
        $userLat = $request->getGet('lat');
        $userLng = $request->getGet('lng');
        $radius = $request->getGet('radius') ?? 25;

        $data = [
            'title' => 'Georgian Restaurant Near Me - Find Authentic Georgian Food Nearby',
            'meta_description' => 'Find Georgian restaurants near your location. Interactive map with real-time search for khachapuri, khinkali, and authentic Georgian cuisine.',
            'canonical_url' => base_url('georgian-restaurant-near-me'),
            'searchQuery' => $searchQuery,
            'userLocation' => [
                'lat' => $userLat,
                'lng' => $userLng
            ],
            'defaultRadius' => $radius,
            'isNearMePage' => true,
            'google_maps_key' => env('GOOGLE_MAPS_API_KEY') // ДОБАВЛЕНО: ключ для карт
        ];

        // Если есть координаты пользователя, найдем ближайшие рестораны
        if ($userLat && $userLng) {
            $nearbyRestaurants = $this->findNearbyRestaurants($userLat, $userLng, $radius, $searchQuery);
            $data['nearbyRestaurants'] = $nearbyRestaurants;
            $data['hasResults'] = !empty($nearbyRestaurants);
        }

        return view('map/near_me', $data);
    }

    /**
     * Карта всех ресторанов
     */
    public function index()
    {
        // Получаем все города для селектора
        $cities = $this->cityModel->findAll();
        
        // Получаем рестораны из нашей базы для начального отображения
        $restaurants = $this->restaurantModel->getAllActive(50);

        $data = [
            'title' => 'Georgian Restaurants Map - Find Georgian Food Near You',
            'meta_description' => 'Interactive map of Georgian restaurants. Find authentic Georgian cuisine, khachapuri, khinkali near your location.',
            'cities' => $cities,
            'restaurants' => $restaurants,
            'google_maps_key' => env('GOOGLE_MAPS_API_KEY')
        ];

        return view('map/index', $data);
    }

    /**
     * API для поиска ближайших ресторанов - КАК В РАБОЧЕМ index.php
     */
    public function searchNearby()
    {
        $request = $this->request;
        $lat = $request->getPost('latitude');
        $lng = $request->getPost('longitude');
        $radius = $request->getPost('radius') ?? 50;
        $query = $request->getPost('query');

        if (!$lat || !$lng) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Location coordinates required'
            ]);
        }

        $results = $this->findNearbyRestaurants($lat, $lng, $radius, $query);

        return $this->response->setJSON([
            'success' => true,
            'restaurants' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * API для поиска по запросу в конкретном городе
     */
    public function searchLocal()
    {
        $request = $this->request;
        $query = $request->getPost('query');
        $cityId = $request->getPost('city_id');
        $lat = $request->getPost('lat');
        $lng = $request->getPost('lng');
        $radius = $request->getPost('radius') ?? 25;

        if ($lat && $lng) {
            // Если есть координаты, используем поиск по близости
            $results = $this->findNearbyRestaurants($lat, $lng, $radius, $query);
        } else {
            // Иначе поиск по городу и запросу
            $builder = $this->restaurantModel
                ->select('restaurants.*, cities.name as city_name')
                ->join('cities', 'cities.id = restaurants.city_id', 'left')
                ->where('restaurants.is_active', 1);

            if ($query) {
                $builder->groupStart()
                    ->like('restaurants.name', $query)
                    ->orLike('restaurants.description', $query)
                    ->groupEnd();
            }

            if ($cityId) {
                $builder->where('restaurants.city_id', $cityId);
            }

            $results = $builder->findAll();
        }

        return $this->response->setJSON([
            'success' => true,
            'restaurants' => $results,
            'total' => count($results)
        ]);
    }

    /**
     * API для получения данных карты
     */
    public function getMapData()
    {
        $request = $this->request;
        $cityId = $request->getGet('city_id');

        $builder = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name')
            ->join('cities', 'cities.id = restaurants.city_id', 'left')
            ->where('restaurants.is_active', 1)
            ->where('restaurants.latitude IS NOT NULL')
            ->where('restaurants.longitude IS NOT NULL');

        if ($cityId) {
            $builder->where('restaurants.city_id', $cityId);
        }

        $restaurants = $builder->findAll();

        return $this->response->setJSON([
            'success' => true,
            'restaurants' => $restaurants,
            'total' => count($restaurants)
        ]);
    }

    /**
     * УЛУЧШЕННЫЙ: Поиск ближайших ресторанов по координатам
     */
    private function findNearbyRestaurants($lat, $lng, $radius = 25, $searchQuery = null)
    {
        // Валидация входных данных
        $lat = floatval($lat);
        $lng = floatval($lng);
        $radius = floatval($radius);

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            throw new \InvalidArgumentException('Invalid coordinates');
        }

        if ($radius <= 0 || $radius > 500) {
            $radius = 25; // Значение по умолчанию
        }

        // SQL запрос с расчетом расстояния
        $sql = "SELECT restaurants.*, cities.name as city_name,
                (6371 * acos(cos(radians(?)) 
                * cos(radians(restaurants.latitude)) 
                * cos(radians(restaurants.longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(restaurants.latitude)))) AS distance 
                FROM restaurants 
                LEFT JOIN cities ON cities.id = restaurants.city_id
                WHERE restaurants.is_active = 1 
                AND restaurants.latitude IS NOT NULL 
                AND restaurants.longitude IS NOT NULL";

        $params = [$lat, $lng, $lat];

        // Добавляем поиск по названию/описанию если есть запрос
        if ($searchQuery && !empty(trim($searchQuery))) {
            $sql .= " AND (restaurants.name LIKE ? OR restaurants.description LIKE ? OR restaurants.address LIKE ?)";
            $searchTerm = "%{$searchQuery}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " HAVING distance < ? ORDER BY distance LIMIT 50";
        $params[] = $radius;

        $db = \Config\Database::connect();
        $results = $db->query($sql, $params)->getResultArray();

        // Добавляем дополнительные данные
        foreach ($results as &$restaurant) {
            $restaurant['distance'] = round($restaurant['distance'], 1);
            
            // Если нет рейтинга, устанавливаем значение по умолчанию
            if (!isset($restaurant['rating']) || $restaurant['rating'] == 0) {
                $restaurant['rating'] = 4.0;
            }
            
            // Если нет уровня цен, устанавливаем значение по умолчанию
            if (!isset($restaurant['price_level']) || $restaurant['price_level'] == 0) {
                $restaurant['price_level'] = 2;
            }
        }

        return $results;
    }
}