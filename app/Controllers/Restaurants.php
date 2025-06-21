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

    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
        $this->googlePhotoService = new GooglePhotoService();
        $this->photoModel = new RestaurantPhotoModel();
        // Загружаем хелпер text для функции character_limiter
        helper('text');
    }

    // Список всех ресторанов
    public function index()
    {
        $request = $this->request;
        
        // Параметры фильтрации
        $cityId = $request->getGet('city');
        $priceLevel = $request->getGet('price');
        $sortBy = $request->getGet('sort') ?? 'rating';
        $page = $request->getGet('page') ?? 1;
        $perPage = 12;

        // Получаем рестораны с фильтрацией
        $builder = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
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

        // Получаем города для фильтра
        $cities = $this->cityModel->findAll();
        
        // Выбранный город для фильтра
        $selectedCity = $cityId ? $this->cityModel->find($cityId) : null;

        $data = [
            'title' => 'All Georgian Restaurants - Georgian Food Near Me',
            'meta_description' => 'Browse all Georgian restaurants. Find authentic Georgian cuisine, khachapuri, khinkali and traditional dishes.',
            'restaurants' => $restaurants,
            'cities' => $cities,
            'selectedCity' => $selectedCity,
            'selectedPrice' => $priceLevel,
            'selectedSort' => $sortBy,
            'pager' => $pager,
            'currentPage' => $page
        ];

        return view('restaurants/index', $data);
    }

    /**
     * AJAX метод для получения фотографий из Google Places
     */
    public function getGooglePhotos($restaurantId)
    {
        if (!is_numeric($restaurantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Неверный ID ресторана'
            ]);
        }

        // Проверяем существование ресторана
        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ресторан не найден'
            ]);
        }

        $maxPhotos = $this->request->getGet('max_photos') ?? 5;
        $result = $this->googlePhotoService->importGooglePhotos($restaurantId, $maxPhotos);
        
        return $this->response->setJSON($result);
    }

    /**
     * AJAX метод для поиска и установки Place ID
     */
    public function findPlaceId($restaurantId)
    {
        if (!is_numeric($restaurantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Неверный ID ресторана'
            ]);
        }

        $result = $this->googlePhotoService->findAndSetPlaceId($restaurantId);
        
        return $this->response->setJSON($result);
    }
    /**
     * Детальная страница ресторана - ОБНОВЛЕННАЯ ВЕРСИЯ
     */
    public function view($id)
    {
        if (!is_numeric($id)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }

        $restaurant = $this->restaurantModel->getRestaurantWithCity($id);

        if (!$restaurant) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }

        // НОВОЕ: Получаем фотографии ресторана
        $photos = $this->photoModel->getRestaurantPhotos($restaurant['id']);
        $mainPhoto = $this->photoModel->getMainPhoto($restaurant['id']);
        $galleryPhotos = $this->photoModel->getGalleryPhotos($restaurant['id']);

        // Получаем похожие рестораны
        $similarRestaurants = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                                   ->join('cities', 'cities.id = restaurants.city_id')
                                                   ->where('restaurants.city_id', $restaurant['city_id'])
                                                   ->where('restaurants.id !=', $id)
                                                   ->where('restaurants.is_active', 1)
                                                   ->orderBy('restaurants.rating', 'DESC')
                                                   ->limit(4)
                                                   ->findAll();

        // НОВОЕ: Добавляем фото для похожих ресторанов
        foreach ($similarRestaurants as &$similar) {
            $similar['main_photo'] = $this->photoModel->getMainPhoto($similar['id']);
        }

        $data = [
            'title' => $restaurant['name'] . ' - Georgian Restaurant in ' . $restaurant['city_name'],
            'meta_description' => strip_tags($restaurant['description']) . ' - Authentic Georgian restaurant in ' . $restaurant['city_name'],
            'restaurant' => $restaurant,
            'photos' => $photos, // НОВОЕ: Все фото
            'mainPhoto' => $mainPhoto, // НОВОЕ: Главное фото
            'galleryPhotos' => $galleryPhotos, // НОВОЕ: Фото галереи
            'similarRestaurants' => $similarRestaurants
        ];

        return view('restaurants/view', $data);
    }

    public function detailByCity($restaurantSlug, $citySlug)
    {
        // Ищем город по slug
        $city = $this->cityModel->where('slug', $citySlug)->first();
        if (!$city) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("City not found: {$citySlug}");
        }
        
        // Ищем ресторан по slug в конкретном городе
        $restaurant = $this->restaurantModel->getRestaurantWithCityBySlug($restaurantSlug, $city['id']);
        
        if (!$restaurant) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Restaurant not found: {$restaurantSlug} in {$citySlug}");
        }

        // Получаем похожие рестораны (из того же города)
        $similarRestaurants = $this->restaurantModel->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
                                                ->join('cities', 'cities.id = restaurants.city_id')
                                                ->where('restaurants.city_id', $restaurant['city_id'])
                                                ->where('restaurants.id !=', $restaurant['id'])
                                                ->where('restaurants.is_active', 1)
                                                ->orderBy('restaurants.rating', 'DESC')
                                                ->limit(4)
                                                ->findAll();

        $data = [
            'title' => $restaurant['name'] . ' - Georgian Restaurant in ' . $restaurant['city_name'],
            'meta_description' => strip_tags($restaurant['description']) . ' - Authentic Georgian restaurant in ' . $restaurant['city_name'],
            'restaurant' => $restaurant,
            'similarRestaurants' => $similarRestaurants,
            'city' => $city,
            'canonical_url' => base_url($restaurantSlug . '-restaurant-' . $citySlug),
            'isNewUrlStructure' => true
        ];

        return view('restaurants/view', $data);
    }

    // Замените метод bySeoUrl в контроллере Restaurants.php

    public function bySeoUrl($seoUrl)
    {
        // Проверяем, это ли URL ресторана (содержит -restaurant-)
        if (strpos($seoUrl, '-restaurant-') === false) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Page not found: {$seoUrl}");
        }
        
        $restaurant = $this->restaurantModel->getBySeoUrl($seoUrl);
        
        if (!$restaurant) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Restaurant not found: {$seoUrl}");
        }

        // Получаем фотографии ресторана
        $photos = $this->photoModel->getRestaurantPhotos($restaurant['id']);
        $mainPhoto = $this->photoModel->getMainPhoto($restaurant['id']);
        $galleryPhotos = $this->photoModel->getGalleryPhotos($restaurant['id']);

        // Получаем похожие рестораны (из того же города)
        $similarRestaurants = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('restaurants.city_id', $restaurant['city_id'])
            ->where('restaurants.id !=', $restaurant['id'])
            ->where('restaurants.is_active', 1)
            ->orderBy('restaurants.rating', 'DESC')
            ->limit(4)
            ->findAll();

        // Добавляем фото для похожих ресторанов
        foreach ($similarRestaurants as &$similar) {
            $similar['main_photo'] = $this->photoModel->getMainPhoto($similar['id']);
        }
        unset($similar); // Очищаем ссылку после цикла

        $data = [
            'title' => $restaurant['name'] . ' - Georgian Restaurant in ' . $restaurant['city_name'],
            'meta_description' => strip_tags($restaurant['description']) . ' - Authentic Georgian restaurant in ' . $restaurant['city_name'],
            'restaurant' => $restaurant,
            'photos' => $photos,
            'mainPhoto' => $mainPhoto,
            'galleryPhotos' => $galleryPhotos,
            'similarRestaurants' => $similarRestaurants,
            'canonical_url' => base_url($restaurant['seo_url']),
            'isNewUrlStructure' => true
        ];

        return view('restaurants/view', $data);
    }

    // Рестораны по городу
    public function byCity($cityId)
    {
        if (!is_numeric($cityId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('City not found');
        }

        $city = $this->cityModel->find($cityId);
        if (!$city) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('City not found');
        }

        // Получаем параметры фильтрации
        $request = $this->request;
        $priceLevel = $request->getGet('price');
        $sortBy = $request->getGet('sort') ?? 'rating';

        // Строим запрос с фильтрами
        $builder = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                       ->join('cities', 'cities.id = restaurants.city_id')
                                       ->where('restaurants.city_id', $cityId)
                                       ->where('restaurants.is_active', 1);

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

        $restaurants = $builder->findAll();

        $data = [
            'title' => 'Georgian Food in ' . $city['name'] . ' - Georgian Food Near Me',
            'meta_description' => 'Find the best Georgian restaurants in ' . $city['name'] . '. Authentic Georgian cuisine and traditional dishes.',
            'city' => $city,
            'restaurants' => $restaurants,
            'totalRestaurants' => count($restaurants),
            'selectedPrice' => $priceLevel,
            'selectedSort' => $sortBy
        ];

        return view('restaurants/by_city', $data);
    }
    
    public function byCitySlug($slug)
    {
    // Специальные случаи (не города, а регионы)
    $specialCases = [
        'new-york-city' => 'newYorkCity',
        'nyc' => 'newYorkCity'
    ];
    
    if (isset($specialCases[$slug])) {
        return $this->{$specialCases[$slug]}();
    }
    
    // Ищем город по slug
    $city = $this->cityModel->where('slug', $slug)->first();
    
    if (!$city) {
        throw new \CodeIgniter\Exceptions\PageNotFoundException("City not found: {$slug}");
    }
    
    // Используем существующий метод byCity
    return $this->byCity($city['id']);
    }
    
    //Рестораны по штату
    public function byState($state)
    {
        $state = urldecode($state);
        
        // Получаем все города в штате
        $cities = $this->cityModel->where('state', $state)->findAll();
        
        if (empty($cities)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('State not found');
        }

        // Получаем ID городов
        $cityIds = array_column($cities, 'id');

        // Получаем параметры фильтрации
        $request = $this->request;
        $priceLevel = $request->getGet('price');
        $sortBy = $request->getGet('sort') ?? 'rating';
        $cityFilter = $request->getGet('city');

        // Строим запрос для ресторанов в штате
        $builder = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                    ->join('cities', 'cities.id = restaurants.city_id')
                                    ->whereIn('restaurants.city_id', $cityIds)
                                    ->where('restaurants.is_active', 1);

        if ($priceLevel) {
            $builder->where('restaurants.price_level', $priceLevel);
        }

        if ($cityFilter) {
            $builder->where('restaurants.city_id', $cityFilter);
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

        $restaurants = $builder->findAll();

        $data = [
            'title' => 'Georgian Food in ' . $state . ' - Georgian Food Near Me',
            'meta_description' => 'Discover authentic Georgian restaurants throughout ' . $state . '. Find khachapuri, khinkali and traditional Georgian dishes.',
            'state' => $state,
            'cities' => $cities,
            'restaurants' => $restaurants,
            'totalRestaurants' => count($restaurants),
            // Передаем данные фильтров в представление
            'selectedPrice' => $priceLevel,
            'selectedSort' => $sortBy,
            'selectedCity' => $cityFilter
        ];

        return view('restaurants/by_state', $data);
    }

    // Рестораны по уровню цен
    public function byPrice($priceLevel)
    {
        if (!in_array($priceLevel, [1, 2, 3, 4])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid price level');
        }

        $restaurants = $this->restaurantModel->getByPriceLevel($priceLevel);

        $priceLevels = [
            1 => 'Budget-Friendly',
            2 => 'Moderate',
            3 => 'Expensive',
            4 => 'Very Expensive'
        ];

        $data = [
            'title' => $priceLevels[$priceLevel] . ' Georgian Restaurants - Georgian Food Near Me',
            'meta_description' => 'Find ' . strtolower($priceLevels[$priceLevel]) . ' Georgian restaurants. Quality Georgian cuisine at great prices.',
            'restaurants' => $restaurants,
            'priceLevel' => $priceLevel,
            'priceLevelName' => $priceLevels[$priceLevel],
            'totalRestaurants' => count($restaurants)
        ];

        return view('restaurants/by_price', $data);
    }

    // ИСПРАВЛЕННАЯ версия методов для контроллера Restaurants.php

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
     * Применение сортировки к билдеру
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
            case 'price_low':
                $builder->orderBy('restaurants.price_level', 'ASC');
                break;
            case 'price_high':
                $builder->orderBy('restaurants.price_level', 'DESC');
                break;
            default:
                $builder->orderBy('restaurants.rating', 'DESC');
        }
    }

    //для страницы restaurants
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

