<?php

namespace App\Controllers;

use App\Models\RestaurantModel;
use App\Models\CityModel;
use App\Models\RestaurantPhotoModel;
use App\Services\GooglePhotoService;

class RestaurantsEnhance extends BaseController
{
    protected $restaurantModel;
    protected $cityModel;
    protected $photoModel;
    protected $googlePhotoService;

    public function __construct()
    {
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
        $this->googlePhotoService = new GooglePhotoService();
        $this->photoModel = new RestaurantPhotoModel();
        helper('text');
    }

    /**
     * УЛУЧШЕННАЯ детальная страница ресторана с полным функционалом
     */
    public function view($id)
    {
        if (!is_numeric($id)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }

        $restaurant = $this->restaurantModel->getRestaurantWithCity($id);

        if (!$restaurant) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }

        // Получаем фотографии ресторана
        $photos = $this->photoModel->getRestaurantPhotos($restaurant['id']);
        $mainPhoto = $this->photoModel->getMainPhoto($restaurant['id']);
        $galleryPhotos = $this->photoModel->getGalleryPhotos($restaurant['id']);

        // Обрабатываем JSON поля и декодируем их
        $restaurant = $this->processRestaurantData($restaurant);

        // Получаем похожие рестораны
        $similarRestaurants = $this->getSimilarRestaurants($restaurant);

        // Получаем статистику популярности
        $popularityStats = $this->calculatePopularityStats($restaurant);

        // Генерируем структурированные данные для SEO
        $structuredData = $this->generateStructuredData($restaurant, $photos);

        $data = [
            'title' => $restaurant['name'] . ' - Georgian Restaurant in ' . $restaurant['city_name'],
            'meta_description' => $this->generateMetaDescription($restaurant),
            'restaurant' => $restaurant,
            'photos' => $photos,
            'mainPhoto' => $mainPhoto,
            'galleryPhotos' => $galleryPhotos,
            'similarRestaurants' => $similarRestaurants,
            'popularityStats' => $popularityStats,
            'structuredData' => $structuredData,
            'breadcrumbs' => $this->generateBreadcrumbs($restaurant),
            'og_image' => $mainPhoto ? base_url($mainPhoto['file_path']) : null,
            'canonical_url' => base_url($restaurant['seo_url'] ?: 'restaurant/' . $id)
        ];

        return view('restaurants/enhanced_view', $data);
    }


    /**
     * УЛУЧШЕННАЯ детальная страница ресторана с полным функционалом
     */
    public function restaurantDetail($restaurantSlug)
    {
        $restaurant = $this->restaurantModel->getBySeoUrl($restaurantSlug);

        if (!$restaurant) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Restaurant not found');
        }

        // Получаем фотографии ресторана
        $photos = $this->photoModel->getRestaurantPhotos($restaurant['id']);
        $mainPhoto = $this->photoModel->getMainPhoto($restaurant['id']);
        $galleryPhotos = $this->photoModel->getGalleryPhotos($restaurant['id']);

        // Обрабатываем JSON поля и декодируем их
        $restaurant = $this->processRestaurantData($restaurant);

        // Получаем похожие рестораны
        $similarRestaurants = $this->getSimilarRestaurants($restaurant);

        // Получаем статистику популярности
        $popularityStats = $this->calculatePopularityStats($restaurant);

        // Генерируем структурированные данные для SEO
        $structuredData = $this->generateStructuredData($restaurant, $photos);

        $data = [
            'title' => $restaurant['name'] . ' - Georgian Restaurant in ' . $restaurant['city_name'],
            'meta_description' => $this->generateMetaDescription($restaurant),
            'restaurant' => $restaurant,
            'photos' => $photos,
            'mainPhoto' => $mainPhoto,
            'galleryPhotos' => $galleryPhotos,
            'similarRestaurants' => $similarRestaurants,
            'popularityStats' => $popularityStats,
            'structuredData' => $structuredData,
            'breadcrumbs' => $this->generateBreadcrumbs($restaurant),
            'og_image' => $mainPhoto ? base_url($mainPhoto['file_path']) : null,
            'canonical_url' => base_url($restaurant['seo_url'] ?: 'restaurant/' . $id)
        ];

        return view('restaurants/enhanced_view', $data);
    }

