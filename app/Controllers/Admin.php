<?php

namespace App\Controllers;

use App\Libraries\AdminLibrary;

class Admin extends BaseController
{
    protected $adminLib;
    protected $helpers = ['url', 'text', 'form'];

    public function __construct()
    {
        $this->adminLib = new AdminLibrary();
    }

    /**
     * Страница входа в админку
     */
    public function login()
    {
        $config = config('AdminAuth');
        
        // Если уже авторизован, редиректим на дашборд
        if ($this->adminLib->isLoggedIn()) {
            return redirect()->to('/admin/dashboard');
        }

        if ($this->request->getMethod(true) === 'POST') {
            $adminKey = $this->request->getPost('admin_key');
            
            if ($config->isValidAdminKey($adminKey)) {
                // Устанавливаем сессию
                session()->set([
                    'admin_logged_in' => true,
                    'admin_login_time' => time(),
                    'admin_key' => $adminKey
                ]);
                
                // Обрабатываем "Запомнить меня"
                if ($this->request->getPost('remember_me') && $config->useRememberMe) {
                    $rememberToken = bin2hex(random_bytes(32));
                    session()->set('admin_remember_token', $rememberToken);
                    
                    setcookie(
                        $config->rememberMeCookie,
                        $rememberToken,
                        time() + ($config->rememberMeExpire * 24 * 60 * 60),
                        '/',
                        '',
                        false,
                        true
                    );
                }
                
                return redirect()->to('/admin/dashboard')->with('success', 'Вход выполнен успешно!');
            } else {
                return redirect()->back()->with('error', 'Неверный ключ доступа');
            }
        }

        $data = [
            'title' => 'Вход в админку - Georgian Food Near Me',
            'config' => $config
        ];

        return view('admin/login', $data);
    }

    /**
     * Выход из админки
     */
    public function logout()
    {
        $this->adminLib->logout();
        return redirect()->to('/admin/login')->with('success', 'Вы вышли из админки');
    }

    /**
     * Главная страница админки (дашборд)
     */
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $restaurantModel = model('RestaurantModel');
        $cityModel = model('CityModel');

        // Основная статистика
        $totalRestaurants = $restaurantModel->countAllResults();
        $activeRestaurants = $restaurantModel->where('is_active', 1)->countAllResults();
        $totalCities = $cityModel->countAllResults();
        $recentAdditions = $restaurantModel
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->countAllResults();

        // Координаты статистика
        $restaurantsWithCoordinates = $restaurantModel
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->countAllResults();

        $citiesWithCoordinates = $cityModel
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->countAllResults();

        $stats = [
            'title' => 'Админка - Georgian Food Near Me',
            'total_restaurants' => $totalRestaurants,
            'active_restaurants' => $activeRestaurants,
            'total_cities' => $totalCities,
            'recent_additions' => $recentAdditions,
            'restaurants_with_coordinates' => $restaurantsWithCoordinates,
            'cities_with_coordinates' => $citiesWithCoordinates
        ];

        $recentRestaurants = $restaurantModel
            ->select('restaurants.*, cities.name as city_name')
            ->join('cities', 'cities.id = restaurants.city_id', 'left')
            ->orderBy('restaurants.created_at', 'DESC')
            ->limit(10)
            ->find();

        $data = [
            'title' => 'Админка - Georgian Food Near Me',
            'stats' => $stats,
            'recent_restaurants' => $recentRestaurants
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Поиск ресторанов
     */
    public function searchRestaurants()
    {
        if ($this->request->getMethod() === 'POST') {
            $cityName = $this->request->getPost('city_name');
            
            if (empty($cityName)) {
                return redirect()->back()->with('error', 'Введите название города');
            }

            // TODO: Интеграция с Google Places API
            return redirect()->back()->with('success', "Поиск в городе {$cityName} - функция в разработке");
        }

        return redirect()->to('/admin/dashboard');
    }

    /**
     * Управление ресторанами
     */
    public function restaurants()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'city_id' => $this->request->getGet('city')
        ];

        $data = [
            'title' => 'Управление ресторанами - Админка',
            'restaurants' => $this->adminLib->getRestaurants($filters, 100),
            'cities' => $this->adminLib->getCities(),
            'filters' => $filters
        ];

