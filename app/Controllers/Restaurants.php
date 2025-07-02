<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\CityModel;
use App\Models\RestaurantPhotoModel;
use App\Services\GooglePhotoService;

class Restaurants extends BaseController
{
    protected $restaurantModel;
    protected $cityModel;
    protected $photoModel;
    protected $googlePhotoService;
    protected $cacheEnabled; // НОВОЕ: Флаг для управления кешированием

    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
        $this->googlePhotoService = new GooglePhotoService();
        $this->photoModel = new RestaurantPhotoModel();
        
        // НОВОЕ: Управление кешированием через .env
        $this->cacheEnabled = env('ENABLE_CACHE', false); // По умолчанию выключено
        
        // Загружаем хелпер text для функции character_limiter
        helper('text');
    }

    /**
     * НОВЫЙ метод для работы с кешем
     */
    private function getFromCache($key)
    {
        if (!$this->cacheEnabled) {
            return null;
        }
        return cache($key);
    }

    private function saveToCache($key, $data, $ttl = 3600)
    {
        if (!$this->cacheEnabled) {
            return false;
        }
        return cache()->save($key, $data, $ttl);
    }

    public function newYorkCity()
    {
        // Получаем параметры фильтрации
        $request = $this->request;
        $priceLevel = $request->getGet('price');
        $sortBy = $request->getGet('sort') ?? 'rating';
        $boroughFilter = $request->getGet('borough');

        // Координаты центра NYC для поиска по радиусу
        $nycCoordinates = [
            'latitude' => 40.7128,
            'longitude' => -74.0060
        ];

        // Создаем "виртуальный" город NYC
        $nycCity = [
            'id' => 'nyc',
            'name' => 'New York City',
            'state' => 'NY',
            'slug' => 'nyc',
            'latitude' => $nycCoordinates['latitude'],
            'longitude' => $nycCoordinates['longitude']
        ];

        // Каскадный поиск ресторанов
        $searchResult = $this->findRestaurantsCascadeNYC($nycCity, $priceLevel, $sortBy, $boroughFilter);

        $data = [
            'title' => 'Georgian Food in New York City - Georgian Food Near Me',
            'meta_description' => 'Find the best Georgian restaurants in New York City. Manhattan, Brooklyn, Queens and other NYC locations with authentic Georgian cuisine.',
            'city' => $nycCity,
            'restaurants' => $searchResult['restaurants'],
            'totalRestaurants' => count($searchResult['restaurants']),
            'selectedPrice' => $priceLevel,
            'selectedSort' => $sortBy,
            'selectedBorough' => $boroughFilter,
            'isNYC' => true,
            'search_info' => $searchResult['search_info']
        ];

        return view('restaurants/by_city', $data);
    }
/**
     * Каскадный поиск ресторанов для NYC
     */
    private function findRestaurantsCascadeNYC($nycCity, $priceLevel = null, $sortBy = 'rating', $boroughFilter = null)
    {
    // Шаг 1: Поиск в конкретных районах NYC (Brooklyn, Manhattan)
    $builder = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                   ->join('cities', 'cities.id = restaurants.city_id')
                                   ->whereIn('restaurants.city_id', [4, 7]) // Brooklyn и Manhattan
                                   ->where('restaurants.is_active', 1);

    if ($boroughFilter) {
        $builder->where('restaurants.city_id', $boroughFilter);
    }

    if ($priceLevel) {
        $builder->where('restaurants.price_level', $priceLevel);
    }

    $this->applySorting($builder, $sortBy);
    $restaurants = $builder->findAll();

    // Добавляем фотографии
    foreach ($restaurants as &$restaurant) {
        $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
    }
    unset($restaurant);

    if (!empty($restaurants)) {
        return [
            'restaurants' => $restaurants,
            'search_info' => [
                'method' => 'exact_boroughs',
                'message' => 'Georgian restaurants in NYC boroughs',
                'count' => count($restaurants)
            ]
        ];
    }

    // Шаг 2: Поиск в радиусе 30км от центра NYC
    $restaurants = $this->searchByRadiusWithFilters($nycCity, 30, $priceLevel, $sortBy);

    if (!empty($restaurants)) {
        return [
            'restaurants' => $restaurants,
            'search_info' => [
                'method' => 'radius_30',
                'message' => 'Georgian restaurants within 30km of NYC',
                'count' => count($restaurants)
            ]
        ];
    }

    // Шаг 3: Поиск по всему штату NY
    $restaurants = $this->searchByStateWithFilters('NY', $priceLevel, $sortBy);

    if (!empty($restaurants)) {
        return [
            'restaurants' => $restaurants,
            'search_info' => [
                'method' => 'state_ny',
                'message' => 'Georgian restaurants in New York State',
                'count' => count($restaurants)
            ]
        ];
    }

    // Шаг 4: Расширенный поиск в радиусе 100км
    $restaurants = $this->searchByRadiusWithFilters($nycCity, 100, $priceLevel, $sortBy);

    return [
        'restaurants' => $restaurants,
        'search_info' => [
            'method' => 'radius_100',
            'message' => !empty($restaurants) 
                ? 'Georgian restaurants within 100km of NYC' 
                : 'No Georgian restaurants found near NYC',
            'count' => count($restaurants)
        ]
    ];
}