/**
     * Обработка данных ресторана - декодирование JSON полей (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    private function processRestaurantData($restaurant)
    {
        $jsonFields = [
            'work_hours', 'popular_times', 'rating_distribution',
            'service_options', 'accessibility_options', 'dining_options',
            'atmosphere', 'crowd_info', 'payment_options',
            'people_also_search', 'place_topics', 'business_links',
            'category_ids', 'additional_categories', 'attributes'
        ];

        foreach ($jsonFields as $field) {
            if (!empty($restaurant[$field]) && is_string($restaurant[$field])) {
                $decoded = json_decode($restaurant[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $restaurant[$field] = $decoded;
                } else {
                    // Если JSON невалидный, оставляем как есть или делаем пустым массивом
                    if (in_array($field, ['people_also_search', 'place_topics', 'service_options'])) {
                        $restaurant[$field] = [];
                    }
                }
            }
        }

        // Загружаем связанные рестораны из отдельной таблицы
        $restaurant['related_restaurants'] = $this->getRelatedRestaurants($restaurant['id']);

        // Обрабатываем статус ресторана
        $restaurant['status_info'] = $this->getStatusInfo($restaurant);

        // Обрабатываем рабочие часы
        $restaurant['hours_info'] = $this->processWorkHours($restaurant['work_hours'] ?? []);

        // Вычисляем средние популярные времена
        $restaurant['avg_popular_times'] = $this->calculateAveragePopularTimes($restaurant['popular_times'] ?? []);

        return $restaurant;
    }

    /**
     * Получение связанных ресторанов из таблицы restaurant_relations
     */
    private function getRelatedRestaurants($restaurantId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('restaurant_relations')
                  ->where('restaurant_id', $restaurantId)
                  ->where('relation_type', 'people_also_search')
                  ->where('related_name !=', '')
                  ->orderBy('related_rating', 'DESC')
                  ->limit(8)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Получение информации о статусе ресторана
     */
    private function getStatusInfo($restaurant)
    {
        $status = $restaurant['current_status'] ?? 'open';
        $workHours = $restaurant['work_hours'] ?? [];

        $statusInfo = [
            'status' => $status,
            'is_open_now' => false,
            'next_change' => null,
            'class' => 'status-closed',
            'icon' => 'fas fa-clock',
            'message' => 'Status Unknown'
        ];

        if ($status === 'permanently_closed') {
            $statusInfo['message'] = 'Permanently Closed';
            $statusInfo['class'] = 'status-closed';
            $statusInfo['icon'] = 'fas fa-times-circle';
            return $statusInfo;
        }

        if ($status === 'temporarily_closed') {
            $statusInfo['message'] = 'Temporarily Closed';
            $statusInfo['class'] = 'status-temporarily-closed';
            $statusInfo['icon'] = 'fas fa-pause-circle';
            return $statusInfo;
        }

        // Проверяем открыт ли сейчас
        if (!empty($workHours)) {
            $today = strtolower(date('l'));
            $currentTime = date('H:i');

            if (isset($workHours[$today])) {
                $todayHours = $workHours[$today];
                if (!empty($todayHours['open']) && !empty($todayHours['close'])) {
                    $openTime = $todayHours['open'];
                    $closeTime = $todayHours['close'];

                    if ($currentTime >= $openTime && $currentTime < $closeTime) {
                        $statusInfo['is_open_now'] = true;
                        $statusInfo['message'] = 'Open Now';
                        $statusInfo['class'] = 'status-open';
                        $statusInfo['icon'] = 'fas fa-clock';
                        $statusInfo['next_change'] = "Closes at {$closeTime}";
                    } else {
                        $statusInfo['message'] = 'Closed';
                        $statusInfo['next_change'] = "Opens at {$openTime}";
                    }
                }
            }
        }

        return $statusInfo;
    }

    /**
     * Обработка рабочих часов
     */
    private function processWorkHours($workHours)
    {
        if (empty($workHours)) {
            return [
                'formatted' => [],
                'today' => null,
                'is_open_today' => false
            ];
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $today = strtolower(date('l'));

        $formatted = [];
        $todayHours = null;

        foreach ($days as $index => $day) {
            $hours = $workHours[$day] ?? null;
            $isToday = $day === $today;

            if ($hours && !empty($hours['open']) && !empty($hours['close'])) {
                $timeString = $this->formatTime($hours['open']) . ' - ' . $this->formatTime($hours['close']);
                $isOpen = true;
            } else {
                $timeString = 'Closed';
                $isOpen = false;
            }

            $formatted[] = [
                'day' => $dayNames[$index],
                'day_key' => $day,
                'hours' => $timeString,
                'is_today' => $isToday,
                'is_open' => $isOpen,
                'raw' => $hours
            ];

            if ($isToday) {
                $todayHours = [
                    'hours' => $timeString,
                    'is_open' => $isOpen,
                    'raw' => $hours
                ];
            }
        }

        return [
            'formatted' => $formatted,
            'today' => $todayHours,
            'is_open_today' => $todayHours ? $todayHours['is_open'] : false
        ];
    }

    /**
     * Форматирование времени
     */
    private function formatTime($time)
    {
        if (empty($time)) return '';
        
        // Конвертируем 24-часовой формат в 12-часовой
        $timestamp = strtotime($time);
        return $timestamp ? date('g:i A', $timestamp) : $time;
    }

    /**
     * Вычисление средних популярных времен
     */
    private function calculateAveragePopularTimes($popularTimes)
    {
        if (empty($popularTimes)) return [];

        $hours = [];
        $totalDays = 0;

        foreach ($popularTimes as $day => $times) {
            if (is_array($times)) {
                $totalDays++;
                foreach ($times as $hour => $value) {
                    if (!isset($hours[$hour])) $hours[$hour] = 0;
                    $hours[$hour] += is_numeric($value) ? (int)$value : 0;
                }
            }
        }

        if ($totalDays === 0) return [];

        // Вычисляем среднее
        foreach ($hours as $hour => $total) {
            $hours[$hour] = round($total / $totalDays);
        }

        return $hours;
    }

    /**
     * Получение похожих ресторанов с дополнительной информацией
     */
    private function getSimilarRestaurants($restaurant)
    {
        $similarRestaurants = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('restaurants.city_id', $restaurant['city_id'])
            ->where('restaurants.id !=', $restaurant['id'])
            ->where('restaurants.is_active', 1)
            ->orderBy('restaurants.rating', 'DESC')
            ->limit(4)
            ->findAll();

        // Добавляем фото и обрабатываем данные для каждого похожего ресторана
        foreach ($similarRestaurants as &$similar) {
            $similar['main_photo'] = $this->photoModel->getMainPhoto($similar['id']);
            $similar = $this->processRestaurantData($similar);
        }

        return $similarRestaurants;
    }

    /**
     * Вычисление статистики популярности
     */
    private function calculatePopularityStats($restaurant)
    {
        $stats = [
            'peak_hours' => [],
            'busiest_day' => null,
            'quietest_day' => null,
            'average_wait_time' => null,
            'popularity_score' => 0
        ];

        $popularTimes = $restaurant['popular_times'] ?? [];
        if (empty($popularTimes)) return $stats;

        $dayTotals = [];
        $hourTotals = [];

        // Анализируем популярные времена
        foreach ($popularTimes as $day => $hours) {
            if (!is_array($hours)) continue;

            $dayTotal = array_sum(array_filter($hours, 'is_numeric'));
            $dayTotals[$day] = $dayTotal;

            foreach ($hours as $hour => $value) {
                if (!isset($hourTotals[$hour])) $hourTotals[$hour] = 0;
                $hourTotals[$hour] += is_numeric($value) ? (int)$value : 0;
            }
        }

        // Находим самый загруженный и самый тихий день
        if (!empty($dayTotals)) {
            $stats['busiest_day'] = array_keys($dayTotals, max($dayTotals))[0];
            $stats['quietest_day'] = array_keys($dayTotals, min($dayTotals))[0];
        }

        // Находим пиковые часы
        if (!empty($hourTotals)) {
            arsort($hourTotals);
            $stats['peak_hours'] = array_slice(array_keys($hourTotals), 0, 3);
        }

        // Вычисляем общий балл популярности
        $totalRating = $restaurant['rating'] ?? 0;
        $totalReviews = $restaurant['rating_count'] ?? 0;
        $avgPopularity = !empty($dayTotals) ? array_sum($dayTotals) / count($dayTotals) : 0;

        $stats['popularity_score'] = ($totalRating * 10) + ($totalReviews * 0.1) + ($avgPopularity * 0.5);

        return $stats;
    }

    /**
     * Генерация структурированных данных для SEO
     */
    private function generateStructuredData($restaurant, $photos = [])
    {
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $restaurant['name'],
            'description' => $restaurant['description'],
            'url' => base_url($restaurant['seo_url'] ?: 'restaurant/' . $restaurant['id']),
            'telephone' => $restaurant['phone'],
            'servesCuisine' => 'Georgian',
            'priceRange' => str_repeat('$', intval($restaurant['price_level'] ?? 1)),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $restaurant['address'],
                'addressLocality' => $restaurant['city_name'],
                'addressRegion' => $restaurant['address_region'] ?? '',
                'postalCode' => $restaurant['address_zip'] ?? '',
                'addressCountry' => $restaurant['address_country_code'] ?? 'US'
            ]
        ];

        // Добавляем координаты если есть
        if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])) {
            $structuredData['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $restaurant['latitude'],
                'longitude' => $restaurant['longitude']
            ];
        }

        // Добавляем рейтинг если есть
        if (!empty($restaurant['rating']) && !empty($restaurant['rating_count'])) {
            $structuredData['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $restaurant['rating'],
                'reviewCount' => $restaurant['rating_count'],
                'bestRating' => '5',
                'worstRating' => '1'
            ];
        }

        // Добавляем рабочие часы
        if (!empty($restaurant['work_hours'])) {
            $openingHours = [];
            $days = [
                'monday' => 'Mo',
                'tuesday' => 'Tu', 
                'wednesday' => 'We',
                'thursday' => 'Th',
                'friday' => 'Fr',
                'saturday' => 'Sa',
                'sunday' => 'Su'
            ];

            foreach ($restaurant['work_hours'] as $day => $hours) {
                if (!empty($hours['open']) && !empty($hours['close'])) {
                    $dayCode = $days[$day] ?? '';
                    if ($dayCode) {
                        $openingHours[] = $dayCode . ' ' . $hours['open'] . '-' . $hours['close'];
                    }
                }
            }

            if (!empty($openingHours)) {
                $structuredData['openingHours'] = $openingHours;
            }
        }

        // Добавляем фотографии
        if (!empty($photos)) {
            $images = [];
            foreach ($photos as $photo) {
                $images[] = base_url($photo['file_path']);
            }
            $structuredData['image'] = $images;
        }

        // Добавляем веб-сайт
        if (!empty($restaurant['website'])) {
            $structuredData['sameAs'] = [$restaurant['website']];
        }

        return $structuredData;
    }

    /**
     * Генерация breadcrumbs
     */
    private function generateBreadcrumbs($restaurant)
    {
        return [
            [
                'name' => 'Home',
                'url' => base_url()
            ],
            [
                'name' => 'Georgian Restaurants in ' . $restaurant['city_name'],
                'url' => base_url('georgian-restaurants-' . ($restaurant['city_slug'] ?? strtolower(str_replace(' ', '-', $restaurant['city_name']))))
            ],
            [
                'name' => $restaurant['name'],
                'url' => current_url()
            ]
        ];
    }

    /**
     * Генерация мета-описания
     */
    private function generateMetaDescription($restaurant)
    {
        $description = $restaurant['description'] ?? '';
        $city = $restaurant['city_name'] ?? '';
        $rating = $restaurant['rating'] ?? 0;

        if (strlen($description) > 100) {
            $description = character_limiter(strip_tags($description), 120);
        }

        $metaDescription = $description;
        if ($rating > 0) {
            $metaDescription .= " Rated {$rating}/5.0.";
        }
        if ($city) {
            $metaDescription .= " Authentic Georgian restaurant in {$city}.";
        }

        return character_limiter($metaDescription, 155);
    }

    /**
     * AJAX метод для добавления/удаления из избранного
     */
    public function toggleFavorite($restaurantId)
    {
        if (!is_numeric($restaurantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid restaurant ID'
            ]);
        }

        $session = session();
        $favorites = $session->get('favorites') ?? [];

        $isFavorite = in_array($restaurantId, $favorites);

        if ($isFavorite) {
            $favorites = array_diff($favorites, [$restaurantId]);
            $action = 'removed';
        } else {
            $favorites[] = $restaurantId;
            $action = 'added';
        }

        $session->set('favorites', $favorites);

        return $this->response->setJSON([
            'success' => true,
            'action' => $action,
            'is_favorite' => !$isFavorite,
            'count' => count($favorites)
        ]);
    }

    /**
     * AJAX метод для получения популярных времен конкретного дня
     */
    public function getPopularTimes($restaurantId, $day = null)
    {
        if (!is_numeric($restaurantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid restaurant ID'
            ]);
        }

        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Restaurant not found'
            ]);
        }

        $popularTimes = json_decode($restaurant['popular_times'] ?? '{}', true);
        
        if ($day && isset($popularTimes[$day])) {
            $times = $popularTimes[$day];
        } else {
            $times = $popularTimes;
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $times,
            'day' => $day
        ]);
    }

    /**
     * AJAX метод для получения статуса ресторана в реальном времени
     */
    public function getRestaurantStatus($restaurantId)
    {
        if (!is_numeric($restaurantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid restaurant ID'
            ]);
        }

        $restaurant = $this->restaurantModel->find($restaurantId);
        if (!$restaurant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Restaurant not found'
            ]);
        }

        $restaurant = $this->processRestaurantData($restaurant);
        $statusInfo = $restaurant['status_info'];

        return $this->response->setJSON([
            'success' => true,
            'status' => $statusInfo
        ]);
    }
}