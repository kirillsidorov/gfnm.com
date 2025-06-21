<?php

namespace App\Services;

class DataForSeoImportService
{
    private $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Импорт данных Chama Mama из JSON ответа DataForSEO
     */
    public function importChamaMamaData($jsonData)
    {
        $imported = 0;
        $updated = 0;
        $errors = [];
        
        try {
            // Парсим JSON данные
            if (is_string($jsonData)) {
                $data = json_decode($jsonData, true);
            } else {
                $data = $jsonData;
            }
            
            if (!$data || !isset($data['result'])) {
                throw new \Exception('Invalid JSON data structure');
            }
            
            // Обрабатываем каждый ресторан
            foreach ($data['result'] as $resultSet) {
                if (isset($resultSet['items'])) {
                    foreach ($resultSet['items'] as $item) {
                        try {
                            $result = $this->importSingleRestaurant($item);
                            if ($result['action'] === 'created') {
                                $imported++;
                            } elseif ($result['action'] === 'updated') {
                                $updated++;
                            }
                        } catch (\Exception $e) {
                            $errors[] = [
                                'restaurant' => $item['title'] ?? 'Unknown',
                                'error' => $e->getMessage()
                            ];
                        }
                    }
                }
            }
            
            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
                'message' => "Successfully processed {$imported} new and {$updated} updated restaurants"
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Импорт одного ресторана
     */
    private function importSingleRestaurant($item)
    {
        // Проверяем существование по Google Place ID или CID
        $existing = $this->findExistingRestaurant($item);
        
        // Подготавливаем данные для вставки/обновления
        $restaurantData = $this->prepareRestaurantData($item);
        
        if ($existing) {
            // Обновляем существующий ресторан
            $this->db->table('restaurants')
                     ->where('id', $existing->id)
                     ->update($restaurantData);
            
            $restaurantId = $existing->id;
            $action = 'updated';
        } else {
            // Создаем новый ресторан
            $this->db->table('restaurants')->insert($restaurantData);
            $restaurantId = $this->db->insertID();
            $action = 'created';
        }
        
        // Обновляем атрибуты
        $this->updateRestaurantAttributes($restaurantId, $item);
        
        // Обновляем часы работы
        $this->updateRestaurantHours($restaurantId, $item);
        
        // Обновляем популярные времена
        $this->updatePopularTimes($restaurantId, $item);
        
        // Обновляем связанные рестораны
        $this->updateRelatedRestaurants($restaurantId, $item);
        
        // Обновляем топики
        $this->updateRestaurantTopics($restaurantId, $item);
        
        return ['action' => $action, 'id' => $restaurantId];
    }
    
    /**
     * Поиск существующего ресторана
     */
    private function findExistingRestaurant($item)
    {
        // Сначала ищем по Google Place ID
        if (!empty($item['place_id'])) {
            $existing = $this->db->table('restaurants')
                               ->where('google_place_id', $item['place_id'])
                               ->get()
                               ->getRow();
            if ($existing) return $existing;
        }
        
        // Затем по CID
        if (!empty($item['cid'])) {
            $existing = $this->db->table('restaurants')
                               ->where('cid', $item['cid'])
                               ->get()
                               ->getRow();
            if ($existing) return $existing;
        }
        
        // Наконец по названию и адресу (приблизительно)
        if (!empty($item['title']) && !empty($item['address'])) {
            $existing = $this->db->table('restaurants')
                               ->like('name', $item['title'])
                               ->like('address', substr($item['address'], 0, 50))
                               ->get()
                               ->getRow();
            if ($existing) return $existing;
        }
        
        return null;
    }
    
    /**
     * Подготовка данных ресторана
     */
    private function prepareRestaurantData($item)
    {
        $addressInfo = $item['address_info'] ?? [];
        
        return [
            // Основная информация
            'name' => $item['title'] ?? '',
            'original_title' => $item['original_title'] ?? null,
            'description' => $item['description'] ?? '',
            'category' => $item['category'] ?? 'Georgian restaurant',
            'category_ids' => json_encode($item['category_ids'] ?? []),
            'additional_categories' => json_encode($item['additional_categories'] ?? []),
            
            // Идентификаторы
            'google_place_id' => $item['place_id'] ?? null,
            'cid' => $item['cid'] ?? null,
            'feature_id' => $item['feature_id'] ?? null,
            
            // Адрес (детализированный)
            'address' => $item['address'] ?? '',
            'address_borough' => $addressInfo['borough'] ?? null,
            'address_city' => $addressInfo['city'] ?? null,
            'address_zip' => $addressInfo['zip'] ?? null,
            'address_region' => $addressInfo['region'] ?? null,
            'address_country_code' => $addressInfo['country_code'] ?? null,
            
            // Геолокация
            'latitude' => $item['latitude'] ?? null,
            'longitude' => $item['longitude'] ?? null,
            
            // Контакты
            'phone' => $item['phone'] ?? null,
            'website' => $item['url'] ?? null,
            'domain' => $item['domain'] ?? null,
            
            // Рейтинги
            'rating' => $item['rating']['value'] ?? 0,
            'rating_count' => $item['rating']['votes_count'] ?? 0,
            'rating_type' => $item['rating']['rating_type'] ?? 'Max5',
            'rating_distribution' => json_encode($item['rating_distribution'] ?? null),
            
            // Статус и цены
            'price_level' => $item['price_level'] ?? null,
            'current_status' => $this->mapWorkStatus($item),
            
            // Медиа
            'logo_url' => $item['logo'] ?? null,
            'main_image_url' => $item['main_image'] ?? null,
            'total_photos' => $item['total_photos'] ?? 0,
            'snippet' => $item['snippet'] ?? null,
            
            // JSON данные
            'work_hours' => json_encode($item['work_time'] ?? null),
            'popular_times' => json_encode($item['popular_times'] ?? null),
            'attributes' => json_encode($item['attributes'] ?? null),
            'people_also_search' => json_encode($item['people_also_search'] ?? null),
            'place_topics' => json_encode($item['place_topics'] ?? null),
            'business_links' => json_encode($item['local_business_links'] ?? null),
            
            // Структурированные атрибуты (для поиска)
            'service_options' => json_encode($item['attributes']['available_attributes']['service_options'] ?? []),
            'accessibility_options' => json_encode($item['attributes']['available_attributes']['accessibility'] ?? []),
            'dining_options' => json_encode($item['attributes']['available_attributes']['dining_options'] ?? []),
            'atmosphere' => json_encode($item['attributes']['available_attributes']['atmosphere'] ?? []),
            'crowd_info' => json_encode($item['attributes']['available_attributes']['crowd'] ?? []),
            'payment_options' => json_encode($item['attributes']['available_attributes']['payments'] ?? []),
            
            // API метаданные
            'check_url' => $item['check_url'] ?? null,
            'data_source' => 'DataForSEO',
            'last_updated_api' => $item['last_updated_time'] ?? date('Y-m-d H:i:s'),
            'first_seen_api' => $item['first_seen'] ?? date('Y-m-d H:i:s'),
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Обновление структурированных атрибутов
     */
    private function updateRestaurantAttributes($restaurantId, $item)
    {
        // Удаляем старые атрибуты
        $this->db->table('restaurant_attributes')
                 ->where('restaurant_id', $restaurantId)
                 ->delete();
        
        if (!isset($item['attributes']['available_attributes'])) {
            return;
        }
        
        $attributes = $item['attributes']['available_attributes'];
        $batch = [];
        
        // Добавляем доступные атрибуты
        foreach ($attributes as $category => $items) {
            foreach ($items as $attributeName) {
                $batch[] = [
                    'restaurant_id' => $restaurantId,
                    'attribute_category' => $category,
                    'attribute_name' => $attributeName,
                    'is_available' => 1
                ];
            }
        }
        
        // Добавляем недоступные атрибуты
        if (isset($item['attributes']['unavailable_attributes'])) {
            $unavailableAttributes = $item['attributes']['unavailable_attributes'];
            foreach ($unavailableAttributes as $category => $items) {
                foreach ($items as $attributeName) {
                    $batch[] = [
                        'restaurant_id' => $restaurantId,
                        'attribute_category' => $category,
                        'attribute_name' => $attributeName,
                        'is_available' => 0
                    ];
                }
            }
        }
        
        if (!empty($batch)) {
            $this->db->table('restaurant_attributes')->insertBatch($batch);
        }
    }
    
    /**
     * Обновление часов работы
     */
    private function updateRestaurantHours($restaurantId, $item)
    {
        // Удаляем старые часы
        $this->db->table('restaurant_hours')
                 ->where('restaurant_id', $restaurantId)
                 ->delete();
        
        if (!isset($item['work_time']['work_hours']['timetable'])) {
            return;
        }
        
        $timetable = $item['work_time']['work_hours']['timetable'];
        $batch = [];
        
        $dayMapping = [
            'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
            'thursday' => 4, 'friday' => 5, 'saturday' => 6
        ];
        
        foreach ($dayMapping as $dayName => $dayNumber) {
            if (isset($timetable[$dayName]) && $timetable[$dayName]) {
                foreach ($timetable[$dayName] as $period) {
                    $openTime = sprintf('%02d:%02d:00', $period['open']['hour'], $period['open']['minute']);
                    $closeTime = sprintf('%02d:%02d:00', $period['close']['hour'], $period['close']['minute']);
                    
                    $batch[] = [
                        'restaurant_id' => $restaurantId,
                        'day_of_week' => $dayNumber,
                        'open_time' => $openTime,
                        'close_time' => $closeTime,
                        'is_closed' => 0
                    ];
                }
            } else {
                // День закрыт
                $batch[] = [
                    'restaurant_id' => $restaurantId,
                    'day_of_week' => $dayNumber,
                    'open_time' => null,
                    'close_time' => null,
                    'is_closed' => 1
                ];
            }
        }
        
        if (!empty($batch)) {
            $this->db->table('restaurant_hours')->insertBatch($batch);
        }
    }
    
    /**
     * Обновление популярных времен
     */
    private function updatePopularTimes($restaurantId, $item)
    {
        // Удаляем старые данные
        $this->db->table('restaurant_popular_times')
                 ->where('restaurant_id', $restaurantId)
                 ->delete();
        
        if (!isset($item['popular_times']['popular_times_by_days'])) {
            return;
        }
        
        $popularTimes = $item['popular_times']['popular_times_by_days'];
        $batch = [];
        
        $dayMapping = [
            'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
            'thursday' => 4, 'friday' => 5, 'saturday' => 6
        ];
        
        foreach ($dayMapping as $dayName => $dayNumber) {
            if (isset($popularTimes[$dayName])) {
                foreach ($popularTimes[$dayName] as $timeSlot) {
                    $batch[] = [
                        'restaurant_id' => $restaurantId,
                        'day_of_week' => $dayNumber,
                        'hour' => $timeSlot['time']['hour'],
                        'popularity_index' => $timeSlot['popular_index']
                    ];
                }
            }
        }
        
        if (!empty($batch)) {
            $this->db->table('restaurant_popular_times')->insertBatch($batch);
        }
    }
    
    /**
     * Обновление связанных ресторанов
     */
    private function updateRelatedRestaurants($restaurantId, $item)
    {
        // Удаляем старые связи
        $this->db->table('restaurant_relations')
                 ->where('restaurant_id', $restaurantId)
                 ->delete();
        
        if (!isset($item['people_also_search'])) {
            return;
        }
        
        $batch = [];
        foreach ($item['people_also_search'] as $related) {
            $batch[] = [
                'restaurant_id' => $restaurantId,
                'related_cid' => $related['cid'] ?? '',
                'related_name' => $related['title'] ?? '',
                'related_rating' => $related['rating']['value'] ?? null,
                'related_rating_count' => $related['rating']['votes_count'] ?? null,
                'relation_type' => 'people_also_search'
            ];
        }
        
        if (!empty($batch)) {
            $this->db->table('restaurant_relations')->insertBatch($batch);
        }
    }
    
    /**
     * Обновление топиков ресторана
     */
    private function updateRestaurantTopics($restaurantId, $item)
    {
        // Удаляем старые топики
        $this->db->table('restaurant_topics')
                 ->where('restaurant_id', $restaurantId)
                 ->delete();
        
        if (!isset($item['place_topics'])) {
            return;
        }
        
        $batch = [];
        foreach ($item['place_topics'] as $topic => $count) {
            $batch[] = [
                'restaurant_id' => $restaurantId,
                'topic' => $topic,
                'mention_count' => $count
            ];
        }
        
        if (!empty($batch)) {
            $this->db->table('restaurant_topics')->insertBatch($batch);
        }
    }
    
    /**
     * Маппинг статуса работы
     */
    private function mapWorkStatus($item)
    {
        $workStatus = $item['work_time']['work_hours']['current_status'] ?? 'open';
        
        switch (strtolower($workStatus)) {
            case 'open':
                return 'open';
            case 'close':
            case 'closed':
                return 'closed';
            case 'closed_forever':
            case 'permanently_closed':
                return 'permanently_closed';
            case 'temporarily_closed':
                return 'temporarily_closed';
            default:
                return 'open';
        }
    }
    
    /**
     * Получение детализированной информации о ресторане
     */
    public function getRestaurantDetails($restaurantId)
    {
        // Основная информация
        $restaurant = $this->db->table('restaurants')
                              ->where('id', $restaurantId)
                              ->get()
                              ->getRowArray();
        
        if (!$restaurant) {
            return null;
        }
        
        // Атрибуты с иконками
        $attributes = $this->getRestaurantAttributesWithIcons($restaurantId);
        
        // Часы работы
        $hours = $this->db->table('restaurant_hours')
                         ->where('restaurant_id', $restaurantId)
                         ->orderBy('day_of_week')
                         ->get()
                         ->getResultArray();
        
        // Популярные времена
        $popularTimes = $this->db->table('restaurant_popular_times')
                               ->where('restaurant_id', $restaurantId)
                               ->orderBy('day_of_week, hour')
                               ->get()
                               ->getResultArray();
        
        // Связанные рестораны
        $relatedRestaurants = $this->db->table('restaurant_relations')
                                     ->where('restaurant_id', $restaurantId)
                                     ->get()
                                     ->getResultArray();
        
        // Топики
        $topics = $this->db->table('restaurant_topics')
                          ->where('restaurant_id', $restaurantId)
                          ->orderBy('mention_count', 'DESC')
                          ->get()
                          ->getResultArray();
        
        return [
            'restaurant' => $restaurant,
            'attributes' => $attributes,
            'hours' => $hours,
            'popular_times' => $popularTimes,
            'related_restaurants' => $relatedRestaurants,
            'topics' => $topics
        ];
    }
    
    /**
     * Получение атрибутов с иконками
     */
    private function getRestaurantAttributesWithIcons($restaurantId)
    {
        $query = "
            SELECT 
                ra.attribute_category,
                ra.attribute_name,
                ra.is_available,
                ad.display_name,
                ad.icon,
                ad.description,
                ad.sort_order
            FROM restaurant_attributes ra
            LEFT JOIN attribute_definitions ad ON ra.attribute_category = ad.category 
                AND ra.attribute_name = ad.name
            WHERE ra.restaurant_id = ?
            ORDER BY ad.sort_order, ra.attribute_category, ra.attribute_name
        ";
        
        $results = $this->db->query($query, [$restaurantId])->getResultArray();
        
        $attributes = [];
        foreach ($results as $row) {
            $category = $row['attribute_category'];
            if (!isset($attributes[$category])) {
                $attributes[$category] = [
                    'category_name' => ucfirst(str_replace('_', ' ', $category)),
                    'attributes' => []
                ];
            }
            
            $attributes[$category]['attributes'][] = [
                'name' => $row['attribute_name'],
                'display_name' => $row['display_name'] ?: ucfirst(str_replace('_', ' ', $row['attribute_name'])),
                'icon' => $row['icon'] ?: '✓',
                'description' => $row['description'],
                'is_available' => (bool)$row['is_available']
            ];
        }
        
        return $attributes;
    }
    
    /**
     * Поиск ресторанов с расширенными фильтрами
     */
    public function searchRestaurants($filters = [])
    {
        $builder = $this->db->table('restaurants r');
        $builder->select('r.*, COUNT(DISTINCT ra.id) as matching_attributes');
        $builder->join('restaurant_attributes ra', 'r.id = ra.restaurant_id', 'left');
        $builder->where('r.is_active', 1);
        
        // Фильтр по тексту
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                   ->like('r.name', $search)
                   ->orLike('r.description', $search)
                   ->orLike('r.category', $search)
                   ->groupEnd();
        }
        
        // Фильтр по городу
        if (!empty($filters['city'])) {
            $builder->like('r.address_city', $filters['city']);
        }
        
        // Фильтр по категории
        if (!empty($filters['category'])) {
            $builder->like('r.category', $filters['category']);
        }
        
        // Фильтр по рейтингу
        if (!empty($filters['min_rating'])) {
            $builder->where('r.rating >=', $filters['min_rating']);
        }
        
        // Фильтр по атрибутам
        if (!empty($filters['attributes'])) {
            $attributes = is_array($filters['attributes']) ? $filters['attributes'] : explode(',', $filters['attributes']);
            $builder->whereIn('ra.attribute_name', $attributes);
            $builder->where('ra.is_available', 1);
        }
        
        // Фильтр по статусу
        if (!empty($filters['status'])) {
            $builder->where('r.current_status', $filters['status']);
        }
        
        // Геолокационный поиск
        if (!empty($filters['lat']) && !empty($filters['lng'])) {
            $lat = $filters['lat'];
            $lng = $filters['lng'];
            $radius = $filters['radius'] ?? 25; // км
            
            $builder->select('r.*, COUNT(DISTINCT ra.id) as matching_attributes, 
                (6371 * acos(cos(radians(?)) * cos(radians(r.latitude)) * 
                cos(radians(r.longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(r.latitude)))) AS distance', false);
            $builder->having('distance <=', $radius);
            $builder->orderBy('distance', 'ASC');
        }
        
        $builder->groupBy('r.id');
        
        // Сортировка
        $orderBy = $filters['order_by'] ?? 'rating';
        switch ($orderBy) {
            case 'rating':
                $builder->orderBy('r.rating', 'DESC');
                $builder->orderBy('r.rating_count', 'DESC');
                break;
            case 'name':
                $builder->orderBy('r.name', 'ASC');
                break;
            case 'newest':
                $builder->orderBy('r.created_at', 'DESC');
                break;
            case 'attributes':
                $builder->orderBy('matching_attributes', 'DESC');
                break;
        }
        
        // Лимит
        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;
        $builder->limit($limit, $offset);
        
        // Выполняем запрос с параметрами для геолокации
        if (!empty($filters['lat']) && !empty($filters['lng'])) {
            return $builder->get([$filters['lat'], $filters['lng'], $filters['lat']])->getResultArray();
        } else {
            return $builder->get()->getResultArray();
        }
    }
    
    /**
     * Статистика по атрибутам
     */
    public function getAttributeStats()
    {
        $query = "
            SELECT 
                ad.category,
                ad.name,
                ad.display_name,
                ad.icon,
                COUNT(CASE WHEN ra.is_available = 1 THEN 1 END) as available_count,
                COUNT(CASE WHEN ra.is_available = 0 THEN 1 END) as unavailable_count,
                COUNT(ra.id) as total_mentions
            FROM attribute_definitions ad
            LEFT JOIN restaurant_attributes ra ON ad.category = ra.attribute_category 
                AND ad.name = ra.attribute_name
            GROUP BY ad.id
            ORDER BY ad.sort_order, available_count DESC
        ";
        
        return $this->db->query($query)->getResultArray();
    }
}