<?php

namespace App\Services;

use App\Models\ApiLogModel;
use CodeIgniter\HTTP\CURLRequest;

class DataForSeoService
{
    private $apiLogin;
    private $apiPassword;
    private $apiUrl;
    private $client;
    private $apiLogModel;
    
    public function __construct()
    {
        $this->apiLogin = getenv('DATAFORSEO_LOGIN');
        $this->apiPassword = getenv('DATAFORSEO_PASSWORD');
        $this->apiUrl = getenv('DATAFORSEO_API_URL') ?: 'https://api.dataforseo.com';
        $this->client = \Config\Services::curlrequest();
        $this->apiLogModel = new ApiLogModel();
    }
    
    /**
     * Поиск грузинских ресторанов
     */
    public function searchGeorgianRestaurants($latitude, $longitude, $radius = 25, $limit = 20, $minRating = 3.0)
    {
        $postData = [
            [
                'title' => 'georgian restaurant',
                'description' => 'georgian cuisine khachapuri khinkali',
                'categories' => ['restaurant', 'ethnic_restaurant'],
                'location_coordinate' => "{$latitude},{$longitude},{$radius}",
                'is_claimed' => true,
                'order_by' => ['rating.value,desc'],
                'filters' => [
                    ['rating.value', '>', $minRating]
                ],
                'limit' => $limit
            ]
        ];
        
        return $this->makeRequest('/v3/business_data/business_listings/search/live', $postData);
    }
    
    /**
     * Универсальный поиск ресторанов
     */
    public function searchRestaurants($searchParams)
    {
        $defaultParams = [
            'is_claimed' => true,
            'order_by' => ['rating.value,desc'],
            'filters' => [['rating.value', '>', 3.0]],
            'limit' => 20
        ];
        
        $postData = [array_merge($defaultParams, $searchParams)];
        
        return $this->makeRequest('/v3/business_data/business_listings/search/live', $postData);
    }
    
    /**
     * Поиск по ключевым словам и локации
     */
    public function searchByKeywords($keywords, $latitude, $longitude, $radius = 25, $options = [])
    {
        $searchTerms = is_array($keywords) ? $keywords : [$keywords];
        
        $postData = [
            [
                'title' => $searchTerms[0],
                'description' => implode(' ', $searchTerms),
                'categories' => $options['categories'] ?? ['restaurant'],
                'location_coordinate' => "{$latitude},{$longitude},{$radius}",
                'is_claimed' => $options['is_claimed'] ?? true,
                'order_by' => $options['order_by'] ?? ['rating.value,desc'],
                'filters' => $options['filters'] ?? [['rating.value', '>', 3.0]],
                'limit' => $options['limit'] ?? 20
            ]
        ];
        
        return $this->makeRequest('/v3/business_data/business_listings/search/live', $postData);
    }
    
    /**
     * Получение информации о местах по city/region
     */
    public function searchByLocation($cityName, $keywords = 'georgian restaurant', $options = [])
    {
        $postData = [
            [
                'title' => $keywords,
                'description' => $keywords,
                'categories' => $options['categories'] ?? ['restaurant'],
                'city' => $cityName,
                'is_claimed' => $options['is_claimed'] ?? true,
                'order_by' => $options['order_by'] ?? ['rating.value,desc'],
                'filters' => $options['filters'] ?? [['rating.value', '>', 3.0]],
                'limit' => $options['limit'] ?? 20
            ]
        ];
        
        return $this->makeRequest('/v3/business_data/business_listings/search/live', $postData);
    }
    
    /**
     * Получение детальной информации о заведении
     */
    public function getBusinessDetails($businessId, $type = 'cid')
    {
        $postData = [
            [
                $type => $businessId,
                'priority' => 2
            ]
        ];
        
        return $this->makeRequest('/v3/business_data/business_listings/info/live', $postData);
    }
    
    /**
     * Проверка доступных локаций
     */
    public function getAvailableLocations()
    {
        return $this->makeRequest('/v3/business_data/business_listings/locations');
    }
    
    /**
     * Получение доступных категорий
     */
    public function getAvailableCategories()
    {
        return $this->makeRequest('/v3/business_data/business_listings/categories');
    }
    
