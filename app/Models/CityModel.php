<?php

namespace App\Models;

use CodeIgniter\Model;

class CityModel extends Model
{
    protected $table = 'cities';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'name', 'state', 'country', 'latitude', 'longitude', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Валидация
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'state' => 'permit_empty|max_length[100]',
        'country' => 'permit_empty|max_length[100]'
    ];

    // Получить активные города
    public function getActiveCities()
    {
        return $this->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    // Получить города с количеством ресторанов
    public function getCitiesWithRestaurantCount()
    {
        return $this->select('cities.*, COUNT(restaurants.id) as restaurant_count')
                   ->join('restaurants', 'restaurants.city_id = cities.id AND restaurants.is_active = 1', 'left')
                   ->where('cities.is_active', 1)
                   ->groupBy('cities.id')
                   ->having('restaurant_count >', 0)
                   ->orderBy('restaurant_count', 'DESC')
                   ->findAll();
    }

    // Поиск городов
    public function searchCities($query)
    {
        return $this->like('name', $query)
                   ->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
}