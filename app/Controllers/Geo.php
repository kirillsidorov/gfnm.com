<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\CityModel;

class Geo extends BaseController
{
    protected $restaurantModel;
    protected $cityModel;

    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
        helper('text');
    }

    // Georgian Restaurant NYC - 4.4K поисков
    public function nyc()
    {
        return $this->cityPage('New York', 'NYC', 'georgian-restaurants-nyc');
    }

    // Georgian Restaurant New York - 4.4K поисков  
    public function newYork()
    {
        return $this->cityPage('New York', 'New York', 'georgian-restaurants-new-york');
    }

    // Georgian Restaurant Chicago - 590 поисков
    public function chicago()
    {
        return $this->cityPage('Chicago', 'Chicago', 'georgian-restaurants-chicago');
    }

    // Georgian Restaurant Manhattan - 590 поисков
    public function manhattan()
    {
        return $this->cityPage('Manhattan', 'Manhattan', 'georgian-restaurants-manhattan');
    }

    // Универсальный метод для городских страниц
    private function cityPage($cityName, $displayName, $slug)
    {
        // Находим город в базе
        $city = $this->cityModel->where('name', $cityName)->first();
        
        if (!$city) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("City {$cityName} not found");
        }

        // Получаем рестораны города
        $restaurants = $this->restaurantModel->getByCity($city['id']);

        // Статистика
        $avgRating = 0;
        $priceRange = '';
        if (!empty($restaurants)) {
            $totalRating = array_sum(array_column($restaurants, 'rating'));
            $avgRating = $totalRating / count($restaurants);
            
            $prices = array_column($restaurants, 'price_level');
            $minPrice = min($prices);
            $maxPrice = max($prices);
            
            for ($i = 0; $i < $minPrice; $i++) $priceRange .= '$';
            if ($minPrice != $maxPrice) {
                $priceRange .= ' - ';
                for ($i = 0; $i < $maxPrice; $i++) $priceRange .= '$';
            }
        }

        // Похожие города в том же штате
        $similarCities = [];
        if (!empty($city['state'])) {
            $similarCities = $this->cityModel->select('cities.*, COUNT(restaurants.id) as restaurant_count')
                                            ->join('restaurants', 'restaurants.city_id = cities.id AND restaurants.is_active = 1', 'left')
                                            ->where('cities.state', $city['state'])
                                            ->where('cities.id !=', $city['id'])
                                            ->groupBy('cities.id')
                                            ->having('restaurant_count >', 0)
                                            ->orderBy('restaurant_count', 'DESC')
                                            ->limit(5)
                                            ->findAll();
        }

        $data = [
            'title' => "Georgian Restaurant {$displayName} - Best Georgian Food in {$displayName}",
            'meta_description' => "Find the best Georgian restaurants in {$displayName}. Authentic khachapuri, khinkali, and traditional Georgian cuisine in {$displayName}.",
            'city' => $city,
            'displayName' => $displayName,
            'restaurants' => $restaurants,
            'totalRestaurants' => count($restaurants),
            'avgRating' => $avgRating,
            'priceRange' => $priceRange,
            'similarCities' => $similarCities,
            'slug' => $slug
        ];

        return view('geo/city', $data);
    }
}