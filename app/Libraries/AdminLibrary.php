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
     * Получение ресторанов с фильтрацией
     */
    public function getRestaurants(array $filters = [], int $limit = 50): array
    {
        try {
            if (!$this->db->tableExists('restaurants')) {
                return [];
            }

            $builder = $this->db->table('restaurants')
                ->select('restaurants.*, cities.name as city_name')
                ->join('cities', 'cities.id = restaurants.city_id', 'left');

            if (!empty($filters['search'])) {
                $builder->like('restaurants.name', $filters['search']);
            }

            if (!empty($filters['city_id'])) {
                $builder->where('restaurants.city_id', $filters['city_id']);
            }

            return $builder->orderBy('restaurants.created_at', 'DESC')
                          ->limit($limit)
                          ->get()
                          ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary getRestaurants error: ' . $e->getMessage());
        }

        return [];
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
     * Массовые операции с ресторанами
     */
    public function bulkOperationRestaurants(string $action, array $ids): int
    {
        try {
            if (!$this->db->tableExists('restaurants') || empty($ids)) {
                return 0;
            }

            switch ($action) {
                case 'activate':
                    return $this->db->table('restaurants')
                                   ->whereIn('id', $ids)
                                   ->update(['is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);

                case 'deactivate':
                    return $this->db->table('restaurants')
                                   ->whereIn('id', $ids)
                                   ->update(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

                case 'delete':
                    return $this->db->table('restaurants')->whereIn('id', $ids)->delete();
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary bulkOperationRestaurants error: ' . $e->getMessage());
        }

        return 0;
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