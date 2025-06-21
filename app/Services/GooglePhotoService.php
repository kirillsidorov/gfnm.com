<?php

namespace App\Services;

use App\Libraries\GooglePlacesAPI;
use App\Models\RestaurantModel;
use App\Models\RestaurantPhotoModel;

/**
 * Сервис для работы с Google Places API
 * ТОЛЬКО для административных операций
 */
class GooglePhotoService
{
    private $googleAPI;
    private $restaurantModel;
    private $photoModel;

    public function __construct()
    {
        $this->googleAPI = new GooglePlacesAPI();
        $this->restaurantModel = new RestaurantModel();
        $this->photoModel = new RestaurantPhotoModel();
    }

    /**
     * Поиск и заполнение Place ID для ресторана по адресу и названию
     */
    public function findAndSetPlaceId($restaurantId)
    {
        // Получаем ресторан с информацией о городе
        $restaurant = $this->restaurantModel->getRestaurantWithCity($restaurantId);
        
        if (!$restaurant) {
            return ['success' => false, 'message' => 'Ресторан не найден'];
        }

        // Если Place ID уже есть, возвращаем его
        if (!empty($restaurant['google_place_id'])) {
            return [
                'success' => true, 
                'place_id' => $restaurant['google_place_id'],
                'message' => 'Place ID уже установлен'
            ];
        }

        // Формируем запрос для поиска
        $searchQuery = $restaurant['name'] . ' ' . $restaurant['address'];
        
        // Пробуем поиск по тексту
        $result = $this->googleAPI->searchByText($searchQuery);
        
        if (!$result['success']) {
            return [
                'success' => false, 
                'message' => 'Ошибка поиска в Google Places: ' . $result['message']
            ];
        }

        $places = $result['data']['results'] ?? [];
        
        if (empty($places)) {
            return [
                'success' => false, 
                'message' => 'Место не найдено в Google Places'
            ];
        }

        // Ищем наиболее подходящее место
        $bestMatch = $this->findBestMatch($restaurant, $places);
        
        if (!$bestMatch) {
            return [
                'success' => false, 
                'message' => 'Не удалось найти точное совпадение'
            ];
        }

        // Сохраняем Place ID
        $updated = $this->restaurantModel->update($restaurantId, [
            'google_place_id' => $bestMatch['place_id']
        ]);

        if ($updated) {
            return [
                'success' => true,
                'place_id' => $bestMatch['place_id'],
                'message' => 'Place ID успешно установлен'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка сохранения Place ID'
            ];
        }
    }

