<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\CityModel;

class Restaurants extends BaseController
{
    protected $restaurantModel;
    protected $cityModel;

    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
        
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

    // Детальная страница ресторана
    public function view($id)
    {
        if (!is_numeric($id)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }

        $restaurant = $this->restaurantModel->getRestaurantWithCity($id);

        if (!$restaurant) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }

        // Получаем похожие рестораны (из того же города)
        $similarRestaurants = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                                   ->join('cities', 'cities.id = restaurants.city_id')
                                                   ->where('restaurants.city_id', $restaurant['city_id'])
                                                   ->where('restaurants.id !=', $id)
                                                   ->where('restaurants.is_active', 1)
                                                   ->orderBy('restaurants.rating', 'DESC')
                                                   ->limit(4)
                                                   ->findAll();

        $data = [
            'title' => $restaurant['name'] . ' - Georgian Restaurant in ' . $restaurant['city_name'],
            'meta_description' => strip_tags($restaurant['description']) . ' - Authentic Georgian restaurant in ' . $restaurant['city_name'],
            'restaurant' => $restaurant,
            'similarRestaurants' => $similarRestaurants
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

    
    //по штату Нью-Йорк
    public function newYorkCity()
    {
        // Получаем параметры фильтрации (как в существующем byCity методе)
        $request = $this->request;
        $priceLevel = $request->getGet('price');
        $sortBy = $request->getGet('sort') ?? 'rating';
        $boroughFilter = $request->getGet('borough'); // Новый фильтр для NYC

        // Строим запрос для Manhattan + Brooklyn
        $builder = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                    ->join('cities', 'cities.id = restaurants.city_id')
                                    ->whereIn('restaurants.city_id', [4, 7]) // Brooklyn и Manhattan
                                    ->where('restaurants.is_active', 1);

        // Применяем фильтр по району если указан
        if ($boroughFilter) {
            $builder->where('restaurants.city_id', $boroughFilter);
        }

        // Применяем фильтр по цене (как в существующих методах)
        if ($priceLevel) {
            $builder->where('restaurants.price_level', $priceLevel);
        }

        // Сортировка (копируем логику из byCity)
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

        // Создаем "виртуальный" город NYC для представления by_city.php
        $nycCity = [
            'id' => 'nyc',
            'name' => 'New York City',
            'state' => 'NY'
        ];

        // Используем точно такую же структуру данных как в byCity()
        $data = [
            'title' => 'Georgian Food in New York City - Georgian Food Near Me',
            'meta_description' => 'Find the best Georgian restaurants in New York City. Manhattan and Brooklyn locations with authentic Georgian cuisine.',
            'city' => $nycCity,
            'restaurants' => $restaurants,
            'totalRestaurants' => count($restaurants),
            'selectedPrice' => $priceLevel,
            'selectedSort' => $sortBy,
            'selectedBorough' => $boroughFilter, // Дополнительно для NYC
            'isNYC' => true // Флаг для кастомизации представления
        ];

        return view('restaurants/by_city', $data);
    }

    public function bySlug($slug)
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

    //для страницы restaurants
    public function browse()
{
    // Получаем города сгруппированные по странам с количеством ресторанов
    $citiesQuery = $this->cityModel
        ->select('cities.id, cities.name, cities.state, cities.country, cities.slug, COUNT(restaurants.id) as restaurant_count')
        ->join('restaurants', 'restaurants.city_id = cities.id AND restaurants.is_active = 1', 'left')
        ->groupBy('cities.id, cities.name, cities.state, cities.country, cities.slug')
        ->having('restaurant_count >', 0) // Только города с ресторанами
        ->orderBy('cities.country, cities.state, cities.name')
        ->findAll();
    
    // Группируем по странам
    $countriesData = [];
    $totalRestaurants = 0;
    $totalCities = 0;
    
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
        
        $totalRestaurants += $city['restaurant_count'];
        $totalCities++;
    }
    
    // Сортируем страны по количеству ресторанов
    uasort($countriesData, function($a, $b) {
        return $b['total_restaurants'] <=> $a['total_restaurants'];
    });
    
    // Получаем топ рестораны для показа
    $topRestaurants = $this->restaurantModel
        ->select('restaurants.*, cities.name as city_name, cities.state')
        ->join('cities', 'cities.id = restaurants.city_id')
        ->where('restaurants.is_active', 1)
        ->orderBy('restaurants.rating', 'DESC')
        ->limit(6)
        ->findAll();
    
    $data = [
        'title' => 'Browse Georgian Restaurants by Location - Georgian Food Near Me',
        'meta_description' => 'Browse Georgian restaurants by country and city. Find authentic Georgian cuisine, khachapuri, and khinkali worldwide.',
        'countriesData' => $countriesData,
        'topRestaurants' => $topRestaurants,
        'totalRestaurants' => $totalRestaurants,
        'totalCities' => $totalCities,
        'totalCountries' => count($countriesData)
    ];
    
    return view('restaurants/browse', $data); // представление browse.php
}

}