/**
     * ИСПРАВЛЕННЫЙ поиск по радиусу с фильтрами
     */
    private function searchByRadiusWithFilters($city, $radiusKm, $priceLevel = null, $sortBy = 'rating')
    {
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

        $params = [$city['latitude'], $city['longitude'], $city['latitude']];

        if ($priceLevel) {
            $sql .= " AND restaurants.price_level = ?";
            $params[] = $priceLevel;
        }

        $sql .= " HAVING distance < ?";
        $params[] = $radiusKm;

        // Сортировка
        switch ($sortBy) {
            case 'name':
                $sql .= " ORDER BY restaurants.name ASC";
                break;
            case 'rating':
                $sql .= " ORDER BY restaurants.rating DESC";
                break;
            case 'price_low':
                $sql .= " ORDER BY restaurants.price_level ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY restaurants.price_level DESC";
                break;
            case 'distance':
                $sql .= " ORDER BY distance ASC";
                break;
            default:
                $sql .= " ORDER BY distance ASC";
        }

        $sql .= " LIMIT 50";

        // ИСПРАВЛЕНО: используем $this->db() вместо $this->db
        $db = \Config\Database::connect();
        $results = $db->query($sql, $params)->getResultArray();

        // Добавляем фотографии
        foreach ($results as &$restaurant) {
            $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
        }
        unset($restaurant);

        return $results;
    }

    /**
     * ИСПРАВЛЕННЫЙ поиск по штату с фильтрами
     */
    private function searchByStateWithFilters($state, $priceLevel = null, $sortBy = 'rating')
    {
        $builder = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                    ->join('cities', 'cities.id = restaurants.city_id')
                                    ->where('cities.state', $state)
                                    ->where('restaurants.is_active', 1);

        if ($priceLevel) {
            $builder->where('restaurants.price_level', $priceLevel);
        }

        $this->applySorting($builder, $sortBy);
        
        $restaurants = $builder->limit(50)->findAll();

        // Добавляем фотографии
        foreach ($restaurants as &$restaurant) {
            $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
        }
        unset($restaurant);

        return $restaurants;
    }

    /**
     * ИСПРАВЛЕННЫЙ метод byCitySlugNew БЕЗ ЦЕНЫ
     */
    public function byCitySlugNew($citySlug)
    {
        set_time_limit(60);
        ini_set('max_execution_time', 60);
        
        $citySlug = str_replace('georgian-restaurants-', '', $citySlug);
        
        if (in_array($citySlug, ['new-york-city', 'nyc'])) {
            return $this->newYorkCity();
        }
        
        $city = $this->cityModel->where('slug', $citySlug)->first();
        
        if (!$city) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("City not found: {$citySlug}");
        }
        
        $request = $this->request;
        $sortBy = $request->getGet('sort') ?? 'rating'; // УБРАЛИ priceLevel

        $cacheKey = "restaurants_city_{$city['slug']}_" . md5($sortBy); // УПРОСТИЛИ ключ кеша
        $searchResult = $this->getFromCache($cacheKey);
        
        if (!$searchResult) {
            try {
                $searchResult = $this->findRestaurantsForCity($city, $sortBy); // УБРАЛИ priceLevel
                
                if (!$this->cacheEnabled) {
                    $searchResult['cache_info'] = 'Cache disabled';
                } else {
                    $searchResult['cache_info'] = 'Cached for 1 hour';
                }
                
            } catch (\Exception $e) {
                log_message('error', 'Restaurant search failed for city ' . $citySlug . ': ' . $e->getMessage());
                
                $searchResult = [
                    'restaurants' => [],
                    'search_info' => [
                        'method' => 'error',
                        'message' => "Unable to load restaurants for {$city['name']} at this time",
                        'count' => 0
                    ],
                    'error_details' => ENVIRONMENT === 'development' ? $e->getMessage() : null
                ];
            }
            
            $this->saveToCache($cacheKey, $searchResult, 3600);
        } else {
            $searchResult['cache_info'] = 'From cache';
        }

        return $this->renderCityPage($city, $searchResult, $sortBy); // УБРАЛИ priceLevel
    }
