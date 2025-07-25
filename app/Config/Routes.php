<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Главная страница
$routes->get('/', 'Home::index');
//Pages
$routes->get('about', 'Pages::index');
$routes->get('privacy', 'Pages::privacy');
$routes->get('terms', 'Pages::terms');

//Pages redirect
$routes->get('privacy-policy', function() {
    return redirect()->to(base_url('privacy'));
});
$routes->get('terms-of-service', function() {
    return redirect()->to(base_url('terms'));
});
$routes->get('contact', function(){
    return redirect()->to(base_url('about'));
});

// Bug Report routes
$routes->get('bug-report', 'BugReport::index');
$routes->post('bug-report/submit', 'BugReport::submit');

// API роуты
$routes->get('api/search/suggestions', 'Home::searchSuggestions');

// Поиск
$routes->get('search', 'Home::search');

// ИСПРАВЛЕНО: Новая правильная логика маршрутов
$routes->get('georgian-restaurant-near-me', 'MapController::nearMe');  
$routes->get('restaurants', 'Restaurants::browse');    
$routes->get('georgian-restaurant', 'Restaurants::browse');

// Карта (можно оставить отдельно или сделать редирект)
$routes->get('map', 'MapController::index');

// Временные редиректы (ОБНОВЛЕНЫ)
$routes->get('khachapuri', function() {
    return redirect()->to(base_url('georgian-restaurant-near-me'))->with('message', 'Khachapuri restaurants coming soon!');
});

$routes->get('khinkali', function() {
    return redirect()->to(base_url('georgian-restaurant-near-me'))->with('message', 'Khinkali restaurants coming soon!');
});

$routes->get('georgian-cuisine', function() {
    return redirect()->to(base_url('georgian-restaurant-near-me'))->with('message', 'Georgian cuisine guide coming soon!');
});

// Географические редиректы
$routes->get('georgian-restaurants-nyc', 'Restaurants::newYorkCity');

$routes->get('georgian-restaurant-nyc', function() {
    return redirect()->to(base_url('georgian-restaurants-nyc'));
});

// Near me редиректы (ОБНОВЛЕНЫ)
$routes->get('khachapuri-near-me', function() {
    return redirect()->to(base_url('georgian-restaurant-near-me?q=khachapuri'));
});

$routes->get('khinkali-near-me', function() {
    return redirect()->to(base_url('georgian-restaurant-near-me?q=khinkali'));
});

$routes->get('chama-mama-catering-restaurant-new-york', function() {
    return redirect()->to(base_url('chama-mama-catering-restaurant-manhattan'));
});

// SEO-оптимизированные маршруты для городов
$routes->get('georgian-restaurants-(:segment)', 'Restaurants::byCitySlugNew/$1');

// SEO-оптимизированные маршруты для ресторанов
//$routes->get('(:segment)-restaurant-(:segment)', 'Restaurants::restaurantDetail/$1/$2');
$routes->get('(:segment)', 'RestaurantsEnhance::restaurantDetail/$1');

// Штаты (редиректы на основные города)
$routes->get('georgian-restaurants-state-(:segment)', 'RestaurantsEnhance::redirectStateToCity/$1');

// Специальные случаи
$routes->get('new-york-city', 'RestaurantsEnhance::newYorkCity');
$routes->get('nyc', 'RestaurantsEnhance::newYorkCity');

// ==========================================
// ADMIN ROUTES
// ==========================================

// Публичный вход в админку
$routes->match(['get', 'post'], 'admin/login', 'Admin::login');

