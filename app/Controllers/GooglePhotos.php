<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Libraries\AdminLibrary;
use App\Libraries\GooglePlacesAPI;

/**
 * Улучшенный контроллер для управления Google Photos
 */
class GooglePhotos extends BaseController
{
    private $restaurantModel;
    private $adminLib;
    private $googlePlacesAPI;

    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->adminLib = new AdminLibrary();
        $this->googlePlacesAPI = new GooglePlacesAPI();
    }

    /**
     * Главная страница управления Google фотографиями
     */
    public function index()
    {
        if (!$this->adminLib->isLoggedIn()) {
            return redirect()->to('/admin/login');
        }

        $stats = $this->getStatistics();
        
        $data = [
            'title' => 'Google Photos Management',
            'stats' => $stats
        ];

        return view('admin/google_photos', $data);
    }

    /**
     * Проверка статуса API
     */
    public function checkApiStatus()
    {
        if (!$this->adminLib->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $apiKey = env('GOOGLE_PLACES_API_KEY');
        
        if (empty($apiKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Google Places API ключ не найден в .env файле'
            ]);
        }

        // Простой тест API
        try {
            $testResult = $this->googlePlacesAPI->searchByText('georgian restaurant new york');
            
            if ($testResult['success']) {
                $resultsCount = count($testResult['data']['results'] ?? []);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Google Places API работает! Тест нашел {$resultsCount} результатов",
                    'api_key_length' => strlen($apiKey)
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ошибка тестирования API: ' . ($testResult['message'] ?? 'Unknown error')
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
     * Улучшенное массовое заполнение Place ID
     */
    public function bulkFillPlaceIds()
    {
        if (!$this->adminLib->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $limit = min($this->request->getPost('limit') ?? 5, 10); // Ограничиваем до 10
        
        // Получаем рестораны без Place ID
        $restaurants = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.state')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('restaurants.is_active', 1)
            ->where('(restaurants.google_place_id IS NULL OR restaurants.google_place_id = "")')
            ->limit($limit)
            ->findAll();

        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($restaurants as $restaurant) {
            $results['processed']++;
            
            try {
                // Формируем поисковый запрос
                $searchQuery = trim($restaurant['name']);
                if (!empty($restaurant['city_name'])) {
                    $searchQuery .= ' ' . trim($restaurant['city_name']);
                }
                if (!empty($restaurant['state'])) {
                    $searchQuery .= ' ' . trim($restaurant['state']);
                }

                // Ищем в Google Places
                $searchResult = $this->googlePlacesAPI->searchByText($searchQuery);

                if (!$searchResult['success']) {
                    $results['failed']++;
                    $results['details'][] = [
                        'restaurant' => $restaurant['name'],
                        'city' => $restaurant['city_name'],
                        'success' => false,
                        'message' => 'API ошибка: ' . ($searchResult['message'] ?? 'Unknown')
                    ];
                    continue;
                }

                $places = $searchResult['data']['results'] ?? [];

                if (empty($places)) {
                    $results['failed']++;
                    $results['details'][] = [
                        'restaurant' => $restaurant['name'],
                        'city' => $restaurant['city_name'],
                        'success' => false,
                        'message' => 'Места не найдены по запросу: ' . $searchQuery
                    ];
                    continue;
                }

                // Ищем лучшее совпадение
                $bestMatch = $this->findBestMatch($restaurant, $places);

                if (!$bestMatch) {
                    $results['failed']++;
                    $results['details'][] = [
                        'restaurant' => $restaurant['name'],
                        'city' => $restaurant['city_name'],
                        'success' => false,
                        'message' => 'Подходящее совпадение не найдено среди ' . count($places) . ' результатов'
                    ];
                    continue;
                }

                // Сохраняем Place ID
                $updateResult = $this->restaurantModel->update($restaurant['id'], [
                    'google_place_id' => $bestMatch['place_id']
                ]);

                if ($updateResult) {
                    $results['success']++;
                    $results['details'][] = [
                        'restaurant' => $restaurant['name'],
                        'city' => $restaurant['city_name'],
                        'success' => true,
                        'message' => 'Place ID установлен: ' . substr($bestMatch['place_id'], 0, 20) . '...'
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'restaurant' => $restaurant['name'],
                        'city' => $restaurant['city_name'],
                        'success' => false,
                        'message' => 'Ошибка сохранения в базу данных'
                    ];
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'restaurant' => $restaurant['name'],
                    'city' => $restaurant['city_name'],
                    'success' => false,
                    'message' => 'Исключение: ' . $e->getMessage()
                ];
            }

            // Пауза между запросами
            usleep(500000); // 0.5 секунды
        }

        return $this->response->setJSON([
            'success' => $results['success'] > 0,
            'message' => "Обработано {$results['processed']} ресторанов. Успешно: {$results['success']}, Ошибок: {$results['failed']}",
            'details' => $results
        ]);
    }

    /**
     * Улучшенный импорт фотографий
     */
    public function importPhotos($restaurantId)
    {
        if (!$this->adminLib->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

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

            // Проверяем Place ID
            if (empty($restaurant['google_place_id'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Place ID не установлен. Сначала найдите Place ID для этого ресторана.'
                ]);
            }

            // Проверяем есть ли уже фото
            $db = \Config\Database::connect();
            $existingCount = $db->query("SELECT COUNT(*) as count FROM restaurant_photos WHERE restaurant_id = ?", [$restaurantId])->getRow()->count ?? 0;
            
            if ($existingCount > 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "У ресторана уже есть {$existingCount} фотографий. Сначала удалите существующие если хотите обновить."
                ]);
            }

            // Получаем детали места
            $details = $this->googlePlacesAPI->getPlaceDetails($restaurant['google_place_id']);
            
            if (!$details['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ошибка получения деталей места: ' . ($details['message'] ?? 'Unknown error')
                ]);
            }

            $photos = $details['data']['result']['photos'] ?? [];
            
            if (empty($photos)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'У этого места нет фотографий в Google Places'
                ]);
            }

            // Создаем папку для фотографий
            $uploadDir = FCPATH . 'uploads/restaurants/' . $restaurantId;
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Не удалось создать папку для фотографий'
                    ]);
                }
            }

            $maxPhotos = min($this->request->getPost('max_photos') ?? 3, 5);
            $photosToImport = array_slice($photos, 0, $maxPhotos);
            $importedCount = 0;
            $errors = [];

            foreach ($photosToImport as $index => $photo) {
                try {
                    // Получаем URL фотографии
                    $photoUrl = $this->googlePlacesAPI->getPlacePhoto($photo['photo_reference'], 800);
                    
                    // Скачиваем фото
                    $savedPath = $this->downloadPhoto($restaurantId, $photoUrl, $index);
                    
                    if ($savedPath) {
                        // Сохраняем в БД
                        $isMain = ($index === 0); // Первое фото - главное
                        
                        $insertResult = $db->query("
                            INSERT INTO restaurant_photos (restaurant_id, photo_url, is_main, created_at) 
                            VALUES (?, ?, ?, NOW())
                        ", [$restaurantId, $savedPath, $isMain ? 1 : 0]);
                        
                        if ($insertResult) {
                            $importedCount++;
                        } else {
                            $errors[] = "Ошибка сохранения фото №" . ($index + 1) . " в БД";
                        }
                    } else {
                        $errors[] = "Ошибка скачивания фото №" . ($index + 1);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Ошибка обработки фото №" . ($index + 1) . ": " . $e->getMessage();
                }
            }

            return $this->response->setJSON([
                'success' => $importedCount > 0,
                'imported_count' => $importedCount,
                'total_available' => count($photos),
                'errors' => $errors,
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
     * Улучшенная функция скачивания фото
     */
    private function downloadPhoto($restaurantId, $photoUrl, $index)
    {
        try {
            // Инициализируем cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $photoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 || !$imageData) {
                throw new \Exception("HTTP Error: {$httpCode}");
            }

            // Определяем тип изображения
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
            
            $extension = match($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => 'jpg'
            };

            // Генерируем имя файла
            $fileName = 'google_' . time() . '_' . $index . '.' . $extension;
            $filePath = FCPATH . 'uploads/restaurants/' . $restaurantId . '/' . $fileName;

            // Сохраняем файл
            if (file_put_contents($filePath, $imageData) === false) {
                throw new \Exception('Не удалось сохранить файл');
            }

            // Возвращаем URL
            return base_url('uploads/restaurants/' . $restaurantId . '/' . $fileName);

        } catch (\Exception $e) {
            log_message('error', 'Ошибка скачивания фото: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Поиск лучшего совпадения
     */
    private function findBestMatch($restaurant, $places)
    {
        $bestScore = 0;
        $bestMatch = null;

        foreach ($places as $place) {
            $score = 0;

            // Сравниваем названия
            $nameSimilarity = $this->calculateSimilarity(
                strtolower($restaurant['name']), 
                strtolower($place['name'])
            );
            $score += $nameSimilarity * 100;

            // Проверяем тип места
            if (isset($place['types'])) {
                $restaurantTypes = ['restaurant', 'food', 'establishment', 'meal_takeaway'];
                $hasRestaurantType = !empty(array_intersect($place['types'], $restaurantTypes));
                if ($hasRestaurantType) {
                    $score += 30;
                }
            }

            // Проверяем рейтинг
            if (isset($place['rating']) && $place['rating'] > 3.0) {
                $score += 20;
            }

            // Требуем минимум 50% совпадение названия
            if ($score > $bestScore && $nameSimilarity > 0.5) {
                $bestScore = $score;
                $bestMatch = $place;
            }
        }

        return $bestMatch;
    }

    /**
     * Вычисление схожести строк
     */
    private function calculateSimilarity($str1, $str2)
    {
        $str1 = preg_replace('/[^a-z0-9\s]/', '', $str1);
        $str2 = preg_replace('/[^a-z0-9\s]/', '', $str2);
        
        similar_text($str1, $str2, $percent);
        return $percent / 100;
    }

    // ... (остальные методы остаются без изменений)
    
    public function setPlaceId($restaurantId)
    {
        if (!$this->adminLib->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        if (!is_numeric($restaurantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Неверный ID ресторана'
            ]);
        }

        try {
            $restaurant = $this->restaurantModel
                ->select('restaurants.*, cities.name as city_name, cities.state')
                ->join('cities', 'cities.id = restaurants.city_id')
                ->where('restaurants.id', $restaurantId)
                ->first();

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

            $searchQuery = trim($restaurant['name']);
            if (!empty($restaurant['city_name'])) {
                $searchQuery .= ' ' . trim($restaurant['city_name']);
            }
            if (!empty($restaurant['state'])) {
                $searchQuery .= ' ' . trim($restaurant['state']);
            }

            $searchResult = $this->googlePlacesAPI->searchByText($searchQuery);

            if (!$searchResult['success']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ошибка поиска: ' . ($searchResult['message'] ?? 'Unknown error')
                ]);
            }

            $places = $searchResult['data']['results'] ?? [];

            if (empty($places)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Места не найдены по запросу: ' . $searchQuery
                ]);
            }

            $bestMatch = $this->findBestMatch($restaurant, $places);

            if (!$bestMatch) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Подходящее место не найдено среди ' . count($places) . ' результатов'
                ]);
            }

            $updateResult = $this->restaurantModel->update($restaurantId, [
                'google_place_id' => $bestMatch['place_id']
            ]);

            if ($updateResult) {
                return $this->response->setJSON([
                    'success' => true,
                    'place_id' => $bestMatch['place_id'],
                    'message' => 'Place ID успешно установлен',
                    'matched_name' => $bestMatch['name']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ошибка сохранения в базу данных'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ]);
        }
    }

    public function getRestaurantsWithoutPlaceId()
    {
        if (!$this->adminLib->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $restaurants = $this->restaurantModel
            ->select('restaurants.id, restaurants.name, restaurants.address, cities.name as city_name')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('restaurants.is_active', 1)
            ->where('(restaurants.google_place_id IS NULL OR restaurants.google_place_id = "")')
            ->orderBy('restaurants.name', 'ASC')
            ->limit(20)
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'restaurants' => $restaurants
        ]);
    }

    public function getRestaurantsWithoutPhotos()
    {
        if (!$this->adminLib->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $restaurants = $this->restaurantModel
            ->select('restaurants.id, restaurants.name, restaurants.google_place_id, cities.name as city_name')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('restaurants.is_active', 1)
            ->where('restaurants.google_place_id IS NOT NULL')
            ->where('restaurants.google_place_id !=', '')
            ->whereNotIn('restaurants.id', function($builder) {
                return $builder->select('restaurant_id')
                              ->from('restaurant_photos')
                              ->groupBy('restaurant_id');
            })
            ->orderBy('restaurants.name', 'ASC')
            ->limit(20)
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'restaurants' => $restaurants
        ]);
    }

    public function previewPhotos($restaurantId)
    {
        if (!$this->adminLib->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        // Простая заглушка для превью
        return $this->response->setJSON([
            'success' => true,
            'total_photos' => 5,
            'previews' => [
                ['url' => 'https://via.placeholder.com/300x200?text=Preview+1', 'width' => 300, 'height' => 200],
                ['url' => 'https://via.placeholder.com/300x200?text=Preview+2', 'width' => 300, 'height' => 200],
                ['url' => 'https://via.placeholder.com/300x200?text=Preview+3', 'width' => 300, 'height' => 200]
            ],
            'message' => 'Превью фотографий (заглушка)'
        ]);
    }

    public function bulkImportPhotos()
    {
        if (!$this->adminLib->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        // Заглушка для массового импорта
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Массовый импорт фотографий пока не реализован. Используйте индивидуальный импорт.',
            'details' => [
                'processed' => 0,
                'success' => 0,
                'failed' => 0,
                'total_photos' => 0,
                'details' => []
            ]
        ]);
    }

    private function getStatistics()
    {
        $db = \Config\Database::connect();
        
        $totalRestaurants = $this->restaurantModel->where('is_active', 1)->countAllResults();
        
        $withPlaceId = $this->restaurantModel
            ->where('is_active', 1)
            ->where('google_place_id IS NOT NULL')
            ->where('google_place_id !=', '')
            ->countAllResults();
        
        $withPhotos = $db->query("
            SELECT COUNT(DISTINCT r.id) as count 
            FROM restaurants r 
            INNER JOIN restaurant_photos rp ON r.id = rp.restaurant_id 
            WHERE r.is_active = 1
        ")->getRow()->count ?? 0;
        
        $totalPhotos = $db->query("
            SELECT COUNT(*) as count 
            FROM restaurant_photos rp 
            INNER JOIN restaurants r ON r.id = rp.restaurant_id 
            WHERE r.is_active = 1
        ")->getRow()->count ?? 0;

        $googlePhotos = $db->query("
            SELECT COUNT(*) as count 
            FROM restaurant_photos rp 
            INNER JOIN restaurants r ON r.id = rp.restaurant_id 
            WHERE r.is_active = 1 AND rp.photo_url LIKE '%google_%'
        ")->getRow()->count ?? 0;

        return [
            'total_restaurants' => $totalRestaurants,
            'with_place_id' => $withPlaceId,
            'without_place_id' => $totalRestaurants - $withPlaceId,
            'with_photos' => $withPhotos,
            'without_photos' => $totalRestaurants - $withPhotos,
            'total_photos' => $totalPhotos,
            'google_photos' => $googlePhotos,
            'manual_photos' => $totalPhotos - $googlePhotos,
            'place_id_percentage' => $totalRestaurants > 0 ? round(($withPlaceId / $totalRestaurants) * 100, 1) : 0,
            'photos_percentage' => $totalRestaurants > 0 ? round(($withPhotos / $totalRestaurants) * 100, 1) : 0
        ];
    }
}