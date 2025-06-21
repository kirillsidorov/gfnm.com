<?php
// =============================================================================
// НОВЫЙ КОНТРОЛЛЕР: app/Controllers/DataForSeoImport.php
// =============================================================================

namespace App\Controllers;

use App\Services\DataForSeoImportService;
use App\Services\DataForSeoService;

class DataForSeoImport extends BaseController
{
    protected $importService;
    protected $dataForSeoService;
    
    public function __construct()
    {
        $this->importService = new DataForSeoImportService();
        $this->dataForSeoService = new DataForSeoService();
    }

    /**
     * Главная страница импорта
     */
    public function index()
    {
        if (!session('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $restaurantModel = model('RestaurantModel');
        $cityModel = model('CityModel');

        $stats = [
            'total_restaurants' => $restaurantModel->countAllResults(),
            'with_place_id' => $restaurantModel->where('google_place_id IS NOT NULL')->where('google_place_id !=', '')->countAllResults(),
            'without_place_id' => $restaurantModel->where('google_place_id IS NULL OR google_place_id =', '')->countAllResults(),
            'last_import' => $this->getLastImportTime(),
            'total_cities' => $cityModel->countAllResults()
        ];

        $recentImports = $this->getRecentImports();
        $cities = $cityModel->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'DataForSEO Import - Georgian Food Admin',
            'stats' => $stats,
            'recent_imports' => $recentImports,
            'cities' => $cities
        ];

        return view('admin/dataforseo_import', $data);
    }

    /**
     * Обновление данных конкретного ресторана
     */
    public function updateRestaurant($restaurantId)
    {
        if (!session('admin_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $restaurantModel = model('RestaurantModel');
        $restaurant = $restaurantModel->find($restaurantId);

        if (!$restaurant) {
            return $this->response->setJSON(['success' => false, 'message' => 'Restaurant not found']);
        }

        try {
            // Проверяем есть ли Place ID
            if (empty($restaurant['google_place_id'])) {
                // Пытаемся найти Place ID по названию и адресу
                $placeId = $this->findPlaceId($restaurant['name'], $restaurant['address']);
                if (!$placeId) {
                    return $this->response->setJSON([
                        'success' => false, 
                        'message' => 'Google Place ID not found. Please add it manually first.'
                    ]);
                }
                
                // Сохраняем найденный Place ID
                $restaurantModel->update($restaurantId, ['google_place_id' => $placeId]);
                $restaurant['google_place_id'] = $placeId;
            }

            // Получаем данные из DataForSEO
            $searchResult = $this->dataForSeoService->searchByPlaceId($restaurant['google_place_id']);
            
            if (!$searchResult['success'] || empty($searchResult['data'])) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'No data found in DataForSEO for this Place ID'
                ]);
            }

            // Импортируем/обновляем данные
            $importResult = $this->importService->importSingleRestaurant($searchResult['data'][0]);
            
            // Логируем импорт
            $this->logImport('single_restaurant', $restaurantId, $importResult);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Restaurant data updated successfully',
                'action' => $importResult['action'],
                'restaurant_id' => $importResult['id']
            ]);

        } catch (\Exception $e) {
            log_message('error', 'DataForSEO import error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Import failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Массовое обновление ресторанов с Place ID
     */
    public function bulkUpdate()
    {
        if (!session('admin_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $limit = $this->request->getPost('limit') ?? 10;
        $onlyOutdated = $this->request->getPost('only_outdated') === 'true';

        $restaurantModel = model('RestaurantModel');
        
        // Строим запрос
        $builder = $restaurantModel->where('google_place_id IS NOT NULL')
                                  ->where('google_place_id !=', '')
                                  ->where('is_active', 1);

        if ($onlyOutdated) {
            // Обновляем только те, что давно не обновлялись
            $cutoffDate = date('Y-m-d H:i:s', strtotime('-30 days'));
            $builder->where('(last_updated_api IS NULL OR last_updated_api <', $cutoffDate . ')');
        }

        $restaurants = $builder->limit($limit)->findAll();

        if (empty($restaurants)) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'No restaurants found for bulk update'
            ]);
        }

        $results = [
            'processed' => 0,
            'updated' => 0,
            'errors' => 0,
            'error_details' => []
        ];

        foreach ($restaurants as $restaurant) {
            try {
                $searchResult = $this->dataForSeoService->searchByPlaceId($restaurant['google_place_id']);
                
                if ($searchResult['success'] && !empty($searchResult['data'])) {
                    $importResult = $this->importService->importSingleRestaurant($searchResult['data'][0]);
                    
                    if ($importResult['action'] === 'updated') {
                        $results['updated']++;
                    }
                } else {
                    $results['errors']++;
                    $results['error_details'][] = "No data for {$restaurant['name']} (Place ID: {$restaurant['google_place_id']})";
                }

                $results['processed']++;
                
                // Пауза между запросами
                usleep(500000); // 0.5 секунды

            } catch (\Exception $e) {
                $results['errors']++;
                $results['error_details'][] = "Error updating {$restaurant['name']}: " . $e->getMessage();
            }
        }

        // Логируем массовый импорт
        $this->logImport('bulk_update', null, $results);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Bulk update completed. Processed: {$results['processed']}, Updated: {$results['updated']}, Errors: {$results['errors']}",
            'results' => $results
        ]);
    }

    /**
     * Поиск и импорт новых ресторанов по городу
     */
    public function searchAndImport()
    {
        if (!session('admin_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $cityId = $this->request->getPost('city_id');
        $searchQuery = $this->request->getPost('search_query') ?: 'georgian restaurant';
        $limit = $this->request->getPost('limit') ?: 20;

        if (!$cityId) {
            return $this->response->setJSON(['success' => false, 'message' => 'City ID is required']);
        }

        $cityModel = model('CityModel');
        $city = $cityModel->find($cityId);

        if (!$city || !$city['latitude'] || !$city['longitude']) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'City not found or missing coordinates'
            ]);
        }

        try {
            // Поиск через DataForSEO
            $searchResult = $this->dataForSeoService->searchByKeywords(
                $searchQuery,
                $city['latitude'],
                $city['longitude'],
                25, // радиус 25км
                ['limit' => $limit]
            );

            if (!$searchResult['success'] || empty($searchResult['data'])) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'No restaurants found in DataForSEO'
                ]);
            }

            // Импортируем найденные рестораны
            $results = [
                'found' => count($searchResult['data']),
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            foreach ($searchResult['data'] as $item) {
                try {
                    $importResult = $this->importService->importSingleRestaurant($item);
                    
                    if ($importResult['action'] === 'created') {
                        $results['imported']++;
                        
                        // Устанавливаем правильный город для нового ресторана
                        model('RestaurantModel')->update($importResult['id'], ['city_id' => $cityId]);
                        
                    } elseif ($importResult['action'] === 'updated') {
                        $results['updated']++;
                    } else {
                        $results['skipped']++;
                    }

                } catch (\Exception $e) {
                    $results['errors'][] = "Error importing {$item['title']}: " . $e->getMessage();
                }
            }

            // Логируем поиск и импорт
            $this->logImport('search_and_import', $cityId, $results);

            return $this->response->setJSON([
                'success' => true,
                'message' => "Import completed. Found: {$results['found']}, Imported: {$results['imported']}, Updated: {$results['updated']}",
                'results' => $results
            ]);

        } catch (\Exception $e) {
            log_message('error', 'DataForSEO search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Search failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Проверка статуса API
     */
    public function checkApiStatus()
    {
        try {
            $result = $this->dataForSeoService->testConnection();
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Поиск Place ID по названию и адресу
     */
    private function findPlaceId($name, $address)
    {
        try {
            $result = $this->dataForSeoService->findPlaceId($name, $address);
            return $result['success'] ? $result['place_id'] : null;
        } catch (\Exception $e) {
            log_message('error', 'Place ID search error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получение времени последнего импорта
     */
    private function getLastImportTime()
    {
        $result = $this->db->table('restaurants')
                          ->select('MAX(last_updated_api) as last_import')
                          ->where('last_updated_api IS NOT NULL')
                          ->get()
                          ->getRow();
        
        return $result ? $result->last_import : null;
    }

    /**
     * Получение недавних импортов
     */
    private function getRecentImports()
    {
        // Если есть таблица логов импорта
        if ($this->db->tableExists('import_logs')) {
            return $this->db->table('import_logs')
                           ->orderBy('created_at', 'DESC')
                           ->limit(10)
                           ->get()
                           ->getResultArray();
        }
        
        return [];
    }

    /**
     * Логирование импорта
     */
    private function logImport($type, $entityId, $result)
    {
        try {
            // Если есть таблица логов
            if ($this->db->tableExists('import_logs')) {
                $this->db->table('import_logs')->insert([
                    'import_type' => $type,
                    'entity_id' => $entityId,
                    'result_data' => json_encode($result),
                    'success' => isset($result['success']) ? $result['success'] : true,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Логируем в файл
                log_message('info', "DataForSEO Import: {$type} - " . json_encode($result));
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to log import: ' . $e->getMessage());
        }
    }
}

// =============================================================================
// ДОПОЛНЕНИЕ К DataForSeoService: app/Services/DataForSeoService.php
// =============================================================================

/**
 * Поиск по Place ID
 */
public function searchByPlaceId($placeId)
{
    $postData = [
        [
            'place_id' => $placeId,
            'language_name' => 'English'
        ]
    ];
    
    return $this->makeRequest('/v3/business_data/business_listings/live', $postData);
}

/**
 * Поиск Place ID по названию и адресу
 */
public function findPlaceId($name, $address)
{
    $query = trim($name . ' ' . $address);
    
    $postData = [
        [
            'keyword' => $query,
            'limit' => 5
        ]
    ];
    
    $result = $this->makeRequest('/v3/business_data/business_listings/search/live', $postData);
    
    if ($result['success'] && !empty($result['data'])) {
        foreach ($result['data'] as $item) {
            // Ищем наиболее похожий результат
            if (stripos($item['title'], $name) !== false) {
                return [
                    'success' => true,
                    'place_id' => $item['place_id']
                ];
            }
        }
    }
    
    return ['success' => false, 'message' => 'Place ID not found'];
}

/**
 * Тест подключения к API
 */
public function testConnection()
{
    try {
        $result = $this->makeRequest('/v3/business_data/business_listings/locations');
        
        return [
            'success' => true,
            'message' => 'API connection successful',
            'available_locations' => count($result['data'] ?? [])
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'API connection failed: ' . $e->getMessage()
        ];
    }
}