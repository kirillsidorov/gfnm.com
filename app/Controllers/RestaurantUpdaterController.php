<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\RestaurantModel;
use App\Libraries\GooglePlacesAPI;

class RestaurantUpdaterController extends Controller
{
    protected $restaurantModel;
    protected $googleAPI;
    
    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->googleAPI = new GooglePlacesAPI();
    }

    /**
     * Проверка авторизации администратора
     */
    private function checkAdminAuth()
    {
        if (!session('admin_logged_in')) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Требуется авторизация администратора'
            ]);
        }
        return null;
    }

    /**
     * Обновление всех ресторанов с place_id
     */
    public function updateAllRestaurants()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        // Получаем все рестораны с place_id
        $restaurants = $this->restaurantModel
            ->where('google_place_id IS NOT NULL')
            ->where('google_place_id !=', '')
            ->findAll();

        if (empty($restaurants)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Не найдены рестораны с place_id для обновления'
            ]);
        }

        $results = [
            'total' => count($restaurants),
            'updated' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($restaurants as $restaurant) {
            $updateResult = $this->updateSingleRestaurant($restaurant['id']);
            
            if ($updateResult['success']) {
                $results['updated']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'id' => $restaurant['id'],
                'name' => $restaurant['name'],
                'success' => $updateResult['success'],
                'message' => $updateResult['message']
            ];

            // Небольшая пауза между запросами к API
            usleep(200000); // 0.2 секунды
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Обновлено: {$results['updated']}, Ошибок: {$results['failed']}, Всего: {$results['total']}",
            'data' => $results
        ]);
    }

    /**
     * Обновление одного ресторана
     */
    public function updateRestaurant($restaurantId = null)
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        $restaurantId = $restaurantId ?? $this->request->getPost('restaurant_id');
        
        if (!$restaurantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID ресторана не указан'
            ]);
        }

        $result = $this->updateSingleRestaurant($restaurantId);
        return $this->response->setJSON($result);
    }

    /**
     * Основная логика обновления ресторана
     */
    private function updateSingleRestaurant($restaurantId)
    {
        try {
            $restaurant = $this->restaurantModel->find($restaurantId);
            
            if (!$restaurant) {
                return [
                    'success' => false,
                    'message' => 'Ресторан не найден'
                ];
            }

            if (empty($restaurant['google_place_id'])) {
                return [
                    'success' => false,
                    'message' => 'У ресторана отсутствует place_id'
                ];
            }

            // Получаем детали места от Google Places API
            $placeDetails = $this->getPlaceDetails($restaurant['google_place_id']);
            
            if (!$placeDetails['success']) {
                return [
                    'success' => false,
                    'message' => 'Ошибка получения данных из Google Places: ' . $placeDetails['message']
                ];
            }

            // Подготавливаем данные для обновления
            $updateData = $this->prepareUpdateData($placeDetails['data'], $restaurant);
            
            // Обновляем ресторан в базе данных
            if ($this->restaurantModel->update($restaurantId, $updateData)) {
                return [
                    'success' => true,
                    'message' => 'Ресторан успешно обновлен',
                    'updated_fields' => array_keys($updateData)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Ошибка сохранения в базу данных'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Получение детальной информации о месте через Google Places API
     */
    private function getPlaceDetails($placeId)
    {
        try {
            // Указываем все поля, которые нам нужны
            $fields = [
                'name',
                'formatted_address',
                'international_phone_number',
                'formatted_phone_number',
                'website',
                'url',
                'editorial_summary',
                'rating',
                'user_ratings_total',
                'price_level',
                'opening_hours',
                'business_status',
                'types',
                'geometry',
                'photos',
                'reviews'
            ];

            $url = 'https://maps.googleapis.com/maps/api/place/details/json';
            $params = [
                'place_id' => $placeId,
                'fields' => implode(',', $fields),
                'key' => env('GOOGLE_PLACES_API_KEY'),
                'language' => 'ru' // Получаем данные на русском языке
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url . '?' . http_build_query($params),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'GeorgianFoodNearMe/1.0'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'message' => 'HTTP Error: ' . $httpCode
                ];
            }

            $data = json_decode($response, true);

            if ($data['status'] !== 'OK') {
                return [
                    'success' => false,
                    'message' => 'Google Places API Error: ' . ($data['status'] ?? 'Unknown error')
                ];
            }

            return [
                'success' => true,
                'data' => $data['result']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Подготовка данных для обновления ресторана
     * ОБНОВЛЕНО под существующую структуру базы данных
     */
    private function prepareUpdateData($googleData, $currentRestaurant)
    {
        $updateData = [];

        // Обновляем название, если оно есть и отличается
        if (!empty($googleData['name']) && $googleData['name'] !== $currentRestaurant['name']) {
            $updateData['name'] = $googleData['name'];
        }

        // Обновляем описание (используем существующее поле description)
        $description = $this->generateDescription($googleData);
        if (!empty($description) && $description !== $currentRestaurant['description']) {
            $updateData['description'] = $description;
        }

        // Обновляем адрес (используем существующее поле address)
        if (!empty($googleData['formatted_address']) && $googleData['formatted_address'] !== $currentRestaurant['address']) {
            $updateData['address'] = $googleData['formatted_address'];
        }

        // Обновляем телефон (используем существующее поле phone)
        $phone = $this->extractCleanPhone($googleData);
        if (!empty($phone) && $phone !== $currentRestaurant['phone']) {
            $updateData['phone'] = $phone;
        }

        // Обновляем сайт (используем существующее поле website, очищаем от UTM меток)
        $website = $this->extractCleanWebsite($googleData);
        if (!empty($website) && $website !== $currentRestaurant['website']) {
            $updateData['website'] = $website;
        }

        // Обновляем рейтинг (используем существующее поле rating)
        if (!empty($googleData['rating'])) {
            $newRating = (float)$googleData['rating'];
            if ($newRating !== (float)$currentRestaurant['rating']) {
                $updateData['rating'] = $newRating;
            }
        }

        // Обновляем количество отзывов (используем существующее поле rating_count)
        if (!empty($googleData['user_ratings_total'])) {
            $newRatingCount = (int)$googleData['user_ratings_total'];
            if ($newRatingCount !== (int)$currentRestaurant['rating_count']) {
                $updateData['rating_count'] = $newRatingCount;
            }
        }

        // Обновляем ценовую категорию (используем существующее поле price_level)
        if (isset($googleData['price_level'])) {
            $priceLevelMap = [
                1 => 'Недорого',
                2 => 'Умеренные цены', 
                3 => 'Дорого',
                4 => 'Очень дорого'
            ];
            $newPriceLevel = $priceLevelMap[$googleData['price_level']] ?? null;
            if ($newPriceLevel && $newPriceLevel !== $currentRestaurant['price_level']) {
                $updateData['price_level'] = $newPriceLevel;
            }
        }

        // Обновляем координаты (используем существующие поля latitude, longitude)
        if (!empty($googleData['geometry']['location'])) {
            $newLat = (float)$googleData['geometry']['location']['lat'];
            $newLng = (float)$googleData['geometry']['location']['lng'];
            
            if ($newLat !== (float)$currentRestaurant['latitude']) {
                $updateData['latitude'] = $newLat;
            }
            if ($newLng !== (float)$currentRestaurant['longitude']) {
                $updateData['longitude'] = $newLng;
            }
        }

        // Обновляем часы работы (используем существующее поле work_hours как JSON)
        if (!empty($googleData['opening_hours']['weekday_text'])) {
            $newWorkHours = json_encode($googleData['opening_hours']['weekday_text'], JSON_UNESCAPED_UNICODE);
            if ($newWorkHours !== $currentRestaurant['work_hours']) {
                $updateData['work_hours'] = $newWorkHours;
            }
        }

        // Обновляем статус бизнеса (используем существующее поле current_status)
        if (!empty($googleData['business_status'])) {
            $statusMap = [
                'OPERATIONAL' => 'open',
                'CLOSED_TEMPORARILY' => 'temporarily_closed',
                'CLOSED_PERMANENTLY' => 'permanently_closed'
            ];
            $newStatus = $statusMap[$googleData['business_status']] ?? 'open';
            if ($newStatus !== $currentRestaurant['current_status']) {
                $updateData['current_status'] = $newStatus;
            }
        }

        // Обновляем типы заведения (используем существующее поле category_ids как JSON)
        if (!empty($googleData['types'])) {
            $newTypes = json_encode($googleData['types'], JSON_UNESCAPED_UNICODE);
            if ($newTypes !== $currentRestaurant['category_ids']) {
                $updateData['category_ids'] = $newTypes;
            }
        }

        // Обновляем метку времени последнего обновления API (используем существующее поле last_updated_api)
        $updateData['last_updated_api'] = date('Y-m-d H:i:s');
        
        // Проверяем наличие дополнительного поля google_updated_at
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('restaurants');
        if (in_array('google_updated_at', $fields)) {
            $updateData['google_updated_at'] = date('Y-m-d H:i:s');
        }

        return $updateData;
    }

    /**
     * Генерация описания на основе данных Google Places
     */
    private function generateDescription($googleData)
    {
        $description = '';

        // Используем editorial_summary если есть
        if (!empty($googleData['editorial_summary']['overview'])) {
            $description = $googleData['editorial_summary']['overview'];
        }
        // Иначе создаем описание из доступных данных
        else {
            $parts = [];
            
            if (!empty($googleData['name'])) {
                $parts[] = $googleData['name'] . ' - грузинский ресторан';
            }
            
            if (!empty($googleData['rating']) && !empty($googleData['user_ratings_total'])) {
                $parts[] = "Рейтинг: {$googleData['rating']} ({$googleData['user_ratings_total']} отзывов)";
            }
            
            if (!empty($googleData['price_level'])) {
                $priceLabels = [
                    1 => 'Недорого',
                    2 => 'Умеренные цены', 
                    3 => 'Дорого',
                    4 => 'Очень дорого'
                ];
                if (isset($priceLabels[$googleData['price_level']])) {
                    $parts[] = $priceLabels[$googleData['price_level']];
                }
            }
            
            if (!empty($googleData['types'])) {
                $restaurantTypes = array_filter($googleData['types'], function($type) {
                    return in_array($type, ['restaurant', 'food', 'meal_takeaway', 'meal_delivery']);
                });
                if (!empty($restaurantTypes)) {
                    $parts[] = 'Услуги: ' . implode(', ', $restaurantTypes);
                }
            }
            
            $description = implode('. ', $parts);
        }

        return trim($description);
    }

    /**
     * Извлечение и очистка телефона
     */
    private function extractCleanPhone($googleData)
    {
        $phone = '';
        
        // Предпочитаем international_phone_number
        if (!empty($googleData['international_phone_number'])) {
            $phone = $googleData['international_phone_number'];
        } elseif (!empty($googleData['formatted_phone_number'])) {
            $phone = $googleData['formatted_phone_number'];
        }
        
        if (!empty($phone)) {
            // Очищаем телефон от лишних символов, оставляем только цифры и + ( ) - пробелы
            $phone = preg_replace('/[^\d\+\(\)\-\s]/', '', $phone);
            $phone = trim($phone);
        }
        
        return $phone;
    }

    /**
     * Извлечение и очистка веб-сайта от UTM меток
     */
    private function extractCleanWebsite($googleData)
    {
        $website = '';
        
        if (!empty($googleData['website'])) {
            $website = $googleData['website'];
        }
        
        if (!empty($website)) {
            // Парсим URL
            $parsedUrl = parse_url($website);
            
            if ($parsedUrl !== false && !empty($parsedUrl['host'])) {
                // Очищаем URL от UTM параметров и других трекинговых параметров
                $cleanUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                
                // Добавляем путь если есть
                if (!empty($parsedUrl['path']) && $parsedUrl['path'] !== '/') {
                    $cleanUrl .= $parsedUrl['path'];
                }
                
                // Обрабатываем query параметры, удаляя UTM и трекинговые
                if (!empty($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParams);
                    
                    // Удаляем UTM и другие трекинговые параметры
                    $trackingParams = [
                        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
                        'fbclid', 'gclid', 'mc_cid', 'mc_eid', '_ga', 'ref', 'referrer'
                    ];
                    
                    foreach ($trackingParams as $param) {
                        unset($queryParams[$param]);
                    }
                    
                    // Если остались полезные параметры, добавляем их
                    if (!empty($queryParams)) {
                        $cleanUrl .= '?' . http_build_query($queryParams);
                    }
                }
                
                $website = $cleanUrl;
            }
        }
        
        return $website;
    }

    /**
     * Пакетное обновление ресторанов по списку ID
     */
    public function updateRestaurantsBatch()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        $restaurantIds = $this->request->getPost('restaurant_ids');
        
        if (empty($restaurantIds) || !is_array($restaurantIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Не указаны ID ресторанов для обновления'
            ]);
        }

        $results = [
            'total' => count($restaurantIds),
            'updated' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($restaurantIds as $restaurantId) {
            $updateResult = $this->updateSingleRestaurant($restaurantId);
            
            if ($updateResult['success']) {
                $results['updated']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = [
                'id' => $restaurantId,
                'success' => $updateResult['success'],
                'message' => $updateResult['message']
            ];

            // Пауза между запросами
            usleep(200000);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Обновлено: {$results['updated']}, Ошибок: {$results['failed']}",
            'data' => $results
        ]);
    }

    /**
     * Получение статистики ресторанов с place_id
     */
    public function getRestaurantsStats()
    {
        $authCheck = $this->checkAdminAuth();
        if ($authCheck !== null) return $authCheck;

        $totalRestaurants = $this->restaurantModel->countAll();
        $withPlaceId = $this->restaurantModel
            ->where('google_place_id IS NOT NULL')
            ->where('google_place_id !=', '')
            ->countAllResults();
        
        // Проверяем существование поля last_updated_api
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('restaurants');
        
        if (in_array('last_updated_api', $fields)) {
            $needUpdate = $this->restaurantModel
                ->where('google_place_id IS NOT NULL')
                ->where('google_place_id !=', '')
                ->where('(last_updated_api IS NULL OR last_updated_api < DATE_SUB(NOW(), INTERVAL 30 DAY))')
                ->countAllResults();
        } else {
            // Если поле не существует, считаем что все нуждаются в обновлении
            $needUpdate = $withPlaceId;
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'total_restaurants' => $totalRestaurants,
                'with_place_id' => $withPlaceId,
                'without_place_id' => $totalRestaurants - $withPlaceId,
                'need_update' => $needUpdate
            ]
        ]);
    }

    /**
     * Отображение страницы управления обновлениями
     */
    public function updaterPage()
    {
        if (!session('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        return view('admin/restaurant_updater');
    }
}