        return view('admin/restaurants', $data);
    }

    /**
     * Удаление ресторана
     */
    public function deleteRestaurant($id)
    {
        if ($this->adminLib->deleteRestaurant($id)) {
            return redirect()->to('/admin/restaurants')->with('success', 'Ресторан успешно удален');
        } else {
            return redirect()->to('/admin/restaurants')->with('error', 'Ошибка при удалении ресторана');
        }
    }

    /**
     * Управление городами
     */
    public function cities()
    {
        if ($this->request->getMethod() === 'POST') {
            $cityData = [
                'name' => $this->request->getPost('name'),
                'state' => $this->request->getPost('state'),
                'country' => $this->request->getPost('country')
            ];

            if ($this->adminLib->addCity($cityData)) {
                return redirect()->back()->with('success', 'Город успешно добавлен');
            } else {
                return redirect()->back()->with('error', 'Ошибка при добавлении города');
            }
        }

        $data = [
            'title' => 'Управление городами - Админка',
            'cities' => $this->adminLib->getCitiesWithCounts()
        ];

        return view('admin/cities', $data);
    }

    /**
     * Массовые операции
     */
    public function bulkOperations()
    {
        $action = $this->request->getPost('action');
        $restaurantIds = $this->request->getPost('restaurant_ids');

        if (empty($restaurantIds)) {
            return redirect()->back()->with('error', 'Рестораны не выбраны');
        }

        $count = $this->adminLib->bulkOperationRestaurants($action, $restaurantIds);
        
        if ($count > 0) {
            return redirect()->back()->with('success', "Операция выполнена для {$count} ресторанов");
        } else {
            return redirect()->back()->with('error', 'Ошибка при выполнении операции');
        }
    }

    /**
     * Экспорт данных
     */
    public function export($format = 'csv')
    {
        if ($format === 'csv') {
            $this->adminLib->exportRestaurantsCSV();
        } else {
            return redirect()->back()->with('error', 'Неподдерживаемый формат');
        }
    }

    /**
     * Загрузка фотографий для ресторана
     */
    public function uploadPhoto($restaurantId)
    {
        // Будет реализован позже в контроллере Restaurants
        return redirect()->to("/admin/restaurants/edit/{$restaurantId}")
                        ->with('info', 'Управление фотографиями будет добавлено в ближайшее время');
    }

    /**
     * AJAX метод для проверки уникальности SEO URL (только SEO URL, slug не проверяем)
     */
    public function checkSeoUrlAvailability()
    {
        // Проверяем, что это AJAX запрос
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $seoUrl = $this->request->getPost('seo_url');
        $excludeId = $this->request->getPost('exclude_id');
        
        if (!$seoUrl) {
            return $this->response->setJSON([
                'available' => true, 
                'message' => 'SEO URL is optional'
            ]);
        }
        
        // Проверяем формат SEO URL
        if (!preg_match('/^[a-z0-9-]+$/', $seoUrl)) {
            return $this->response->setJSON([
                'available' => false, 
                'message' => 'Invalid SEO URL format. Use only lowercase letters, numbers, and hyphens'
            ]);
        }

        // Проверяем минимальную длину
        if (strlen($seoUrl) < 5) {
            return $this->response->setJSON([
                'available' => false, 
                'message' => 'SEO URL should be at least 5 characters long'
            ]);
        }
        
        // Загружаем модель ресторанов
        $restaurantModel = model('RestaurantModel');
        
        // Проверяем уникальность
        $builder = $restaurantModel->where('seo_url', $seoUrl);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        $exists = $builder->countAllResults() > 0;
        
        return $this->response->setJSON([
            'available' => !$exists,
            'message' => $exists ? 'This SEO URL is already in use' : 'SEO URL is available'
        ]);
    }

    /**
     * AJAX метод для генерации slug из названия
     */
    public function generateSlug()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $name = $this->request->getPost('name');
        $cityId = $this->request->getPost('city_id');
        
        if (!$name) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Restaurant name is required'
            ]);
        }
        
        // Генерируем базовый slug
        $baseSlug = $this->createSlugFromText($name);
        
        // Если указан город, создаем SEO URL
        $seoUrl = '';
        if ($cityId) {
            $cityModel = model('CityModel');
            $city = $cityModel->find($cityId);
            if ($city) {
                $citySlug = $this->createSlugFromText($city['name']);
                $seoUrl = $baseSlug . '-restaurant-' . $citySlug;
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'slug' => $baseSlug,
            'seo_url' => $seoUrl
        ]);
    }

    /**
     * Массовое обновление SEO URL для существующих ресторанов
     */
    public function generateMissingSeoUrls()
    {
        // Только для админа
        if (!session()->get('is_admin')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $restaurantModel = model('RestaurantModel');

        // Получаем рестораны без SEO URL
        $restaurants = $restaurantModel
            ->select('restaurants.*, cities.name as city_name, cities.slug as city_slug')
            ->join('cities', 'cities.id = restaurants.city_id')
            ->where('(restaurants.seo_url IS NULL OR restaurants.seo_url = "")')
            ->findAll();

        $updated = 0;
        $errors = [];

        foreach ($restaurants as $restaurant) {
            $restaurantSlug = $this->createSlugFromText($restaurant['name']);
            $citySlug = $restaurant['city_slug'] ?: $this->createSlugFromText($restaurant['city_name']);
            $seoUrl = $restaurantSlug . '-restaurant-' . $citySlug;

            // Проверяем уникальность SEO URL (если уже существует, добавляем номер)
            $counter = 1;
            $originalSeoUrl = $seoUrl;
            while ($this->checkSeoUrlExists($seoUrl, $restaurant['id'])) {
                $seoUrl = $originalSeoUrl . '-' . $counter;
                $counter++;
            }

            try {
                if ($restaurantModel->update($restaurant['id'], ['seo_url' => $seoUrl])) {
                    $updated++;
                } else {
                    $errors[] = "Failed to update restaurant ID {$restaurant['id']}";
                }
            } catch (\Exception $e) {
                $errors[] = "Error updating restaurant ID {$restaurant['id']}: " . $e->getMessage();
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'updated' => $updated,
            'total_processed' => count($restaurants),
            'errors' => $errors
        ]);
    }

    /**
     * Вспомогательный метод: создание slug из текста
     */
    private function createSlugFromText($text)
    {
        // Приводим к нижнему регистру
        $slug = mb_strtolower($text, 'UTF-8');
        
        // Заменяем специальные символы и пробелы на дефисы
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug;
    }

    /**
     * Вспомогательный метод: проверка существования SEO URL
     */
    private function checkSeoUrlExists($seoUrl, $excludeId = null)
    {
        $restaurantModel = model('RestaurantModel');
        $builder = $restaurantModel->where('seo_url', $seoUrl);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
    * Редактирование ресторана
    */
    public function editRestaurant($id = null)
    {
    if (!$id || !is_numeric($id)) {
        return redirect()->to('admin/restaurants')->with('error', 'Restaurant not found');
    }

    $restaurantModel = model('RestaurantModel');
    $cityModel = model('CityModel');
    
    $restaurant = $restaurantModel->find($id);
    if (!$restaurant) {
        return redirect()->to('admin/restaurants')->with('error', 'Restaurant not found');
    }

    // Обработка POST запроса
    if ($this->request->getMethod() === 'POST') {
        $validationRules = [
            'name' => 'required|max_length[255]',
            'slug' => 'required|max_length[255]|regex_match[/^[a-z0-9-]+$/]',
            'seo_url' => 'permit_empty|max_length[255]|regex_match[/^[a-z0-9-]+$/]',
            'city_id' => 'required|integer',
            'address' => 'permit_empty|max_length[500]',
            'phone' => 'permit_empty|max_length[20]',
            'website' => 'permit_empty|valid_url|max_length[255]',
            'rating' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[5]',
            'price_level' => 'permit_empty|integer|in_list[0,1,2,3,4]',
            'is_active' => 'required|in_list[0,1]',
            'description' => 'permit_empty|max_length[2000]',
            'is_georgian' => 'permit_empty|in_list[0,1]'
        ];

        $validationMessages = [
            'slug.regex_match' => 'Slug can only contain lowercase letters, numbers, and hyphens',
            'seo_url.regex_match' => 'SEO URL can only contain lowercase letters, numbers, and hyphens',
            'rating.greater_than_equal_to' => 'Rating must be between 0 and 5',
            'rating.less_than_equal_to' => 'Rating must be between 0 and 5',
            'is_georgian.in_list' => 'Значение должно быть 0 (не грузинский) или 1 (грузинский)'
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'slug' => $this->request->getPost('slug'),
            'seo_url' => $this->request->getPost('seo_url') ?: null,
            'city_id' => $this->request->getPost('city_id'),
            'address' => $this->request->getPost('address') ?: null,
            'phone' => $this->request->getPost('phone') ?: null,
            'website' => $this->request->getPost('website') ?: null,
            'rating' => $this->request->getPost('rating') ?: null,
            'price_level' => $this->request->getPost('price_level') ?: 0,
            'is_active' => $this->request->getPost('is_active'),
            'description' => $this->request->getPost('description') ?: null,
            'is_georgian' => $this->request->getPost('is_georgian') !== '' ? (int)$this->request->getPost('is_georgian') : null
        ];

        // Проверяем уникальность seo_url если указан (slug не проверяем)
        if (!empty($data['seo_url']) && $this->checkSeoUrlExists($data['seo_url'], $id)) {
            return redirect()->back()->withInput()->with('error', 'This SEO URL is already in use by another restaurant');
        }

        try {
            if ($restaurantModel->update($id, $data)) {
                return redirect()->to('admin/restaurants')->with('success', 'Restaurant updated successfully');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to update restaurant');
            }
        } catch (\Exception $e) {
            log_message('error', 'Restaurant update error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Database error occurred');
        }
    }

    // GET запрос - показываем форму
    $cities = $cityModel->findAll();

    $data = [
        'title' => 'Edit Restaurant - ' . $restaurant['name'],
        'restaurant' => $restaurant,
        'cities' => $cities
    ];

    return view('admin/edit_restaurant', $data);
    }

/**
 * Страница импорта CSV файлов
 */
public function importCsv()
{
    $data = [
        'title' => 'Import Restaurants from CSV',
        'restaurants' => [], // Пустой массив для начала
        'importStats' => null
    ];

    return view('admin/import_csv', $data);
}

/**
 * Обработка загрузки и импорта CSV файла
 */
public function processCsvImport()
{
    // Проверка AJAX запроса
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
    }

    // Валидация файла
    $validationRules = [
        'csv_file' => [
            'uploaded[csv_file]',
            'max_size[csv_file,5120]', // 5MB максимум
            'ext_in[csv_file,csv]'
        ]
    ];

    if (!$this->validate($validationRules)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'File validation failed',
            'errors' => $this->validator->getErrors()
        ]);
    }

    $file = $this->request->getFile('csv_file');
    
    if (!$file->hasMoved()) {
        try {
            // Обрабатываем CSV файл
            $result = $this->processCsvFile($file);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'CSV import completed successfully',
                'stats' => $result
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'CSV import error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ]);
        }
    }

    return $this->response->setJSON([
        'success' => false,
        'message' => 'File upload failed'
    ]);
}

/**
 * Обработка CSV файла
 */