// =============================================================================
// НОВЫЕ МЕТОДЫ ДЛЯ SEO URL СТРУКТУРЫ
// Добавить эти методы в класс Restaurants
// =============================================================================

// ИСПРАВЛЕННЫЕ универсальные методы для контроллера Restaurants.php

    /**
     * ИСПРАВЛЕННЫЙ универсальный метод с каскадным поиском
     */
    public function byCitySlugNew($citySlug)
    {
        // Нормализуем slug города
        $citySlug = str_replace('georgian-restaurants-', '', $citySlug);
        
        // Специальные случаи для NYC
        if (in_array($citySlug, ['new-york-city', 'nyc'])) {
            return $this->newYorkCity();
        }
        
        // Ищем город по slug
        $city = $this->cityModel->where('slug', $citySlug)->first();
        
        if (!$city) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("City not found: {$citySlug}");
        }
        
        // Получаем параметры фильтрации
        $request = $this->request;
        $priceLevel = $request->getGet('price');
        $sortBy = $request->getGet('sort') ?? 'rating';

        // Каскадный поиск
        $searchResult = $this->findRestaurantsCascadeUniversal($city, $priceLevel, $sortBy);

        $data = [
            'title' => 'Georgian Restaurants in ' . $city['name'] . ' - Find Authentic Georgian Food',
            'meta_description' => 'Find the best Georgian restaurants in ' . $city['name'] . '. Authentic khachapuri, khinkali and traditional Georgian cuisine.',
            'city' => $city,
            'restaurants' => $searchResult['restaurants'],
            'totalRestaurants' => count($searchResult['restaurants']),
            'selectedPrice' => $priceLevel,
            'selectedSort' => $sortBy,
            'canonical_url' => base_url('georgian-restaurants-' . $city['slug']),
            'isNewUrlStructure' => true,
            'search_info' => $searchResult['search_info']
        ];

        return view('restaurants/by_city', $data);
    }

    /**
     * ИСПРАВЛЕННЫЙ универсальный каскадный поиск для любого города
     */
    private function findRestaurantsCascadeUniversal($city, $priceLevel = null, $sortBy = 'rating')
    {
        // Проверяем кэш
        $cacheKey = "restaurants_cascade_{$city['slug']}_" . md5(serialize([$priceLevel, $sortBy]));
        $cached = cache($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        // Шаг 1: Точное совпадение по городу
        $builder = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                    ->join('cities', 'cities.id = restaurants.city_id')
                                    ->where('restaurants.city_id', $city['id'])
                                    ->where('restaurants.is_active', 1);

        if ($priceLevel) {
            $builder->where('restaurants.price_level', $priceLevel);
        }

        $this->applySorting($builder, $sortBy);
        $restaurants = $builder->findAll();

        if (!empty($restaurants)) {
            // Добавляем фотографии
            foreach ($restaurants as &$restaurant) {
                $restaurant['main_photo'] = $this->photoModel->getMainPhoto($restaurant['id']);
            }
            unset($restaurant);

            $result = [
                'restaurants' => $restaurants,
                'search_info' => [
                    'method' => 'exact_city',
                    'message' => "Georgian restaurants in {$city['name']}",
                    'count' => count($restaurants)
                ]
            ];

            cache()->save($cacheKey, $result, 3600);
            return $result;
        }

        // Шаг 2: Поиск в радиусе 50км (только если есть координаты города)
        if (!empty($city['latitude']) && !empty($city['longitude'])) {
            $restaurants = $this->searchByRadiusWithFilters($city, 50, $priceLevel, $sortBy);

            if (!empty($restaurants)) {
                $result = [
                    'restaurants' => $restaurants,
                    'search_info' => [
                        'method' => 'radius_50',
                        'message' => "Georgian restaurants within 50km of {$city['name']}",
                        'count' => count($restaurants)
                    ]
                ];

                cache()->save($cacheKey, $result, 3600);
                return $result;
            }
        }

        // Шаг 3: Поиск по штату
        if (!empty($city['state'])) {
            $restaurants = $this->searchByStateWithFilters($city['state'], $priceLevel, $sortBy);

            if (!empty($restaurants)) {
                $result = [
                    'restaurants' => $restaurants,
                    'search_info' => [
                        'method' => 'state',
                        'message' => "Georgian restaurants in {$city['state']}",
                        'count' => count($restaurants)
                    ]
                ];

                cache()->save($cacheKey, $result, 3600);
                return $result;
            }
        }

        // Шаг 4: Расширенный радиус 100км (если есть координаты)
        if (!empty($city['latitude']) && !empty($city['longitude'])) {
            $restaurants = $this->searchByRadiusWithFilters($city, 100, $priceLevel, $sortBy);

            $result = [
                'restaurants' => $restaurants,
                'search_info' => [
                    'method' => 'radius_100',
                    'message' => !empty($restaurants) 
                        ? "Georgian restaurants within 100km of {$city['name']}" 
                        : "No Georgian restaurants found near {$city['name']}",
                    'count' => count($restaurants)
                ]
            ];

            cache()->save($cacheKey, $result, 1800);
            return $result;
        }

        // Если ничего не найдено и нет координат
        $result = [
            'restaurants' => [],
            'search_info' => [
                'method' => 'none',
                'message' => "No Georgian restaurants found near {$city['name']}",
                'count' => 0
            ]
        ];

        cache()->save($cacheKey, $result, 1800);
        return $result;
    }

    /**
     * Детальная страница ресторана: /aragvi-restaurant-chicago
     */
    public function restaurantDetail($restaurantSlug, $citySlug)
    {
        // Ищем город
        $city = $this->cityModel->where('slug', $citySlug)->first();
        if (!$city) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("City not found: {$citySlug}");
        }
        
        // Ищем ресторан
        $restaurant = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.state, cities.slug as city_slug')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('restaurants.slug', $restaurantSlug)
            ->where('restaurants.city_id', $city['id'])
            ->where('restaurants.is_active', 1)
            ->first();
        
        if (!$restaurant) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Restaurant not found: {$restaurantSlug} in {$citySlug}");
        }

        // Получаем фотографии ресторана
        $photos = $this->photoModel->getRestaurantPhotos($restaurant['id']);
        $mainPhoto = $this->photoModel->getMainPhoto($restaurant['id']);
        $galleryPhotos = $this->photoModel->getGalleryPhotos($restaurant['id']);

        // Получаем похожие рестораны (из того же города)
        $similarRestaurants = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('restaurants.city_id', $restaurant['city_id'])
            ->where('restaurants.id !=', $restaurant['id'])
            ->where('restaurants.is_active', 1)
            ->orderBy('restaurants.rating', 'DESC')
            ->limit(4)
            ->findAll();

        // ИСПРАВЛЕНО: Добавляем фото для похожих ресторанов используя &$similar
        foreach ($similarRestaurants as &$similar) {
            $similar['main_photo'] = $this->photoModel->getMainPhoto($similar['id']);
        }
        unset($similar); // Очищаем ссылку после цикла

        // Генерируем SEO-оптимизированный контент
        $seoTitle = $restaurant['name'] . ' in ' . $restaurant['city_name'];
        if ($restaurant['city_name'] == 'Manhattan') {
            $seoTitle .= ', NYC';
        } elseif (!empty($restaurant['state'])) {
            $seoTitle .= ', ' . $restaurant['state'];
        }
        $seoTitle .= ' - Authentic Georgian Restaurant';

        $data = [
            'title' => $seoTitle,
            'meta_description' => 'Visit ' . $restaurant['name'] . ' in ' . $restaurant['city_name'] . ' for authentic Georgian cuisine. ' . character_limiter(strip_tags($restaurant['description']), 120),
            'restaurant' => $restaurant,
            'city' => $city,
            'photos' => $photos,
            'mainPhoto' => $mainPhoto,
            'galleryPhotos' => $galleryPhotos,
            'similarRestaurants' => $similarRestaurants,
            'canonical_url' => base_url($restaurantSlug . '-restaurant-' . $city['slug']),
            'isNewUrlStructure' => true
        ];

        return view('restaurants/view', $data);
    }

