<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\CityModel;
use App\Models\RestaurantPhotoModel; // ДОБАВЛЕНО

class Home extends BaseController
{
    protected $restaurantModel;
    protected $cityModel;
    protected $photoModel; // ДОБАВЛЕНО

    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
        $this->photoModel = new RestaurantPhotoModel(); // ДОБАВЛЕНО
        
        // Загружаем хелпер text для функции character_limiter
        helper('text');
    }

    public function index()
    {
        // Получаем топ рестораны для главной страницы
        $topRestaurants = $this->restaurantModel->getTopRated(6);
        
        // ДОБАВЛЕНО: Получаем фотографии для каждого ресторана
        foreach ($topRestaurants as &$restaurant) {
            $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
        }
        unset($restaurant); // Очищаем ссылку после цикла
        
        // Получаем все города для поиска
        $cities = $this->cityModel->findAll();
        
        // Статистика для главной страницы
        $stats = [
            'total_restaurants' => $this->restaurantModel->where('is_active', 1)->countAllResults(),
            'total_cities' => $this->cityModel->countAllResults()
        ];

       // В контроллере Home::index() измените title:
        $data = [
            'title' => 'Georgian Food Near Me - Find Authentic Georgian Restaurants',
            'meta_description' => 'Find Georgian food near you. Discover authentic Georgian restaurants, khachapuri, khinkali and traditional dishes in your area.',
            'topRestaurants' => $topRestaurants,
            'cities' => $cities,
            'stats' => $stats
        ];

        return view('home/index', $data);
    }

    public function search()
    {
        helper('text');
        
        $request = $this->request;
        
        // Получаем параметры поиска
        $searchQuery = $request->getGet('q');
        $cityId = $request->getGet('city');
        $location = $request->getGet('location');
        
        if (empty($searchQuery) && empty($location)) {
            return redirect()->to('/')->with('error', 'Please enter search term');
        }

        // Если ищем по местоположению или городу
        if ($location || $searchQuery) {
            $query = $location ?: $searchQuery;
            
            // Проверяем, не ищет ли пользователь конкретный город
            $cityMatch = $this->cityModel
                ->where('LOWER(name)', strtolower($query))
                ->orLike('name', $query)
                ->first();
            
            if ($cityMatch) {
                // Редиректим на страницу города
                return redirect()->to(base_url('georgian-restaurants-' . $cityMatch['slug']));
            }
            
            // Проверяем популярные поисковые запросы
            $popularSearchRedirects = [
                'nyc' => 'georgian-restaurant-nyc',
                'new york' => 'georgian-restaurant-nyc', 
                'manhattan' => 'georgian-restaurant-manhattan',
                'brooklyn' => 'georgian-restaurant-brooklyn',
                'chicago' => 'georgian-restaurant-chicago',
                'washington dc' => 'georgian-restaurant-washington-dc',
                'dc' => 'georgian-restaurant-washington-dc'
            ];
            
            $lowerQuery = strtolower($query);
            if (isset($popularSearchRedirects[$lowerQuery])) {
                return redirect()->to(base_url($popularSearchRedirects[$lowerQuery]));
            }
        }

        // Выполняем обычный поиск по ресторанам
        $restaurants = $this->restaurantModel->search($searchQuery, $cityId);
        
        // ДОБАВЛЕНО: Получаем фотографии для найденных ресторанов
        foreach ($restaurants as &$restaurant) {
            $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
        }
        unset($restaurant); // Очищаем ссылку после цикла
        
        // Получаем информацию о выбранном городе
        $selectedCity = null;
        if ($cityId) {
            $selectedCity = $this->cityModel->find($cityId);
        }

        // Получаем все города для фильтра
        $cities = $this->cityModel->findAll();

        // Если результатов мало, предлагаем альтернативы
        $suggestions = [];
        if (count($restaurants) < 3) {
            // Ищем похожие города
            if ($searchQuery) {
                $similarCities = $this->cityModel
                    ->like('name', $searchQuery)
                    ->limit(5)
                    ->findAll();
                
                foreach ($similarCities as $city) {
                    $suggestions[] = [
                        'type' => 'city',
                        'name' => $city['name'],
                        'url' => base_url($city['seo_url'])
                    ];
                }
            }
        }

        $data = [
            'title' => 'Search Results for "' . ($searchQuery ?: $location) . '" - Georgian Food Near Me',
            'meta_description' => "Search results for '{$searchQuery}' Georgian restaurants",
            'restaurants' => $restaurants,
            'searchQuery' => $searchQuery ?: $location,
            'selectedCity' => $selectedCity,
            'cities' => $cities,
            'totalFound' => count($restaurants),
            'suggestions' => $suggestions
        ];

        return view('restaurants/search_results', $data);
    }
    
    public function searchSuggestions()
    {
        $query = $this->request->getGet('q');
        if (strlen($query) < 2) {
            return $this->response->setJSON(['suggestions' => []]);
        }
    
        $suggestions = [];
    
        // Поиск городов
        $cities = $this->cityModel
            ->select('id, name, state, slug, seo_url')
            ->like('name', $query)
            ->limit(3)
            ->findAll();
        
        foreach ($cities as $city) {
            $suggestions[] = [
                'type' => 'city',
                'name' => $city['name'],
                'location' => $city['state'] ? $city['name'] . ', ' . $city['state'] : $city['name'],
                'url' => $city['seo_url'] 
                
            ];
        }
        
        // Поиск ресторанов С SEO_URL
        $restaurants = $this->restaurantModel
            ->select('restaurants.id, restaurants.name, restaurants.slug, restaurants.seo_url, cities.name as city_name, cities.slug as city_slug')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->like('restaurants.name', $query)
            ->where('restaurants.is_active', 1)
            ->limit(4)
            ->findAll();
        
        foreach ($restaurants as $restaurant) {
            // Используем seo_url если есть, иначе генерируем
            $restaurantUrl = !empty($restaurant['seo_url']) 
                ? $restaurant['seo_url'] 
                : $restaurant['slug'] . '-restaurant-' . $restaurant['city_slug'];
                
            $suggestions[] = [
                'type' => 'restaurant',
                'name' => $restaurant['name'],
                'location' => $restaurant['city_name'],
                'url' => $restaurantUrl // Убрали base_url отсюда
            ];
        }
        
        // Поиск блюд
        $dishes = [
            ['name' => 'Khachapuri', 'description' => 'Traditional cheese-filled bread'],
            ['name' => 'Khinkali', 'description' => 'Georgian dumplings'],
            ['name' => 'Mtsvadi', 'description' => 'Grilled meat skewers'],
            ['name' => 'Lobio', 'description' => 'Bean stew']
        ];
        
        foreach ($dishes as $dish) {
            if (stripos($dish['name'], $query) !== false) {
                $suggestions[] = [
                    'type' => 'dish',
                    'name' => $dish['name'],
                    'location' => $dish['description'],
                    'url' => 'search?q=' . urlencode($dish['name'])
                ];
            }
        }
    
        return $this->response->setJSON(['suggestions' => $suggestions]);
    }
}