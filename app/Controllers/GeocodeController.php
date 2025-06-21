<?php

namespace App\Controllers;

use App\Libraries\GooglePlacesAPI;
use App\Models\RestaurantModel;
use App\Models\CityModel;

class GeocodeController extends BaseController
{
    protected $googlePlaces;
    protected $restaurantModel;
    protected $cityModel;

    public function __construct()
    {
        $this->googlePlaces = new GooglePlacesAPI();
        $this->restaurantModel = new RestaurantModel();
        $this->cityModel = new CityModel();
    }

    /**
     * Показать статус геокодирования
     */
    public function index()
    {
        if (!session('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        // Статистика по городам
        $cities = $this->cityModel->findAll();
        $citiesWithCoordinates = 0;
        foreach ($cities as $city) {
            if ($city['latitude'] && $city['longitude']) {
                $citiesWithCoordinates++;
            }
        }

        // Статистика по ресторанам
        $totalRestaurants = $this->restaurantModel->countAllResults();
        $withCoordinates = $this->restaurantModel
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->countAllResults();

        $data = [
            'title' => 'Геокодирование - Статус',
            'cities' => $cities,
            'citiesWithCoordinates' => $citiesWithCoordinates,
            'totalRestaurants' => $totalRestaurants,
            'restaurantsWithCoordinates' => $withCoordinates,
            'restaurantsWithoutCoordinates' => $totalRestaurants - $withCoordinates
        ];

        return view('admin/geocode/status', $data);
    }

    /**
     * Показать форму для обновления координат городов
     */
    public function cities()
    {
        if (!session('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $cities = $this->cityModel->findAll();

        $data = [
            'title' => 'Обновление координат городов',
            'cities' => $cities
        ];

        return view('admin/geocode/cities', $data);
    }

    /**
     * Обновление координат городов через AJAX
     */
    public function updateCityCoordinates()
    {
        if (!session('admin_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Требуется авторизация']);
        }

        $cityId = $this->request->getPost('city_id');
        
        if (!$cityId) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID города не указан']);
        }

        $city = $this->cityModel->find($cityId);
        if (!$city) {
            return $this->response->setJSON(['success' => false, 'message' => 'Город не найден']);
        }

        // Формируем запрос для геокодирования
        $searchQuery = $city['name'] . ', ' . $city['state'] . ', USA';
        
        // Получаем координаты через Google Geocoding API
        $result = $this->googlePlaces->geocodeAddress($searchQuery);
        
        if ($result['success'] && !empty($result['data']['results'])) {
            $location = $result['data']['results'][0]['geometry']['location'];
            $formattedAddress = $result['data']['results'][0]['formatted_address'];
            
            // Обновляем координаты в базе
            $updateData = [
                'latitude' => $location['lat'],
                'longitude' => $location['lng']
            ];

            if ($this->cityModel->update($cityId, $updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Координаты успешно обновлены',
                    'data' => [
                        'latitude' => $location['lat'],
                        'longitude' => $location['lng'],
                        'formatted_address' => $formattedAddress
                    ]
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Ошибка сохранения в базу данных']);
            }
        } else {
            $errorMsg = isset($result['message']) ? $result['message'] : 'Координаты не найдены';
            return $this->response->setJSON(['success' => false, 'message' => $errorMsg]);
        }
    }

    /**
     * Обновление координат ресторана через AJAX
     */
    public function updateRestaurantCoordinates()
    {
        if (!session('admin_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Требуется авторизация']);
        }

        $restaurantId = $this->request->getPost('restaurant_id');
        
        if (!$restaurantId) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID ресторана не указан']);
        }

        $restaurant = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.state')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->find($restaurantId);

        if (!$restaurant) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ресторан не найден']);
        }

        // Формируем полный адрес для геокодирования
        $fullAddress = $restaurant['address'];
        if (!empty($restaurant['city_name'])) {
            $fullAddress .= ', ' . $restaurant['city_name'];
        }
        if (!empty($restaurant['state'])) {
            $fullAddress .= ', ' . $restaurant['state'];
        }
        
        // Получаем координаты через Google Geocoding API
        $result = $this->googlePlaces->geocodeAddress($fullAddress);
        
        if ($result['success'] && !empty($result['data']['results'])) {
            $location = $result['data']['results'][0]['geometry']['location'];
            $formattedAddress = $result['data']['results'][0]['formatted_address'];
            
            // Обновляем координаты в базе
            $updateData = [
                'latitude' => $location['lat'],
                'longitude' => $location['lng']
            ];

            if ($this->restaurantModel->update($restaurantId, $updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Координаты успешно обновлены',
                    'data' => [
                        'latitude' => $location['lat'],
                        'longitude' => $location['lng'],
                        'formatted_address' => $formattedAddress
                    ]
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Ошибка сохранения в базу данных']);
            }
        } else {
            $errorMsg = isset($result['message']) ? $result['message'] : 'Координаты не найдены';
            return $this->response->setJSON(['success' => false, 'message' => $errorMsg]);
        }
    }
                    

    /**
     * Показать форму для обновления координат ресторанов
     */
    public function restaurants()
    {
        if (!session('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $restaurants = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.state')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('(restaurants.latitude IS NULL OR restaurants.longitude IS NULL)')
            ->findAll();

        $data = [
            'title' => 'Обновление координат ресторанов',
            'restaurants' => $restaurants
        ];

        return view('admin/geocode/restaurants', $data);
    }

    /**
     * Обновление координат для всех ресторанов (старый метод для обратной совместимости)
     * Доступно только для админов
     */
    public function updateRestaurantCoordinatesOld()
    {
        // Проверка админских прав
        if (!session('admin_logged_in')) {
            die('Admin access required. <a href="/admin/login">Login</a>');
        }

        echo "<h2>Обновление координат ресторанов</h2>";
        echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

        $restaurants = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.state')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('(restaurants.latitude IS NULL OR restaurants.longitude IS NULL)')
            ->findAll();

        echo "<p>Найдено ресторанов без координат: <strong>" . count($restaurants) . "</strong></p>";
        echo "<hr>";

        $updated = 0;
        $errors = [];

        foreach ($restaurants as $restaurant) {
            // Формируем полный адрес для геокодирования
            $fullAddress = $restaurant['address'];
            if (!empty($restaurant['city_name'])) {
                $fullAddress .= ', ' . $restaurant['city_name'];
            }
            if (!empty($restaurant['state'])) {
                $fullAddress .= ', ' . $restaurant['state'];
            }

            echo "<div style='border:1px solid #ddd; padding:10px; margin:10px 0;'>";
            echo "<strong>{$restaurant['name']}</strong><br>";
            echo "Адрес: {$fullAddress}<br>";
            
            // Получаем координаты через Google Geocoding API
            $result = $this->googlePlaces->geocodeAddress($fullAddress);
            
            if ($result['success'] && !empty($result['data']['results'])) {
                $location = $result['data']['results'][0]['geometry']['location'];
                $formattedAddress = $result['data']['results'][0]['formatted_address'];
                
                echo "✅ <strong>Найдено:</strong><br>";
                echo "Координаты: <strong>{$location['lat']}, {$location['lng']}</strong><br>";
                echo "Адрес Google: <em>{$formattedAddress}</em><br>";
                
                // Обновляем координаты в базе
                $updateData = [
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng']
                ];

                try {
                    $updateResult = $this->restaurantModel->update($restaurant['id'], $updateData);
                    if ($updateResult) {
                        echo "<span class='success'>✅ Успешно сохранено в базу данных</span>";
                        $updated++;
                    } else {
                        echo "<span class='error'>❌ update() вернул false</span>";
                        $errors[] = "Ошибка сохранения для: {$restaurant['name']}";
                    }
                } catch (Exception $e) {
                    echo "<span class='error'>❌ Исключение: " . $e->getMessage() . "</span>";
                    $errors[] = "Исключение для {$restaurant['name']}: " . $e->getMessage();
                }
                
            } else {
                $errorMsg = isset($result['message']) ? $result['message'] : 'Неизвестная ошибка';
                $errors[] = "Не найдены координаты для: {$restaurant['name']} - {$errorMsg}";
                echo "<span class='warning'>⚠ Координаты не найдены: {$errorMsg}</span>";
            }
            
            echo "</div>";

            // Пауза между запросами (чтобы не превысить лимиты API)
            sleep(1);
            flush(); // Показываем прогресс в реальном времени
        }

        echo "<hr>";
        echo "<h3>Итоговый результат:</h3>";
        echo "<p><strong>Обновлено ресторанов: {$updated}</strong></p>";
        
        if (!empty($errors)) {
            echo "<h4>Ошибки:</h4>";
            foreach ($errors as $error) {
                echo "<p class='error'>{$error}</p>";
            }
        }

        echo "<p><a href='/admin'>← Вернуться в админку</a></p>";
        echo "<p><a href='/map'>Проверить карту →</a></p>";
        
        return;
    }

    /**
     * Показать статус геокодирования
     */
    public function status()
    {
        return $this->index(); // Redirect to index method
    }

    /**
     * Тестовое геокодирование без записи в базу
     */
    public function testGeocoding()
    {
        if (!session('admin_logged_in')) {
            die('Admin access required. <a href="/admin/login">Login</a>');
        }

        echo "<h2>Тестирование геокодирования ресторанов</h2>";
        echo "<style>
            body{font-family:Arial;margin:20px;} 
            .success{color:green;} 
            .error{color:red;} 
            .warning{color:orange;}
            .restaurant-box{border:1px solid #ddd; padding:15px; margin:15px 0; border-radius:5px;}
            .address{color:#666; font-style:italic;}
            .coordinates{background:#f0f8ff; padding:10px; border-radius:3px; margin:10px 0;}
        </style>";

        // Получаем ВСЕ рестораны
        $restaurants = $this->restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.state')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->findAll();

        echo "<p>Найдено ресторанов: <strong>" . count($restaurants) . "</strong></p>";
        echo "<p><em>Тестируем API запросы без записи в базу данных</em></p>";
        echo "<hr>";

        $successful = 0;
        $failed = 0;

        foreach ($restaurants as $index => $restaurant) {
            echo "<div class='restaurant-box'>";
            echo "<h3>" . ($index + 1) . ". {$restaurant['name']}</h3>";
            
            // Формируем полный адрес
            $fullAddress = $restaurant['address'];
            if (!empty($restaurant['city_name'])) {
                $fullAddress .= ', ' . $restaurant['city_name'];
            }
            if (!empty($restaurant['state'])) {
                $fullAddress .= ', ' . $restaurant['state'];
            }

            echo "<div class='address'>📍 Адрес: {$fullAddress}</div>";
            
            // Делаем запрос к Google Geocoding API
            echo "<p>🔍 Отправляем запрос к Google API...</p>";
            
            $result = $this->googlePlaces->geocodeAddress($fullAddress);

            if ($result['success'] && !empty($result['data']['results'])) {
                $location = $result['data']['results'][0]['geometry']['location'];
                $formattedAddress = $result['data']['results'][0]['formatted_address'] ?? 'N/A';
                
                echo "<div class='coordinates success'>";
                echo "<strong>✅ Успех!</strong><br>";
                echo "<strong>Координаты:</strong> lat: {$location['lat']}, lng: {$location['lng']}<br>";
                echo "<strong>Отформатированный адрес:</strong> {$formattedAddress}";
                echo "</div>";
                
                $successful++;
            } else {
                $errorMsg = isset($result['message']) ? $result['message'] : 'Неизвестная ошибка';
                $errorDetails = isset($result['error']) ? $result['error'] : 'N/A';
                
                echo "<div class='coordinates error'>";
                echo "<strong>❌ Ошибка!</strong><br>";
                echo "<strong>Сообщение:</strong> {$errorMsg}<br>";
                echo "<strong>Код ошибки:</strong> {$errorDetails}";
                echo "</div>";
                
                $failed++;
            }

            echo "</div>";
            
            // Пауза между запросами
            if ($index < count($restaurants) - 1) {
                echo "<p style='text-align:center; color:#666;'>⏳ Пауза 1 сек...</p>";
                sleep(1);
                flush(); // Показываем прогресс в реальном времени
            }
        }

        echo "<hr>";
        echo "<div style='background:#f8f9fa; padding:20px; border-radius:5px; margin:20px 0;'>";
        echo "<h3>📊 Итоговая статистика:</h3>";
        echo "<p><strong>Всего ресторанов протестировано:</strong> " . count($restaurants) . "</p>";
        echo "<p><strong class='success'>✅ Успешно геокодировано:</strong> {$successful}</p>";
        echo "<p><strong class='error'>❌ Ошибок:</strong> {$failed}</p>";
        echo "<p><strong>Процент успеха:</strong> " . round(($successful / count($restaurants)) * 100, 1) . "%</p>";
        echo "</div>";

        echo "<p><a href='/admin/geocode'>← Назад к статусу геокодирования</a></p>";
        echo "<p><a href='/admin'>← Вернуться в админку</a></p>";
        
        return;
    }
}