// Защищенные админские роуты
$routes->group('admin', ['filter' => 'adminauth'], function($routes) {
    // Дашборд
    $routes->get('', 'Admin::index');
    $routes->get('dashboard', 'Admin::dashboard');
    
    // Очистка кеша
    $routes->get('clear-cache', 'Admin::clearCache');
    $routes->post('clear-cache', 'Admin::clearCache');
    // Выход
    $routes->get('logout', 'Admin::logout');
    
    // ===== УПРАВЛЕНИЕ РЕСТОРАНАМИ - РАСШИРЕННАЯ ВЕРСИЯ =====
    $routes->get('restaurants', 'Admin::restaurants');
    // ===== УПРАВЛЕНИЕ ФИЛЬТРАМИ РЕСТОРАНОВ =====
    $routes->get('filters/status', 'Admin::filtersStatus');
    $routes->post('filters/save', 'Admin::saveFilters');
    $routes->post('filters/clear', 'Admin::clearFilters');
    $routes->get('filters/load', 'Admin::loadSavedFilters');
    $routes->get('filters/stats', 'Admin::filtersStats');
    $routes->get('filters/export', 'Admin::exportFilters');
    $routes->post('filters/import', 'Admin::importFilters');
    
    // Дополнительные методы для типов ресторанов
    $routes->post('restaurants/set-type/(:num)', 'Admin::setRestaurantType/$1');
    $routes->post('restaurants/auto-detect-types', 'Admin::autoDetectTypes');
    $routes->match(['get', 'post'], 'restaurants/edit/(:num)', 'Admin::editRestaurant/$1');
    $routes->get('restaurants/delete/(:num)', 'Admin::deleteRestaurant/$1');
    $routes->post('restaurants/bulk', 'Admin::bulk');

    // ===== УПРАВЛЕНИЕ ФОТОГРАФИЯМИ РЕСТОРАНОВ =====
    $routes->match(['get', 'post'], 'restaurants/(:num)/photos', 'Admin::restaurantPhotos/$1');
    $routes->post('restaurants/photos/(:num)/set-main', 'Admin::setMainPhoto/$1');
    $routes->delete('restaurants/photos/(:num)/delete', 'Admin::deletePhoto/$1');
    $routes->get('restaurants/(:num)/preview-google-photos', 'Admin::previewGooglePhotos/$1');
    $routes->post('restaurants/(:num)/import-google-photos', 'Admin::importGooglePhotos/$1');
    $routes->post('restaurants/update-from-dataforseo/(:num)', 'Admin::updateFromDataForSeo/$1');
    
    // AJAX методы для SEO полей (только SEO URL проверка)
    $routes->post('restaurants/check-seo-url-availability', 'Admin::checkSeoUrlAvailability');
    $routes->post('restaurants/generate-slug', 'Admin::generateSlug');
    $routes->post('restaurants/generate-missing-seo-urls', 'Admin::generateMissingSeoUrls');


    $routes->get('restaurants/stats', 'RestaurantUpdaterController::getRestaurantsStats');
    $routes->get('restaurants/updater', 'RestaurantUpdaterController::updaterPage');
    $routes->post('restaurants/update-all', 'RestaurantUpdaterController::updateAllRestaurants');
    $routes->post('restaurants/update/(:num)', 'RestaurantUpdaterController::updateRestaurant/$1');
    $routes->post('restaurants/update', 'RestaurantUpdaterController::updateRestaurant');
    $routes->post('restaurants/update-batch', 'RestaurantUpdaterController::updateRestaurantsBatch');

    // ===== УПРАВЛЕНИЕ ФОТОГРАФИЯМИ =====
    $routes->match(['get', 'post'], 'restaurants/(:num)/photos', 'Restaurants::uploadPhoto/$1');
    $routes->post('photos/(:num)/set-main', 'Restaurants::setMainPhoto/$1');
    $routes->delete('photos/(:num)/delete', 'Restaurants::deletePhoto/$1');
    
    // Управление городами
    $routes->match(['get', 'post'], 'cities', 'Admin::cities');
    
    // Поиск ресторанов
    $routes->match(['get', 'post'], 'search', 'Admin::searchRestaurants');
         
    // Экспорт данных
    $routes->get('export/(:segment)', 'Admin::export/$1');

    // CSV ИМПОРТ
    $routes->get('restaurants/import-csv', 'Admin::importCsv');
    $routes->post('restaurants/process-csv-import', 'Admin::processCsvImport');
    $routes->post('restaurants/preview-csv', 'Admin::previewCsv');
    $routes->get('restaurants/db-stats', 'Admin::getDbStats');
    $routes->post('restaurants/check-duplicates', 'Admin::checkDuplicates');
    $routes->post('restaurants/update-place-id', 'Admin::updateRestaurantPlaceId');
    $routes->post('restaurants/fill-missing-data', 'Admin::fillMissingData');
    $routes->post('restaurants/fix-without-city', 'Admin::fixRestaurantsWithoutCity');

    // Геокодирование
    $routes->get('geocode', 'GeocodeController::index');
    $routes->get('geocode/status', 'GeocodeController::index'); // alias
    
    // Городы
    $routes->get('geocode/cities', 'GeocodeController::cities');
    $routes->post('geocode/update-city', 'GeocodeController::updateCityCoordinates');
    
    // Рестораны
    $routes->get('geocode/restaurants', 'GeocodeController::restaurants');
    $routes->post('geocode/update-restaurant', 'GeocodeController::updateRestaurantCoordinates');
    
    // Старые маршруты для обратной совместимости
    $routes->get('geocode/restaurants-old', 'GeocodeController::updateRestaurantCoordinatesOld');
    $routes->get('geocode/test', 'GeocodeController::testGeocoding');
    
    // ===== GOOGLE PHOTOS =====
    // GOOGLE PHOTOS - ФИНАЛЬНАЯ ВЕРСИЯ
    $routes->get('google-photos', 'GooglePhotosController::index');
    $routes->get('google-photos/check-api-status', 'GooglePhotosController::checkApiStatus');
    $routes->get('google-photos/preview-photos/(:num)', 'GooglePhotosController::previewPhotos/$1');
    $routes->post('google-photos/import-photos/(:num)', 'GooglePhotosController::importPhotos/$1');
    $routes->post('google-photos/bulk-import-photos', 'GooglePhotosController::bulkImportPhotos');
    $routes->post('google-photos/set-place-id/(:num)', 'GooglePhotosController::setPlaceId/$1');
    $routes->get('google-photos/restaurants-without-place-id', 'GooglePhotosController::restaurantsWithoutPlaceId');
    $routes->get('google-photos/restaurants-without-photos', 'GooglePhotosController::restaurantsWithoutPhotos');
    $routes->get('google-photos/stats', 'GooglePhotosController::getPhotosStats');

    // Диагностические методы
    $routes->get('google-photos/test-basic', 'GooglePhotosController::testBasic');
    $routes->get('google-photos/test-database', 'GooglePhotosController::testDatabase');
    $routes->get('google-photos/test-api-key', 'GooglePhotosController::testApiKey');
    
    // НОВЫЕ МЕТОДЫ для Google Photos интеграции с редактированием ресторанов
    $routes->get('restaurants/(:num)/google-photos', 'Restaurants::getGooglePhotos/$1');
    $routes->post('restaurants/(:num)/find-place-id', 'Restaurants::findPlaceId/$1');
    $routes->get('restaurants/(:num)/preview-google-photos', 'Restaurants::previewGooglePhotos/$1');
    $routes->post('restaurants/update-missing-photos', 'Restaurants::updateMissingPhotos');
    
    // Sitemap управление
    $routes->get('sitemap', 'Admin::sitemap');
    $routes->post('sitemap/generate', 'Admin::generateSitemap');
    $routes->post('sitemap/validate', 'Admin::validateSitemap');
    $routes->post('sitemap/delete', 'Admin::deleteSitemap');
    
    // Dashboard stats
    $routes->get('dashboard/stats', 'Admin::dashboardStats');

    // Главная страница импорта
    $routes->get('dataforseo-import', 'DataForSeoImport::index');
   
    // Обновление одного ресторана
    $routes->post('update-restaurant/(:num)', 'DataForSeoImport::updateRestaurant/$1');
    
    // Массовое обновление
    $routes->post('bulk-update', 'DataForSeoImport::bulkUpdate');
    
    // Поиск и импорт новых
    $routes->post('search-and-import', 'DataForSeoImport::searchAndImport');
    
    // Проверка статуса API
    $routes->get('check-api-status', 'DataForSeoImport::checkApiStatus');
    
    // Поиск Place ID
    $routes->post('find-place-id', 'DataForSeoImport::findPlaceId');
    
    // Экспорт логов
    $routes->get('export-logs', 'DataForSeoImport::exportLogs');
});

// ===== API ДЛЯ КАРТЫ =====
$routes->get('/api/map/data', 'MapController::getMapData');
$routes->post('/api/map/search', 'MapController::searchLocal');
$routes->post('/api/map/nearby', 'MapController::searchNearby');

// ===== SITEMAP И SEO =====
$routes->get('sitemap.xml', function() {
    return redirect()->to(base_url('uploads/sitemap.xml'));
});

// Простой API тест
$routes->get('simple-api-test', 'SimpleApiTestController::index');
$routes->get('simple-api-test/(:alpha)', 'SimpleApiTestController::index/$1');
$routes->get('simple-api-test/download/(:any)', 'SimpleApiTestController::download/$1');



// ВАЖНО: Универсальный обработчик остается в конце
$routes->get('(:segment)', 'Restaurants::bySeoUrl/$1');