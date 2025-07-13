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
            'inactive_restaurants' => 0,
            'recent_additions' => 0,
            'total_georgian' => 0,
            'total_undetermined' => 0,
            'total_all' => 0,
            'total_active' => 0,
            'total_inactive' => 0
        ];

        try {
            if ($this->db->tableExists('restaurants')) {
                $stats['total_restaurants'] = $this->db->table('restaurants')->countAllResults();
                $stats['total_all'] = $stats['total_restaurants']; // Алиас
                $stats['active_restaurants'] = $this->db->table('restaurants')->where('is_active', 1)->countAllResults();
                $stats['total_active'] = $stats['active_restaurants']; // Алиас
                $stats['inactive_restaurants'] = $this->db->table('restaurants')->where('is_active', 0)->countAllResults();
                $stats['total_inactive'] = $stats['inactive_restaurants']; // Алиас
                $stats['recent_additions'] = $this->db->table('restaurants')
                    ->where('created_at >', date('Y-m-d', strtotime('-7 days')))
                    ->countAllResults();
                $stats['total_georgian'] = $this->db->table('restaurants')->where('is_georgian', 1)->countAllResults();
                $stats['total_undetermined'] = $this->db->table('restaurants')->where('is_georgian IS NULL')->countAllResults();
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
                return $this->db->table('cities')->orderBy('name')->get()->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary getCities error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Сохранение фильтров в сессию
     */
    public function saveFilters(array $filters): void
    {
        // Исключаем специальные параметры
        $filtersToSave = array_filter($filters, function($key) {
            return !in_array($key, ['page', 'show_all', 'export']);
        }, ARRAY_FILTER_USE_KEY);

        $this->session->set('admin_filters', $filtersToSave);
    }

    /**
     * Получение сохраненных фильтров
     */
    public function getSavedFilters(): array
    {
        return $this->session->get('admin_filters') ?? [];
    }

    /**
     * Очистка фильтров
     */
    public function clearFilters(): void
    {
        $this->session->remove('admin_filters');
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
     * Быстрое изменение типа ресторана
     */
    public function setRestaurantType(int $id, string $type): bool
    {
        try {
            if (!$this->db->tableExists('restaurants')) {
                return false;
            }

            $updateData = ['updated_at' => date('Y-m-d H:i:s')];

            switch ($type) {
                case 'georgian':
                    $updateData['is_georgian'] = 1;
                    break;
                case 'non_georgian':
                    $updateData['is_georgian'] = 0;
                    break;
                case 'undetermined':
                    $updateData['is_georgian'] = null;
                    break;
                default:
                    return false;
            }

            return $this->db->table('restaurants')->where('id', $id)->update($updateData);
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary setRestaurantType error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Автоопределение типов ресторанов
     */
    public function autoDetectTypes(): array
    {
        $stats = [
            'total_processed' => 0,
            'updated' => 0,
            'georgian_found' => 0,
            'remaining_undetermined' => 0
        ];

        try {
            if (!$this->db->tableExists('restaurants')) {
                return $stats;
            }

            // Получаем рестораны с неопределенным типом
            $restaurants = $this->db->table('restaurants')->where('is_georgian IS NULL')->get()->getResultArray();
            $stats['total_processed'] = count($restaurants);

            $georgianKeywords = [
                'georgian', 'georgia', 'tbilisi', 'khachapuri', 'khinkali', 
                'adjarian', 'supra', 'caucas', 'грузин', 'тбилиси', 
                'хачапури', 'хинкали', 'кавказ', 'сухуми'
            ];

            foreach ($restaurants as $restaurant) {
                $isGeorgian = false;
                $text = strtolower(
                    ($restaurant['name'] ?? '') . ' ' . 
                    ($restaurant['category'] ?? '') . ' ' . 
                    ($restaurant['description'] ?? '')
                );

                // Проверяем наличие грузинских ключевых слов
                foreach ($georgianKeywords as $keyword) {
                    if (strpos($text, $keyword) !== false) {
                        $isGeorgian = true;
                        break;
                    }
                }

                if ($isGeorgian) {
                    $this->db->table('restaurants')->where('id', $restaurant['id'])->update([
                        'is_georgian' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $stats['updated']++;
                    $stats['georgian_found']++;
                }
            }

            $stats['remaining_undetermined'] = $this->db->table('restaurants')->where('is_georgian IS NULL')->countAllResults();

        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary autoDetectTypes error: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Удаление ресторана
     */
    public function deleteRestaurant(int $id): bool
    {
        try {
            if ($this->db->tableExists('restaurants')) {
                // Также удаляем связанные фотографии если есть таблица
                if ($this->db->tableExists('restaurant_photos')) {
                    $this->db->table('restaurant_photos')->where('restaurant_id', $id)->delete();
                }
                
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
                    // Удаляем связанные фотографии
                    if ($this->db->tableExists('restaurant_photos')) {
                        $this->db->table('restaurant_photos')->whereIn('restaurant_id', $ids)->delete();
                    }
                    return $this->db->table('restaurants')->whereIn('id', $ids)->delete();
                    
                case 'geocode':
                    // Можно добавить логику геокодирования
                    return count($ids); // Заглушка
            }
        } catch (\Exception $e) {
            log_message('error', 'AdminLibrary bulkOperationRestaurants error: ' . $e->getMessage());
        }

        return 0;
    }
}