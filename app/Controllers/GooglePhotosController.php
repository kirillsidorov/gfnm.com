<?php

namespace App\Controllers;

use App\Libraries\GooglePlacesAPI;
use App\Models\RestaurantPhotoModel;  
use App\Models\RestaurantModel;
use App\Libraries\AdminLibrary;

/**
 * Финальный контроллер для управления Google Photos
 * Объединяет функционал превью и сохранения фотографий
 */
class GooglePhotosController extends BaseController
{
    protected $placesAPI;
    protected $photoModel;
    protected $restaurantModel;
    protected $adminLib;
    protected $db;

    public function __construct()
    {
        // Инициализируем компоненты как в Admin.php
        $this->photoModel = new RestaurantPhotoModel();
        $this->restaurantModel = new RestaurantModel();
        $this->adminLib = new AdminLibrary();
        $this->db = \Config\Database::connect();
        
        // GooglePlacesAPI инициализируем только при необходимости
        // чтобы избежать ошибок если API ключ не настроен
        try {
            $this->placesAPI = new GooglePlacesAPI();
        } catch (\Exception $e) {
            $this->placesAPI = null;
            log_message('warning', 'GooglePlacesAPI not initialized: ' . $e->getMessage());
        }
    }

    /**
     * Проверка авторизации админа
     */
    private function checkAdminAuth()
    {
        if (!$this->adminLib->isLoggedIn()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Access denied'
                ]);
            }
            return redirect()->to('/admin/login');
        }
        return null;
    }

    /**
     * Главная страница управления Google Photos
     */
    public function index()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        $stats = $this->getStatistics();

        $data = [
            'title' => 'Google Photos Management',
            'stats' => $stats
        ];

        return view('admin/google_photos', $data);
    }

    /**
     * Получить статистику для дашборда
     */
    private function getStatistics()
    {
        $totalRestaurants = $this->restaurantModel->where('is_active', 1)->countAllResults();

        $withPlaceId = $this->restaurantModel
            ->where('is_active', 1)
            ->where('google_place_id IS NOT NULL')
            ->where('google_place_id !=', '')
            ->countAllResults();

        $withPhotos = $this->db->query("
            SELECT COUNT(DISTINCT r.id) as count 
            FROM restaurants r 
            JOIN restaurant_photos rp ON rp.restaurant_id = r.id 
            WHERE r.is_active = 1
        ")->getRow()->count;

        $totalPhotos = $this->photoModel->countAllResults();

        $googlePhotos = $this->photoModel
            ->where('photo_reference IS NOT NULL')
            ->where('photo_reference !=', '')
            ->countAllResults();

        return [
            'total_restaurants' => $totalRestaurants,
            'with_place_id' => $withPlaceId,
            'place_id_percentage' => $totalRestaurants > 0 ? round(($withPlaceId / $totalRestaurants) * 100, 1) : 0,
            'with_photos' => $withPhotos,
            'photos_percentage' => $totalRestaurants > 0 ? round(($withPhotos / $totalRestaurants) * 100, 1) : 0,
            'total_photos' => $totalPhotos,
            'google_photos' => $googlePhotos,
            'without_place_id' => $totalRestaurants - $withPlaceId,
            'without_photos' => $totalRestaurants - $withPhotos
        ];
    }

    /**
     * Проверка статуса Google Places API
     */
    public function checkApiStatus()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        $apiKey = env('GOOGLE_PLACES_API_KEY');
        
        if (empty($apiKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Google Places API ключ не найден в .env файле'
            ]);
        }

        if (!$this->placesAPI) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'GooglePlacesAPI не инициализирован. Проверьте настройки API ключа.'
            ]);
        }

        try {
            // Тестируем с известным Place ID
            $testPlaceId = 'ChIJ7TQBkY_RD4gRQ286GefKeMk';
            $result = $this->placesAPI->getPlaceDetails($testPlaceId, ['place_id', 'name']);

            if (isset($result['status']) && $result['status'] === 'OK') {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Google Places API работает корректно',
                    'api_key_length' => strlen($apiKey)
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ошибка API: ' . ($result['error_message'] ?? $result['status'] ?? 'Unknown error')
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Исключение при тестировании API: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Превью фотографий ресторана (РАБОЧАЯ ВЕРСИЯ из старого контроллера)
     */
    public function previewPhotos($restaurantId)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        if (!$this->placesAPI) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Google Places API не инициализирован'
            ]);
        }

        $restaurant = $this->restaurantModel->find($restaurantId);

        if (!$restaurant || !$restaurant['google_place_id']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ресторан не найден или нет Google Place ID'
            ]);
        }

        try {
            $placeData = $this->placesAPI->getPlaceDetails(
                $restaurant['google_place_id'], 
                ['name', 'photos']
            );

            if (!isset($placeData['status']) || $placeData['status'] !== 'OK') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Не удалось получить данные из Google Places: ' . ($placeData['status'] ?? 'Unknown error')
                ]);
            }

            $photos = $placeData['result']['photos'] ?? [];
            $previews = [];

            // Создаем превью для первых 6 фотографий
            foreach (array_slice($photos, 0, 6) as $photo) {
                $photoReference = $photo['photo_reference'];
                $previewUrl = sprintf(
                    'https://maps.googleapis.com/maps/api/place/photo?photoreference=%s&maxwidth=400&key=%s',
                    $photoReference,
                    env('GOOGLE_PLACES_API_KEY')
                );

                $previews[] = [
                    'url' => $previewUrl,
                    'width' => $photo['width'] ?? 0,
                    'height' => $photo['height'] ?? 0,
                    'photo_reference' => $photoReference
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'restaurant_name' => $placeData['result']['name'] ?? $restaurant['name'],
                'total_photos' => count($photos),
                'previews' => $previews
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Импорт фотографий для конкретного ресторана (ФИНАЛЬНАЯ РАБОЧАЯ ВЕРСИЯ)
     */
    public function importPhotos($restaurantId)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        if (!is_numeric($restaurantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Неверный ID ресторана'
            ]);
        }

        try {
            $restaurant = $this->restaurantModel->find($restaurantId);
            if (!$restaurant) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ресторан не найден'
                ]);
            }

            if (empty($restaurant['google_place_id'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Place ID не установлен. Сначала найдите Place ID для этого ресторана.'
                ]);
            }

            // Проверяем есть ли уже фото
            $existingCount = $this->photoModel->getPhotoCount($restaurantId);
            
            if ($existingCount > 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "У ресторана уже есть {$existingCount} фотографий. Сначала удалите существующие если хотите обновить."
                ]);
            }

            $maxPhotos = min($this->request->getPost('max_photos') ?? 3, 5);
            $importedCount = $this->importPhotosForRestaurant($restaurantId, $restaurant['google_place_id'], $maxPhotos);

            return $this->response->setJSON([
                'success' => $importedCount > 0,
                'imported_count' => $importedCount,
                'message' => $importedCount > 0 ? 
                    "Успешно импортировано {$importedCount} фотографий" :
                    "Не удалось импортировать ни одной фотографии"
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Общая ошибка импорта: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Рабочий метод импорта фотографий (используем проверенный код)
     */
    private function importPhotosForRestaurant($restaurantId, $placeId, $maxPhotos = 3)
    {
        log_message('info', "🍽️ Начинаем импорт фотографий для ресторана ID {$restaurantId}");

        // Получаем фотографии из Google Places API (используем рабочий код)
        $url = 'https://maps.googleapis.com/maps/api/place/details/json';
        $params = [
            'place_id' => $placeId,
            'fields' => 'name,photos',
            'key' => env('GOOGLE_PLACES_API_KEY')
        ];

        $fullUrl = $url . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            log_message('error', "❌ Ошибка получения фотографий: {$curlError}, HTTP: {$httpCode}");
            return 0;
        }

        $data = json_decode($response, true);
        
        if (($data['status'] ?? '') !== 'OK') {
            log_message('error', "❌ API Error: " . ($data['status'] ?? 'Invalid JSON'));
            return 0;
        }

        $photos = $data['result']['photos'] ?? [];
        $restaurantName = $data['result']['name'] ?? 'Unknown';
        
        if (empty($photos)) {
            log_message('warning', "⚠️ Нет фотографий для ресторана {$restaurantName}");
            return 0;
        }

        // Создаем папку для фотографий ресторана
        $uploadDir = FCPATH . '../uploads/restaurants/' . $restaurantId . '/';
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                log_message('error', "❌ Не удалось создать папку: {$uploadDir}");
                return 0;
            }
        }

        $importedCount = 0;
        $processedPhotos = min($maxPhotos, count($photos));

        for ($i = 0; $i < $processedPhotos; $i++) {
            $photo = $photos[$i];
            $photoReference = $photo['photo_reference'];
            
            // Проверяем дубликаты
            if ($this->photoModel->photoReferenceExists($photoReference)) {
                log_message('info', "⏭️ Фото " . ($i + 1) . " уже существует, пропускаем");
                continue;
            }

            // Скачиваем фотографию (используем рабочий код)
            $photoUrl = 'https://maps.googleapis.com/maps/api/place/photo';
            $photoParams = [
                'photoreference' => $photoReference,
                'maxwidth' => 800,
                'key' => env('GOOGLE_PLACES_API_KEY')
            ];

            $photoFullUrl = $photoUrl . '?' . http_build_query($photoParams);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $photoFullUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);

            $photoData = curl_exec($ch);
            $photoHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $photoCurlError = curl_error($ch);
            curl_close($ch);

            if ($photoCurlError || $photoHttpCode !== 200 || empty($photoData)) {
                log_message('warning', "⚠️ Не удалось скачать фото " . ($i + 1));
                continue;
            }

            // Определяем расширение и сохраняем файл
            $header = substr($photoData, 0, 10);
            $extension = 'jpg';
            
            if (strpos($header, "\xFF\xD8\xFF") === 0) {
                $extension = 'jpg';
            } elseif (strpos($header, "\x89PNG") === 0) {
                $extension = 'png';
            } elseif (strpos($header, "GIF") === 0) {
                $extension = 'gif';
            } elseif (strpos($header, "WEBP") !== false) {
                $extension = 'webp';
            }

            $fileName = 'photo_' . $restaurantId . '_' . ($i + 1) . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            $relativePath = 'uploads/restaurants/' . $restaurantId . '/' . $fileName;

            $saved = file_put_contents($filePath, $photoData);
            
            if ($saved === false) {
                log_message('warning', "⚠️ Не удалось сохранить фото " . ($i + 1) . " на диск");
                continue;
            }

            // Получаем метаданные изображения
            $imageInfo = @getimagesize($filePath);
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;
            $fileSize = strlen($photoData);

            // Сохраняем в базу данных
            $photoMetadata = [
                'width' => $width,
                'height' => $height,
                'file_size' => $fileSize,
                'is_primary' => ($i === 0), // Первое фото главное
                'sort_order' => $i + 1,
                'alt_text' => $restaurantName . ' - photo ' . ($i + 1)
            ];

            $photoId = $this->photoModel->addPhoto($restaurantId, $relativePath, $photoReference, $photoMetadata);
            
            if ($photoId) {
                $importedCount++;
                log_message('info', "✅ Фото " . ($i + 1) . " импортировано (ID: {$photoId})");
            } else {
                // Удаляем файл если не удалось сохранить в БД
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                log_message('warning', "⚠️ Не удалось сохранить фото " . ($i + 1) . " в БД");
            }

            // Пауза между фотографиями
            usleep(200000); // 0.2 секунды
        }

        log_message('info', "🎉 Импорт завершен: {$importedCount}/{$processedPhotos} фотографий импортировано");
        return $importedCount;
    }

    /**
     * Массовый импорт фотографий
     */
    public function bulkImportPhotos()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        $limit = min($this->request->getPost('limit') ?? 5, 10);
        $photosPerRestaurant = min($this->request->getPost('photos_per_restaurant') ?? 3, 5);

        // Получаем рестораны без фотографий
        $restaurants = $this->photoModel->getRestaurantsWithoutPhotos($limit);

        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'total_photos' => 0,
            'details' => []
        ];

        foreach ($restaurants as $restaurant) {
            $results['processed']++;
            
            try {
                $importedCount = $this->importPhotosForRestaurant(
                    $restaurant['id'], 
                    $restaurant['google_place_id'],
                    $photosPerRestaurant
                );

                if ($importedCount > 0) {
                    $results['success']++;
                    $results['total_photos'] += $importedCount;
                    $results['details'][] = [
                        'restaurant' => $restaurant['name'],
                        'city' => $restaurant['city_name'],
                        'success' => true,
                        'photos_imported' => $importedCount,
                        'message' => "Импортировано {$importedCount} фото"
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'restaurant' => $restaurant['name'],
                        'city' => $restaurant['city_name'],
                        'success' => false,
                        'photos_imported' => 0,
                        'message' => 'Фотографии не найдены или ошибка импорта'
                    ];
                }

                // Пауза между ресторанами
                sleep(1);

            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'restaurant' => $restaurant['name'],
                    'city' => $restaurant['city_name'],
                    'success' => false,
                    'photos_imported' => 0,
                    'message' => 'Ошибка: ' . $e->getMessage()
                ];
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Обработано {$results['processed']} ресторанов. Успешно: {$results['success']}, Ошибок: {$results['failed']}, Всего фото: {$results['total_photos']}",
            'details' => $results
        ]);
    }

    /**
     * Поиск и установка Place ID для ресторана
     */
    public function setPlaceId($restaurantId)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        $restaurant = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.state')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->find($restaurantId);

        if (!$restaurant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ресторан не найден'
            ]);
        }

        if (!empty($restaurant['google_place_id'])) {
            return $this->response->setJSON([
                'success' => true,
                'place_id' => $restaurant['google_place_id'],
                'message' => 'Place ID уже установлен'
            ]);
        }

        try {
            $placeId = $this->findPlaceIdForRestaurant($restaurant);

            if ($placeId) {
                $this->restaurantModel->update($restaurantId, [
                    'google_place_id' => $placeId
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'place_id' => $placeId,
                    'message' => 'Place ID успешно установлен'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Place ID не найден в Google Places'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Поиск Place ID для ресторана
     */
    private function findPlaceIdForRestaurant($restaurant)
    {
        $query = $restaurant['name'] . ' restaurant ' . $restaurant['city_name'];
        if ($restaurant['state']) {
            $query .= ' ' . $restaurant['state'];
        }

        try {
            $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json';
            $params = [
                'query' => $query,
                'type' => 'restaurant',
                'key' => env('GOOGLE_PLACES_API_KEY')
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url . '?' . http_build_query($params),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true
            ]);

            $response = curl_exec($ch);
            curl_close($ch);
            
            $data = json_decode($response, true);

            if ($data['status'] === 'OK' && !empty($data['results'])) {
                return $data['results'][0]['place_id'];
            }

            return null;

        } catch (\Exception $e) {
            log_message('error', 'Place ID search error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получить список ресторанов без Place ID
     */
    public function restaurantsWithoutPlaceId()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        $restaurants = $this->restaurantModel
            ->select('restaurants.id, restaurants.name, cities.name as city_name')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('restaurants.is_active', 1)
            ->groupStart()
                ->where('restaurants.google_place_id IS NULL')
                ->orWhere('restaurants.google_place_id', '')
            ->groupEnd()
            ->orderBy('restaurants.name')
            ->limit(50)
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'restaurants' => $restaurants
        ]);
    }

    /**
     * Получить список ресторанов без фотографий
     */
    public function restaurantsWithoutPhotos()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        $restaurants = $this->photoModel->getRestaurantsWithoutPhotos(50);

        return $this->response->setJSON([
            'success' => true,
            'restaurants' => $restaurants
        ]);
    }

    /**
     * Статистика по фотографиям
     */
    public function getPhotosStats()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        try {
            $stats = [
                'total_restaurants' => 0,
                'restaurants_with_photos' => 0,
                'restaurants_without_photos' => 0,
                'total_photos' => 0,
                'total_file_size_mb' => 0,
                'average_photos_per_restaurant' => 0
            ];

            $stats['total_restaurants'] = $this->db->table('restaurants')
                ->where('is_active', 1)
                ->where('google_place_id IS NOT NULL')
                ->where('google_place_id !=', '')
                ->countAllResults();

            $stats['total_photos'] = $this->db->table('restaurant_photos')->countAllResults();

            $stats['restaurants_with_photos'] = $this->db->query("
                SELECT COUNT(DISTINCT restaurant_id) as count 
                FROM restaurant_photos 
                WHERE restaurant_id IN (
                    SELECT id FROM restaurants 
                    WHERE is_active = 1 
                    AND google_place_id IS NOT NULL 
                    AND google_place_id != ''
                )
            ")->getRowArray()['count'];

            $stats['restaurants_without_photos'] = $stats['total_restaurants'] - $stats['restaurants_with_photos'];

            $sizeResult = $this->db->query("SELECT SUM(file_size) as total_size FROM restaurant_photos")->getRowArray();
            $stats['total_file_size_mb'] = round(($sizeResult['total_size'] ?? 0) / 1024 / 1024, 2);

            if ($stats['restaurants_with_photos'] > 0) {
                $stats['average_photos_per_restaurant'] = round($stats['total_photos'] / $stats['restaurants_with_photos'], 2);
            }

            return $this->response->setJSON([
                'success' => true,
                'stats' => $stats,
                'progress_percentage' => $stats['total_restaurants'] > 0 
                    ? round(($stats['restaurants_with_photos'] / $stats['total_restaurants']) * 100, 2) 
                    : 0
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
        }
    }

    // ===========================================
    // ДИАГНОСТИЧЕСКИЕ МЕТОДЫ (для отладки)
    // ===========================================

    /**
     * Тест работы API
     */
    public function testBasic()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Контроллер работает!',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'method' => 'testBasic'
        ]);
    }

    /**
     * Тест базы данных
     */
    public function testDatabase()
    {
        try {
            $db = \Config\Database::connect();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'База данных подключена!',
                'db_name' => $db->getDatabase(),
                'method' => 'testDatabase'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ошибка БД: ' . $e->getMessage(),
                'method' => 'testDatabase'
            ]);
        }
    }

    /**
     * Тест API ключа
     */
    public function testApiKey()
    {
        $apiKey = env('GOOGLE_PLACES_API_KEY');
        
        return $this->response->setJSON([
            'success' => !empty($apiKey),
            'api_key_found' => !empty($apiKey),
            'api_key_length' => strlen($apiKey ?? ''),
            'api_key_preview' => !empty($apiKey) ? substr($apiKey, 0, 15) . '...' : 'not found',
            'method' => 'testApiKey'
        ]);
    }
}