// =============================================================================
// МЕТОДЫ РЕДИРЕКТОВ СО СТАРЫХ URL
// =============================================================================

    /**
     * Редирект со старого URL города: /georgian-restaurant-Manhattan -> /georgian-restaurants-manhattan
     */
    public function redirectOldCityUrl($citySlug)
    {
        // Приводим к нижнему регистру
        $citySlug = strtolower($citySlug);
        
        // Специальные случаи
        $redirectMap = [
            'manhattan' => 'georgian-restaurants-manhattan',
            'brooklyn' => 'georgian-restaurants-brooklyn',
            'new-york-city' => 'georgian-restaurants-new-york-city',
            'nyc' => 'georgian-restaurants-manhattan'
        ];
        
        if (isset($redirectMap[$citySlug])) {
            return redirect()->permanent(base_url($redirectMap[$citySlug]));
        }
        
        // Ищем город в базе
        $city = $this->cityModel
            ->where('LOWER(slug)', $citySlug)
            ->orWhere('LOWER(name)', str_replace('-', ' ', $citySlug))
            ->first();
        
        if ($city) {
            return redirect()->permanent(base_url('georgian-restaurants-' . $city['slug']));
        }
        
        // Если не найден, редиректим на общий список
        return redirect()->permanent(base_url('georgian-restaurant-near-me'));
    }

    /**
     * Редирект со старого URL ресторана: /georgian-restaurant-Manhattan/aragvi -> /aragvi-restaurant-manhattan
     */
    public function redirectOldRestaurantUrl($citySlug, $restaurantSlug)
    {
        $citySlug = strtolower($citySlug);
        $restaurantSlug = strtolower($restaurantSlug);
        
        // Ищем город
        $city = $this->cityModel
            ->where('LOWER(slug)', $citySlug)
            ->orWhere('LOWER(name)', str_replace('-', ' ', $citySlug))
            ->first();
        
        if (!$city) {
            return redirect()->permanent(base_url('georgian-restaurant-near-me'));
        }
        
        // Ищем ресторан
        $restaurant = $this->restaurantModel
            ->where('LOWER(slug)', $restaurantSlug)
            ->where('city_id', $city['id'])
            ->where('is_active', 1)
            ->first();
        
        if ($restaurant) {
            return redirect()->permanent(base_url($restaurant['slug'] . '-restaurant-' . $city['slug']));
        }
        
        // Если ресторан не найден, редиректим на город
        return redirect()->permanent(base_url('georgian-restaurants-' . $city['slug']));
    }

    /**
     * Редирект со страниц штатов на главный город штата
     */
    public function redirectStateToCity($state)
    {
        // Карта штатов к основным городам
        $stateToMainCity = [
            'ny' => 'georgian-restaurants-manhattan',
            'new-york' => 'georgian-restaurants-manhattan',
            'ca' => 'georgian-restaurants-los-angeles',
            'california' => 'georgian-restaurants-los-angeles',
            'il' => 'georgian-restaurants-chicago',
            'illinois' => 'georgian-restaurants-chicago'
        ];
        
        $state = strtolower($state);
        
        if (isset($stateToMainCity[$state])) {
            return redirect()->permanent(base_url($stateToMainCity[$state]));
        }
        
        // Если штат не найден, ищем первый город в этом штате
        $city = $this->cityModel
            ->where('LOWER(state)', $state)
            ->orderBy('name', 'ASC')
            ->first();
        
        if ($city) {
            return redirect()->permanent(base_url('georgian-restaurants-' . $city['slug']));
        }
        
        // Если ничего не найдено
        return redirect()->permanent(base_url('restaurants'));
    }

    /**
     * Управление фотографиями ресторана
     */
    public function uploadPhoto($restaurantId)
    {
        // Загружаем модели
        $restaurantModel = model('RestaurantModel');
        $photoModel = model('RestaurantPhotoModel');
        
        // Получаем ресторан
        $restaurant = $restaurantModel->find($restaurantId);
        if (!$restaurant) {
            return redirect()->to('/admin/restaurants')->with('error', 'Ресторан не найден');
        }
        
        // Обработка POST запроса (загрузка фотографий)
        if ($this->request->getMethod() === 'POST') {
            $uploadedFiles = $this->request->getFiles();
            
            if (!isset($uploadedFiles['photos']) || empty($uploadedFiles['photos'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Файлы не выбраны'
                ]);
            }
            
            $uploadPath = FCPATH . 'uploads/restaurants/' . $restaurantId . '/';
            
            // Создаем директорию если не существует
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $uploadedCount = 0;
            $errors = [];
            
            foreach ($uploadedFiles['photos'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    // Проверяем тип файла
                    if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
                        $errors[] = 'Файл ' . $file->getName() . ' не является изображением';
                        continue;
                    }
                    
                    // Проверяем размер файла (максимум 5MB)
                    if ($file->getSize() > 5 * 1024 * 1024) {
                        $errors[] = 'Файл ' . $file->getName() . ' превышает максимальный размер 5MB';
                        continue;
                    }
                    
                    // Генерируем уникальное имя файла
                    $newName = $file->getRandomName();
                    
                    if ($file->move($uploadPath, $newName)) {
                        // Сохраняем в базу данных
                        $photoUrl = base_url('uploads/restaurants/' . $restaurantId . '/' . $newName);
                        
                        // Если это первое фото ресторана, делаем его главным
                        $isMain = $photoModel->getPhotoCount($restaurantId) === 0;
                        
                        if ($photoModel->addPhoto($restaurantId, $photoUrl, $isMain)) {
                            $uploadedCount++;
                        } else {
                            $errors[] = 'Ошибка сохранения в базе данных для файла ' . $file->getName();
                            // Удаляем файл если не удалось сохранить в БД
                            unlink($uploadPath . $newName);
                        }
                    } else {
                        $errors[] = 'Ошибка загрузки файла ' . $file->getName();
                    }
                } else {
                    $errors[] = 'Файл ' . $file->getName() . ' поврежден или уже перемещен';
                }
            }
            
            if ($uploadedCount > 0) {
                $message = "Успешно загружено {$uploadedCount} фото";
                if (!empty($errors)) {
                    $message .= '. Ошибки: ' . implode('; ', $errors);
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message,
                    'uploaded_count' => $uploadedCount
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Не удалось загрузить ни одного файла. ' . implode('; ', $errors)
                ]);
            }
        }
        
        // GET запрос - показываем страницу управления фотографиями
        $photos = $photoModel->getRestaurantPhotos($restaurantId);
        
        $data = [
            'title' => 'Управление фотографиями - ' . $restaurant['name'],
            'restaurant' => $restaurant,
            'photos' => $photos
        ];
        
        return view('admin/restaurant_photos', $data);
    }

    /**
     * Установить главное фото
     */
    public function setMainPhoto($photoId)
    {
        $photoModel = model('RestaurantPhotoModel');
        
        // Получаем информацию о фото
        $photo = $photoModel->find($photoId);
        if (!$photo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Фото не найдено'
            ]);
        }
        
        if ($photoModel->setMainPhoto($photo['restaurant_id'], $photoId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Главное фото установлено'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ошибка при установке главного фото'
            ]);
        }
    }

    /**
     * Удалить фото
     */
    public function deletePhoto($photoId)
    {
        $photoModel = model('RestaurantPhotoModel');
        
        // Получаем информацию о фото
        $photo = $photoModel->find($photoId);
        if (!$photo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Фото не найдено'
            ]);
        }
        
        // Удаляем файл с диска
        $photoPath = str_replace(base_url(), FCPATH, $photo['photo_url']);
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
        
        // Удаляем запись из базы данных
        if ($photoModel->deletePhoto($photoId)) {
            // Если удаляли главное фото, назначаем новое главное фото (первое из оставшихся)
            if ($photo['is_main']) {
                $remainingPhotos = $photoModel->getRestaurantPhotos($photo['restaurant_id']);
                if (!empty($remainingPhotos)) {
                    $photoModel->setMainPhoto($photo['restaurant_id'], $remainingPhotos[0]['id']);
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Фото удалено'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ошибка при удалении фото из базы данных'
            ]);
        }
    }

    /**
     * Обновленный метод view() с автоматическим получением фото из Google
     */
    public function viewWithGooglePhotos($id)
    {
        if (!is_numeric($id)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }

        $restaurant = $this->restaurantModel->getRestaurantWithCity($id);

        if (!$restaurant) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }

        // Получаем существующие фотографии
        $photos = $this->photoModel->getRestaurantPhotos($restaurant['id']);
        $mainPhoto = $this->photoModel->getMainPhoto($restaurant['id']);
        $galleryPhotos = $this->photoModel->getGalleryPhotos($restaurant['id']);

        // НОВОЕ: Если фотографий нет, пытаемся загрузить из Google
        if (empty($photos) && !empty($restaurant['google_place_id'])) {
            $googleResult = $this->googlePhotoService->importGooglePhotos($restaurant['id'], 3);
            
            if ($googleResult['success']) {
                // Обновляем данные фотографий после импорта
                $photos = $this->photoModel->getRestaurantPhotos($restaurant['id']);
                $mainPhoto = $this->photoModel->getMainPhoto($restaurant['id']);
                $galleryPhotos = $this->photoModel->getGalleryPhotos($restaurant['id']);
            }
        }

        // НОВОЕ: Если Place ID нет, пытаемся найти его
        if (empty($restaurant['google_place_id'])) {
            $placeIdResult = $this->googlePhotoService->findAndSetPlaceId($restaurant['id']);
            
            if ($placeIdResult['success']) {
                // Обновляем данные ресторана
                $restaurant['google_place_id'] = $placeIdResult['place_id'];
                
                // И пытаемся загрузить фото
                if (empty($photos)) {
                    $googleResult = $this->googlePhotoService->importGooglePhotos($restaurant['id'], 3);
                    
                    if ($googleResult['success']) {
                        $photos = $this->photoModel->getRestaurantPhotos($restaurant['id']);
                        $mainPhoto = $this->photoModel->getMainPhoto($restaurant['id']);
                        $galleryPhotos = $this->photoModel->getGalleryPhotos($restaurant['id']);
                    }
                }
            }
        }

        // Получаем похожие рестораны
        $similarRestaurants = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                                ->join('cities', 'cities.id = restaurants.city_id')
                                                ->where('restaurants.city_id', $restaurant['city_id'])
                                                ->where('restaurants.id !=', $id)
                                                ->where('restaurants.is_active', 1)
                                                ->orderBy('restaurants.rating', 'DESC')
                                                ->limit(4)
                                                ->findAll();

        // Добавляем фото для похожих ресторанов
        foreach ($similarRestaurants as &$similar) {
            $similar['main_photo'] = $this->photoModel->getMainPhoto($similar['id']);
        }

        $data = [
            'title' => $restaurant['name'] . ' - Georgian Restaurant in ' . $restaurant['city_name'],
            'meta_description' => strip_tags($restaurant['description']) . ' - Authentic Georgian restaurant in ' . $restaurant['city_name'],
            'restaurant' => $restaurant,
            'photos' => $photos,
            'mainPhoto' => $mainPhoto,
            'galleryPhotos' => $galleryPhotos,
            'similarRestaurants' => $similarRestaurants,
            'hasGoogleIntegration' => true // Флаг для view
        ];

        return view('restaurants/view', $data);
    }

    /**
     * Пакетное обновление фотографий для ресторанов без фото
     */
    public function updateMissingPhotos()
    {
        // Только для админа или через CLI
        if (!$this->request->isCLI() && !session()->get('is_admin')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        set_time_limit(0); // Убираем лимит времени
        
        echo "Начинаем обновление фотографий...\n";
        
        // Сначала заполняем Place ID
        echo "Этап 1: Заполнение Place ID...\n";
        
        $placeIdResult = $this->googlePhotoService->fillMissingPlaceIds(20);
        echo "Place ID - Обработано: {$placeIdResult['processed']}, Успешно: {$placeIdResult['success']}\n";
        
        // Затем импортируем фотографии
        echo "Этап 2: Импорт фотографий...\n";
        
        $photosResult = $this->googlePhotoService->massImportPhotos(10, 4);
        echo "Фото - Обработано: {$photosResult['processed']}, Импортировано: {$photosResult['total_photos']} фотографий\n";
        
        if ($this->request->isCLI()) {
            echo "Готово!\n";
            return;
        } else {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Обновление завершено',
                'place_ids' => $placeIdResult,
                'photos' => $photosResult
            ]);
        }
    }

    /**
     * Получение превью Google фотографий без сохранения
     */
    public function previewGooglePhotos($restaurantId)
    {
        if (!is_numeric($restaurantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Неверный ID ресторана'
            ]);
        }

        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ресторан не найден'
            ]);
        }

        // Если нет Place ID, пытаемся найти
        if (empty($restaurant['google_place_id'])) {
            $placeIdResult = $this->googlePhotoService->findAndSetPlaceId($restaurantId);
            if (!$placeIdResult['success']) {
                return $this->response->setJSON($placeIdResult);
            }
            $placeId = $placeIdResult['place_id'];
        } else {
            $placeId = $restaurant['google_place_id'];
        }

        // Получаем детали места
        $googleAPI = new \App\Libraries\GooglePlacesAPI();
        $details = $googleAPI->getPlaceDetails($placeId);
        
        if (!$details['success']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ошибка получения деталей: ' . $details['message']
            ]);
        }

        $photos = $details['data']['result']['photos'] ?? [];
        
        if (empty($photos)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Фотографии не найдены'
            ]);
        }

        // Формируем превью URL для первых 6 фотографий
        $previews = [];
        $maxPreviews = min(6, count($photos));
        
        for ($i = 0; $i < $maxPreviews; $i++) {
            $previews[] = [
                'reference' => $photos[$i]['photo_reference'],
                'url' => $googleAPI->getPlacePhoto($photos[$i]['photo_reference'], 300),
                'width' => $photos[$i]['width'] ?? 0,
                'height' => $photos[$i]['height'] ?? 0
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'total_photos' => count($photos),
            'previews' => $previews,
            'place_id' => $placeId
        ]);
    }
}