private function processCsvFile($file)
{
    $restaurantModel = model('RestaurantModel');
    $cityModel = model('CityModel');
    
    $stats = [
        'total_rows' => 0,
        'processed' => 0,
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => []
    ];

    // Открываем CSV файл
    if (($handle = fopen($file->getTempName(), "r")) !== FALSE) {
        
        // Читаем заголовок (первую строку)
        $header = fgetcsv($handle, 1000, ",");
        
        if (!$header) {
            throw new \Exception('Cannot read CSV header');
        }
        
        // Проверяем наличие обязательных колонок
        $requiredColumns = ['Name', 'Address', 'Rating', 'Place ID'];
        $headerMap = [];
        
        foreach ($requiredColumns as $required) {
            $columnIndex = array_search($required, $header);
            if ($columnIndex === false) {
                throw new \Exception("Required column '{$required}' not found in CSV");
            }
            $headerMap[$required] = $columnIndex;
        }
        
        // Обрабатываем каждую строку
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $stats['total_rows']++;
            
            try {
                // Извлекаем данные из строки
                $restaurantData = [
                    'name' => trim($data[$headerMap['Name']] ?? ''),
                    'address' => trim($data[$headerMap['Address']] ?? ''),
                    'rating' => (float)($data[$headerMap['Rating']] ?? 0),
                    'google_place_id' => trim($data[$headerMap['Place ID']] ?? '')
                ];
                
                // Пропускаем строки с пустыми обязательными полями
                if (empty($restaurantData['name']) || empty($restaurantData['google_place_id'])) {
                    $stats['skipped']++;
                    $stats['errors'][] = "Row {$stats['total_rows']}: Missing required data (name or place_id)";
                    continue;
                }
                
                // Проверяем дубликаты по google_place_id
                $existingRestaurant = $restaurantModel->where('google_place_id', $restaurantData['google_place_id'])->first();
                
                if ($existingRestaurant) {
                    // Обновляем существующий ресторан
                    $updateData = [
                        'name' => $restaurantData['name'],
                        'address' => $restaurantData['address'],
                        'rating' => $restaurantData['rating']
                    ];
                    
                    if ($restaurantModel->update($existingRestaurant['id'], $updateData)) {
                        $stats['updated']++;
                    }
                } else {
                    // Подготавливаем данные для нового ресторана
                    $newRestaurantData = $this->prepareRestaurantData($restaurantData, $cityModel);
                    
                    if ($restaurantModel->insert($newRestaurantData)) {
                        $stats['inserted']++;
                    }
                }
                
                $stats['processed']++;
                
            } catch (\Exception $e) {
                $stats['errors'][] = "Row {$stats['total_rows']}: " . $e->getMessage();
            }
        }
        
        fclose($handle);
    }

    return $stats;
}

    /**
     * Подготовка данных ресторана для вставки
     */
    private function prepareRestaurantData($csvData, $cityModel)
    {
        // Определяем город по адресу (с автосозданием)
        $cityId = $this->determineCityFromAddress($csvData['address'], $cityModel);
        
        // ОБЯЗАТЕЛЬНАЯ ПРОВЕРКА: если город не найден и не создан, используем дефолтный
        if (!$cityId) {
            // Получаем первый доступный город как fallback
            $defaultCity = $cityModel->first();
            if ($defaultCity) {
                $cityId = $defaultCity['id'];
                log_message('warning', "Used default city ID {$cityId} for restaurant: {$csvData['name']}");
            } else {
                throw new \Exception("No cities available in database. Please add at least one city first.");
            }
        }
        
        // Генерируем slug
        $slug = $this->generateUniqueSlug($csvData['name']);
        
        // Генерируем SEO URL
        $seoUrl = null;
        if ($cityId) {
            $city = $cityModel->find($cityId);
            if ($city) {
                $seoUrl = $this->generateSeoUrl($csvData['name'], $city['name']);
            }
        }
        
        // Генерируем базовое описание
        $description = $this->generateDescription($csvData['name']);
        
        // ВАЛИДАЦИЯ: проверяем все обязательные поля
        $data = [
            'name' => $csvData['name'],
            'slug' => $slug,
            'seo_url' => $seoUrl,
            'address' => $csvData['address'],
            'rating' => $csvData['rating'],
            'google_place_id' => $csvData['google_place_id'],
            'city_id' => $cityId, // ОБЯЗАТЕЛЬНО должен быть заполнен
            'price_level' => 2, // Средний уровень цен по умолчанию
            'description' => $description,
            'is_active' => 1
        ];
        
        // Финальная проверка
        if (empty($data['city_id']) || empty($data['name']) || empty($data['slug'])) {
            throw new \Exception("Missing required fields for restaurant: " . $csvData['name']);
        }
        
        return $data;
    }

    /**
     * Определение города по адресу БЕЗ создания дубликатов
     */
    private function determineCityFromAddress($address, $cityModel)
    {
        log_message('info', "Looking for city in address: {$address}");
        
        // Извлекаем данные города из адреса
        $extractedCity = $this->extractCityFromAddress($address);
        
        if (!$extractedCity) {
            log_message('warning', "Could not extract city from address: {$address}");
            return $this->getDefaultCity($cityModel);
        }
        
        $cityName = $extractedCity['name'];
        $state = $extractedCity['state'];
        
        log_message('info', "Extracted city: {$cityName}, {$state}");
        
        // ПОИСК СУЩЕСТВУЮЩЕГО ГОРОДА (улучшенный алгоритм)
        
        // 1. Точное совпадение: название + штат
        $existingCity = $cityModel->where('name', $cityName)
                                ->where('state', $state)
                                ->first();
        
        if ($existingCity) {
            log_message('info', "Found exact match: ID {$existingCity['id']} - {$existingCity['name']}, {$existingCity['state']}");
            return $existingCity['id'];
        }
        
        // 2. Поиск без учета регистра
        $existingCity = $cityModel->where('LOWER(name)', strtolower($cityName))
                                ->where('LOWER(state)', strtolower($state))
                                ->first();
        
        if ($existingCity) {
            log_message('info', "Found case-insensitive match: ID {$existingCity['id']} - {$existingCity['name']}, {$existingCity['state']}");
            return $existingCity['id'];
        }
        
        // 3. Специальные правила для известных городов
        $cityId = $this->checkSpecialCityRules($cityName, $state, $address, $cityModel);
        if ($cityId) {
            return $cityId;
        }
        
        // 4. Поиск по вариациям названия (только для крупных городов)
        $cityId = $this->findCityByVariations($cityName, $state, $cityModel);
        if ($cityId) {
            return $cityId;
        }
        
        // СОЗДАНИЕ НОВОГО ГОРОДА (только если точно не найден)
        log_message('info', "Creating new city: {$cityName}, {$state}");
        
        try {
            $newCityId = $cityModel->insert($extractedCity);
            if ($newCityId) {
                log_message('info', "Successfully created new city ID: {$newCityId} - {$cityName}, {$state}");
                return $newCityId;
            } else {
                log_message('error', "Failed to create city: {$cityName}, {$state}");
            }
        } catch (\Exception $e) {
            log_message('error', "Exception creating city {$cityName}, {$state}: " . $e->getMessage());
        }
        
        // Fallback: используем дефолтный город
        return $this->getDefaultCity($cityModel);
    }

    /**
     * Специальные правила для известных городов
     */
    private function checkSpecialCityRules($cityName, $state, $address, $cityModel)
    {
        // Правила для NYC
        if ($state === 'NY' && (stripos($address, 'New York') !== false || stripos($address, 'NYC') !== false)) {
            
            // Manhattan варианты
            if (stripos($address, 'Manhattan') !== false || 
                stripos($address, 'New York, NY') !== false ||
                preg_match('/\bNY\s+\d{5}/', $address)) {
                
                $manhattan = $cityModel->where('LOWER(name)', 'manhattan')
                                    ->where('LOWER(state)', 'ny')
                                    ->first();
                
                if ($manhattan) {
                    log_message('info', "Found Manhattan by special rule: ID {$manhattan['id']}");
                    return $manhattan['id'];
                }
            }
            
            // Brooklyn варианты
            if (stripos($address, 'Brooklyn') !== false) {
                $brooklyn = $cityModel->where('LOWER(name)', 'brooklyn')
                                    ->where('LOWER(state)', 'ny')
                                    ->first();
                
                if ($brooklyn) {
                    log_message('info', "Found Brooklyn by special rule: ID {$brooklyn['id']}");
                    return $brooklyn['id'];
                }
            }
            
            // Queens варианты
            if (stripos($address, 'Queens') !== false) {
                $queens = $cityModel->where('LOWER(name)', 'queens')
                                ->where('LOWER(state)', 'ny')
                                ->first();
                
                if ($queens) {
                    log_message('info', "Found Queens by special rule: ID {$queens['id']}");
                    return $queens['id'];
                }
            }
        }
        
        return null;
    }

    /**
     * Поиск по вариациям названия города
     */
    private function findCityByVariations($cityName, $state, $cityModel)
    {
        // Известные вариации названий
        $variations = [
            'New York' => ['Manhattan', 'New York City', 'NYC'],
            'Manhattan' => ['New York', 'New York City'],
            'Los Angeles' => ['LA', 'L.A.'],
            'San Francisco' => ['SF', 'San Fran'],
            'Washington' => ['Washington DC', 'DC'],
            'Chicago' => ['Chi-town']
        ];
        
        foreach ($variations as $canonical => $alts) {
            // Если наш город - это альтернативное название
            if (in_array($cityName, $alts)) {
                $found = $cityModel->where('LOWER(name)', strtolower($canonical))
                                ->where('LOWER(state)', strtolower($state))
                                ->first();
                
                if ($found) {
                    log_message('info', "Found city by variation: {$cityName} -> {$canonical}, ID {$found['id']}");
                    return $found['id'];
                }
            }
            
            // Если наш город - каноническое название, ищем по альтернативам
            if (strtolower($cityName) === strtolower($canonical)) {
                foreach ($alts as $alt) {
                    $found = $cityModel->where('LOWER(name)', strtolower($alt))
                                    ->where('LOWER(state)', strtolower($state))
                                    ->first();
                    
                    if ($found) {
                        log_message('info', "Found city by reverse variation: {$cityName} -> {$alt}, ID {$found['id']}");
                        return $found['id'];
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Получить дефолтный город (создать если нет)
     */
    private function getDefaultCity($cityModel)
    {
        // Ищем существующий дефолтный город
        $defaultCity = $cityModel->first();
        
        if ($defaultCity) {
            log_message('warning', "Using existing default city: ID {$defaultCity['id']} - {$defaultCity['name']}");
            return $defaultCity['id'];
        }
        
        // Создаем дефолтный город если его нет
        $defaultCityData = [
            'name' => 'Manhattan',
            'state' => 'NY',
            'country' => 'United States',
            'slug' => 'manhattan'
        ];
        
        try {
            $newCityId = $cityModel->insert($defaultCityData);
            if ($newCityId) {
                log_message('info', "Created default city: ID {$newCityId} - Manhattan, NY");
                return $newCityId;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to create default city: ' . $e->getMessage());
        }
        
        throw new \Exception('Cannot determine or create city');
    }

    /**
     * Извлечение данных города из адреса (улучшенная версия)
     */
    private function extractCityFromAddress($address)
    {
        log_message('info', "Extracting city from: {$address}");
        
        // Регулярное выражение для US адресов: "Street, City, State ZIP"
        $patterns = [
            // Основной паттерн: "Street, City, State ZIP"
            '/^(.+),\s*([^,]+),\s*([A-Z]{2})\s+(\d{5}(?:-\d{4})?)/',
            // Альтернативный: "City, State"
            '/([^,]+),\s*([A-Z]{2})$/',
            // Для адресов без запятых: "Street City State ZIP"
            '/(.+)\s+([A-Z]{2})\s+(\d{5}(?:-\d{4})?)$/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($address), $matches)) {
                
                if (count($matches) >= 4) {
                    // Полный адрес с улицей
                    $street = trim($matches[1]);
                    $cityName = trim($matches[2]);
                    $state = trim($matches[3]);
                    
                    // Очищаем название города от номеров домов
                    $cityName = $this->cleanCityName($cityName);
                    
                    log_message('info', "Extracted (pattern 1): {$cityName}, {$state}");
                    
                    return [
                        'name' => $cityName,
                        'state' => $state,
                        'country' => 'United States',
                        'slug' => $this->createSlugFromText($cityName)
                    ];
                    
                } elseif (count($matches) >= 3) {
                    // Только город и штат
                    $cityName = trim($matches[1]);
                    $state = trim($matches[2]);
                    
                    $cityName = $this->cleanCityName($cityName);
                    $citySlug = $this->createSlugFromText($cityName);
                    $citySEO_URL = 'georgian-restaurants-' . $citySlug;

                    log_message('info', "Extracted (pattern 2): {$cityName}, {$state}");

                    return [
                        'name' => $cityName,
                        'state' => $state,
                        'country' => 'United States',
                        'slug' => $citySlug,
                        'seo_url' => $citySEO_URL
                    ];
                }
            }
        }
        
        // Специальная обработка для NYC адресов
        if (stripos($address, 'New York') !== false || stripos($address, 'NY') !== false) {
            
            if (stripos($address, 'Manhattan') !== false) {
                return [
                    'name' => 'Manhattan',
                    'state' => 'NY',
                    'country' => 'United States',
                    'slug' => 'manhattan'
                ];
            }
            
            if (stripos($address, 'Brooklyn') !== false) {
                return [
                    'name' => 'Brooklyn',
                    'state' => 'NY',
                    'country' => 'United States',
                    'slug' => 'brooklyn'
                ];
            }
            
            if (stripos($address, 'Queens') !== false) {
                return [
                    'name' => 'Queens',
                    'state' => 'NY',
                    'country' => 'United States',
                    'slug' => 'queens'
                ];
            }
            
            // По умолчанию Manhattan для NYC адресов
            return [
                'name' => 'Manhattan',
                'state' => 'NY',
                'country' => 'United States',
                'slug' => 'manhattan'
            ];
        }
        
        log_message('warning', "Could not extract city from address: {$address}");
        return null;
    }

    /**
     * Очистка названия города от лишних элементов
     */
    private function cleanCityName($cityName)
    {
        // Убираем номера домов в начале
        $cityName = preg_replace('/^\d+\s+/', '', $cityName);
        
        // Убираем типы улиц в конце названия города
        $streetTypes = ['St', 'Ave', 'Blvd', 'Rd', 'Dr', 'Ln', 'Ct', 'Pl', 'Way'];
        foreach ($streetTypes as $type) {
            $cityName = preg_replace('/\s+' . $type . '$/i', '', $cityName);
        }
        
        // Специальные случаи
        $cityName = str_ireplace(['NYC', 'New York City'], 'Manhattan', $cityName);
        
        return trim($cityName);
    }

    /**
     * Генерация уникального slug
     */
    private function generateUniqueSlug($name)
    {
        $restaurantModel = model('RestaurantModel');
        
        // Базовый slug
        $baseSlug = $this->createSlugFromText($name);
        $slug = $baseSlug;
        $counter = 1;
        
        // Проверяем уникальность
        while ($restaurantModel->where('slug', $slug)->first()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Генерация SEO URL
     */
    private function generateSeoUrl($restaurantName, $cityName)
    {
        $restaurantSlug = $this->createSlugFromText($restaurantName);
        $citySlug = $this->createSlugFromText($cityName);
        
        return $restaurantSlug . '-restaurant-' . $citySlug;
    }

    /**
     * Генерация базового описания
     */
    private function generateDescription($name)
    {
        return "Authentic Georgian restaurant {$name} serving traditional Georgian dishes like khachapuri, khinkali, and other delicious Georgian cuisine.";
    }

    /**
     * Предварительный просмотр CSV файла
     */
    public function previewCsv()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $file = $this->request->getFile('csv_file');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid file'
            ]);
        }

        try {
            $preview = [];
            $handle = fopen($file->getTempName(), "r");
            
            if ($handle) {
                // Читаем заголовок
                $header = fgetcsv($handle, 1000, ",");
                $preview['header'] = $header;
                
                // Читаем первые 5 строк для предварительного просмотра
                $preview['rows'] = [];
                $rowCount = 0;
                
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE && $rowCount < 100) {
                    $preview['rows'][] = $data;
                    $rowCount++;
                }
                
                fclose($handle);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'preview' => $preview
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error reading file: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Проверка дубликатов в базе данных
     */
    public function checkDuplicates()
    {
        $restaurantModel = model('RestaurantModel');
        
        // Проверяем рестораны без google_place_id но с похожими названиями/адресами
        $duplicates = $restaurantModel->select('restaurants.*, cities.name as city_name')
                                    ->join('cities', 'cities.id = restaurants.city_id', 'left')
                                    ->where('restaurants.google_place_id IS NULL OR restaurants.google_place_id = ""')
                                    ->findAll();
        
        $potentialDuplicates = [];
        
        foreach ($duplicates as $restaurant) {
            // Здесь можно добавить логику поиска похожих ресторанов
            // По названию, адресу и т.д.
            $potentialDuplicates[] = [
                'id' => $restaurant['id'],
                'name' => $restaurant['name'],
                'address' => $restaurant['address'],
                'city' => $restaurant['city_name'],
                'has_place_id' => !empty($restaurant['google_place_id'])
            ];
        }
        
        return $this->response->setJSON([
            'success' => true,
            'duplicates' => $potentialDuplicates
        ]);
    }
    /**
     * Управление фотографиями ресторана
     */
    public function restaurantPhotos($restaurantId)
    {
        if (!is_numeric($restaurantId)) {
            return redirect()->to('/admin/restaurants')->with('error', 'Неверный ID ресторана');
        }

        // Загружаем модели
        $restaurantModel = model('RestaurantModel');
        $photoModel = new \App\Models\RestaurantPhotoModel();
        
        // Получаем ресторан
        $restaurant = $restaurantModel->find($restaurantId);
        if (!$restaurant) {
            return redirect()->to('/admin/restaurants')->with('error', 'Ресторан не найден');
        }
        
        // Обработка POST запроса (загрузка фотографий)
        if ($this->request->getMethod() === 'POST') {
            return $this->handlePhotoUpload($restaurantId, $photoModel);
        }
        
        // GET запрос - показываем страницу управления фотографиями
        $photos = $photoModel->getRestaurantPhotos($restaurantId);
        $mainPhoto = $photoModel->getMainPhoto($restaurantId);
        
        $data = [
            'title' => 'Управление фотографиями - ' . $restaurant['name'],
            'restaurant' => $restaurant,
            'photos' => $photos,
            'mainPhoto' => $mainPhoto,
            'totalPhotos' => count($photos)
        ];
        
        return view('admin/restaurant_photos', $data);
    }

    /**
     * Обработка загрузки фотографий (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    private function handlePhotoUpload($restaurantId, $photoModel)
    {
        $uploadedFiles = $this->request->getFiles();
        
        if (!isset($uploadedFiles['photos']) || empty($uploadedFiles['photos'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Файлы не выбраны'
            ]);
        }
        
        $uploadPath = FCPATH . '../uploads/restaurants/' . $restaurantId . '/';
        
        // Создаем директорию если не существует
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Не удалось создать папку для загрузки'
                ]);
            }
        }
        
        $uploadedCount = 0;
        $errors = [];
        $existingPhotosCount = $photoModel->getPhotoCount($restaurantId);
        
        foreach ($uploadedFiles['photos'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                // Проверяем тип файла
                if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
                    $errors[] = 'Файл ' . $file->getName() . ' не является изображением';
                    continue;
                }
                
                // Проверяем размер файла (максимум 5MB)
                if ($file->getSize() > 5 * 1024 * 1024) {
                    $errors[] = 'Файл ' . $file->getName() . ' превышает максимальный размер 5MB';
                    continue;
                }
                
                // Генерируем уникальное имя файла
                $extension = $file->getExtension();
                $fileName = 'photo_' . $restaurantId . '_' . time() . '_' . uniqid() . '.' . $extension;
                $filePath = $uploadPath . $fileName;
                
                // ИСПРАВЛЕНО: путь для БД без writable/
                $relativePath = 'uploads/restaurants/' . $restaurantId . '/' . $fileName;
                
                if ($file->move($uploadPath, $fileName)) {
                    // Получаем размеры изображения
                    $imageInfo = @getimagesize($filePath);
                    $width = $imageInfo[0] ?? null;
                    $height = $imageInfo[1] ?? null;
                    
                    // Если это первое фото ресторана, делаем его главным
                    $isPrimary = ($existingPhotosCount + $uploadedCount) === 0;
                    
                    // Сохраняем в базу данных
                    $photoData = [
                        'width' => $width,
                        'height' => $height,
                        'file_size' => $file->getSize(),
                        'is_primary' => $isPrimary,
                        'sort_order' => $existingPhotosCount + $uploadedCount + 1,
                        'alt_text' => $restaurant['name'] . ' - photo ' . ($uploadedCount + 1)
                    ];
                    
                    if ($photoModel->addPhoto($restaurantId, $relativePath, null, $photoData)) {
                        $uploadedCount++;
                    } else {
                        $errors[] = 'Ошибка сохранения в базе данных для файла ' . $file->getName();
                        // Удаляем файл если не удалось сохранить в БД
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                } else {
                    $errors[] = 'Ошибка загрузки файла ' . $file->getName();
                }
            } else {
                $errors[] = 'Файл ' . $file->getName() . ' поврежден или уже перемещен';
            }
        }
        
        if ($uploadedCount > 0) {
            $message = "Успешно загружено {$uploadedCount} фото";
            if (!empty($errors)) {
                $message .= '. Ошибки: ' . implode('; ', $errors);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'uploaded_count' => $uploadedCount
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Не удалось загрузить ни одного файла. ' . implode('; ', $errors)
            ]);
        }
    }

    /**
     * Установить главное фото
     */
    public function setMainPhoto($photoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/restaurants');
        }
        
        $photoModel = new \App\Models\RestaurantPhotoModel();
        
        // Получаем информацию о фото
        $photo = $photoModel->find($photoId);
        if (!$photo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Фото не найдено'
            ]);
        }
        
        if ($photoModel->setMainPhoto($photo['restaurant_id'], $photoId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Главное фото установлено'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ошибка при установке главного фото'
            ]);
        }
    }

    /**
     * Удалить фото (ПРАВИЛЬНАЯ ВЕРСИЯ - согласуется с загрузкой)
     */
    public function deletePhoto($photoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/restaurants');
        }
        
        $photoModel = new \App\Models\RestaurantPhotoModel();
        
        try {
            // Получаем информацию о фото
            $photo = $photoModel->find($photoId);
            if (!$photo) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Фото не найдено'
                ]);
            }
            
            // ИСПРАВЛЕНО: используем тот же путь, что и при загрузке
            // Загрузка: FCPATH . '../uploads/restaurants/' . $restaurantId . '/'
            // Удаление: FCPATH . '../' . $photo['file_path']
            // где $photo['file_path'] = 'uploads/restaurants/' . $restaurantId . '/' . $fileName
            $filePath = FCPATH . '../' . $photo['file_path'];
            
            $fileDeleted = false;
            
            // Удаляем физический файл
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    $fileDeleted = true;
                    log_message('info', "Successfully deleted file: {$filePath}");
                } else {
                    log_message('error', "Failed to delete file: {$filePath}");
                }
            } else {
                log_message('warning', "File not found: {$filePath}");
            }
            
            // Удаляем запись из базы данных
            if ($photoModel->deletePhoto($photoId)) {
                // Если удаляли главное фото, назначаем новое главное фото
                if ($photo['is_primary']) {
                    $remainingPhotos = $photoModel->getRestaurantPhotos($photo['restaurant_id']);
                    if (!empty($remainingPhotos)) {
                        $photoModel->setMainPhoto($photo['restaurant_id'], $remainingPhotos[0]['id']);
                    }
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $fileDeleted ? 'Фото успешно удалено' : 'Фото удалено из базы (файл не найден на сервере)'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ошибка при удалении фото из базы данных'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', "Exception in deletePhoto: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Произошла ошибка при удалении фото'
            ]);
        }
    }

    /**
     * AJAX превью Google фотографий
     */
    public function previewGooglePhotos($restaurantId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/restaurants');
        }

        $restaurantModel = model('RestaurantModel');
        $restaurant = $restaurantModel->find($restaurantId);

        if (!$restaurant || empty($restaurant['google_place_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ресторан не найден или нет Google Place ID'
            ]);
        }

        try {
            // Используем прямой запрос к API (как в рабочем коде)
            $apiKey = env('GOOGLE_PLACES_API_KEY');
            $url = 'https://maps.googleapis.com/maps/api/place/details/json';
            $params = [
                'place_id' => $restaurant['google_place_id'],
                'fields' => 'name,photos',
                'key' => $apiKey
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url . '?' . http_build_query($params),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ошибка запроса к Google API: ' . $httpCode
                ]);
            }

            $data = json_decode($response, true);
            
            if (($data['status'] ?? '') !== 'OK') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'API Error: ' . ($data['status'] ?? 'Invalid JSON')
                ]);
            }

            $photos = $data['result']['photos'] ?? [];
            
            if (empty($photos)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'У данного места нет фотографий в Google Places'
                ]);
            }

            // Формируем превью URL для первых 6 фотографий
            $previews = [];
            foreach (array_slice($photos, 0, 6) as $photo) {
                $photoReference = $photo['photo_reference'];
                $previewUrl = sprintf(
                    'https://maps.googleapis.com/maps/api/place/photo?photoreference=%s&maxwidth=400&key=%s',
                    $photoReference,
                    $apiKey
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
                'restaurant_name' => $data['result']['name'] ?? $restaurant['name'],
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
     * Импорт фотографий из Google Places
     */
    public function importGooglePhotos($restaurantId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/restaurants');
        }

        $restaurantModel = model('RestaurantModel');
        $photoModel = new \App\Models\RestaurantPhotoModel();
        
        $restaurant = $restaurantModel->find($restaurantId);
        if (!$restaurant || empty($restaurant['google_place_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ресторан не найден или нет Google Place ID'
            ]);
        }

        $maxPhotos = min($this->request->getPost('max_photos') ?? 3, 5);
        
        // Используем рабочий код импорта
        $importedCount = $this->importPhotosFromGoogle($restaurantId, $restaurant['google_place_id'], $maxPhotos);
        
        return $this->response->setJSON([
            'success' => $importedCount > 0,
            'imported_count' => $importedCount,
            'message' => $importedCount > 0 ? 
                "Успешно импортировано {$importedCount} фотографий" :
                "Не удалось импортировать ни одной фотографии"
        ]);
    }

    /**
     * Рабочий метод импорта фотографий из Google (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    private function importPhotosFromGoogle($restaurantId, $placeId, $maxPhotos = 3)
    {
        $photoModel = new \App\Models\RestaurantPhotoModel();
        $apiKey = env('GOOGLE_PLACES_API_KEY');
        
        // Получаем фотографии
        $url = 'https://maps.googleapis.com/maps/api/place/details/json';
        $params = [
            'place_id' => $placeId,
            'fields' => 'name,photos',
            'key' => $apiKey
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return 0;
        }

        $data = json_decode($response, true);
        if (($data['status'] ?? '') !== 'OK') {
            return 0;
        }

        $photos = $data['result']['photos'] ?? [];
        $restaurantName = $data['result']['name'] ?? 'Restaurant';
        
        if (empty($photos)) {
            return 0;
        }

        
        $uploadDir = FCPATH . '../uploads/restaurants/' . $restaurantId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $importedCount = 0;
        $existingPhotosCount = $photoModel->getPhotoCount($restaurantId);
        
        for ($i = 0; $i < min($maxPhotos, count($photos)); $i++) {
            $photo = $photos[$i];
            $photoReference = $photo['photo_reference'];
            
            // Проверяем дубликаты
            if ($photoModel->photoReferenceExists($photoReference)) {
                continue;
            }

            // Скачиваем фото
            $photoUrl = 'https://maps.googleapis.com/maps/api/place/photo';
            $photoParams = [
                'photoreference' => $photoReference,
                'maxwidth' => 800,
                'key' => $apiKey
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $photoUrl . '?' . http_build_query($photoParams),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);

            $photoData = curl_exec($ch);
            $photoHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($photoHttpCode !== 200 || empty($photoData)) {
                continue;
            }

            // Определяем расширение
            $header = substr($photoData, 0, 10);
            $extension = 'jpg';
            if (strpos($header, "\xFF\xD8\xFF") === 0) {
                $extension = 'jpg';
            } elseif (strpos($header, "\x89PNG") === 0) {
                $extension = 'png';
            }

            // Сохраняем файл
            $fileName = 'google_' . $restaurantId . '_' . ($i + 1) . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            // ИСПРАВЛЕНО: путь для БД без writable/
            $relativePath = 'uploads/restaurants/' . $restaurantId . '/' . $fileName;

            if (file_put_contents($filePath, $photoData)) {
                // Получаем размеры
                $imageInfo = @getimagesize($filePath);
                $width = $imageInfo[0] ?? null;
                $height = $imageInfo[1] ?? null;

                $photoMetadata = [
                    'width' => $width,
                    'height' => $height,
                    'file_size' => strlen($photoData),
                    'is_primary' => ($existingPhotosCount + $importedCount) === 0,
                    'sort_order' => $existingPhotosCount + $importedCount + 1,
                    'alt_text' => $restaurantName . ' - фото ' . ($i + 1)
                ];

                if ($photoModel->addPhoto($restaurantId, $relativePath, $photoReference, $photoMetadata)) {
                    $importedCount++;
                } else {
                        unlink($filePath);
                    }
                }
            }

        return $importedCount;
    }

    /**
     * Управление sitemap
     */
    public function sitemap()
    {
        if (!session('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $restaurantModel = model('RestaurantModel');
        $cityModel = model('CityModel');
        $sitemapPath = FCPATH . '../writable/uploads/sitemap.xml';

        $stats = [
            'restaurants_count' => $restaurantModel->where('is_active', 1)->countAllResults(),
            'cities_count' => $cityModel->countAllResults(),
            'sitemap_exists' => file_exists($sitemapPath),
            'sitemap_size' => file_exists($sitemapPath) ? filesize($sitemapPath) : 0,
            'sitemap_modified' => file_exists($sitemapPath) ? filemtime($sitemapPath) : null,
            'robots_exists' => file_exists(FCPATH . 'robots.txt')
        ];

        $defaultSettings = [
            'include_restaurants' => true,
            'include_cities' => true,
            'include_static_pages' => true,
            'restaurants_priority' => '0.6',
            'cities_priority' => '0.8',
            'static_priority' => '0.5',
            'restaurants_changefreq' => 'monthly',
            'cities_changefreq' => 'weekly',
            'static_changefreq' => 'monthly'
        ];

        $data = [
            'title' => 'Sitemap Generator',
            'stats' => $stats,
            'settings' => $defaultSettings
        ];

        return view('admin/sitemap', $data);
    }

    public function generateSitemap()
    {
        if (!session('admin_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Требуется авторизация']);
        }

        try {
            $settings = $this->getSitemapSettings();
            $sitemap = $this->buildSitemap($settings);
            
            // Сохраняем только в uploads
            $uploadsPath = FCPATH . '../writable/uploads/sitemap.xml';
            
            // Создаем папку uploads если не существует
            $uploadsDir = dirname($uploadsPath);
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            if (file_put_contents($uploadsPath, $sitemap)) {
                $this->createRobotsTxt();
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Sitemap успешно сгенерирован',
                    'data' => [
                        'file_size' => filesize($uploadsPath),
                        'file_size_formatted' => $this->formatBytes(filesize($uploadsPath)),
                        'url_count' => substr_count($sitemap, '<url>'),
                        'file_path' => base_url('sitemap.xml') // стандартный URL
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
        }
    }

    /**
     * Получение настроек sitemap
     */
    private function getSitemapSettings()
    {
        return [
            'include_restaurants' => $this->request->getPost('include_restaurants') === 'true',
            'include_cities' => $this->request->getPost('include_cities') === 'true',
            'include_static_pages' => $this->request->getPost('include_static_pages') === 'true',
            'restaurants_priority' => $this->request->getPost('restaurants_priority') ?: '0.6',
            'cities_priority' => $this->request->getPost('cities_priority') ?: '0.8',
            'static_priority' => $this->request->getPost('static_priority') ?: '0.5',
            'restaurants_changefreq' => $this->request->getPost('restaurants_changefreq') ?: 'monthly',
            'cities_changefreq' => $this->request->getPost('cities_changefreq') ?: 'weekly',
            'static_changefreq' => $this->request->getPost('static_changefreq') ?: 'monthly'
        ];
    }

    /**
     * Построение XML sitemap
     */
    private function buildSitemap($settings)
    {
        $restaurantModel = model('RestaurantModel');
        $cityModel = model('CityModel');
        
        $baseUrl = base_url();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Главная страница
        $xml .= $this->addSitemapUrl($baseUrl, '1.0', 'daily', date('Y-m-d'));

        // Города
        if ($settings['include_cities']) {
            $cities = $cityModel->findAll();
            foreach ($cities as $city) {
                $cityUrl = $baseUrl . $city['seo_url'];
                $xml .= $this->addSitemapUrl($cityUrl, $settings['cities_priority'], $settings['cities_changefreq'], date('Y-m-d'));
            }
        }

        // Рестораны
        if ($settings['include_restaurants']) {
            $restaurants = $restaurantModel
                ->select('restaurants.*, cities.name as city_name')
                ->join('cities', 'cities.id = restaurants.city_id')
                ->where('restaurants.is_active', 1)
                ->where('restaurants.seo_url IS NOT NULL')
                ->findAll();

            foreach ($restaurants as $restaurant) {
                if (!empty($restaurant['seo_url'])) {
                    $restaurantUrl = $baseUrl . $restaurant['seo_url'];
                    $lastMod = $restaurant['updated_at'] ? 
                        date('Y-m-d', strtotime($restaurant['updated_at'])) : 
                        date('Y-m-d');
                    
                    $xml .= $this->addSitemapUrl($restaurantUrl, $settings['restaurants_priority'], $settings['restaurants_changefreq'], $lastMod);
                }
            }
        }

        // Статические страницы
        if ($settings['include_static_pages']) {
            $staticPages = ['about', 'contact', 'map', 'privacy-policy', 'terms-of-service'];
            foreach ($staticPages as $page) {
                $xml .= $this->addSitemapUrl($baseUrl . $page, $settings['static_priority'], $settings['static_changefreq'], date('Y-m-d'));
            }
        }

        $xml .= '</urlset>';
        return $xml;
    }

    /**
     * Добавление URL в sitemap
     */
    private function addSitemapUrl($url, $priority, $changefreq, $lastmod)
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
        $xml .= "    <changefreq>" . $changefreq . "</changefreq>\n";
        $xml .= "    <priority>" . $priority . "</priority>\n";
        $xml .= "  </url>\n";
        
        return $xml;
    }

    /**
     * Создание/обновление robots.txt
     */
    private function createRobotsTxt()
    {
        $robotsPath = FCPATH . 'robots.txt';
        $baseUrl = base_url();
        
        $robotsContent = "User-agent: *\n";
        $robotsContent .= "Allow: /\n\n";
        $robotsContent .= "# Sitemap\n";
        $robotsContent .= "Sitemap: {$baseUrl}sitemap.xml\n\n";
        $robotsContent .= "# Generated by Georgian Food Near Me Admin\n";
        $robotsContent .= "# " . date('Y-m-d H:i:s') . "\n";
        
        file_put_contents($robotsPath, $robotsContent);
    }

    /**
    * Валидация sitemap
    */
    public function validateSitemap()
    {
    if (!session('admin_logged_in')) {
        return $this->response->setJSON(['success' => false, 'message' => 'Требуется авторизация']);
    }

    $filePath = FCPATH . '../writable/uploads/sitemap.xml';
    
    if (!file_exists($filePath)) {
        return $this->response->setJSON(['success' => false, 'message' => 'Sitemap не найден']);
    }

    $xml = file_get_contents($filePath);
    
    libxml_use_internal_errors(true);
    $doc = simplexml_load_string($xml);
    $errors = libxml_get_errors();
    
    if ($errors) {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->message;
        }
        return $this->response->setJSON([
            'success' => false, 
            'message' => 'XML ошибки: ' . implode(', ', $errorMessages)
        ]);
    }

    $urlCount = substr_count($xml, '<url>');
    $fileSize = filesize($filePath);

    return $this->response->setJSON([
        'success' => true,
        'message' => 'Sitemap валиден',
        'data' => [
            'url_count' => $urlCount,
            'file_size' => $fileSize,
            'file_size_formatted' => $this->formatBytes($fileSize)
        ]
    ]);
    }

    /**
    * Удаление sitemap
    */
    public function deleteSitemap()
    {
    if (!session('admin_logged_in')) {
        return $this->response->setJSON(['success' => false, 'message' => 'Требуется авторизация']);
    }

    $filePath = FCPATH . '../writable/uploads/sitemap.xml';
    
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Sitemap удален']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Ошибка удаления файла']);
        }
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'Файл не найден']);
    }
    }

    /**
    * Форматирование размера файла
    */
    private function formatBytes($size, $precision = 2)
    {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
    }

    /**
    * Обновление статистики дашборда для добавления данных геокодирования
    */
    public function dashboardStats()
    {
        if (!session('admin_logged_in')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $restaurantModel = model('RestaurantModel');
        $cityModel = model('CityModel');
        $sitemapPath = FCPATH . '../writable/uploads/sitemap.xml';
        $robottxtPath = FCPATH . 'robots.txt';

        $stats = [
        'total_restaurants' => $restaurantModel->countAllResults(),
        'active_restaurants' => $restaurantModel->where('is_active', 1)->countAllResults(),
        'total_cities' => $cityModel->countAllResults(),
        'recent_additions' => $restaurantModel
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->countAllResults(),
        'restaurants_with_coordinates' => $restaurantModel
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->countAllResults(),
        'cities_with_coordinates' => $cityModel
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->countAllResults(),
        'sitemap_exists' => file_exists($sitemapPath),
        'sitemap_modified' => file_exists($sitemapPath) ? filemtime($sitemapPath) : null,
        'sitemap_size' => file_exists($sitemapPath) ? filesize($sitemapPath) : 0,
        'robots_exists' => file_exists($robottxtPath)
        ];

        return $this->response->setJSON($stats);
    }

    /**
     * Публичный доступ к sitemap
     */
    public function publicSitemap()
    {
        //$sitemapPath = WRITEPATH . 'uploads/sitemap.xml';
        $sitemapPath = FCPATH . '../writable/uploads/sitemap.xml';
        if (!file_exists($sitemapPath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Sitemap not found');
        }
        
        // Устанавливаем правильные заголовки
        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=utf-8')
            ->setBody(file_get_contents($sitemapPath));
    }
    /**
     * AJAX геокодирование адреса
     */
    public function geocodeAddress()
    {
        $address = $this->request->getPost('address');
        
        if (!$address) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Адрес не указан'
            ]);
        }
        
        // Здесь интеграция с Google Geocoding API
        // Пока возвращаем заглушку
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Геокодирование не настроено'
        ]);
    }

    /**
     * Поиск Google Place ID
     */
    public function findPlaceId()
    {
        $name = $this->request->getPost('name');
        $address = $this->request->getPost('address');
        
        // Здесь интеграция с Google Places API
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Поиск Place ID не настроен'
        ]);
    }

    /**
     * Обновление ресторана из реального DataForSEO API (ФИНАЛЬНАЯ ВЕРСИЯ)
     */
    public function updateFromDataForSeo($restaurantId)
    {
        // Проверяем авторизацию админа
        if (!session()->get('admin_logged_in')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Access denied - admin authorization required'
            ]);
        }

        // Проверяем AJAX запрос
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        try {
            $restaurantModel = model('RestaurantModel');
            $restaurant = $restaurantModel->find($restaurantId);
            
            if (!$restaurant) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Restaurant not found'
                ]);
            }

            // Получаем данные из POST запроса
            $requestData = $this->request->getJSON(true);
            $currentPlaceId = $requestData['current_place_id'] ?? $restaurant['google_place_id'];
            $restaurantName = $requestData['restaurant_name'] ?? $restaurant['name'];
            $restaurantAddress = $requestData['restaurant_address'] ?? $restaurant['address'];

            log_message('info', "DataForSEO update started for restaurant {$restaurantId}: {$restaurantName}");

            // Используем реальный DataForSEO API
            $dataForSeoService = new \App\Services\DataForSeoService();
            
            // Проверяем учетные данные API
            $credentialsCheck = $dataForSeoService->validateCredentials();
            if (!$credentialsCheck['valid']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'DataForSEO API credentials invalid: ' . $credentialsCheck['message']
                ]);
            }

            $realApiData = null;

            // Если есть Place ID, ищем по нему
            if (!empty($currentPlaceId)) {
                $realApiData = $this->searchByPlaceId($dataForSeoService, $currentPlaceId);
            }

            // Если нет данных по Place ID, ищем по названию и адресу
            if (!$realApiData && !empty($restaurantName)) {
                $realApiData = $this->searchByNameAndAddress($dataForSeoService, $restaurantName, $restaurantAddress, $restaurant);
            }

            // Если всё равно нет данных, сообщаем об этом
            if (!$realApiData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No matching restaurant found in DataForSEO API. Try updating the Google Place ID or restaurant name.'
                ]);
            }

            // Выполняем импорт/обновление
            $importService = new \App\Services\DataForSeoImportService();
            $result = $importService->importChamaMamaData($realApiData);
            
            log_message('info', 'DataForSEO update result: ' . json_encode($result));
            
            if ($result['success']) {
                // Получаем обновленные данные ресторана
                $updatedRestaurant = $restaurantModel->find($restaurantId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => '🎉 Restaurant updated with REAL DataForSEO API data! Source: ' . ($realApiData['_source'] ?? 'unknown'),
                    'updated_data' => $updatedRestaurant,
                    'api_source' => $realApiData['_source'] ?? 'real_api',
                    'api_cost' => $realApiData['cost'] ?? 0,
                    'import_stats' => [
                        'imported' => $result['imported'] ?? 0,
                        'updated' => $result['updated'] ?? 0,
                        'errors' => count($result['errors'] ?? [])
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to update restaurant data from DataForSEO',
                    'details' => $result
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'DataForSEO update error for restaurant ' . $restaurantId . ': ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating restaurant: ' . $e->getMessage(),
                'trace' => ENVIRONMENT === 'development' ? $e->getTraceAsString() : null
            ]);
        }
    }

    /**
     * Поиск в DataForSEO по Place ID (ПРАВИЛЬНЫЙ ФОРМАТ)
     */
    private function searchByPlaceId($dataForSeoService, $placeId)
    {
        try {
            log_message('info', "DataForSEO: Searching by Place ID: {$placeId}");

            // Используем правильный метод из сервиса
            $apiResponse = $dataForSeoService->searchByPlaceId($placeId);
            
            if ($apiResponse['success'] && !empty($apiResponse['data']['tasks'])) {
                foreach ($apiResponse['data']['tasks'] as $task) {
                    if ($task['status_code'] === 20000 && !empty($task['result'])) {
                        foreach ($task['result'] as $resultSet) {
                            if (!empty($resultSet['items'])) {
                                // Создаем правильную структуру ответа
                                $realData = [
                                    'id' => 'real-place-id-' . time(),
                                    'status_code' => 20000,
                                    'status_message' => 'Ok.',
                                    'time' => $task['time'] ?? '0.1 sec.',
                                    'cost' => $task['cost'] ?? 0,
                                    'result_count' => 1,
                                    'result' => [
                                        [
                                            'total_count' => $resultSet['total_count'] ?? 1,
                                            'count' => $resultSet['count'] ?? 1,
                                            'offset' => 0,
                                            'items' => $resultSet['items']
                                        ]
                                    ],
                                    '_source' => 'real_place_id_api'
                                ];

                                log_message('info', 'DataForSEO: Found real data by Place ID - ' . ($resultSet['items'][0]['title'] ?? 'Unknown'));
                                return $realData;
                            }
                        }
                    }
                }
            }

            log_message('info', 'DataForSEO: No data found by Place ID: ' . $placeId);
            return null;

        } catch (\Exception $e) {
            log_message('error', 'DataForSEO Place ID search error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Поиск в DataForSEO по названию и координатам
     */
    private function searchByNameAndAddress($dataForSeoService, $name, $address, $restaurant)
    {
        try {
            // Определяем координаты для поиска
            $latitude = $restaurant['latitude'] ?: 37.7749; // Default SF
            $longitude = $restaurant['longitude'] ?: -122.4194;

            // Очищаем название от лишних слов
            $cleanName = $this->cleanRestaurantName($name);
            $keyword = $cleanName . ' georgian restaurant';

            log_message('info', "DataForSEO: Searching by keyword '{$keyword}' at {$latitude},{$longitude}");

            // Используем метод из сервиса
            $apiResponse = $dataForSeoService->searchByKeywordAndLocation($keyword, $latitude, $longitude, 10, 10);
            
            if ($apiResponse['success'] && !empty($apiResponse['data']['tasks'])) {
                foreach ($apiResponse['data']['tasks'] as $task) {
                    if ($task['status_code'] === 20000 && !empty($task['result'])) {
                        foreach ($task['result'] as $resultSet) {
                            if (!empty($resultSet['items'])) {
                                // Ищем лучшее совпадение по названию
                                $bestMatch = $this->findBestRestaurantMatch($resultSet['items'], $name, $address);
                                if ($bestMatch) {
                                    // Создаем структуру ответа
                                    $realData = [
                                        'id' => 'real-name-search-' . time(),
                                        'status_code' => 20000,
                                        'status_message' => 'Ok.',
                                        'time' => $task['time'] ?? '0.1 sec.',
                                        'cost' => $task['cost'] ?? 0,
                                        'result_count' => 1,
                                        'result' => [
                                            [
                                                'total_count' => 1,
                                                'count' => 1,
                                                'offset' => 0,
                                                'items' => [$bestMatch]
                                            ]
                                        ],
                                        '_source' => 'real_name_search_api'
                                    ];

                                    log_message('info', 'DataForSEO: Found real data by name/location - ' . $bestMatch['title']);
                                    return $realData;
                                }
                            }
                        }
                    }
                }
            }

            log_message('info', 'DataForSEO: No matching restaurant found by name/location');
            return null;

        } catch (\Exception $e) {
            log_message('error', 'DataForSEO name search error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Остальные вспомогательные методы остаются без изменений
     */
    private function cleanRestaurantName($name)
    {
        $commonWords = ['restaurant', 'cafe', 'bar', 'grill', 'kitchen', 'house', 'the', 'a', 'an'];
        $words = explode(' ', strtolower($name));
        $cleanWords = array_filter($words, function($word) use ($commonWords) {
            return !in_array(trim($word), $commonWords) && strlen(trim($word)) > 2;
        });
        
        return implode(' ', $cleanWords) ?: $name;
    }

    private function findBestRestaurantMatch($items, $targetName, $targetAddress)
    {
        $bestMatch = null;
        $bestScore = 0;

        foreach ($items as $item) {
            $score = 0;
            $itemName = $item['title'] ?? '';
            $itemAddress = $item['address'] ?? '';

            // Сравнение названий (основной критерий)
            $nameScore = $this->calculateSimilarity($targetName, $itemName);
            $score += $nameScore * 0.7; // 70% веса

            // Сравнение адресов (дополнительный критерий)
            if (!empty($targetAddress) && !empty($itemAddress)) {
                $addressScore = $this->calculateSimilarity($targetAddress, $itemAddress);
                $score += $addressScore * 0.3; // 30% веса
            }

            // Проверяем что это действительно ресторан грузинской кухни
            $isGeorgian = stripos($itemName, 'georgian') !== false || 
                         stripos($item['category'] ?? '', 'georgian') !== false ||
                         stripos($item['description'] ?? '', 'georgian') !== false ||
                         stripos($item['description'] ?? '', 'khachapuri') !== false ||
                         stripos($item['description'] ?? '', 'khinkali') !== false;

            if ($isGeorgian) {
                $score += 0.2; // Бонус за грузинскую кухню
            }

            log_message('info', "Restaurant match score for '{$itemName}': {$score}");

            if ($score > $bestScore && $score > 0.4) { // Снижаем порог до 0.4
                $bestScore = $score;
                $bestMatch = $item;
            }
        }

        return $bestMatch;
    }

    private function calculateSimilarity($str1, $str2)
    {
        if (empty($str1) || empty($str2)) {
            return 0;
        }

        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        // Exact match
        if ($str1 === $str2) {
            return 1.0;
        }

        // Contains match
        if (strpos($str1, $str2) !== false || strpos($str2, $str1) !== false) {
            return 0.8;
        }

        // Levenshtein distance
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) {
            return 0;
        }

        $distance = levenshtein($str1, $str2);
        return 1 - ($distance / $maxLen);
    }

    /**
     * Создание mock данных DataForSEO для тестирования
     * В реальном проекте здесь был бы настоящий API запрос
     */
    private function createMockDataForSeoResponse($restaurant, $placeId)
    {
        return [
            "id" => "test-" . time(),
            "status_code" => 20000,
            "status_message" => "Ok.",
            "time" => "0.1246 sec.",
            "cost" => 0.0106,
            "result_count" => 1,
            "result" => [
                [
                    "total_count" => 1,
                    "count" => 1,
                    "offset" => 0,
                    "items" => [
                        [
                            "type" => "business_listing",
                            "title" => $restaurant['name'],
                            "original_title" => null,
                            "description" => $restaurant['description'] ?: "Updated description from DataForSEO API",
                            "category" => $restaurant['category'] ?: "Georgian restaurant",
                            "category_ids" => ["georgian_restaurant", "restaurant"],
                            "additional_categories" => ["Georgian restaurant", "Restaurant"],
                            "phone" => $restaurant['phone'] ?: "+1 555-0123",
                            "website" => $restaurant['website'] ?: "https://example.com",
                            "domain" => "example.com",
                            "snippet" => "Updated snippet from DataForSEO",
                            "address" => $restaurant['address'] ?: "123 Test Street, Test City",
                            "address_info" => [
                                "borough" => "Test Borough",
                                "address" => $restaurant['address'] ?: "123 Test Street",
                                "city" => "Test City",
                                "zip" => "10001",
                                "region" => "NY",
                                "country_code" => "US"
                            ],
                            "place_id" => $placeId,
                            "cid" => "test_cid_" . time(),
                            "feature_id" => "test_feature_id",
                            "latitude" => $restaurant['latitude'] ?: 40.7580,
                            "longitude" => $restaurant['longitude'] ?: -73.9855,
                            "is_claimed" => true,
                            "rating" => [
                                "rating_type" => "Max5",
                                "value" => max($restaurant['rating'] ?: 4.2, 4.2), // Улучшаем рейтинг
                                "votes_count" => 150,
                                "rating_max" => 5
                            ],
                            "rating_distribution" => [
                                "1" => 5,
                                "2" => 3,
                                "3" => 12,
                                "4" => 45,
                                "5" => 85
                            ],
                            "price_level" => $restaurant['price_level'] ?: "$$",
                            "total_photos" => 50,
                            "logo" => "https://example.com/logo.jpg",
                            "main_image" => "https://example.com/main.jpg",
                            "attributes" => [
                                "available_attributes" => [
                                    "service_options" => [
                                        "dine_in",
                                        "takeout",
                                        "delivery"
                                    ],
                                    "dining_options" => [
                                        "dinner",
                                        "lunch"
                                    ],
                                    "atmosphere" => [
                                        "casual",
                                        "cozy"
                                    ],
                                    "crowd" => [
                                        "families",
                                        "couples"
                                    ],
                                    "payments" => [
                                        "credit_cards",
                                        "mobile_payments"
                                    ],
                                    "accessibility" => [
                                        "wheelchair_accessible"
                                    ]
                                ]
                            ],
                            "work_time" => [
                                "work_hours" => [
                                    "timetable" => [
                                        "monday" => [
                                            [
                                                "open" => ["hour" => 17, "minute" => 0],
                                                "close" => ["hour" => 23, "minute" => 0]
                                            ]
                                        ],
                                        "tuesday" => [
                                            [
                                                "open" => ["hour" => 17, "minute" => 0],
                                                "close" => ["hour" => 23, "minute" => 0]
                                            ]
                                        ],
                                        "wednesday" => [
                                            [
                                                "open" => ["hour" => 17, "minute" => 0],
                                                "close" => ["hour" => 23, "minute" => 0]
                                            ]
                                        ],
                                        "thursday" => [
                                            [
                                                "open" => ["hour" => 17, "minute" => 0],
                                                "close" => ["hour" => 23, "minute" => 0]
                                            ]
                                        ],
                                        "friday" => [
                                            [
                                                "open" => ["hour" => 17, "minute" => 0],
                                                "close" => ["hour" => 23, "minute" => 30]
                                            ]
                                        ],
                                        "saturday" => [
                                            [
                                                "open" => ["hour" => 10, "minute" => 0],
                                                "close" => ["hour" => 23, "minute" => 30]
                                            ]
                                        ],
                                        "sunday" => [
                                            [
                                                "open" => ["hour" => 10, "minute" => 0],
                                                "close" => ["hour" => 22, "minute" => 0]
                                            ]
                                        ]
                                    ],
                                    "current_status" => "open"
                                ]
                            ],
                            "popular_times" => [
                                "monday" => [
                                    "popular_times_histogram" => [
                                        17 => 15,
                                        18 => 35,
                                        19 => 65,
                                        20 => 85,
                                        21 => 75,
                                        22 => 45
                                    ]
                                ],
                                "friday" => [
                                    "popular_times_histogram" => [
                                        17 => 25,
                                        18 => 45,
                                        19 => 75,
                                        20 => 95,
                                        21 => 85,
                                        22 => 65,
                                        23 => 35
                                    ]
                                ]
                            ],
                            "people_also_search" => [
                                [
                                    "title" => "Similar Georgian Restaurant",
                                    "place_id" => "ChIJ_similar_1",
                                    "cid" => "similar_cid_1",
                                    "rating" => ["value" => 4.1, "votes_count" => 89]
                                ]
                            ],
                            "place_topics" => [
                                "georgian_cuisine" => 25,
                                "khachapuri" => 18,
                                "cozy_atmosphere" => 12
                            ],
                            "local_business_links" => [
                                [
                                    "type" => "reservation",
                                    "title" => "OpenTable",
                                    "url" => "https://www.opentable.com/test"
                                ],
                                [
                                    "type" => "menu",
                                    "title" => "Menu",
                                    "url" => $restaurant['website'] ?: "https://example.com/menu"
                                ]
                            ],
                            "check_url" => "https://www.google.com/maps?cid=test_check_url",
                            "last_updated_time" => date('Y-m-d H:i:s'),
                            "first_seen" => $restaurant['created_at'] ?: date('Y-m-d H:i:s')
                        ]
                    ]
                ]
            ]
        ];
    }


    /**
     * Поиск Place ID для ресторана
     */
    private function findPlaceIdForRestaurant($name, $address = '')
    {
        try {
            $dataForSeoService = new \App\Services\DataForSeoService();
            return $dataForSeoService->findPlaceId($name, $address);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка поиска Place ID: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Получение данных из DataForSEO API
     */
    private function getDataFromDataForSEO($placeId)
    {
        try {
            $dataForSeoService = new \App\Services\DataForSeoService();
            $result = $dataForSeoService->searchByPlaceId($placeId);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => 'Ошибка API DataForSEO: ' . ($result['error'] ?? 'Неизвестная ошибка')
                ];
            }

            // Извлекаем данные из ответа API
            $data = $result['data'];
            if (empty($data['tasks']) || $data['tasks'][0]['status_code'] !== 20000) {
                return [
                    'success' => false,
                    'message' => 'API вернул ошибку или не найдены данные'
                ];
            }

            $items = $data['tasks'][0]['result'][0]['items'] ?? [];
            if (empty($items)) {
                return [
                    'success' => false,
                    'message' => 'Данные ресторана не найдены в DataForSEO'
                ];
            }

            return [
                'success' => true,
                'data' => $items[0] // Берем первый результат
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка получения данных: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Подготовка данных для обновления
     */
    private function prepareUpdateData($apiData, $currentData)
    {
        $updateData = [];

        // Маппинг полей API -> поля БД
        $fieldMapping = [
            'title' => 'name',
            'description' => 'description',
            'phone' => 'phone',
            'url' => 'website',
            'address' => 'address',
            'latitude' => 'latitude',
            'longitude' => 'longitude'
        ];

        // Обновляем только если новые данные отличаются и не пустые
        foreach ($fieldMapping as $apiField => $dbField) {
            if (isset($apiData[$apiField]) && !empty($apiData[$apiField])) {
                $newValue = $apiData[$apiField];
                $oldValue = $currentData[$dbField] ?? '';
                
                // Специальная обработка для некоторых полей
                if ($dbField === 'website' && !filter_var($newValue, FILTER_VALIDATE_URL)) {
                    continue; // Пропускаем невалидные URL
                }
                
                if ($dbField === 'phone') {
                    $newValue = $this->formatPhone($newValue);
                }
                
                // Обновляем только если значения отличаются
                if (trim($oldValue) !== trim($newValue)) {
                    $updateData[$dbField] = $newValue;
                }
            }
        }

        // Обновляем рейтинг
        if (isset($apiData['rating']['value']) && $apiData['rating']['value'] > 0) {
            $newRating = floatval($apiData['rating']['value']);
            $oldRating = floatval($currentData['rating'] ?? 0);
            
            if (abs($newRating - $oldRating) > 0.1) { // Обновляем если разница больше 0.1
                $updateData['rating'] = $newRating;
            }
        }

        // Обновляем уровень цен
        if (isset($apiData['price_level']) && !empty($apiData['price_level'])) {
            $priceLevel = $this->convertPriceLevel($apiData['price_level']);
            if ($priceLevel !== $currentData['price_level']) {
                $updateData['price_level'] = $priceLevel;
            }
        }

        // Обновляем CID если есть
        if (isset($apiData['cid']) && !empty($apiData['cid'])) {
            $updateData['cid'] = $apiData['cid'];
        }

        return $updateData;
    }

    /**
     * Форматирование номера телефона
     */
    private function formatPhone($phone)
    {
        // Простое форматирование - убираем лишние символы
        return preg_replace('/[^\d+()-\s]/', '', $phone);
    }

    /**
     * Конвертация уровня цен
     */
    private function convertPriceLevel($priceLevel)
    {
        if (is_string($priceLevel)) {
            return substr_count($priceLevel, '$');
        }
        return max(0, min(4, intval($priceLevel)));
    }

    /**
     * Очистка кеша
     */
    public function clearCache()
    {
        if ($this->request->getMethod() === 'POST') {
            try {
                // Очищаем весь кеш
                cache()->clean();
                
                // Можно также очистить кеш CodeIgniter
                if (function_exists('opcache_reset')) {
                    opcache_reset();
                }
                
                // Подсчитываем сколько было очищено (приблизительно)
                $cacheInfo = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'method' => 'manual_admin_clear',
                    'success' => true
                ];
                
                // Логируем действие
                log_message('info', 'Admin cache cleared manually from admin panel');
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Cache cleared successfully!',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'info' => $cacheInfo
                ]);
                
            } catch (\Exception $e) {
                log_message('error', 'Cache clear failed: ' . $e->getMessage());
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to clear cache: ' . $e->getMessage()
                ]);
            }
        }
        
        // GET запрос - показываем страницу управления кешем
        $data = [
            'title' => 'Cache Management - Admin',
            'cache_info' => $this->getCacheInfo()
        ];
        
        return view('admin/cache', $data);
    }

    /**
     * Получение информации о кеше
     */
    private function getCacheInfo()
    {
        $info = [
            'cache_enabled' => true,
            'cache_driver' => 'file', // или получить из конфигурации
            'cache_path' => WRITEPATH . 'cache/',
            'total_files' => 0,
            'total_size' => 0,
            'last_cleared' => null
        ];
        
        try {
            // Подсчитываем файлы кеша если используется file driver
            $cachePath = WRITEPATH . 'cache/';
            if (is_dir($cachePath)) {
                $files = glob($cachePath . '*');
                $info['total_files'] = count($files);
                
                $totalSize = 0;
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $totalSize += filesize($file);
                    }
                }
                $info['total_size'] = $totalSize;
                $info['total_size_formatted'] = $this->formatBytes($totalSize);
            }
            
        } catch (\Exception $e) {
            $info['error'] = $e->getMessage();
        }
        
        return $info;
    }
}