    /**
     * Выполнение запроса к API
     */
    private function makeRequest($endpoint, $postData = null, $method = 'POST')
    {
        $startTime = microtime(true);
        $fullUrl = $this->apiUrl . $endpoint;
        
        try {
            $options = [
                'auth' => [$this->apiLogin, $this->apiPassword],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 30
            ];
            
            if ($postData && $method === 'POST') {
                $options['json'] = $postData;
                $response = $this->client->post($fullUrl, $options);
            } else {
                $response = $this->client->get($fullUrl, $options);
            }
            
            $responseTime = microtime(true) - $startTime;
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody();
            
            // Парсим ответ
            $data = json_decode($responseBody, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }
            
            // Логируем запрос
            if (getenv('ENABLE_API_LOGGING') === 'true') {
                $cost = $data['cost'] ?? null;
                $this->apiLogModel->logApiRequest(
                    'DataForSEO',
                    $endpoint,
                    $postData,
                    $responseBody,
                    $statusCode,
                    $responseTime,
                    $cost
                );
            }
            
            return [
                'success' => $statusCode === 200 && $data['status_code'] === 20000,
                'data' => $data,
                'status_code' => $statusCode,
                'response_time' => $responseTime,
                'cost' => $data['cost'] ?? null
            ];
            
        } catch (\Exception $e) {
            $responseTime = microtime(true) - $startTime;
            
            // Логируем ошибку
            if (getenv('ENABLE_API_LOGGING') === 'true') {
                $this->apiLogModel->logApiRequest(
                    'DataForSEO',
                    $endpoint,
                    $postData,
                    $e->getMessage(),
                    0,
                    $responseTime
                );
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => 0,
                'response_time' => $responseTime
            ];
        }
    }
    
    /**
     * Обработка ответа API для получения структурированных данных ресторанов
     */
    public function processRestaurantsResponse($apiResponse)
    {
        if (!$apiResponse['success']) {
            return [];
        }
        
        $restaurants = [];
        $data = $apiResponse['data'];
        
        foreach ($data['tasks'] as $task) {
            if ($task['status_code'] === 20000) {
                foreach ($task['result'] as $resultSet) {
                    foreach ($resultSet['items'] as $item) {
                        $restaurants[] = $this->formatRestaurantData($item);
                    }
                }
            }
        }
        
        return $restaurants;
    }
    
    /**
     * Форматирование данных ресторана
     */
    private function formatRestaurantData($rawData)
    {
        return [
            // Основная информация
            'external_id' => $rawData['cid'] ?? null,
            'feature_id' => $rawData['feature_id'] ?? null,
            'name' => $rawData['title'] ?? '',
            'description' => $rawData['description'] ?? '',
            'category' => $rawData['category'] ?? '',
            'additional_categories' => $rawData['additional_categories'] ?? [],
            
            // Контактная информация
            'address' => $rawData['address'] ?? '',
            'phone' => $rawData['phone'] ?? '',
            'website' => $rawData['url'] ?? '',
            'domain' => $rawData['domain'] ?? '',
            
            // Местоположение
            'google_place_id' => $rawData['place_id'] ?? '',
            'latitude' => $rawData['latitude'] ?? 0,
            'longitude' => $rawData['longitude'] ?? 0,
            
            // Рейтинг и отзывы
            'rating' => $rawData['rating']['value'] ?? 0,
            'rating_count' => $rawData['rating']['votes_count'] ?? 0,
            'rating_type' => $rawData['rating']['rating_type'] ?? 'Max5',
            'rating_distribution' => $rawData['rating_distribution'] ?? null,
            
            // Дополнительные данные
            'price_level' => $rawData['price_level'] ?? '',
            'is_claimed' => $rawData['is_claimed'] ?? false,
            
            // Медиа
            'logo_url' => $rawData['logo'] ?? '',
            'main_image_url' => $rawData['main_image'] ?? '',
            'total_photos' => $rawData['total_photos'] ?? 0,
            
            // Время работы
            'work_hours' => $rawData['work_time'] ?? null,
            'current_status' => $rawData['work_time']['work_hours']['current_status'] ?? 'unknown',
            
            // Популярность
            'popular_times' => $rawData['popular_times'] ?? null,
            
            // Атрибуты и услуги
            'attributes' => $rawData['attributes'] ?? null,
            'people_also_search' => $rawData['people_also_search'] ?? null,
            'place_topics' => $rawData['place_topics'] ?? null,
            
            // Мета-данные
            'snippet' => $rawData['snippet'] ?? '',
            'check_url' => $rawData['check_url'] ?? '',
            'last_updated_api' => $rawData['last_updated_time'] ?? null,
            'first_seen_api' => $rawData['first_seen'] ?? null,
            'data_source' => 'DataForSEO'
        ];
    }
    
