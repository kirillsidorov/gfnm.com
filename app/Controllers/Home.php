<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\CityModel;

class Home extends BaseController
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

    public function index()
    {
        // Получаем топ рестораны для главной страницы
        $topRestaurants = $this->restaurantModel->getTopRated(6);
        
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
        helper('text'); // Загружаем хелпер для этого метода тоже
        
        $request = $this->request;
        
        // Получаем параметры поиска
        $searchQuery = $request->getGet('q');
        $cityId = $request->getGet('city');
        
        if (empty($searchQuery)) {
            return redirect()->to('/')->with('error', 'Please enter search term');
        }

        // Выполняем поиск
        $restaurants = $this->restaurantModel->search($searchQuery, $cityId);
        
        // Получаем информацию о выбранном городе
        $selectedCity = null;
        if ($cityId) {
            $selectedCity = $this->cityModel->find($cityId);
        }

        // Получаем все города для фильтра
        $cities = $this->cityModel->findAll();

        $data = [
            'title' => 'Search Results - Georgian Food Near Me',
            'meta_description' => "Search results for '{$searchQuery}' Georgian restaurants",
            'restaurants' => $restaurants,
            'searchQuery' => $searchQuery,
            'selectedCity' => $selectedCity,
            'cities' => $cities,
            'totalFound' => count($restaurants)
        ];

        return view('restaurants/search_results', $data);
    }
}