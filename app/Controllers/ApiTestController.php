<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ApiTestController extends Controller
{
    public function testDataForSEO($location = null)
    {
        // Настройки API
        $apiLogin = getenv('DATAFORSEO_LOGIN') ?: 'your_login';
        $apiPassword = getenv('DATAFORSEO_PASSWORD') ?: 'your_password';
        
        // Проверяем учетные данные
        if ($apiLogin === 'your_login' || $apiPassword === 'your_password') {
            return view('api_test_results', [
                'error' => 'Please configure DATAFORSEO_LOGIN and DATAFORSEO_PASSWORD in your .env file',
                'locations' => []
            ]);
        }
        
        // Тестовые локации
        $locations = [
            'nyc' => [
                'name' => 'New York City',
                'coords' => '40.7580,-73.9855,25',
                'search_terms' => ['georgian restaurant', 'khachapuri', 'khinkali']
            ],
            'la' => [
                'name' => 'Los Angeles',
                'coords' => '34.0522,-118.2437,30',
                'search_terms' => ['georgian restaurant', 'georgian cuisine']
            ],
            'chicago' => [
                'name' => 'Chicago',
                'coords' => '41.8781,-87.6298,25',
                'search_terms' => ['georgian restaurant', 'caucasian cuisine']
            ]
        ];
        
        // Получаем локацию из URL или GET параметра
        $location = $location ?: ($this->request->getGet('location') ?: 'nyc');
        $selectedLocation = $locations[$location] ?? $locations['nyc'];
        
        // Подготовка данных для запроса
        $postData = [
            [
                'title' => $selectedLocation['search_terms'][0],
                'description' => implode(' ', $selectedLocation['search_terms']),
                'categories' => [
                    'restaurant',
                    'ethnic_restaurant',
                    'georgian_restaurant'
                ],
                'location_coordinate' => $selectedLocation['coords'],
                'is_claimed' => true,
                'order_by' => ['rating.value,desc'],
                'filters' => [
                    ['rating.value', '>', 3.0]
                ],
                'limit' => 15
            ]
        ];
        
        try {
            // Выполняем запрос
            $client = \Config\Services::curlrequest();
            
            $response = $client->post('https://api.dataforseo.com/v3/business_data/business_listings/search/live', [
                'auth' => [$apiLogin, $apiPassword],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $postData,
                'timeout' => 30
            ]);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed with status: ' . $response->getStatusCode());
            }
            
            $data = json_decode($response->getBody(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }
            
            // Анализируем и форматируем данные
            $analysis = $this->analyzeApiResponse($data, $selectedLocation['name']);
            
            // Сохраняем результат в файл
            $filename = WRITEPATH . 'uploads/dataforseo_test_' . $location . '_' . date('Y-m-d_H-i-s') . '.json';
            file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // Показываем результат
            return view('api_test_results', [
                'analysis' => $analysis,
                'raw_data' => $data,
                'location' => $selectedLocation,
                'filename' => basename($filename),
                'locations' => $locations
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'DataForSEO API Test Error: ' . $e->getMessage());
            
            return view('api_test_results', [
                'error' => 'API Test Failed: ' . $e->getMessage(),
                'locations' => $locations,
                'location' => $selectedLocation
            ]);
        }
    }
    
    private function analyzeApiResponse($data, $locationName)
    {
        $analysis = [
            'success' => $data['status_code'] == 20000,
            'location' => $locationName,
            'general_info' => [
                'status' => $data['status_message'],
                'time' => $data['time'],
                'cost' => $data['cost'],
                'tasks_count' => $data['tasks_count']
            ],
            'restaurants' => [],
            'field_analysis' => [],
            'db_recommendations' => []
        ];
        
        if (!$analysis['success']) {
            $analysis['error'] = $data['status_message'];
            return $analysis;
        }
        
        $allFields = [];
        
        foreach ($data['tasks'] as $task) {
            if ($task['status_code'] == 20000) {
                $result = $task['result'][0];
                
                $analysis['total_found'] = $result['total_count'];
                $analysis['returned_count'] = $result['count'];
                
                foreach ($result['items'] as $restaurant) {
                    // Собираем все поля для анализа
                    $this->collectFields($restaurant, $allFields);
                    
                    // Форматируем данные ресторана
                    $restaurantData = [
                        'name' => $restaurant['title'] ?? 'N/A',
                        'category' => $restaurant['category'] ?? 'N/A',
                        'address' => $restaurant['address'] ?? 'N/A',
                        'phone' => $restaurant['phone'] ?? 'N/A',
                        'website' => $restaurant['url'] ?? 'N/A',
                        'place_id' => $restaurant['place_id'] ?? 'N/A',
                        'rating' => $restaurant['rating']['value'] ?? 0,
                        'rating_count' => $restaurant['rating']['votes_count'] ?? 0,
                        'price_level' => $restaurant['price_level'] ?? 'N/A',
                        'is_claimed' => $restaurant['is_claimed'] ?? false,
                        'photos_count' => $restaurant['total_photos'] ?? 0,
                        'current_status' => $restaurant['work_time']['work_hours']['current_status'] ?? 'unknown',
                        'has_delivery' => false,
                        'has_takeout' => false,
                        'accepts_reservations' => false,
                        'wheelchair_accessible' => false,
                        'serves_alcohol' => false,
                        'family_friendly' => false
                    ];
                    
                    // Анализируем атрибуты
                    if (isset($restaurant['attributes']['available_attributes'])) {
                        $attrs = $restaurant['attributes']['available_attributes'];
                        
                        $restaurantData['has_delivery'] = in_array('has_delivery', $attrs['service_options'] ?? []);
                        $restaurantData['has_takeout'] = in_array('has_takeout', $attrs['service_options'] ?? []);
                        $restaurantData['accepts_reservations'] = in_array('accepts_reservations', $attrs['planning'] ?? []);
                        $restaurantData['wheelchair_accessible'] = !empty($attrs['accessibility']);
                        $restaurantData['serves_alcohol'] = in_array('serves_alcohol', $attrs['offerings'] ?? []);
                        $restaurantData['family_friendly'] = in_array('welcomes_children', $attrs['children'] ?? []);
                        
                        $restaurantData['all_attributes'] = $attrs;
                    }
                    
                    $analysis['restaurants'][] = $restaurantData;
                }
            }
        }
        
        // Анализ полей
        $analysis['field_analysis'] = $this->analyzeFields($allFields);
        
        // Рекомендации по БД
        $analysis['db_recommendations'] = $this->generateDbRecommendations($allFields);
        
        return $analysis;
    }
    
    private function collectFields($data, &$allFields, $prefix = '')
    {
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? $prefix . '.' . $key : $key;
            
            if (!isset($allFields[$fullKey])) {
                $allFields[$fullKey] = [
                    'type' => gettype($value),
                    'count' => 0,
                    'examples' => []
                ];
            }
            
            $allFields[$fullKey]['count']++;
            
            if (is_array($value)) {
                if (!empty($value)) {
                    $allFields[$fullKey]['examples'][] = array_slice($value, 0, 3);
                    if (is_numeric(array_keys($value)[0])) {
                        // Числовой массив
                        foreach ($value as $item) {
                            if (is_array($item)) {
                                $this->collectFields($item, $allFields, $fullKey . '[]');
                            }
                        }
                    } else {
                        // Ассоциативный массив
                        $this->collectFields($value, $allFields, $fullKey);
                    }
                }
            } elseif (!is_null($value) && $value !== '') {
                if (count($allFields[$fullKey]['examples']) < 3) {
                    $allFields[$fullKey]['examples'][] = $value;
                }
            }
        }
    }
    
    private function analyzeFields($allFields)
    {
        $analysis = [
            'total_fields' => count($allFields),
            'important_fields' => [],
            'media_fields' => [],
            'attribute_fields' => [],
            'time_fields' => []
        ];
        
        if (empty($allFields)) {
            return $analysis;
        }
        
        foreach ($allFields as $field => $info) {
            if (strpos($field, 'attributes.') === 0) {
                $analysis['attribute_fields'][$field] = $info;
            } elseif (strpos($field, 'work_time') !== false || strpos($field, 'popular_times') !== false) {
                $analysis['time_fields'][$field] = $info;
            } elseif (strpos($field, 'photo') !== false || strpos($field, 'image') !== false || strpos($field, 'logo') !== false) {
                $analysis['media_fields'][$field] = $info;
            } elseif (in_array($field, ['title', 'description', 'address', 'phone', 'url', 'rating', 'place_id'])) {
                $analysis['important_fields'][$field] = $info;
            }
        }
        
        return $analysis;
    }
    
    private function generateDbRecommendations($allFields)
    {
        $recommendations = [
            'new_tables' => [],
            'new_columns' => [],
            'json_columns' => [],
            'indexes' => []
        ];
        
        // Рекомендации по новым колонкам
        $importantFields = [
            'cid' => 'VARCHAR(255) - Google CID',
            'feature_id' => 'VARCHAR(255) - DataForSEO Feature ID',
            'domain' => 'VARCHAR(255) - Website domain',
            'snippet' => 'TEXT - Short description snippet',
            'total_photos' => 'INT - Number of photos',
            'is_claimed' => 'BOOLEAN - Claimed by owner',
            'price_level' => 'VARCHAR(20) - Price range indicator'
        ];
        
        $recommendations['new_columns'] = $importantFields;
        
        // JSON колонки
        $jsonFields = [
            'attributes_detailed' => 'JSON - All business attributes (service options, accessibility, etc.)',
            'work_hours_detailed' => 'JSON - Detailed working hours by day',
            'popular_times' => 'JSON - Popular times data',
            'people_also_search' => 'JSON - Related businesses',
            'rating_distribution' => 'JSON - Rating breakdown by stars',
            'additional_categories' => 'JSON - Secondary categories',
            'place_topics' => 'JSON - Associated topics/keywords'
        ];
        
        $recommendations['json_columns'] = $jsonFields;
        
        // Новые таблицы
        $recommendations['new_tables'] = [
            'restaurant_attributes' => 'Separate table for searchable attributes',
            'restaurant_photos_enhanced' => 'Enhanced photo storage with metadata',
            'business_hours' => 'Structured working hours',
            'popular_times_data' => 'Time-based popularity metrics'
        ];
        
        // Индексы
        $recommendations['indexes'] = [
            'idx_cid' => 'Index on Google CID',
            'idx_feature_id' => 'Index on DataForSEO Feature ID',
            'idx_price_level' => 'Index on price level',
            'idx_is_claimed' => 'Index on claimed status',
            'idx_attributes' => 'JSON index on attributes for filtering'
        ];
        
        return $recommendations;
    }
    
    public function downloadTestResult($filename)
    {
        $filepath = WRITEPATH . 'uploads/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found');
        }
        
        return $this->response->download($filepath, null);
    }
}