    /**
     * Поиск наиболее подходящего места из результатов Google Places
     */
    private function findBestMatch($restaurant, $places)
    {
        $bestScore = 0;
        $bestMatch = null;

        foreach ($places as $place) {
            $score = 0;

            // Проверяем название (самый важный критерий)
            $nameSimilarity = $this->calculateSimilarity(
                strtolower($restaurant['name']), 
                strtolower($place['name'])
            );
            $score += $nameSimilarity * 100;

            // Проверяем адрес
            if (isset($place['formatted_address'])) {
                $addressSimilarity = $this->calculateSimilarity(
                    strtolower($restaurant['address']), 
                    strtolower($place['formatted_address'])
                );
                $score += $addressSimilarity * 50;
            }

            // Проверяем тип места (должен быть ресторан)
            if (isset($place['types']) && 
                (in_array('restaurant', $place['types']) || in_array('food', $place['types']))) {
                $score += 20;
            }

            // Проверяем рейтинг (места с рейтингом предпочтительнее)
            if (isset($place['rating']) && $place['rating'] > 0) {
                $score += 10;
            }

            if ($score > $bestScore && $nameSimilarity > 0.6) { // Минимум 60% совпадение названия
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

    /**
     * Получение фотографий из Google Places и сохранение в систему
     */
    public function importGooglePhotos($restaurantId, $maxPhotos = 5)
    {
        // Получаем ресторан
        $restaurant = $this->restaurantModel->find($restaurantId);
        
        if (!$restaurant) {
            return ['success' => false, 'message' => 'Ресторан не найден'];
        }

        // Проверяем наличие Place ID
        if (empty($restaurant['google_place_id'])) {
            // Пытаемся найти и установить Place ID
            $placeIdResult = $this->findAndSetPlaceId($restaurantId);
            if (!$placeIdResult['success']) {
                return $placeIdResult;
            }
            $placeId = $placeIdResult['place_id'];
        } else {
            $placeId = $restaurant['google_place_id'];
        }

        // Получаем детали места с фотографиями
        $details = $this->googleAPI->getPlaceDetails($placeId);
        
        if (!$details['success']) {
            return [
                'success' => false,
                'message' => 'Ошибка получения деталей места: ' . $details['message']
            ];
        }

        $photos = $details['data']['result']['photos'] ?? [];
        
        if (empty($photos)) {
            return [
                'success' => false,
                'message' => 'У этого места нет фотографий в Google Places'
            ];
        }

        // Проверяем, есть ли уже фото у ресторана
        $existingPhotos = $this->photoModel->getRestaurantPhotos($restaurantId);
        $isFirstPhoto = empty($existingPhotos);

        $importedCount = 0;
        $errors = [];

        // Ограничиваем количество фото
        $photosToImport = array_slice($photos, 0, $maxPhotos);

        foreach ($photosToImport as $index => $photo) {
            try {
                // Получаем URL фотографии
                $photoUrl = $this->googleAPI->getPlacePhoto($photo['photo_reference'], 800);
                
                // Скачиваем и сохраняем фотографию
                $savedPath = $this->downloadAndSavePhoto($restaurantId, $photoUrl, $index);
                
                if ($savedPath) {
                    // Первое фото делаем главным, если у ресторана еще нет фото
                    $isMain = $isFirstPhoto && $index === 0;
                    
                    // Сохраняем в базу данных
                    $photoId = $this->photoModel->addPhoto($restaurantId, $savedPath, $isMain);
                    
                    if ($photoId) {
                        $importedCount++;
                    } else {
                        $errors[] = "Ошибка сохранения фото #" . ($index + 1) . " в базу данных";
                    }
                } else {
                    $errors[] = "Ошибка скачивания фото #" . ($index + 1);
                }
            } catch (\Exception $e) {
                $errors[] = "Ошибка обработки фото #" . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return [
            'success' => $importedCount > 0,
            'imported_count' => $importedCount,
            'total_available' => count($photos),
            'errors' => $errors,
            'message' => "Импортировано {$importedCount} из " . count($photosToImport) . " фотографий"
        ];
    }

    /**
     * Скачивание и сохранение фотографии на сервер
     */
    private function downloadAndSavePhoto($restaurantId, $photoUrl, $index)
    {
        try {
            // Создаем директорию для ресторана
            $uploadPath = FCPATH . 'uploads/restaurants/' . $restaurantId . '/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Скачиваем изображение
            $client = \Config\Services::curlrequest();
            $response = $client->get($photoUrl, ['timeout' => 30]);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP Error: ' . $response->getStatusCode());
            }

            $imageData = $response->getBody();
            
            // Определяем расширение файла по содержимому
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
            $filePath = $uploadPath . $fileName;

            // Сохраняем файл
            if (file_put_contents($filePath, $imageData) === false) {
                throw new \Exception('Не удалось записать файл');
            }

            // Возвращаем URL для базы данных
            return base_url('uploads/restaurants/' . $restaurantId . '/' . $fileName);

        } catch (\Exception $e) {
            log_message('error', 'Ошибка скачивания фото: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Массовое заполнение Place ID для всех ресторанов без них
     */
    public function fillMissingPlaceIds($limit = 10)
    {
        // Получаем рестораны без Place ID
        $restaurants = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name')
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
            
            $result = $this->findAndSetPlaceId($restaurant['id']);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'restaurant' => $restaurant['name'],
                'city' => $restaurant['city_name'],
                'success' => $result['success'],
                'message' => $result['message']
            ];

            // Небольшая пауза между запросами к Google API
            usleep(200000); // 0.2 секунды
        }

        return $results;
    }

    /**
     * Массовый импорт фотографий для ресторанов с Place ID
     */
    public function massImportPhotos($limit = 5, $photosPerRestaurant = 3)
    {
        // Получаем рестораны с Place ID, но без фотографий
        $restaurants = $this->restaurantModel
            ->select('restaurants.*')
            ->where('restaurants.is_active', 1)
            ->where('restaurants.google_place_id IS NOT NULL')
            ->where('restaurants.google_place_id !=', '')
            ->whereNotIn('restaurants.id', function($builder) {
                return $builder->select('restaurant_id')
                              ->from('restaurant_photos')
                              ->groupBy('restaurant_id');
            })
            ->limit($limit)
            ->findAll();

        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'total_photos' => 0,
            'details' => []
        ];

        foreach ($restaurants as $restaurant) {
            $results['processed']++;
            
            $result = $this->importGooglePhotos($restaurant['id'], $photosPerRestaurant);
            
            if ($result['success']) {
                $results['success']++;
                $results['total_photos'] += $result['imported_count'];
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'restaurant' => $restaurant['name'],
                'success' => $result['success'],
                'photos_imported' => $result['imported_count'] ?? 0,
                'message' => $result['message']
            ];

            // Пауза между запросами
            sleep(1); // 1 секунда между ресторанами
        }

        return $results;
    }
}