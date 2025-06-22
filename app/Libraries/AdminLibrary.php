<?php

namespace App\Libraries;

class AdminLibrary
{
    protected $db;
    protected $session;
    protected $config;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        $this->config = config('AdminAuth');
    }

    /**
     * Проверка авторизации
     */
    public function isLoggedIn(): bool
    {
        return $this->session->get('admin_logged_in') === true;
    }

    /**
     * Получение ключа из сессии
     */
    public function getAdminKey(): ?string
    {
        return $this->session->get('admin_key');
    }

    /**
     * Выход из админки
     */
    public function logout(): void
    {
        // Удаляем cookie
        if ($this->config->useRememberMe) {
            setcookie($this->config->rememberMeCookie, '', time() - 3600, '/');
        }
        
        // Очищаем сессию
        $this->session->destroy();
    }

    /**
     * Получение статистики
     */
    public function getStats(): array
    {
        $stats = [
            'total_restaurants' => 0,
            'total_cities' => 0,
            'active_restaurants' => 0,
            'recent_additions' => 0
        ];

        try {
            if ($this->db->tableExists('restaurants')) {
                $stats['total_restaurants'] = $this->db->table('restaurants')->countAllResults();
                $stats['active_restaurants'] = $this->db->table('restaurants')->where('is_active', 1)->countAllResults();
                $stats['recent_additions'] = $this->db->table('restaurants')
                    ->where('created_at >', date('Y-m-d', strtotime('-7 days')))
                    ->countAllResults();
            }
            
            if ($this->db->tableExists('cities')) {
                $stats['total_cities'] = $this->db->table('cities')->countAllResults();
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary getStats error: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Получение последних ресторанов
     */
    public function getRecentRestaurants(int $limit = 10): array
    {
        try {
            if ($this->db->tableExists('restaurants') && $this->db->tableExists('cities')) {
                return $this->db->table('restaurants')
                    ->select('restaurants.*, cities.name as city_name')
                    ->join('cities', 'cities.id = restaurants.city_id', 'left')
                    ->orderBy('restaurants.created_at', 'DESC')
                    ->limit($limit)
                    ->get()
                    ->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary getRecentRestaurants error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Получение всех городов
     */
    public function getCities(): array
    {
        try {
            if ($this->db->tableExists('cities')) {
                return $this->db->table('cities')->get()->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary getCities error: ' . $e->getMessage());
        }

        return [];
    }

   /**
     * Получение списка ресторанов с фильтрами - ОБНОВЛЕННАЯ ВЕРСИЯ
     */
    public function getRestaurants($filters = [], $limit = 50)
    {
        $restaurantModel = model('RestaurantModel');
        
        // Базовый запрос с объединением городов
        $query = $restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
            ->join('cities', 'cities.id = restaurants.city_id', 'left');

        // Применяем фильтры
        $this->applyRestaurantFilters($query, $filters);

        // Получаем результаты
        return $query->orderBy('restaurants.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Подсчет ресторанов с фильтрами
     */
    public function getRestaurantsCount($filters = [])
    {
        $restaurantModel = model('RestaurantModel');
        
        $query = $restaurantModel
            ->select('restaurants.id')
            ->join('cities', 'cities.id = restaurants.city_id', 'left');

        // Применяем те же фильтры
        $this->applyRestaurantFilters($query, $filters);

        return $query->countAllResults();
    }
    /**
     * Применение фильтров к запросу - НОВЫЙ МЕТОД
     */
    private function applyRestaurantFilters($query, $filters)
    {
        // Поиск по тексту
        if (!empty($filters['search'])) {
            $query->groupStart()
                ->like('restaurants.name', $filters['search'])
                ->orLike('restaurants.address', $filters['search'])
                ->orLike('restaurants.description', $filters['search'])
                ->orLike('restaurants.category', $filters['search'])
                ->groupEnd();
        }

        // Фильтр по городу
        if (!empty($filters['city_id'])) {
            $query->where('restaurants.city_id', $filters['city_id']);
        }

        // Фильтр по статусу активности
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->where('restaurants.is_active', 1);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('restaurants.is_active', 0);
            }
        }

        // НОВЫЙ: Фильтр по типу ресторана (грузинский или нет)
        if (!empty($filters['restaurant_type'])) {
            if ($filters['restaurant_type'] === 'georgian') {
                // Ищем грузинские рестораны
                $query->groupStart()
                    ->like('restaurants.category', 'georgian', 'both')
                    ->orLike('restaurants.category', 'грузин', 'both')
                    ->orLike('restaurants.name', 'georgian', 'both')
                    ->orLike('restaurants.name', 'georgia', 'both')
                    ->orLike('restaurants.name', 'tbilisi', 'both')
                    ->orLike('restaurants.name', 'khachapuri', 'both')
                    ->orLike('restaurants.name', 'khinkali', 'both')
                    ->orLike('restaurants.name', 'грузин', 'both')
                    ->orLike('restaurants.name', 'тбилиси', 'both')
                    ->orLike('restaurants.description', 'georgian', 'both')
                    ->orLike('restaurants.description', 'georgia', 'both')
                    ->orLike('restaurants.description', 'грузин', 'both')
                    ->groupEnd();
            } elseif ($filters['restaurant_type'] === 'non_georgian') {
                // Исключаем грузинские рестораны
                $query->groupStart()
                    ->notLike('restaurants.category', 'georgian', 'both')
                    ->notLike('restaurants.category', 'грузин', 'both')
                    ->notLike('restaurants.name', 'georgian', 'both')
                    ->notLike('restaurants.name', 'georgia', 'both')
                    ->notLike('restaurants.name', 'tbilisi', 'both')
                    ->notLike('restaurants.name', 'khachapuri', 'both')
                    ->notLike('restaurants.name', 'khinkali', 'both')
                    ->notLike('restaurants.name', 'грузин', 'both')
                    ->notLike('restaurants.name', 'тбилиси', 'both')
                    ->notLike('restaurants.description', 'georgian', 'both')
                    ->notLike('restaurants.description', 'georgia', 'both')
                    ->notLike('restaurants.description', 'грузин', 'both')
                    ->groupEnd();
            }
        }

        // Фильтры по данным
        if (!empty($filters['data_filter'])) {
            switch ($filters['data_filter']) {
                case 'no_coords':
                    $query->groupStart()
                        ->where('restaurants.latitude IS NULL')
                        ->orWhere('restaurants.latitude', 0)
                        ->orWhere('restaurants.longitude IS NULL') 
                        ->orWhere('restaurants.longitude', 0)
                        ->groupEnd();
                    break;
                case 'no_place_id':
                    $query->groupStart()
                        ->where('restaurants.google_place_id IS NULL')
                        ->orWhere('restaurants.google_place_id', '')
                        ->groupEnd();
                    break;
                case 'has_website':
                    $query->where('restaurants.website IS NOT NULL')
                        ->where('restaurants.website !=', '');
                    break;
                case 'no_photos':
                    // Подзапрос для проверки наличия фотографий
                    $query->where("restaurants.id NOT IN (
                        SELECT DISTINCT restaurant_id 
                        FROM restaurant_photos 
                        WHERE restaurant_id IS NOT NULL
                    )");
                    break;
            }
        }
    }

    /**
     * Вспомогательная функция для определения типа ресторана - НОВЫЙ МЕТОД
     */
    public function isGeorgianRestaurant($restaurant)
    {
        $georgianKeywords = [
            'georgian', 'georgia', 'tbilisi', 'khachapuri', 'khinkali', 
            'adjarian', 'supra', 'caucas', 'грузин', 'тбилиси', 'хачапури', 'хинкали'
        ];
        
        $searchText = strtolower(
            ($restaurant['name'] ?? '') . ' ' . 
            ($restaurant['category'] ?? '') . ' ' . 
            ($restaurant['description'] ?? '')
        );
        
        foreach ($georgianKeywords as $keyword) {
            if (strpos($searchText, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Получение ресторана по ID
     */
    public function getRestaurant(int $id): ?array
    {
        try {
            if ($this->db->tableExists('restaurants')) {
                $result = $this->db->table('restaurants')->where('id', $id)->get()->getRowArray();
                return $result ?: null;
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary getRestaurant error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Обновление ресторана
     */
    public function updateRestaurant(int $id, array $data): bool
    {
        try {
            if ($this->db->tableExists('restaurants')) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                return $this->db->table('restaurants')->where('id', $id)->update($data);
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary updateRestaurant error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Удаление ресторана
     */
    public function deleteRestaurant(int $id): bool
    {
        try {
            if ($this->db->tableExists('restaurants')) {
                return $this->db->table('restaurants')->where('id', $id)->delete();
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary deleteRestaurant error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Добавление города
     */
    public function addCity(array $data): bool
    {
        try {
            if ($this->db->tableExists('cities')) {
                $data['slug'] = url_title($data['name'], '-', true);
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                return $this->db->table('cities')->insert($data);
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary addCity error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Получение городов с количеством ресторанов
     */
    public function getCitiesWithCounts(): array
    {
        try {
            if ($this->db->tableExists('cities')) {
                return $this->db->table('cities')
                    ->select('cities.*, COUNT(restaurants.id) as restaurant_count')
                    ->join('restaurants', 'restaurants.city_id = cities.id', 'left')
                    ->groupBy('cities.id')
                    ->orderBy('cities.name')
                    ->get()
                    ->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary getCitiesWithCounts error: ' . $e->getMessage());
        }

        return [];
    }

   /**
     * Массовые операции с ресторанами - ОБНОВЛЕННАЯ ВЕРСИЯ
     */
    public function bulkOperationRestaurants($action, $restaurantIds)
    {
        if (empty($restaurantIds) || !is_array($restaurantIds)) {
            return 0;
        }

        $restaurantModel = model('RestaurantModel');
        $affected = 0;

        try {
            switch ($action) {
                case 'activate':
                    $affected = $restaurantModel->whereIn('id', $restaurantIds)
                                            ->set(['is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')])
                                            ->update();
                    break;

                case 'deactivate':
                    $affected = $restaurantModel->whereIn('id', $restaurantIds)
                                            ->set(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')])
                                            ->update();
                    break;

                case 'delete':
                    // Сначала удаляем связанные фотографии
                    $this->deleteRestaurantPhotos($restaurantIds);
                    
                    // Затем удаляем рестораны
                    $affected = $restaurantModel->whereIn('id', $restaurantIds)->delete();
                    break;

                case 'geocode':
                    // Запускаем геокодирование для выбранных ресторанов
                    $affected = $this->geocodeRestaurants($restaurantIds);
                    break;

                default:
                    return 0;
            }

            return $affected;

        } catch (\Exception $e) {
            log_message('error', 'Bulk operation error: ' . $e->getMessage());
            return 0;
        }
    }
    /**
     * Удаление фотографий ресторанов - НОВЫЙ МЕТОД
     */
    private function deleteRestaurantPhotos($restaurantIds)
    {
        try {
            $photoModel = new \App\Models\RestaurantPhotoModel();
            
            foreach ($restaurantIds as $restaurantId) {
                $photos = $photoModel->getRestaurantPhotos($restaurantId);
                
                foreach ($photos as $photo) {
                    // Удаляем физический файл
                    $filePath = FCPATH . '../' . $photo['file_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    
                    // Удаляем запись из БД
                    $photoModel->deletePhoto($photo['id']);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting restaurant photos: ' . $e->getMessage());
        }
    }

    /**
     * Геокодирование ресторанов - НОВЫЙ МЕТОД
     */
    private function geocodeRestaurants($restaurantIds)
    {
        $geocoded = 0;
        
        try {
            $restaurantModel = model('RestaurantModel');
            
            foreach ($restaurantIds as $restaurantId) {
                $restaurant = $restaurantModel->find($restaurantId);
                
                if ($restaurant && !empty($restaurant['address'])) {
                    // Здесь должна быть интеграция с сервисом геокодирования
                    // Пока просто помечаем как обработанные
                    
                    // Заглушка для координат (в реальности здесь будет API запрос)
                    $mockCoordinates = $this->getMockCoordinates($restaurant['address']);
                    
                    if ($mockCoordinates) {
                        $restaurantModel->update($restaurantId, [
                            'latitude' => $mockCoordinates['lat'],
                            'longitude' => $mockCoordinates['lng'],
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        $geocoded++;
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Geocoding error: ' . $e->getMessage());
        }
        
        return $geocoded;
    }

    /**
     * Заглушка для координат - ВРЕМЕННЫЙ МЕТОД
     */
    private function getMockCoordinates($address)
    {
        // В реальности здесь будет запрос к Google Geocoding API
        // Пока возвращаем примерные координаты для NYC
        if (stripos($address, 'New York') !== false || stripos($address, 'NY') !== false) {
            return [
                'lat' => 40.7580 + (rand(-1000, 1000) / 10000), // Небольшое случайное отклонение
                'lng' => -73.9855 + (rand(-1000, 1000) / 10000)
            ];
        }
        
        return null;
    }
    
    /**
     * Экспорт ресторанов в CSV
     */
    public function exportRestaurantsCSV(): void
    {
        try {
            $restaurants = [];
            
            if ($this->db->tableExists('restaurants') && $this->db->tableExists('cities')) {
                $restaurants = $this->db->table('restaurants')
                    ->select('restaurants.*, cities.name as city_name')
                    ->join('cities', 'cities.id = restaurants.city_id', 'left')
                    ->get()
                    ->getResultArray();
            }

            $filename = 'restaurants_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Headers
            fputcsv($output, ['ID', 'Name', 'City', 'Address', 'Phone', 'Website', 'Rating', 'Price Level', 'Active']);
            
            // Data
            foreach ($restaurants as $restaurant) {
                fputcsv($output, [
                    $restaurant['id'] ?? '',
                    $restaurant['name'] ?? '',
                    $restaurant['city_name'] ?? '',
                    $restaurant['address'] ?? '',
                    $restaurant['phone'] ?? '',
                    $restaurant['website'] ?? '',
                    $restaurant['rating'] ?? '',
                    $restaurant['price_level'] ?? '',
                    ($restaurant['is_active'] ?? 0) ? 'Yes' : 'No'
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary exportRestaurantsCSV error: ' . $e->getMessage());
        }
    }

    /**
     * Проверка состояния базы данных
     */
    public function getDatabaseStatus(): array
    {
        $status = [
            'connected' => false,
            'tables' => [],
            'required_tables' => ['restaurants', 'cities'],
            'missing_tables' => [],
            'error' => null
        ];

        try {
            // Тестируем подключение
            $this->db->query('SELECT 1');
            $status['connected'] = true;
            
            // Получаем список таблиц
            $status['tables'] = $this->db->listTables();
            
            // Проверяем необходимые таблицы
            $status['missing_tables'] = array_diff($status['required_tables'], $status['tables']);
            
        } catch (\Exception $e) {
            $status['error'] = $e->getMessage();
        }

        return $status;
    }
}