<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\CityModel;

class Dishes extends BaseController
{
    protected $restaurantModel;
    protected $cityModel;

    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
        helper('text');
    }

    // Khachapuri - 110K поисков
    public function khachapuri()
    {
        // Находим рестораны где есть khachapuri (по описанию)
        $restaurants = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                           ->join('cities', 'cities.id = restaurants.city_id')
                                           ->where('restaurants.is_active', 1)
                                           ->groupStart()
                                               ->like('restaurants.name', 'khachapuri')
                                               ->orLike('restaurants.description', 'khachapuri')
                                           ->groupEnd()
                                           ->orderBy('restaurants.rating', 'DESC')
                                           ->limit(20)
                                           ->findAll();

        // Топ города с khachapuri
        $topCities = $this->cityModel->select('cities.*, COUNT(restaurants.id) as restaurant_count')
                                   ->join('restaurants', 'restaurants.city_id = cities.id AND restaurants.is_active = 1')
                                   ->where('(restaurants.name LIKE "%khachapuri%" OR restaurants.description LIKE "%khachapuri%")')
                                   ->groupBy('cities.id')
                                   ->orderBy('restaurant_count', 'DESC')
                                   ->limit(10)
                                   ->findAll();

        $data = [
            'title' => 'Khachapuri - Find Best Georgian Cheese Bread Near You',
            'meta_description' => 'Discover authentic khachapuri (Georgian cheese bread) at the best Georgian restaurants. Find Adjarian, Imeruli and other khachapuri varieties near you.',
            'restaurants' => $restaurants,
            'topCities' => $topCities,
            'dishName' => 'Khachapuri',
            'dishDescription' => 'Traditional Georgian cheese-filled bread, available in various regional styles including Adjarian (boat-shaped) and Imeruli (round).'
        ];

        return view('dishes/khachapuri', $data);
    }

    // Khinkali - 90.5K поисков  
    public function khinkali()
    {
        $restaurants = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                           ->join('cities', 'cities.id = restaurants.city_id')
                                           ->where('restaurants.is_active', 1)
                                           ->groupStart()
                                               ->like('restaurants.name', 'khinkali')
                                               ->orLike('restaurants.description', 'khinkali')
                                           ->groupEnd()
                                           ->orderBy('restaurants.rating', 'DESC')
                                           ->limit(20)
                                           ->findAll();

        $topCities = $this->cityModel->select('cities.*, COUNT(restaurants.id) as restaurant_count')
                                   ->join('restaurants', 'restaurants.city_id = cities.id AND restaurants.is_active = 1')
                                   ->where('(restaurants.name LIKE "%khinkali%" OR restaurants.description LIKE "%khinkali%")')
                                   ->groupBy('cities.id')
                                   ->orderBy('restaurant_count', 'DESC')
                                   ->limit(10)
                                   ->findAll();

        $data = [
            'title' => 'Khinkali - Georgian Dumplings Near You',
            'meta_description' => 'Find the best khinkali (Georgian soup dumplings) at authentic Georgian restaurants. Discover traditional handmade khinkali near you.',
            'restaurants' => $restaurants,
            'topCities' => $topCities,
            'dishName' => 'Khinkali',
            'dishDescription' => 'Traditional Georgian dumplings filled with spiced meat and broth. Eaten by hand, these iconic dumplings are a must-try Georgian dish.'
        ];

        return view('dishes/khinkali', $data);
    }

    // Georgian Cuisine - 74K поисков
    public function cuisine()
    {
        $restaurants = $this->restaurantModel->getAllActive(24);
        $cities = $this->cityModel->getCitiesWithRestaurantCount();

        $data = [
            'title' => 'Georgian Cuisine - Authentic Georgian Food & Restaurants',
            'meta_description' => 'Explore authentic Georgian cuisine featuring khachapuri, khinkali, mtsvadi and traditional dishes. Find Georgian restaurants serving authentic flavors.',
            'restaurants' => $restaurants,
            'cities' => $cities,
            'totalRestaurants' => count($restaurants)
        ];

        return view('dishes/cuisine', $data);
    }

    // Khachapuri Near Me - 6.6K поисков
    public function khachapuriNearMe()
    {
        $restaurants = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                           ->join('cities', 'cities.id = restaurants.city_id')
                                           ->where('restaurants.is_active', 1)
                                           ->groupStart()
                                               ->like('restaurants.name', 'khachapuri')
                                               ->orLike('restaurants.description', 'khachapuri')
                                           ->groupEnd()
                                           ->orderBy('restaurants.rating', 'DESC')
                                           ->findAll();

        $data = [
            'title' => 'Khachapuri Near Me - Find Georgian Cheese Bread Nearby',
            'meta_description' => 'Find khachapuri near you! Locate the closest Georgian restaurants serving authentic cheese-filled bread. Use your location to find khachapuri nearby.',
            'restaurants' => $restaurants,
            'dishName' => 'Khachapuri',
            'searchType' => 'near_me'
        ];

        return view('dishes/dish_near_me', $data);
    }

    // Khinkali Near Me - 4.4K поисков
    public function khinkaliNearMe()
    {
        $restaurants = $this->restaurantModel->select('restaurants.*, cities.name as city_name')
                                           ->join('cities', 'cities.id = restaurants.city_id')
                                           ->where('restaurants.is_active', 1)
                                           ->groupStart()
                                               ->like('restaurants.name', 'khinkali')
                                               ->orLike('restaurants.description', 'khinkali')
                                           ->groupEnd()
                                           ->orderBy('restaurants.rating', 'DESC')
                                           ->findAll();

        $data = [
            'title' => 'Khinkali Near Me - Find Georgian Dumplings Nearby',
            'meta_description' => 'Find khinkali near you! Discover Georgian restaurants serving authentic soup dumplings. Use your location to find the best khinkali nearby.',
            'restaurants' => $restaurants,
            'dishName' => 'Khinkali',
            'searchType' => 'near_me'
        ];

        return view('dishes/dish_near_me', $data);
    }
}