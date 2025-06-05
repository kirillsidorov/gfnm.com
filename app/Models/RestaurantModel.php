<?php

namespace App\Models;

use CodeIgniter\Model;

class RestaurantModel extends Model
{
    protected $table = 'restaurants';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'name', 'description', 'address', 'phone', 'website',
        'rating', 'price_level', 'google_place_id', 'hours',
        'is_active', 'city_id'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Валидация
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'address' => 'required|min_length[10]',
        'phone' => 'permit_empty|regex_match[/^[\+\d\s\-\(\)]+$/]',
        'website' => 'permit_empty|valid_url',
        'rating' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[5]',
        'price_level' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[4]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Название ресторана обязательно',
            'min_length' => 'Название должно содержать минимум 3 символа'
        ],
        'address' => [
            'required' => 'Адрес обязателен'
        ]
    ];

    // Получить рестораны по городу с информацией о городе
    public function getByCity($cityId)
    {
        return $this->select('restaurants.*, cities.name as city_name')
                   ->join('cities', 'cities.id = restaurants.city_id')
                   ->where('restaurants.city_id', $cityId)
                   ->where('restaurants.is_active', 1)
                   ->orderBy('restaurants.rating', 'DESC')
                   ->findAll();
    }

    // Поиск ресторанов
    public function search($query, $cityId = null)
    {
        $builder = $this->select('restaurants.*, cities.name as city_name')
                       ->join('cities', 'cities.id = restaurants.city_id');
        
        $builder->where('restaurants.is_active', 1);
        
        if ($cityId) {
            $builder->where('restaurants.city_id', $cityId);
        }
        
        $builder->groupStart()
                ->like('restaurants.name', $query)
                ->orLike('restaurants.description', $query)
                ->orLike('restaurants.address', $query)
                ->groupEnd();
                
        return $builder->orderBy('restaurants.rating', 'DESC')
                      ->get()
                      ->getResultArray();
    }

    // Получить все активные рестораны с городами
    public function getAllActive($limit = null)
    {
        $builder = $this->select('restaurants.*, cities.name as city_name')
                       ->join('cities', 'cities.id = restaurants.city_id')
                       ->where('restaurants.is_active', 1)
                       ->orderBy('restaurants.rating', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    // Получить топ рестораны
    public function getTopRated($limit = 10)
    {
        return $this->select('restaurants.*, cities.name as city_name')
                   ->join('cities', 'cities.id = restaurants.city_id')
                   ->where('restaurants.is_active', 1)
                   ->where('restaurants.rating >=', 4.0)
                   ->orderBy('restaurants.rating', 'DESC')
                   ->orderBy('restaurants.name', 'ASC')
                   ->limit($limit)
                   ->findAll();
    }

    // Получить ресторан по ID с информацией о городе
    public function getRestaurantWithCity($id)
    {
        return $this->select('restaurants.*, cities.name as city_name')
                   ->join('cities', 'cities.id = restaurants.city_id')
                   ->where('restaurants.id', $id)
                   ->where('restaurants.is_active', 1)
                   ->first();
    }

    // Получить рестораны по уровню цен
    public function getByPriceLevel($priceLevel)
    {
        return $this->select('restaurants.*, cities.name as city_name')
                   ->join('cities', 'cities.id = restaurants.city_id')
                   ->where('restaurants.price_level', $priceLevel)
                   ->where('restaurants.is_active', 1)
                   ->orderBy('restaurants.rating', 'DESC')
                   ->findAll();
    }
}