<?php

namespace App\Libraries;

class GooglePlacesAPI
{
    private $apiKey;
    private $baseUrl = 'https://maps.googleapis.com/maps/api/place/';

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_PLACES_API_KEY');
        
        if (empty($this->apiKey)) {
            throw new \Exception('Google Places API key not configured');
        }
    }

    /**
     * Поиск грузинских ресторанов поблизости
     */
    public function searchGeorgianRestaurants($latitude, $longitude, $radius = 5000)
    {
        $url = $this->baseUrl . 'nearbysearch/json';
        
        $params = [
            'location' => $latitude . ',' . $longitude,
            'radius' => $radius,
            'type' => 'restaurant',
            'keyword' => 'georgian food',
            'key' => $this->apiKey
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Текстовый поиск грузинских ресторанов
     */
    public function searchByText($query, $location = null)
    {
        $url = $this->baseUrl . 'textsearch/json';
        
        $params = [
            'query' => $query . ' georgian restaurant',
            'key' => $this->apiKey
        ];

        if ($location) {
            $params['location'] = $location;
            $params['radius'] = 50000; // 50km
        }

        return $this->makeRequest($url, $params);
    }

    /**
     * Получение детальной информации о ресторане
     * ОБНОВЛЕНО для поддержки кастомных полей и массивов
     */
    public function getPlaceDetails($placeId, $fields = null)
    {
        $url = $this->baseUrl . 'details/json';
        
        // Если поля не указаны, используем стандартный набор
        if (!$fields) {
            $fields = ['place_id', 'name', 'formatted_address', 'formatted_phone_number', 
                      'website', 'rating', 'opening_hours', 'photos', 'reviews', 'price_level', 'types'];
        }
        
        $params = [
            'place_id' => $placeId,
            'fields' => is_array($fields) ? implode(',', $fields) : $fields,
            'key' => $this->apiKey
        ];

        $response = $this->makeRequest($url, $params);
        
        // Возвращаем сырые данные для совместимости с новым контроллером
        if ($response['success']) {
            return $response['data'];
        } else {
            return [
                'status' => $response['error'] ?? 'ERROR',
                'error_message' => $response['message'] ?? 'Unknown error'
            ];
        }
    }

    /**
     * Получение фотографий места
     */
    public function getPlacePhoto($photoReference, $maxWidth = 400)
    {
        $url = $this->baseUrl . 'photo';
        
        $params = [
            'photoreference' => $photoReference,
            'maxwidth' => $maxWidth,
            'key' => $this->apiKey
        ];

        return $url . '?' . http_build_query($params);
    }

    /**
     * Скачать фотографию по photo_reference (УЛУЧШЕННАЯ ВЕРСИЯ)
     */
    public function downloadPhoto($photoReference, $maxWidth = 1200)
    {
        $url = $this->baseUrl . 'photo';
        $params = [
            'photoreference' => $photoReference,
            'maxwidth' => $maxWidth,
            'key' => $this->apiKey
        ];

        $fullUrl = $url . '?' . http_build_query($params);
        log_message('info', "🌐 Запрос к Google Photos API: " . substr($fullUrl, 0, 100) . "...");

        try {
            $client = \Config\Services::curlrequest();
            $response = $client->get($url, [
                'query' => $params,
                'timeout' => 30
            ]);

            $responseCode = $response->getStatusCode();
            log_message('info', "📡 HTTP ответ: {$responseCode}");

            if ($responseCode !== 200) {
                log_message('error', "❌ HTTP ошибка: {$responseCode}");
                return null;
            }

            // Проверяем что получили изображение
            $contentType = $response->getHeader('Content-Type');
            if ($contentType) {
                $contentTypeValue = $contentType->getValue();
                log_message('info', "📄 Content-Type: {$contentTypeValue}");
                
                if (strpos($contentTypeValue, 'image/') === 0) {
                    $imageData = $response->getBody();
                    $imageSize = strlen($imageData);
                    $imageSizeMB = round($imageSize / 1024 / 1024, 2);
                    
                    log_message('info', "📸 Изображение получено: {$imageSizeMB} MB ({$imageSize} bytes)");
                    return $imageData;
                } else {
                    log_message('error', "❌ Неверный тип контента: {$contentTypeValue}");
                    return null;
                }
            } else {
                log_message('warning', "⚠️ Content-Type не определен");
                return $response->getBody();
            }

        } catch (\Exception $e) {
            log_message('error', "❌ Ошибка скачивания фото: " . $e->getMessage());
            return null;
        }
    }

    /**
     * НОВЫЙ МЕТОД: Определить расширение изображения по данным
     */
    public function getImageExtension($imageData)
    {
        $header = substr($imageData, 0, 10);
        
        if (strpos($header, "\xFF\xD8\xFF") === 0) {
            return 'jpg';
        } elseif (strpos($header, "\x89PNG") === 0) {
            return 'png';
        } elseif (strpos($header, "GIF") === 0) {
            return 'gif';
        } elseif (strpos($header, "WEBP") !== false) {
            return 'webp';
        }
        
        return 'jpg'; // По умолчанию
    }

    /**
     * Сохранить фотографию в файловую систему (ФИНАЛЬНАЯ ВЕРСИЯ)
     */
    public function savePhoto($imageData, $filename)
    {
        // Используем правильный путь из диагностики
        $uploadPath = FCPATH  . '../uploads/restaurants/';
        
        log_message('info', "📁 Базовый путь WRITEPATH: " . FCPATH);
        log_message('info', "📁 Путь для сохранения: {$uploadPath}");
        
        // Создаем всю структуру папок
        if (!is_dir($uploadPath)) {
            log_message('info', "📂 Создаем структуру папок: {$uploadPath}");
            if (mkdir($uploadPath, 0755, true)) {
                log_message('info', "✅ Структура папок создана успешно");
            } else {
                log_message('error', "❌ Не удалось создать структуру папок");
                return null;
            }
        } else {
            log_message('info', "✅ Папка уже существует");
        }

        // Проверяем права записи
        if (!is_writable($uploadPath)) {
            log_message('error', "❌ Папка не доступна для записи: {$uploadPath}");
            log_message('info', "🔧 Пытаемся изменить права доступа...");
            
            if (chmod($uploadPath, 0755)) {
                log_message('info', "✅ Права доступа изменены на 0755");
            } else {
                log_message('error', "❌ Не удалось изменить права доступа");
                return null;
            }
        } else {
            log_message('info', "✅ Папка доступна для записи");
        }

        $fullPath = $uploadPath . $filename;
        log_message('info', "💾 Полный путь файла: {$fullPath}");
        
        // Проверяем свободное место
        $freeSpace = disk_free_space($uploadPath);
        if ($freeSpace !== false) {
            $freeMB = round($freeSpace / 1024 / 1024, 2);
            $imageSize = strlen($imageData);
            $imageMB = round($imageSize / 1024 / 1024, 2);
            
            log_message('info', "💽 Свободное место: {$freeMB} MB, размер изображения: {$imageMB} MB");
            
            if ($freeSpace < $imageSize * 2) {
                log_message('error', "❌ Недостаточно свободного места на диске");
                return null;
            }
        } else {
            log_message('warning', "⚠️ Не удалось определить свободное место");
        }
        
        // Проверяем что файл не существует
        if (file_exists($fullPath)) {
            log_message('warning', "⚠️ Файл уже существует, перезаписываем: {$filename}");
        }

        // Записываем файл
        log_message('info', "💾 Начинаем запись файла размером " . strlen($imageData) . " bytes...");
        
        // Сначала попробуем записать временный файл для теста
        $tempPath = $uploadPath . 'test_write_' . time() . '.tmp';
        $testData = 'test';
        
        if (file_put_contents($tempPath, $testData) !== false) {
            log_message('info', "✅ Тест записи успешен");
            unlink($tempPath); // Удаляем тестовый файл
        } else {
            log_message('error', "❌ Тест записи не удался");
            return null;
        }
        
        // Теперь записываем основной файл
        $bytesWritten = file_put_contents($fullPath, $imageData, LOCK_EX);
        
        if ($bytesWritten !== false) {
            $fileSizeMB = round($bytesWritten / 1024 / 1024, 2);
            log_message('info', "✅ Файл записан успешно: {$filename} ({$fileSizeMB} MB, {$bytesWritten} bytes)");
            
            // Проверяем что файл действительно создался
            if (file_exists($fullPath)) {
                $actualSize = filesize($fullPath);
                log_message('info', "✅ Проверка файла: размер на диске {$actualSize} bytes");
                
                if ($actualSize === $bytesWritten && $actualSize === strlen($imageData)) {
                    log_message('info', "✅ Все размеры совпадают - файл записан корректно");
                    
                    // Проверяем что файл читается
                    $testRead = file_get_contents($fullPath, false, null, 0, 100);
                    if ($testRead !== false) {
                        log_message('info', "✅ Файл успешно читается");
                    } else {
                        log_message('warning', "⚠️ Проблема с чтением файла");
                    }
                    
                    // Возвращаем относительный путь для базы данных
                    $relativePath = 'uploads/restaurants/' . $filename;
                    log_message('info', "📁 Относительный путь для БД: {$relativePath}");
                    return $relativePath;
                } else {
                    log_message('error', "❌ Размеры не совпадают! Ожидали: " . strlen($imageData) . ", записано: {$bytesWritten}, на диске: {$actualSize}");
                    return null;
                }
            } else {
                log_message('error', "❌ Файл не найден после записи!");
                return null;
            }
        } else {
            $error = error_get_last();
            log_message('error', "❌ Ошибка записи файла: {$filename}");
            if ($error) {
                log_message('error', "❌ Системная ошибка: " . $error['message']);
            }
            
            // Дополнительная диагностика
            log_message('error', "🔍 Дополнительная диагностика:");
            log_message('error', "   - Папка существует: " . (is_dir($uploadPath) ? 'да' : 'нет'));
            log_message('error', "   - Папка доступна для записи: " . (is_writable($uploadPath) ? 'да' : 'нет'));
            log_message('error', "   - Размер данных: " . strlen($imageData) . " bytes");
            log_message('error', "   - Имя файла: {$filename}");
            log_message('error', "   - Полный путь: {$fullPath}");
            
            return null;
        }
    }

    /**
     * НОВЫЙ МЕТОД: Диагностика файловой системы
     */
    public function diagnoseFileSystem()
    {
        $info = [
            'php_version' => phpversion(),
            'current_dir' => getcwd(),
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'не определен',
            'writepath_defined' => defined('WRITEPATH'),
            'writepath_value' => defined('WRITEPATH') ? WRITEPATH : 'не определена',
            'rootpath_defined' => defined('ROOTPATH'),
            'rootpath_value' => defined('ROOTPATH') ? ROOTPATH : 'не определена',
            'file_functions' => [
                'file_put_contents' => function_exists('file_put_contents'),
                'mkdir' => function_exists('mkdir'),
                'chmod' => function_exists('chmod'),
                'is_writable' => function_exists('is_writable'),
                'disk_free_space' => function_exists('disk_free_space')
            ]
        ];

        foreach ($info as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    log_message('info', "🔍 {$key}.{$subKey}: " . ($subValue ? 'да' : 'нет'));
                }
            } else {
                log_message('info', "🔍 {$key}: {$value}");
            }
        }

        return $info;
    }
    /**
     * НОВЫЙ МЕТОД: Поиск Place ID для ресторана
     */
    public function findPlaceId($restaurantName, $cityName, $state = null)
    {
        $query = $restaurantName . ' restaurant ' . $cityName;
        if ($state) {
            $query .= ' ' . $state;
        }

        $url = $this->baseUrl . 'textsearch/json';
        $params = [
            'query' => $query,
            'type' => 'restaurant',
            'key' => $this->apiKey
        ];

        $response = $this->makeRequest($url, $params);

        if ($response['success'] && !empty($response['data']['results'])) {
            return $response['data']['results'][0]['place_id'];
        }

        return null;
    }

    /**
     * Автодополнение для поиска мест
     */
    public function autocomplete($input, $location = null)
    {
        $url = $this->baseUrl . 'autocomplete/json';
        
        $params = [
            'input' => $input,
            'types' => 'establishment',
            'key' => $this->apiKey
        ];

        if ($location) {
            $params['location'] = $location;
            $params['radius'] = 50000;
        }

        return $this->makeRequest($url, $params);
    }

    /**
     * Выполнение HTTP запроса
     * ОБНОВЛЕНО с лучшей обработкой ошибок
     */
    private function makeRequest($url, $params)
    {
        $fullUrl = $url . '?' . http_build_query($params);
        
        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->get($fullUrl, [
                'timeout' => 30,
                'headers' => [
                    'User-Agent' => 'GeorgianFoodNearMe/1.0'
                ]
            ]);

            $body = $response->getBody();
            $data = json_decode($body, true);

            // ИСПРАВЛЕНИЕ: Проверяем существование ключа status
            if (!isset($data['status'])) {
                return [
                    'success' => false,
                    'error' => 'INVALID_RESPONSE',
                    'message' => 'Invalid API response format'
                ];
            }

            if ($data['status'] === 'OK') {
                return [
                    'success' => true,
                    'data' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $data['status'],
                    'message' => $data['error_message'] ?? 'Unknown error'
                ];
            }

        } catch (\Exception $e) {
            log_message('error', 'Google Places API request failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'REQUEST_FAILED',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Геокодирование адреса
     */
    public function geocodeAddress($address)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        
        $params = [
            'address' => $address,
            'key' => $this->apiKey
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Обратное геокодирование (координаты в адрес)
     */
    public function reverseGeocode($latitude, $longitude)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        
        $params = [
            'latlng' => $latitude . ',' . $longitude,
            'key' => $this->apiKey
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * НОВЫЙ МЕТОД: Проверить статус API
     */
    public function testConnection()
    {
        // Тестируем с известным Place ID
        $testPlaceId = 'ChIJ7TQBkY_RD4gRQ286GefKeMk';
        
        $result = $this->getPlaceDetails($testPlaceId, ['place_id', 'name']);
        
        return [
            'success' => isset($result['status']) && $result['status'] === 'OK',
            'status' => $result['status'] ?? 'UNKNOWN',
            'message' => $result['error_message'] ?? 'Connection test completed'
        ];
    }
}