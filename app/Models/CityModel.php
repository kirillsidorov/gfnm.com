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
    
    // ОБНОВЛЕНО: добавлен slug в allowedFields
    protected $allowedFields = [
        'name', 'state', 'country', 'slug', 'seo_url', 'latitude', 'longitude', 'last_api_sync'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // ОБНОВЛЕНО: добавлена валидация для slug
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'state' => 'permit_empty|max_length[100]',
        'country' => 'permit_empty|max_length[100]',
        'slug' => 'permit_empty|max_length[100]|regex_match[/^[a-z0-9-]*$/]'
    ];

    protected $validationMessages = [
        'slug.regex_match' => 'Slug can only contain lowercase letters, numbers, and hyphens'
    ];

    // СОХРАНЕНО: ваши существующие методы
    
    /**
     * Получить активные города
     */
    //public function getActiveCities()
    //{
    //    return $this->where('is_active', 1)
    //              ->orderBy('name', 'ASC')
    //               ->findAll();
    //}

    /**
     * Получить города с количеством ресторанов (обновлено для админки)
     */
    public function getCitiesWithRestaurantCount()
    {
        return $this->select('cities.*, COUNT(restaurants.id) as restaurant_count')
                   ->join('restaurants', 'restaurants.city_id = cities.id AND restaurants.is_active = 1', 'left')
                   ->groupBy('cities.id')
                   ->orderBy('cities.name', 'ASC') // Изменено: сортировка по имени для админки
                   ->findAll();
    }

    /**
     * Поиск городов
     */
    public function searchCities($query)
    {
        return $this->like('name', $query)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    // НОВЫЕ МЕТОДЫ для работы с slug

    /**
     * Найти город по slug
     */
    public function findBySlug($slug)
    {
        return $this->where('slug', $slug)
                   ->first();
    }

    /**
     * Получить города по стране
     */
    public function getByCountry($country)
    {
        return $this->where('country', $country)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function getBySeoURL($seo_url)
    {
        return $this->where('seo_url', $seo_url)
        ->orderBy('name','ASC')->findAll();

    }

    /**
     * Получить города по штату
     */
    public function getByState($state)
    {
        return $this->where('state', $state)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Проверить уникальность slug
     */
    public function isSlugUnique($slug, $excludeId = null)
    {
        $builder = $this->where('slug', $slug);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() === 0;
    }

    /**
     * Генерация slug из названия города
     */
    public function generateSlug($name)
    {
        $baseSlug = strtolower(trim($name));
        $baseSlug = preg_replace('/[^a-z0-9\s-]/', '', $baseSlug);
        $baseSlug = preg_replace('/\s+/', '-', $baseSlug);
        $baseSlug = preg_replace('/-+/', '-', $baseSlug);
        $baseSlug = trim($baseSlug, '-');
        
        $slug = $baseSlug;
        $counter = 1;
        
        // Проверяем уникальность
        while (!$this->isSlugUnique($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Получить города без slug
     */
    public function getCitiesWithoutSlug()
    {
        return $this->where('(slug IS NULL OR slug = "")')
                   ->findAll();
    }

    /**
     * Получить активные города с ресторанами для публичного сайта
     */
    public function getActiveCitiesWithRestaurants()
    {
        return $this->select('cities.*, COUNT(restaurants.id) as restaurant_count')
                   ->join('restaurants', 'restaurants.city_id = cities.id AND restaurants.is_active = 1', 'left')
                   ->groupBy('cities.id')
                   ->having('restaurant_count >', 0)
                   ->orderBy('restaurant_count', 'DESC')
                   ->findAll();
    }

    /**
     * Расширенный поиск городов (включая slug и штат)
     */
    public function searchCitiesExtended($query)
    {
        return $this->groupStart()
                   ->like('name', $query)
                   ->orLike('state', $query)
                   ->orLike('country', $query)
                   ->orLike('slug', $query)
                   ->groupEnd()
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    // CALLBACKS для автоматической генерации slug

    protected $beforeInsert = ['generateSlugIfEmpty'];
    protected $beforeUpdate = ['generateSlugIfEmpty'];

    /**
     * Автоматическая генерация slug если не указан
     */
    protected function generateSlugIfEmpty(array $data)
    {
        if (isset($data['data']['name']) && empty($data['data']['slug'])) {
            $data['data']['slug'] = $this->generateSlug($data['data']['name']);
        }
        
        return $data;
    }
}