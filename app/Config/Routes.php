<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Главная страница - georgian food near me
$routes->get('/', 'Home::index');

// Основная страница ресторанов - georgian restaurant near me + georgian restaurant
$routes->get('georgian-restaurant-near-me', 'Restaurants::index');
//страны → города
$routes->get('restaurants', 'Restaurants::browse');

// Временные редиректы на существующие страницы (пока не созданы новые контроллеры)
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
$routes->get('georgian-restaurant-nyc', 'Restaurants::newYorkCity');

$routes->get('restaurants/(:segment)', 'Restaurants::bySlug/$1');

$routes->get('georgian-restaurant-manhattan', function() {
    return redirect()->to(base_url('restaurants/manhattan'));
});

$routes->get('georgian-restaurant-chicago', function() {
    return redirect()->to(base_url('restaurants/chicago'));
});

$routes->get('georgian-restaurant-brooklyn', function() {
    return redirect()->to(base_url('restaurants/brooklyn'));
});

// Near me редиректы
$routes->get('khachapuri-near-me', function() {
    return redirect()->to(base_url('search?q=khachapuri'));
});

$routes->get('khinkali-near-me', function() {
    return redirect()->to(base_url('search?q=khinkali'));
});

// Поиск
$routes->get('search', 'Home::search');

// Рестораны (существующие роуты)
$routes->group('restaurants', function($routes) {
    $routes->get('view/(:num)', 'Restaurants::view/$1');
    $routes->get('city/(:num)', 'Restaurants::byCity/$1');
    $routes->get('state/(:any)', 'Restaurants::byState/$1');
    $routes->get('price/(:num)', 'Restaurants::byPrice/$1');
});

// SEO-friendly маршруты для ресторанов
$routes->get('restaurant/(:num)/(:any)', 'Restaurants::view/$1');
$routes->get('city/(:num)/(:any)', 'Restaurants::byCity/$1');

// Тестовые роуты
$routes->get('test/database', 'Test::database');
$routes->get('test/data', 'Test::data');