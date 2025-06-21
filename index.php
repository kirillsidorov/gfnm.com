<?php

/**
 * CodeIgniter 4 - Root index.php
 * Направляет все запросы в public/index.php
 */

// Получаем запрашиваемый URI
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

// Убираем начальный слеш
$request_uri = ltrim($request_uri, '/');

// Если это прямой запрос к корню
if (empty($request_uri) || $request_uri === 'index.php') {
    // Просто включаем public/index.php
    require_once __DIR__ . '/public/index.php';
    exit;
}

// Для всех остальных запросов устанавливаем правильные переменные
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';

// Включаем public/index.php
require_once __DIR__ . '/public/index.php';