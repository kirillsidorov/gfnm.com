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
    
    // ОБНОВЛЕННЫЕ allowedFields с новыми полями DataForSEO
    protected $allowedFields = [
        // Основные поля (существующие)
        'name', 'original_title', 'slug', 'seo_url', 'description', 'category',
        'address', 'phone', 'website', 'rating', 'price_level', 'google_place_id', 
        'hours', 'is_active', 'city_id', 'latitude', 'longitude', 'is_georgian',
        
        // Новые идентификаторы DataForSEO
        'cid', 'feature_id',
        
        // Расширенная адресная информация
        'address_borough', 'address_city', 'address_zip', 'address_region', 'address_country_code',
        
        // Расширенные категории
        'category_ids', 'additional_categories',
        
        // Расширенные рейтинги
        'rating_count', 'rating_type', 'rating_distribution',
        
        // Статус и цены
        'current_status',
        
        // Медиа и контент
        'domain', 'logo_url', 'main_image_url', 'total_photos', 'snippet',
        
        // Время и популярность
        'work_hours', 'popular_times',
        
        // Атрибуты и услуги
        'attributes', 'service_options', 'accessibility_options', 'dining_options',
        'atmosphere', 'crowd_info', 'payment_options',
        
        // Связанные данные
        'people_also_search', 'place_topics', 'business_links',
        
        // API метаданные
        'check_url', 'data_source', 'last_updated_api', 'first_seen_api'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // ОБНОВЛЕННАЯ валидация
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'address' => 'permit_empty|min_length[10]',
        'phone' => 'permit_empty|regex_match[/^[\+\d\s\-\(\)]+$/]',
        'website' => 'permit_empty|valid_url',
        'rating' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[5]',
        'price_level' => 'permit_empty|max_length[20]',
        'google_place_id' => 'permit_empty|is_unique[restaurants.google_place_id,id,{id}]',
        'cid' => 'permit_empty|is_unique[restaurants.cid,id,{id}]',
        'latitude' => 'permit_empty|decimal',
        'longitude' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Название ресторана обязательно',
            'min_length' => 'Название должно содержать минимум 3 символа'
        ],
        'google_place_id' => [
            'is_unique' => 'Ресторан с таким Google Place ID уже существует'
        ],
        'cid' => [
            'is_unique' => 'Ресторан с таким CID уже существует'
        ]
    ];

    // Callbacks для автоматической обработки
    protected $beforeInsert = ['generateSeoUrl', 'processJsonFields'];
    protected $beforeUpdate = ['generateSeoUrl', 'processJsonFields'];
    protected $afterFind = ['decodeJsonFields'];

    // ==========================================
    // МЕТОДЫ ДЛЯ РАБОТЫ С DATAФОРСEO ДАННЫМИ
    // ==========================================

    /**
     * Поиск ресторана по внешним идентификаторам
     */
    public function findByExternalId($identifier, $type = 'auto')
    {
        if ($type === 'auto') {
            // Автоматически определяем тип идентификатора
            if (strpos($identifier, 'ChIJ') === 0) {
                $type = 'place_id';
            } elseif (is_numeric($identifier)) {
                $type = 'cid';
            } elseif (strpos($identifier, '0x') === 0) {
                $type = 'feature_id';
            }
        }
        
        switch ($type) {
            case 'place_id':
                return $this->where('google_place_id', $identifier)->first();
            case 'cid':
                return $this->where('cid', $identifier)->first();
            case 'feature_id':
                return $this->where('feature_id', $identifier)->first();
            default:
                return null;
        }
    }

    /**
     * Поиск ресторанов рядом с указанными координатами
     */
    public function findNearby($latitude, $longitude, $radius = 25, $limit = 20, $filters = [])
    {
        $sql = "
            SELECT r.*, c.name as city_name, c.slug as city_slug,
            (6371 * acos(cos(radians(?)) * cos(radians(r.latitude)) * 
            cos(radians(r.longitude) - radians(?)) + sin(radians(?)) * 
            sin(radians(r.latitude)))) AS distance
            FROM restaurants r
            LEFT JOIN cities c ON r.city_id = c.id
            WHERE r.is_active = 1
            AND (r.is_georgian IS NULL OR r.is_georgian = 1)
            AND r.latitude IS NOT NULL 
            AND r.longitude IS NOT NULL
        ";
        
        $params = [$latitude, $longitude, $latitude];
        
        // Добавляем фильтры
        if (!empty($filters['category'])) {
            $sql .= " AND r.category LIKE ?";
            $params[] = '%' . $filters['category'] . '%';
        }
        
        if (!empty($filters['min_rating'])) {
            $sql .= " AND r.rating >= ?";
            $params[] = $filters['min_rating'];
        }
        
        if (!empty($filters['price_level'])) {
            $sql .= " AND r.price_level = ?";
            $params[] = $filters['price_level'];
        }
        
        if (!empty($filters['current_status'])) {
            $sql .= " AND r.current_status = ?";
            $params[] = $filters['current_status'];
        }
        
        $sql .= "
            HAVING distance <= ?
            ORDER BY distance ASC, r.rating DESC
            LIMIT ?
        ";
        
        $params[] = $radius;
        $params[] = $limit;
        
        $query = $this->db->query($sql, $params);
        return $query->getResultArray();
    }

    /**
     * Поиск по атрибутам (с использованием JSON полей)
     */
    public function searchByAttributes($attributes, $requireAll = false, $limit = 20)
    {
        $builder = $this->select('restaurants.*, cities.name as city_name')
                       ->join('cities', 'cities.id = restaurants.city_id', 'left')
                       ->where('restaurants.is_active', 1);
        
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }
        
        if ($requireAll) {
            // Все атрибуты должны присутствовать
            foreach ($attributes as $attr) {
                $builder->where("JSON_SEARCH(restaurants.service_options, 'one', ?) IS NOT NULL", $attr)
                       ->orWhere("JSON_SEARCH(restaurants.dining_options, 'one', ?) IS NOT NULL", $attr)
                       ->orWhere("JSON_SEARCH(restaurants.accessibility_options, 'one', ?) IS NOT NULL", $attr);
            }
        } else {
            // Хотя бы один атрибут должен присутствовать
            $builder->groupStart();
            foreach ($attributes as $attr) {
                $builder->orWhere("JSON_SEARCH(restaurants.service_options, 'one', ?) IS NOT NULL", $attr)
                       ->orWhere("JSON_SEARCH(restaurants.dining_options, 'one', ?) IS NOT NULL", $attr)
                       ->orWhere("JSON_SEARCH(restaurants.accessibility_options, 'one', ?) IS NOT NULL", $attr);
            }
            $builder->groupEnd();
        }
        
        return $builder->orderBy('restaurants.rating', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Расширенный поиск с фильтрами
     */
    public function advancedSearch($params = [])
    {
        $builder = $this->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
                       ->join('cities', 'cities.id = restaurants.city_id', 'left')
                       ->where('restaurants.is_active', 1)
                       ->where('(restaurants.is_georgian IS NULL OR restaurants.is_georgian = 1)');
        
        // Текстовый поиск
        if (!empty($params['search'])) {
            $search = $params['search'];
            $builder->groupStart()
                   ->like('restaurants.name', $search)
                   ->orLike('restaurants.description', $search)
                   ->orLike('restaurants.category', $search)
                   ->orLike('restaurants.address', $search)
                   ->groupEnd();
        }
        
        // Фильтр по городу
        if (!empty($params['city_id'])) {
            $builder->where('restaurants.city_id', $params['city_id']);
        }
        
        // Фильтр по категории
        if (!empty($params['category'])) {
            $builder->like('restaurants.category', $params['category']);
        }
        
        // Фильтр по рейтингу
        if (!empty($params['min_rating'])) {
            $builder->where('restaurants.rating >=', $params['min_rating']);
        }
        
        // Фильтр по уровню цен
        if (!empty($params['price_level'])) {
            $builder->where('restaurants.price_level', $params['price_level']);
        }
        
        // Фильтр по статусу
        if (!empty($params['status'])) {
            $builder->where('restaurants.current_status', $params['status']);
        }
        
        // Фильтр по источнику данных
        if (!empty($params['data_source'])) {
            $builder->where('restaurants.data_source', $params['data_source']);
        }
        
        // Сортировка
        $orderBy = $params['order_by'] ?? 'rating';
        switch ($orderBy) {
            case 'name':
                $builder->orderBy('restaurants.name', 'ASC');
                break;
            case 'rating':
                $builder->orderBy('restaurants.rating', 'DESC');
                $builder->orderBy('restaurants.rating_count', 'DESC');
                break;
            case 'newest':
                $builder->orderBy('restaurants.created_at', 'DESC');
                break;
            case 'updated':
                $builder->orderBy('restaurants.updated_at', 'DESC');
                break;
            default:
                $builder->orderBy('restaurants.rating', 'DESC');
        }
        
        // Лимиты
        $limit = $params['limit'] ?? 20;
        $offset = $params['offset'] ?? 0;
        $builder->limit($limit, $offset);
        
        return $builder->get()->getResultArray();
    }

    // ==========================================
    // СУЩЕСТВУЮЩИЕ МЕТОДЫ (ОБНОВЛЕННЫЕ)
    // ==========================================

    public function getByCity($cityId)
    {
        return $this->select('restaurants.*, cities.name as city_name')
                   ->join('cities', 'cities.id = restaurants.city_id')
                   ->where('restaurants.city_id', $cityId)
                   ->where('restaurants.is_active', 1)
                   ->where('(restaurants.is_georgian IS NULL OR restaurants.is_georgian = 1)')
                   ->orderBy('restaurants.rating', 'DESC')
                   ->orderBy('restaurants.rating_count', 'DESC')
                   ->findAll();
    }

    public function search($query, $cityId = null)
    {
        $builder = $this->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
                       ->join('cities', 'cities.id = restaurants.city_id');
        
        $builder->where('restaurants.is_active', 1)
                ->where('(restaurants.is_georgian IS NULL OR restaurants.is_georgian = 1)');
        
        if ($cityId) {
            $builder->where('restaurants.city_id', $cityId);
        }
        
        $builder->groupStart()
                ->like('restaurants.name', $query)
                ->orLike('restaurants.description', $query)
                ->orLike('restaurants.address', $query)
                ->orLike('restaurants.category', $query)
                ->groupEnd();
        
        $results = $builder->orderBy('restaurants.rating', 'DESC')
                          ->orderBy('restaurants.rating_count', 'DESC')
                          ->get()
                          ->getResultArray();
        
        // Убеждаемся что у каждого ресторана есть seo_url
        foreach ($results as &$restaurant) {
            if (empty($restaurant['seo_url']) && !empty($restaurant['slug']) && !empty($restaurant['city_slug'])) {
                $restaurant['seo_url'] = $restaurant['slug'] . '-restaurant-' . $restaurant['city_slug'];
                $this->update($restaurant['id'], ['seo_url' => $restaurant['seo_url']]);
            }
        }
        
        return $results;
    }

    public function getAllActive($limit = null)
    {
        $builder = $this->select('restaurants.*, cities.name as city_name')
                       ->join('cities', 'cities.id = restaurants.city_id')
                       ->where('restaurants.is_active', 1)
                       ->where('(restaurants.is_georgian IS NULL OR restaurants.is_georgian = 1)')
                       ->orderBy('restaurants.rating', 'DESC')
                       ->orderBy('restaurants.rating_count', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get()->getResultArray();
    }

    public function getTopRated($limit = 10)
    {
        return $this->select('restaurants.*, cities.name as city_name')
                   ->join('cities', 'cities.id = restaurants.city_id')
                   ->where('restaurants.is_active', 1)
                   ->where('(restaurants.is_georgian IS NULL OR restaurants.is_georgian = 1)')
                   ->where('restaurants.rating >=', 4.0)
                   ->orderBy('restaurants.rating', 'DESC')
                   ->orderBy('restaurants.rating_count', 'DESC')
                   ->orderBy('restaurants.name', 'ASC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getRestaurantWithCity($id)
    {
        return $this->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
                   ->join('cities', 'cities.id = restaurants.city_id')
                   ->where('restaurants.id', $id)
                   ->where('restaurants.is_active', 1)
                   ->first();
    }

    public function getRestaurantWithCityBySlug($restaurantSlug, $cityId)
    {
        return $this->select('restaurants.*, cities.name as city_name, cities.state, cities.slug as city_slug')
                    ->join('cities', 'cities.id = restaurants.city_id')
                    ->where('restaurants.slug', $restaurantSlug)
                    ->where('restaurants.city_id', $cityId)
                    ->where('restaurants.is_active', 1)
                    ->first();
    }

    public function getBySeoUrl($seoUrl)
    {
        return $this->select('restaurants.*, cities.name as city_name, cities.state, cities.slug as city_slug')
                   ->join('cities', 'cities.id = restaurants.city_id')
                   ->where('restaurants.seo_url', $seoUrl)
                   ->first();
    }

    public function getByPriceLevel($priceLevel)
    {
        return $this->select('restaurants.*, cities.name as city_name')
                   ->join('cities', 'cities.id = restaurants.city_id')
                   ->where('restaurants.price_level', $priceLevel)
                   ->where('restaurants.is_active', 1)
                   ->orderBy('restaurants.rating', 'DESC')
                   ->orderBy('restaurants.rating_count', 'DESC')
                   ->findAll();
    }

    // ==========================================
    // ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    // ==========================================

    /**
     * Автоматическая генерация SEO URL
     */
    protected function generateSeoUrl(array $data)
    {
        if (isset($data['data']['slug']) && isset($data['data']['city_id'])) {
            $cityModel = new \App\Models\CityModel();
            $city = $cityModel->find($data['data']['city_id']);
            
            if ($city && !empty($city['slug'])) {
                $data['data']['seo_url'] = $data['data']['slug'] . '-restaurant-' . $city['slug'];
            }
        }
        
        return $data;
    }

    /**
     * Обработка JSON полей перед сохранением
     */
    protected function processJsonFields(array $data)
    {
        $jsonFields = [
            'category_ids', 'additional_categories', 'rating_distribution',
            'work_hours', 'popular_times', 'attributes', 'service_options',
            'accessibility_options', 'dining_options', 'atmosphere',
            'crowd_info', 'payment_options', 'people_also_search',
            'place_topics', 'business_links'
        ];
        
        foreach ($jsonFields as $field) {
            if (isset($data['data'][$field]) && !is_string($data['data'][$field])) {
                $data['data'][$field] = json_encode($data['data'][$field]);
            }
        }
        
        return $data;
    }

    /**
     * Декодирование JSON полей после получения
     */
    protected function decodeJsonFields(array $data)
    {
        $jsonFields = [
            'category_ids', 'additional_categories', 'rating_distribution',
            'work_hours', 'popular_times', 'attributes', 'service_options',
            'accessibility_options', 'dining_options', 'atmosphere',
            'crowd_info', 'payment_options', 'people_also_search',
            'place_topics', 'business_links'
        ];
        
        if (isset($data['data'])) {
            foreach ($jsonFields as $field) {
                if (isset($data['data'][$field]) && is_string($data['data'][$field])) {
                    $decoded = json_decode($data['data'][$field], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data['data'][$field] = $decoded;
                    }
                }
            }
        }
        
        return $data;
    }

    /**
     * Получение статистики по ресторанам
     */
    public function getStats()
    {
        $total = $this->where('is_active', 1)->countAllResults();
        $withDataForSEO = $this->where('is_active', 1)->where('data_source', 'DataForSEO')->countAllResults();
        $withRatings = $this->where('is_active', 1)->where('rating >', 0)->countAllResults();
        $withPhotos = $this->where('is_active', 1)->where('total_photos >', 0)->countAllResults();
        
        return [
            'total_active' => $total,
            'with_dataforseo' => $withDataForSEO,
            'with_ratings' => $withRatings,
            'with_photos' => $withPhotos,
            'manual_entries' => $total - $withDataForSEO
        ];
    }
}