/**
     * УПРОЩЕННЫЙ каскадный поиск БЕЗ ЦЕНЫ
     */
    private function findRestaurantsForCity($city, $sortBy = 'rating')
    {
        // ШАГ 1: Прямой поиск в городе
        $restaurants = $this->getRestaurantsByCity($city['id'], $sortBy);
        
        if (!empty($restaurants)) {
            return [
                'restaurants' => $restaurants,
                'search_info' => [
                    'method' => 'exact_city',
                    'message' => "Georgian restaurants in {$city['name']}",
                    'count' => count($restaurants)
                ]
            ];
        }
        
        // ШАГ 2: Поиск в радиусе 30км
        if (!empty($city['latitude']) && !empty($city['longitude'])) {
            $restaurants = $this->getRestaurantsByRadius($city, 30, $sortBy);
            
            if (!empty($restaurants)) {
                return [
                    'restaurants' => $restaurants,
                    'search_info' => [
                        'method' => 'radius_30',
                        'message' => "Georgian restaurants within 30km of {$city['name']}",
                        'count' => count($restaurants)
                    ]
                ];
            }
        }
        
        // ШАГ 3: Поиск по штату
        if (!empty($city['state'])) {
            $restaurants = $this->getRestaurantsByState($city['state'], $sortBy);
            
            if (!empty($restaurants)) {
                return [
                    'restaurants' => $restaurants,
                    'search_info' => [
                        'method' => 'state',
                        'message' => "Georgian restaurants in {$city['state']} state",
                        'count' => count($restaurants)
                    ]
                ];
            }
        }
        
        return [
            'restaurants' => [],
            'search_info' => [
                'method' => 'none',
                'message' => "No Georgian restaurants found near {$city['name']}",
                'count' => 0
            ]
        ];
    }

    /**
     * УПРОЩЕННЫЙ поиск по городу БЕЗ ЦЕНЫ
     */
    private function getRestaurantsByCity($cityId, $sortBy = 'rating', $limit = 50)
    {
        if (ENVIRONMENT === 'development') {
            log_message('debug', "Searching restaurants for city ID: {$cityId}, sort: {$sortBy}");
        }
        
        $builder = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('restaurants.city_id', $cityId)
            ->where('restaurants.is_active', 1)
            ->where('(restaurants.is_georgian IS NULL OR restaurants.is_georgian = 1)'); // УПРОСТИЛИ фильтр

        $this->applySorting($builder, $sortBy);
        $restaurants = $builder->limit($limit)->findAll();

        if (ENVIRONMENT === 'development') {
            log_message('debug', "Found " . count($restaurants) . " restaurants for city ID: {$cityId}");
            
            if (empty($restaurants)) {
                $totalInCity = $this->restaurantModel->where('city_id', $cityId)->countAllResults();
                $activeInCity = $this->restaurantModel->where('city_id', $cityId)->where('is_active', 1)->countAllResults();
                
                log_message('debug', "Total restaurants in city: {$totalInCity}, Active: {$activeInCity}");
            }
        }

        // Добавляем фотографии
        foreach ($restaurants as &$restaurant) {
            $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
        }
        unset($restaurant);

        return $restaurants;
    }

    /**
     * УПРОЩЕННЫЙ поиск в радиусе БЕЗ ЦЕНЫ
     */
    private function getRestaurantsByRadius($city, $radiusKm, $sortBy = 'rating', $limit = 20)
    {
        $sql = "SELECT r.*, c.name as city_name, c.slug as city_slug,
                       ROUND((6371 * acos(cos(radians(?)) * cos(radians(r.latitude)) * 
                       cos(radians(r.longitude) - radians(?)) + sin(radians(?)) * 
                       sin(radians(r.latitude)))), 2) AS distance
                FROM restaurants r
                LEFT JOIN cities c ON r.city_id = c.id
                WHERE r.is_active = 1
                AND r.latitude IS NOT NULL 
                AND r.longitude IS NOT NULL
                AND (r.is_georgian IS NULL OR r.is_georgian = 1)
                HAVING distance <= ?
                ORDER BY r.rating DESC, distance ASC 
                LIMIT ?";
        
        $params = [$city['latitude'], $city['longitude'], $city['latitude'], $radiusKm, $limit];
        
        $db = \Config\Database::connect();
        $query = $db->query($sql, $params);
        $restaurants = $query->getResultArray();
        
        // Добавляем фотографии
        foreach ($restaurants as &$restaurant) {
            $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
        }
        unset($restaurant);
        
        return $restaurants;
    }

    /**
     * УПРОЩЕННЫЙ поиск по штату БЕЗ ЦЕНЫ
     */
    private function getRestaurantsByState($state, $sortBy = 'rating', $limit = 15)
    {
        $builder = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('cities.state', $state)
            ->where('restaurants.is_active', 1)
            ->where('(restaurants.is_georgian IS NULL OR restaurants.is_georgian = 1)');

        $this->applySorting($builder, $sortBy);
        $restaurants = $builder->limit($limit)->findAll();

        // Добавляем фотографии
        foreach ($restaurants as &$restaurant) {
            $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
        }
        unset($restaurant);

        return $restaurants;
    }

    /**
     * УПРОЩЕННАЯ сортировка БЕЗ ЦЕНЫ
     */
    private function applySorting($builder, $sortBy)
    {
        switch ($sortBy) {
            case 'name':
                $builder->orderBy('restaurants.name', 'ASC');
                break;
            case 'rating':
                $builder->orderBy('restaurants.rating', 'DESC');
                break;
            case 'distance': // Для радиусного поиска
                // Сортировка по дистанции будет в SQL
                break;
            default:
                $builder->orderBy('restaurants.rating', 'DESC');
        }
    }

    /**
     * УПРОЩЕННЫЙ рендеринг страницы БЕЗ ЦЕНЫ
     */
    private function renderCityPage($city, $searchResult, $sortBy)
    {
        $data = [
            'title' => 'Georgian Restaurants in ' . $city['name'] . ' - Find Authentic Georgian Food',
            'meta_description' => 'Find the best Georgian restaurants in ' . $city['name'] . '. Authentic khachapuri, khinkali and traditional Georgian cuisine.',
            'city' => $city,
            'restaurants' => $searchResult['restaurants'],
            'totalRestaurants' => count($searchResult['restaurants']),
            'selectedSort' => $sortBy, // УБРАЛИ selectedPrice
            'canonical_url' => base_url('georgian-restaurants-' . $city['slug']),
            'isNewUrlStructure' => true,
            'search_info' => $searchResult['search_info'],
            'cache_enabled' => $this->cacheEnabled,
            'cache_info' => $searchResult['cache_info'] ?? null
        ];
        
        if (ENVIRONMENT === 'development') {
            $data['debug_info'] = [
                'city_id' => $city['id'],
                'cache_enabled' => $this->cacheEnabled,
                'search_method' => $searchResult['search_info']['method'] ?? 'unknown',
                'error_details' => $searchResult['error_details'] ?? null
            ];
        }

        return view('restaurants/by_city', $data);
    }

    // ОТЛАДОЧНЫЕ МЕТОДЫ БЕЗ ЦЕНЫ
    public function debug($citySlug = null)
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        $citySlug = $citySlug ?? 'manhattan';
        $city = $this->cityModel->where('slug', $citySlug)->first();
        
        $debugInfo = [
            'requested_slug' => $citySlug,
            'cache_enabled' => $this->cacheEnabled,
            'environment' => ENVIRONMENT,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if (!$city) {
            $debugInfo['error'] = 'City not found';
            $debugInfo['available_cities'] = $this->cityModel->select('slug, name')->findAll();
        } else {
            $debugInfo['city'] = $city;
            
            // Прямой поиск по городу
            $directSearch = $this->getRestaurantsByCity($city['id']);
            $debugInfo['searches']['direct_city'] = [
                'count' => count($directSearch),
                'restaurants' => array_slice($directSearch, 0, 3)
            ];
            
            // Статистика города
            $totalInCity = $this->restaurantModel->where('city_id', $city['id'])->countAllResults();
            $activeInCity = $this->restaurantModel->where('city_id', $city['id'])->where('is_active', 1)->countAllResults();
            
            $debugInfo['city_stats'] = [
                'total_restaurants' => $totalInCity,
                'active_restaurants' => $activeInCity
            ];
            
            // Статистика грузинских ресторанов
            $georgianStats = [
                'is_georgian_1' => $this->restaurantModel->where('city_id', $city['id'])->where('is_georgian', 1)->countAllResults(),
                'is_georgian_null' => $this->restaurantModel->where('city_id', $city['id'])->where('is_georgian IS NULL')->countAllResults(),
                'is_georgian_0' => $this->restaurantModel->where('city_id', $city['id'])->where('is_georgian', 0)->countAllResults()
            ];
            
            $debugInfo['georgian_stats'] = $georgianStats;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function debugAll()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        $cities = $this->cityModel->findAll();
        $results = [];
        
        foreach ($cities as $city) {
            $restaurantCount = $this->restaurantModel
                ->where('city_id', $city['id'])
                ->where('is_active', 1)
                ->where('(is_georgian IS NULL OR is_georgian = 1)')
                ->countAllResults();
                
            if ($restaurantCount > 0) {
                $results[] = [
                    'city' => $city['name'],
                    'slug' => $city['slug'],
                    'restaurant_count' => $restaurantCount,
                    'test_url' => base_url('georgian-restaurants-' . $city['slug'])
                ];
            }
        }
        
        usort($results, function($a, $b) {
            return $b['restaurant_count'] <=> $a['restaurant_count'];
        });
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'total_cities_with_restaurants' => count($results),
            'cache_enabled' => $this->cacheEnabled,
            'cities' => $results
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * НОВЫЙ метод для очистки кеша (только для админов)
     */
    public function clearCache()
    {
        // Проверяем права админа (добавьте свою логику проверки)
        if (!session()->get('is_admin')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        if (!$this->cacheEnabled) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cache is disabled'
            ]);
        }
        
        // Очищаем кеш ресторанов
        $cache = \Config\Services::cache();
        $cache->deleteMatching('restaurants_*');
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Restaurant cache cleared successfully'
        ]);
    }

    /**
     * НОВЫЙ метод для получения информации о кеше
     */
    public function getCacheInfo()
    {
        if (!session()->get('is_admin')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        return $this->response->setJSON([
            'cache_enabled' => $this->cacheEnabled,
            'cache_driver' => config('Cache')->default,
            'environment' => ENVIRONMENT
        ]);
    }

    // ВОССТАНОВЛЕННЫЙ МЕТОД BROWSE
    public function browse()
    {
        // Получаем параметры фильтрации
        $request = $this->request;
        $cityId = $request->getGet('city');
        $priceLevel = $request->getGet('price');
        $sortBy = $request->getGet('sort') ?? 'rating';
        $page = $request->getGet('page') ?? 1;
        $perPage = 12;

        // Получаем рестораны с фильтрацией
        $builder = $this->restaurantModel->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
                                    ->join('cities', 'cities.id = restaurants.city_id')
                                    ->where('restaurants.is_active', 1);

        if ($cityId) {
            $builder->where('restaurants.city_id', $cityId);
        }

        if ($priceLevel) {
            $builder->where('restaurants.price_level', $priceLevel);
        }

        // Сортировка
        switch ($sortBy) {
            case 'name':
                $builder->orderBy('restaurants.name', 'ASC');
                break;
            case 'rating':
                $builder->orderBy('restaurants.rating', 'DESC');
                break;
            case 'price_low':
                $builder->orderBy('restaurants.price_level', 'ASC');
                break;
            case 'price_high':
                $builder->orderBy('restaurants.price_level', 'DESC');
                break;
            default:
                $builder->orderBy('restaurants.rating', 'DESC');
        }

        // Пагинация
        $restaurants = $builder->paginate($perPage, 'default', $page);
        $pager = $this->restaurantModel->pager;

        // ДОБАВЛЕНО: Получаем фотографии для каждого ресторана
        foreach ($restaurants as &$restaurant) {
            $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
        }
        unset($restaurant);

        // Получаем города для фильтра
        $cities = $this->cityModel->getActiveCitiesWithRestaurants();
        
        // Выбранный город для фильтра
        $selectedCity = $cityId ? $this->cityModel->find($cityId) : null;

        // Статистика
        $totalRestaurants = $this->restaurantModel->where('is_active', 1)->countAllResults();
        $totalCities = $this->cityModel->countAllResults();

        // Получаем города сгруппированные по странам для дополнительной навигации
        $citiesQuery = $this->cityModel
            ->select('cities.id, cities.name, cities.state, cities.country, cities.slug, COUNT(restaurants.id) as restaurant_count')
            ->join('restaurants', 'restaurants.city_id = cities.id AND restaurants.is_active = 1', 'left')
            ->groupBy('cities.id, cities.name, cities.state, cities.country, cities.slug')
            ->having('restaurant_count >', 0)
            ->orderBy('cities.country, cities.state, cities.name')
            ->findAll();
        
        // Группируем по странам
        $countriesData = [];
        foreach ($citiesQuery as $city) {
            $country = $city['country'];
            
            if (!isset($countriesData[$country])) {
                $countriesData[$country] = [
                    'name' => $country,
                    'cities' => [],
                    'total_restaurants' => 0,
                    'total_cities' => 0
                ];
            }
            
            $countriesData[$country]['cities'][] = $city;
            $countriesData[$country]['total_restaurants'] += $city['restaurant_count'];
            $countriesData[$country]['total_cities']++;
        }

        // Сортируем страны по количеству ресторанов
        uasort($countriesData, function($a, $b) {
            return $b['total_restaurants'] <=> $a['total_restaurants'];
        });

        $data = [
            'title' => 'Browse All Georgian Restaurants - Georgian Food Directory',
            'meta_description' => 'Browse our complete directory of Georgian restaurants. Filter by city, price, and rating. Find authentic khachapuri, khinkali and traditional Georgian cuisine.',
            'canonical_url' => base_url('restaurants'),
            'restaurants' => $restaurants,
            'cities' => $cities,
            'countriesData' => $countriesData,
            'selectedCity' => $selectedCity,
            'selectedPrice' => $priceLevel,
            'selectedSort' => $sortBy,
            'pager' => $pager,
            'currentPage' => $page,
            'totalRestaurants' => $totalRestaurants,
            'totalCities' => $totalCities,
            'totalCountries' => count($countriesData),
            'showFilters' => true,
            'isBrowsePage' => true
        ];

        return view('restaurants/browse', $data);
    }
}