    /**
     * Валидация учетных данных API
     */
    public function validateCredentials()
    {
        if (empty($this->apiLogin) || empty($this->apiPassword)) {
            return [
                'valid' => false,
                'message' => 'API credentials not configured'
            ];
        }
        
        $response = $this->makeRequest('/v3/business_data/business_listings/locations', null, 'GET');
        
        return [
            'valid' => $response['success'],
            'message' => $response['success'] ? 'Credentials valid' : ($response['error'] ?? 'Invalid credentials')
        ];
    }
    /**
     * Поиск заведения по Google Place ID
     */
    public function searchByPlaceId($placeId)
    {
        $postData = [
            [
                'place_id' => $placeId,
                'language_name' => 'English',
                'priority' => 2
            ]
        ];
        
        return $this->makeRequest('/v3/business_data/business_listings/info/live', $postData);
    }

    /**
     * Поиск Place ID по названию и адресу
     */
    public function findPlaceId($name, $address = '')
    {
        // Формируем поисковый запрос
        $query = trim($name . ' ' . $address);
        
        $postData = [
            [
                'keyword' => $query,
                'location_name' => 'United States',
                'language_name' => 'English',
                'limit' => 5
            ]
        ];
        
        $result = $this->makeRequest('/v3/business_data/business_listings/search/live', $postData);
        
        if ($result['success'] && !empty($result['data']['tasks'])) {
            foreach ($result['data']['tasks'] as $task) {
                if ($task['status_code'] === 20000 && !empty($task['result'])) {
                    foreach ($task['result'] as $resultSet) {
                        if (!empty($resultSet['items'])) {
                            foreach ($resultSet['items'] as $item) {
                                // Проверяем похожесть названия
                                $similarity = 0;
                                similar_text(
                                    strtolower($name), 
                                    strtolower($item['title'] ?? ''), 
                                    $similarity
                                );
                                
                                // Если похожесть > 70%, считаем найденным
                                if ($similarity > 70 && !empty($item['place_id'])) {
                                    return [
                                        'success' => true,
                                        'place_id' => $item['place_id'],
                                        'title' => $item['title'],
                                        'address' => $item['address'] ?? '',
                                        'similarity' => $similarity
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return [
            'success' => false, 
            'message' => 'Place ID not found for: ' . $name
        ];
    }

    /**
     * Тест подключения к API
     */
    public function testConnection()
    {
        try {
            $result = $this->makeRequest('/v3/business_data/business_listings/locations', null, 'GET');
            
            if ($result['success'] && !empty($result['data']['tasks'])) {
                $task = $result['data']['tasks'][0];
                $locationsCount = 0;
                
                if ($task['status_code'] === 20000 && !empty($task['result'])) {
                    $locationsCount = count($task['result']);
                }
                
                return [
                    'success' => true,
                    'message' => 'API connection successful',
                    'available_locations' => $locationsCount,
                    'cost' => $result['cost'] ?? 0,
                    'response_time' => $result['response_time'] ?? 0
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'API responded but no valid data received'
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Получение детальной информации о заведении по различным идентификаторам
     */
    public function getBusinessInfo($identifier, $type = 'place_id')
    {
        $allowedTypes = ['place_id', 'cid', 'feature_id'];
        
        if (!in_array($type, $allowedTypes)) {
            return [
                'success' => false,
                'error' => 'Invalid identifier type. Allowed: ' . implode(', ', $allowedTypes)
            ];
        }
        
        $postData = [
            [
                $type => $identifier,
                'language_name' => 'English',
                'priority' => 2
            ]
        ];
        
        return $this->makeRequest('/v3/business_data/business_listings/info/live', $postData);
    }

    /**
     * Пакетный поиск информации о нескольких заведениях
     */
    public function getBulkBusinessInfo($identifiers, $type = 'place_id')
    {
        $postData = [];
        
        foreach ($identifiers as $identifier) {
            $postData[] = [
                $type => $identifier,
                'language_name' => 'English',
                'priority' => 2
            ];
        }
        
        return $this->makeRequest('/v3/business_data/business_listings/info/live', $postData);
    }

    /**
     * Проверка лимитов и статуса аккаунта
     */
    public function checkAccountStatus()
    {
        try {
            $result = $this->makeRequest('/v3/user_data/info', null, 'GET');
            
            if ($result['success'] && !empty($result['data']['tasks'])) {
                $task = $result['data']['tasks'][0];
                
                if ($task['status_code'] === 20000 && !empty($task['result'])) {
                    $userInfo = $task['result'][0];
                    
                    return [
                        'success' => true,
                        'login' => $userInfo['login'] ?? 'Unknown',
                        'balance' => $userInfo['money']['balance'] ?? 0,
                        'currency' => $userInfo['money']['currency'] ?? 'USD',
                        'limits' => $userInfo['limits'] ?? [],
                        'statistics' => $userInfo['statistics'] ?? []
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Unable to retrieve account information'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error checking account status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Поиск и извлечение данных для одного ресторана (комбинированный метод)
     */
    public function getRestaurantDataForImport($placeId)
    {
        $result = $this->searchByPlaceId($placeId);
        
        if (!$result['success']) {
            return $result;
        }
        
        // Извлекаем данные ресторана из ответа
        if (!empty($result['data']['tasks'])) {
            foreach ($result['data']['tasks'] as $task) {
                if ($task['status_code'] === 20000 && !empty($task['result'])) {
                    foreach ($task['result'] as $resultSet) {
                        if (!empty($resultSet['items'])) {
                            // Возвращаем первый найденный элемент
                            return [
                                'success' => true,
                                'data' => $resultSet['items'][0],
                                'cost' => $result['cost'] ?? 0
                            ];
                        }
                    }
                }
            }
        }
        
        return [
            'success' => false,
            'message' => 'No restaurant data found for Place ID: ' . $placeId
        ];
    }

    /**
     * Логирование использования API (расширенное)
     */
    public function logApiUsage($operation, $result, $additionalData = [])
    {
        if (!getenv('ENABLE_API_LOGGING')) {
            return;
        }
        
        try {
            $logData = [
                'operation' => $operation,
                'success' => $result['success'] ?? false,
                'cost' => $result['cost'] ?? 0,
                'response_time' => $result['response_time'] ?? 0,
                'status_code' => $result['status_code'] ?? 0,
                'additional_data' => json_encode($additionalData),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Если есть таблица для логирования API
            $db = \Config\Database::connect();
            if ($db->tableExists('api_usage_logs')) {
                $db->table('api_usage_logs')->insert($logData);
            } else {
                // Логируем в файл
                log_message('info', 'DataForSEO API Usage: ' . json_encode($logData));
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log API usage: ' . $e->getMessage());
        }
    }

    /**
     * Получение статистики использования API
     */
    public function getApiUsageStats($days = 30)
    {
        try {
            $db = \Config\Database::connect();
            
            if (!$db->tableExists('api_usage_logs')) {
                return [
                    'success' => false,
                    'message' => 'API usage logging not configured'
                ];
            }
            
            $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $stats = $db->table('api_usage_logs')
                       ->select('
                           COUNT(*) as total_requests,
                           SUM(cost) as total_cost,
                           AVG(response_time) as avg_response_time,
                           SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_requests,
                           operation,
                           DATE(created_at) as date
                       ')
                       ->where('created_at >=', $startDate)
                       ->groupBy('operation, DATE(created_at)')
                       ->orderBy('created_at', 'DESC')
                       ->get()
                       ->getResultArray();
            
            return [
                'success' => true,
                'stats' => $stats,
                'period_days' => $days
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving API stats: ' . $e->getMessage()
            ];
        }
    }
}