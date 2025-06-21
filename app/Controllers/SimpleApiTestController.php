<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class SimpleApiTestController extends Controller
{
    public function index($location = null)
    {
        // Настройки API
        $apiLogin = getenv('DATAFORSEO_LOGIN') ?: 'your_login';
        $apiPassword = getenv('DATAFORSEO_PASSWORD') ?: 'your_password';
        
        // Проверяем учетные данные
        if ($apiLogin === 'your_login' || $apiPassword === 'your_password') {
            return $this->showError('Please configure DATAFORSEO_LOGIN and DATAFORSEO_PASSWORD in your .env file');
        }
        
        // Тестовые локации
        $locations = [
            'nyc' => [
                'name' => 'New York City',
                'coords' => '40.7580,-73.9855,25',
                'terms' => 'georgian restaurant khachapuri'
            ],
            'la' => [
                'name' => 'Los Angeles', 
                'coords' => '34.0522,-118.2437,30',
                'terms' => 'georgian restaurant'
            ],
            'chicago' => [
                'name' => 'Chicago',
                'coords' => '41.8781,-87.6298,25', 
                'terms' => 'georgian cuisine'
            ]
        ];
        
        // Выбираем локацию
        $location = $location ?: ($this->request->getGet('location') ?: 'nyc');
        $selectedLocation = $locations[$location] ?? $locations['nyc'];
        
        // Подготовка данных для запроса
        $postData = [
            [
                'title' => 'georgian restaurant',
                'description' => $selectedLocation['terms'],
                'categories' => ['restaurant', 'ethnic_restaurant'],
                'location_coordinate' => $selectedLocation['coords'],
                'is_claimed' => true,
                'order_by' => ['rating.value,desc'],
                'filters' => [['rating.value', '>', 3.0]],
                'limit' => 10
            ]
        ];
        
        try {
            // Выполняем запрос
            $startTime = microtime(true);
            
            $client = \Config\Services::curlrequest();
            $response = $client->post('https://api.dataforseo.com/v3/business_data/business_listings/search/live', [
                'auth' => [$apiLogin, $apiPassword],
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $postData,
                'timeout' => 30
            ]);
            
            $responseTime = microtime(true) - $startTime;
            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                return $this->showError("API request failed with HTTP status: {$statusCode}");
            }
            
            $data = json_decode($response->getBody(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->showError('Invalid JSON response: ' . json_last_error_msg());
            }
            
            // Сохраняем результат
            $filename = 'api_test_' . $location . '_' . date('Y-m-d_H-i-s') . '.json';
            $filepath = WRITEPATH . 'uploads/' . $filename;
            
            // Создаем директорию если не существует
            if (!is_dir(WRITEPATH . 'uploads/')) {
                mkdir(WRITEPATH . 'uploads/', 0755, true);
            }
            
            file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // Обрабатываем результат
            $result = $this->processResults($data, $selectedLocation, $responseTime, $filename);
            
            return view('simple_api_results', [
                'result' => $result,
                'locations' => $locations,
                'current_location' => $location
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'DataForSEO API Error: ' . $e->getMessage());
            return $this->showError('API request failed: ' . $e->getMessage());
        }
    }
    
    private function processResults($data, $location, $responseTime, $filename)
    {
        $result = [
            'success' => $data['status_code'] == 20000,
            'location' => $location,
            'general' => [
                'status' => $data['status_message'],
                'time' => $data['time'],
                'cost' => $data['cost'],
                'response_time' => round($responseTime, 4)
            ],
            'restaurants' => [],
            'filename' => $filename,
            'raw_data' => $data
        ];
        
        if (!$result['success']) {
            $result['error'] = $data['status_message'];
            return $result;
        }
        
        // Обрабатываем рестораны
        foreach ($data['tasks'] as $task) {
            if ($task['status_code'] == 20000) {
                $taskResult = $task['result'][0];
                $result['total_found'] = $taskResult['total_count'];
                $result['returned_count'] = $taskResult['count'];
                
                foreach ($taskResult['items'] as $item) {
                    $restaurant = [
                        'name' => $item['title'] ?? 'N/A',
                        'category' => $item['category'] ?? 'N/A', 
                        'address' => $item['address'] ?? 'N/A',
                        'phone' => $item['phone'] ?? 'N/A',
                        'website' => $item['url'] ?? 'N/A',
                        'place_id' => $item['place_id'] ?? 'N/A',
                        'rating' => $item['rating']['value'] ?? 0,
                        'rating_count' => $item['rating']['votes_count'] ?? 0,
                        'price_level' => $item['price_level'] ?? 'N/A',
                        'photos_count' => $item['total_photos'] ?? 0,
                        'is_claimed' => $item['is_claimed'] ?? false,
                        'current_status' => $item['work_time']['work_hours']['current_status'] ?? 'unknown'
                    ];
                    
                    // Проверяем основные атрибуты
                    if (isset($item['attributes']['available_attributes'])) {
                        $attrs = $item['attributes']['available_attributes'];
                        $restaurant['has_delivery'] = in_array('has_delivery', $attrs['service_options'] ?? []);
                        $restaurant['has_takeout'] = in_array('has_takeout', $attrs['service_options'] ?? []);
                        $restaurant['wheelchair_accessible'] = !empty($attrs['accessibility'] ?? []);
                        $restaurant['accepts_reservations'] = in_array('accepts_reservations', $attrs['planning'] ?? []);
                        $restaurant['serves_alcohol'] = in_array('serves_alcohol', $attrs['offerings'] ?? []);
                        $restaurant['family_friendly'] = in_array('welcomes_children', $attrs['children'] ?? []);
                    }
                    
                    $result['restaurants'][] = $restaurant;
                }
            }
        }
        
        return $result;
    }
    
    private function showError($message)
    {
        return view('simple_api_results', [
            'error' => $message,
            'locations' => [
                'nyc' => ['name' => 'New York City'],
                'la' => ['name' => 'Los Angeles'],
                'chicago' => ['name' => 'Chicago']
            ],
            'current_location' => 'nyc'
        ]);
    }
    
    public function download($filename)
    {
        $filepath = WRITEPATH . 'uploads/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found');
        }
        
        return $this->response->download($filepath, null);
    }
}