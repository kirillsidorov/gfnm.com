<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Manage Restaurants - Georgian Food Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <!-- Заголовок страницы -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-utensils me-2 text-primary"></i>
                Управление ресторанами
            </h1>
            <p class="text-muted mb-0">Добавление, редактирование и управление ресторанами</p>
        </div>
        <div class="btn-group">
            <a href="<?= base_url('admin/restaurants/add') ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Добавить ресторан
            </a>
            <button type="button" class="btn btn-info dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= base_url('admin/import') ?>">
                    <i class="fas fa-download me-1"></i>Импорт из DataForSEO
                </a></li>
                <li><a class="dropdown-item" href="<?= base_url('admin/geocode') ?>">
                    <i class="fas fa-map-marker-alt me-1"></i>Геокодирование
                </a></li>
                <li><a class="dropdown-item" href="<?= base_url('admin/google-photos') ?>">
                    <i class="fab fa-google me-1"></i>Google Photos
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="autoDetectTypes()">
                    <i class="fas fa-magic me-1"></i>Автоопределение типов
                </a></li>
            </ul>
        </div>
    </div>

    <!-- Статистическая панель -->
    <?php if (isset($stats)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-light text-dark fs-6 py-2 px-3">
                    <i class="fas fa-utensils me-1"></i>Всего: <?= $stats['total_all'] ?? 0 ?>
                </span>
                <a href="<?= base_url('admin/restaurants') ?>" 
                   class="badge bg-<?= ($filters['status'] ?? 'active') === 'active' ? 'success' : 'outline-success' ?> text-decoration-none fs-6 py-2 px-3">
                    <i class="fas fa-check me-1"></i>Активных: <?= $stats['total_active'] ?? 0 ?>
                </a>
                <a href="<?= base_url('admin/restaurants?' . http_build_query(array_merge($filters, ['status' => 'inactive']))) ?>" 
                   class="badge bg-<?= ($filters['status'] ?? '') === 'inactive' ? 'secondary' : 'outline-secondary' ?> text-decoration-none fs-6 py-2 px-3">
                    <i class="fas fa-pause me-1"></i>Неактивных: <?= $stats['total_inactive'] ?? 0 ?>
                </a>
                
                <?php if (($filters['status'] ?? 'active') !== '' || !$show_all): ?>
                <a href="<?= base_url('admin/restaurants?show_all=1') ?>" 
                   class="badge bg-info text-decoration-none fs-6 py-2 px-3">
                    <i class="fas fa-eye me-1"></i>Показать все
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Фильтры поиска с запоминанием -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>Фильтры поиска
                </h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="rememberFilters" 
                           <?= !empty($saved_filters_exist ?? false) ? 'checked' : '' ?>>
                    <label class="form-check-label text-muted small" for="rememberFilters">
                        <i class="fas fa-memory me-1"></i>Запомнить фильтры
                    </label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" id="filterForm" class="row g-3">
                <!-- Поиск по тексту -->
                <div class="col-md-3">
                    <label for="search" class="form-label">Поиск</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= esc($filters['search'] ?? '') ?>" 
                               placeholder="Название, адрес, описание...">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Фильтр по городу -->
                <div class="col-md-2">
                    <label for="city" class="form-label">Город</label>
                    <select class="form-select" name="city" id="city">
                        <option value="">Все города</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= $city['id'] ?>" 
                                    <?= ($filters['city_id'] ?? '') == $city['id'] ? 'selected' : '' ?>>
                                <?= esc($city['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Тип ресторана (3 состояния) -->
                <div class="col-md-2">
                    <label for="restaurant_type" class="form-label">Тип</label>
                    <select class="form-select" name="restaurant_type" id="restaurant_type">
                        <option value="">Все типы</option>
                        <option value="georgian" <?= ($filters['restaurant_type'] ?? '') === 'georgian' ? 'selected' : '' ?>>
                            🇬🇪 Грузинские
                        </option>
                        <option value="non_georgian" <?= ($filters['restaurant_type'] ?? '') === 'non_georgian' ? 'selected' : '' ?>>
                            🍽️ Обычные
                        </option>
                        <option value="undetermined" <?= ($filters['restaurant_type'] ?? '') === 'undetermined' ? 'selected' : '' ?>>
                            ❓ Не определено
                        </option>
                        <option value="auto_detected" <?= ($filters['restaurant_type'] ?? '') === 'auto_detected' ? 'selected' : '' ?>>
                            ⚠️ Требует проверки
                        </option>
                    </select>
                </div>

                <!-- Статус активности -->
                <div class="col-md-2">
                    <label for="status" class="form-label">Статус</label>
                    <select class="form-select" name="status" id="status">
                        <option value="active" <?= ($filters['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>
                            ✅ Активные
                        </option>
                        <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                            ⏸️ Неактивные
                        </option>
                        <option value="" <?= empty($filters['status']) && $show_all ? 'selected' : '' ?>>
                            🔍 Все
                        </option>
                    </select>
                </div>

                <!-- Наличие данных -->
                <div class="col-md-2">
                    <label for="data_filter" class="form-label">Данные</label>
                    <select class="form-select" name="data_filter" id="data_filter">
                        <option value="">Все</option>
                        <option value="no_coords" <?= ($filters['data_filter'] ?? '') === 'no_coords' ? 'selected' : '' ?>>
                            Без координат
                        </option>
                        <option value="no_photos" <?= ($filters['data_filter'] ?? '') === 'no_photos' ? 'selected' : '' ?>>
                            Без фотографий
                        </option>
                        <option value="no_place_id" <?= ($filters['data_filter'] ?? '') === 'no_place_id' ? 'selected' : '' ?>>
                            Без Place ID
                        </option>
                        <option value="has_website" <?= ($filters['data_filter'] ?? '') === 'has_website' ? 'selected' : '' ?>>
                            С веб-сайтом
                        </option>
                    </select>
                </div>

                <!-- Кнопки действий -->
                <div class="col-md-1 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary" title="Применить фильтры">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()" title="Сбросить фильтры">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Быстрые фильтры и активные фильтры -->
            <?php if (!empty(array_filter($filters)) || $show_all): ?>
                <div class="mt-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="text-muted me-2">Быстрые действия:</span>
                        
                        <!-- Фильтры типов -->
                        <?php if (empty($filters['restaurant_type'])): ?>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url('admin/restaurants?' . http_build_query(array_merge($filters, ['restaurant_type' => 'georgian']))) ?>" 
                                   class="btn btn-outline-success btn-sm">
                                    🇬🇪 Только грузинские
                                </a>
                                <a href="<?= base_url('admin/restaurants?' . http_build_query(array_merge($filters, ['restaurant_type' => 'undetermined']))) ?>" 
                                   class="btn btn-outline-warning btn-sm">
                                    ❓ Требуют проверки
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Автоопределение типов -->
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="autoDetectTypes()">
                            <i class="fas fa-magic me-1"></i>Автоопределение типов
                        </button>
                        
                        <!-- Управление запоминанием -->
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="saveCurrentFilters()" 
                                title="Сохранить текущие фильтры">
                            <i class="fas fa-save me-1"></i>Сохранить
                        </button>
                        
                        <!-- Сброс всех фильтров -->
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllFilters()">
                            <i class="fas fa-times me-1"></i>Очистить всё
                        </button>
                    </div>
                    
                    <!-- Активные фильтры -->
                    <div class="mt-2">
                        <small class="text-muted">Активные фильтры:</small>
                        <?php foreach ($filters as $key => $value): ?>
                            <?php if (!empty($value)): ?>
                                <span class="badge bg-primary me-1">
                                    <?php
                                    $filterDisplayNames = [
                                        'search' => 'Поиск: ' . $value,
                                        'status' => $value === 'active' ? '✅ Активные' : ($value === 'inactive' ? '⏸️ Неактивные' : $value),
                                        'restaurant_type' => [
                                            'georgian' => '🇬🇪 Грузинские',
                                            'non_georgian' => '🍽️ Обычные', 
                                            'undetermined' => '❓ Не определены',
                                            'auto_detected' => '⚠️ Требуют проверки'
                                        ][$value] ?? $value,
                                        'data_filter' => [
                                            'no_coords' => 'Без координат',
                                            'no_photos' => 'Без фото',
                                            'no_place_id' => 'Без Place ID',
                                            'has_website' => 'С сайтом'
                                        ][$value] ?? $value,
                                        'city_id' => 'Город: ' . ($cities[array_search($value, array_column($cities, 'id'))]['name'] ?? $value)
                                    ];
                                    
                                    echo $filterDisplayNames[$key] ?? ucfirst($key) . ': ' . $value;
                                    ?>
                                    <button type="button" class="btn-close btn-close-white ms-1" 
                                            onclick="removeFilter('<?= $key ?>')" 
                                            style="font-size: 0.6em;" title="Удалить фильтр"></button>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <?php if ($show_all): ?>
                            <span class="badge bg-info me-1">🔍 Показать все</span>
                        <?php endif; ?>
                        
                        <!-- Индикатор сохраненных фильтров -->
                        <?php if (!empty(session()->get('admin_filters'))): ?>
                            <span class="badge bg-success me-1" title="Фильтры сохранены в сессии">
                                <i class="fas fa-check"></i> Сохранено
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Массовые операции -->
    <div id="bulkActions" class="alert alert-info d-none mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-info-circle me-2"></i>
                Выбрано ресторанов: <span id="selectedCount">0</span>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('activate')">
                    <i class="fas fa-check me-1"></i>Активировать
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('deactivate')">
                    <i class="fas fa-pause me-1"></i>Деактивировать
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="bulkAction('geocode')">
                    <i class="fas fa-map-marker-alt me-1"></i>Геокодировать
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                    <i class="fas fa-trash me-1"></i>Удалить
                </button>
            </div>
        </div>
    </div>
    
    <!-- Таблица ресторанов -->
    <div class="card shadow">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?php
                    // Определяем текст заголовка в зависимости от фильтров
                    $headerText = 'Список ресторанов';
                    
                    if (($filters['status'] ?? 'active') === 'active') {
                        $headerText = '✅ Активные рестораны';
                    } elseif (($filters['status'] ?? '') === 'inactive') {
                        $headerText = '⏸️ Неактивные рестораны';
                    } elseif (empty($filters['status']) && $show_all) {
                        $headerText = '🔍 Все рестораны';
                    }
                    
                    // Добавляем тип если фильтр активен
                    if (!empty($filters['restaurant_type'])) {
                        $typeLabels = [
                            'georgian' => '🇬🇪 грузинские',
                            'non_georgian' => '🍽️ обычные',
                            'undetermined' => '❓ неопределенные',
                            'auto_detected' => '⚠️ требующие проверки'
                        ];
                        
                        $typeLabel = $typeLabels[$filters['restaurant_type']] ?? $filters['restaurant_type'];
                        $headerText .= " ({$typeLabel})";
                    }
                    
                    echo $headerText;
                    ?>
                    
                    <?php if (isset($total_restaurants)): ?>
                        <small class="text-muted ms-2">
                            (<?= count($restaurants) ?> из <?= $total_restaurants ?>)
                        </small>
                    <?php else: ?>
                        <small class="text-muted ms-2">
                            (<?= count($restaurants) ?>)
                        </small>
                    <?php endif; ?>
                </h6>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" onclick="exportResults()">
                        <i class="fas fa-download me-1"></i>Экспорт
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="refreshResults()">
                        <i class="fas fa-sync me-1"></i>Обновить
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($restaurants)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Ресторан</th>
                                <th>Город</th>
                                <th>Тип</th>
                                <th>Данные</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th width="130">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($restaurants as $restaurant): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input restaurant-checkbox" 
                                               value="<?= $restaurant['id'] ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <!-- Индикаторы типа ресторана -->
                                            <div class="me-2">
                                                <?php if (!empty($restaurant['google_place_id'])): ?>
                                                    <i class="fab fa-google text-success" title="Google Places"></i>
                                                <?php endif; ?>
                                                <?php if (!empty($restaurant['website'])): ?>
                                                    <i class="fas fa-globe text-info ms-1" title="Веб-сайт"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <strong class="text-dark"><?= esc($restaurant['name']) ?></strong>
                                                <?php if (!empty($restaurant['address'])): ?>
                                                    <br><small class="text-muted">
                                                        <?= esc(mb_substr($restaurant['address'], 0, 60)) ?>
                                                        <?= mb_strlen($restaurant['address']) > 60 ? '...' : '' ?>
                                                    </small>
                                                <?php endif; ?>
                                                <?php if (!empty($restaurant['phone'])): ?>
                                                    <br><small class="text-info">
                                                        <i class="fas fa-phone fa-sm me-1"></i><?= esc($restaurant['phone']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($restaurant['city_name'])): ?>
                                            <span class="badge bg-secondary"><?= esc($restaurant['city_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Не указан</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Определяем тип ресторана по полю is_georgian
                                        $isGeorgian = $restaurant['is_georgian'];
                                        
                                        if ($isGeorgian === '1' || $isGeorgian === 1) {
                                            // Точно грузинский
                                            echo '<span class="badge bg-success restaurant-type-badge" 
                                                        data-restaurant-id="' . $restaurant['id'] . '" 
                                                        data-current-type="georgian" 
                                                        title="Подтверждено как грузинский ресторан. Кликните для изменения." 
                                                        style="cursor: pointer;">
                                                    <i class="fas fa-flag"></i> Грузинский
                                                  </span>';
                                        } elseif ($isGeorgian === '0' || $isGeorgian === 0) {
                                            // Точно не грузинский
                                            echo '<span class="badge bg-secondary restaurant-type-badge" 
                                                        data-restaurant-id="' . $restaurant['id'] . '" 
                                                        data-current-type="non_georgian" 
                                                        title="Подтверждено как обычный ресторан. Кликните для изменения." 
                                                        style="cursor: pointer;">
                                                    <i class="fas fa-times"></i> Обычный
                                                  </span>';
                                        } else {
                                            // Не определено (null) - показываем автоматическое определение
                                            $autoDetected = false;
                                            $georgianIndicators = [];
                                            
                                            // Проверяем категорию
                                            $category = strtolower($restaurant['category'] ?? '');
                                            if (strpos($category, 'georgian') !== false || strpos($category, 'грузин') !== false) {
                                                $autoDetected = true;
                                                $georgianIndicators[] = 'категория';
                                            }
                                            
                                            // Проверяем название
                                            $name = strtolower($restaurant['name'] ?? '');
                                            $georgianKeywords = ['georgian', 'georgia', 'tbilisi', 'khachapuri', 'khinkali', 'adjarian', 'supra', 'caucas', 'грузин', 'тбилиси', 'хачапури', 'хинкали'];
                                            foreach ($georgianKeywords as $keyword) {
                                                if (strpos($name, $keyword) !== false) {
                                                    $autoDetected = true;
                                                    $georgianIndicators[] = 'название';
                                                    break;
                                                }
                                            }
                                            
                                            // Проверяем описание
                                            $description = strtolower($restaurant['description'] ?? '');
                                            foreach ($georgianKeywords as $keyword) {
                                                if (strpos($description, $keyword) !== false) {
                                                    $autoDetected = true;
                                                    if (!in_array('описание', $georgianIndicators)) {
                                                        $georgianIndicators[] = 'описание';
                                                    }
                                                    break;
                                                }
                                            }
                                            
                                            if ($autoDetected) {
                                                echo '<span class="badge bg-warning text-dark restaurant-type-badge" 
                                                            data-restaurant-id="' . $restaurant['id'] . '" 
                                                            data-current-type="auto_detected" 
                                                            title="Автоматически определен как грузинский по: ' . implode(', ', $georgianIndicators) . '. Кликните для подтверждения." 
                                                            style="cursor: pointer;">
                                                        <i class="fas fa-question"></i> Возможно грузинский
                                                      </span>';
                                            } else {
                                                echo '<span class="badge bg-light text-dark restaurant-type-badge" 
                                                            data-restaurant-id="' . $restaurant['id'] . '" 
                                                            data-current-type="undetermined" 
                                                            title="Тип не определен. Кликните для установки типа." 
                                                            style="cursor: pointer;">
                                                        <i class="fas fa-question-circle"></i> Не определен
                                                      </span>';
                                            }
                                        }
                                        ?>
                                        
                                        <!-- Показываем рейтинг под типом, если есть -->
                                        <?php if (!empty($restaurant['rating'])): ?>
                                            <?php 
                                            $rating = floatval($restaurant['rating']);
                                            $badge_class = $rating >= 4.5 ? 'success' : ($rating >= 4.0 ? 'warning' : 'secondary');
                                            ?>
                                            <br><small class="badge bg-<?= $badge_class ?> bg-opacity-75">
                                                <i class="fas fa-star fa-sm"></i> <?= number_format($rating, 1) ?>
                                            </small>
                                        <?php endif; ?>
                                        
                                        <!-- Уровень цен -->
                                        <?php if (!empty($restaurant['price_level']) && $restaurant['price_level'] > 0): ?>
                                            <br><small class="text-success">
                                                <?= str_repeat('$', intval($restaurant['price_level'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Индикаторы наличия данных -->
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
                                                <span class="badge bg-success" title="Есть координаты">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger" title="Нет координат">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                            <?php endif; ?>

                                            <?php if (!empty($restaurant['google_place_id'])): ?>
                                                <span class="badge bg-success" title="Есть Google Place ID">
                                                    <i class="fab fa-google"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary" title="Нет Google Place ID">
                                                    <i class="fab fa-google"></i>
                                                </span>
                                            <?php endif; ?>

                                            <!-- Проверка фотографий -->
                                            <?php 
                                            $hasPhotos = false;
                                            try {
                                                $photoModel = new \App\Models\RestaurantPhotoModel();
                                                $photosCount = $photoModel->where('restaurant_id', $restaurant['id'])->countAllResults();
                                                $hasPhotos = $photosCount > 0;
                                            } catch (Exception $e) {
                                                // Модель фото недоступна
                                            }
                                            ?>
                                            <?php if ($hasPhotos): ?>
                                                <span class="badge bg-info" title="Есть фотографии">
                                                    <i class="fas fa-images"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary" title="Нет фотографий">
                                                    <i class="fas fa-images"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($restaurant['is_active']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Активен
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-pause"></i> Неактивен
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d.m.Y', strtotime($restaurant['created_at'])) ?>
                                        </small>
                                        <?php if ($restaurant['updated_at'] !== $restaurant['created_at']): ?>
                                            <br><small class="text-info" title="Обновлен">
                                                <i class="fas fa-edit fa-sm"></i> 
                                                <?= date('d.m', strtotime($restaurant['updated_at'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('admin/restaurants/edit/' . $restaurant['id']) ?>" 
                                               class="btn btn-outline-primary" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info dropdown-toggle dropdown-toggle-split" 
                                                        data-bs-toggle="dropdown" title="Дополнительно">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="<?= base_url('admin/restaurants/' . $restaurant['id'] . '/photos') ?>">
                                                            <i class="fas fa-images me-1"></i>Фотографии
                                                        </a>
                                                    </li>
                                                    <?php if (!empty($restaurant['seo_url'])): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="<?= base_url($restaurant['seo_url']) ?>" target="_blank">
                                                                <i class="fas fa-external-link-alt me-1"></i>Просмотр
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (!empty($restaurant['google_place_id'])): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="importGooglePhotos(<?= $restaurant['id'] ?>)">
                                                                <i class="fab fa-google me-1"></i>Импорт фото
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           onclick="deleteRestaurant(<?= $restaurant['id'] ?>, '<?= esc($restaurant['name']) ?>')">
                                                            <i class="fas fa-trash me-1"></i>Удалить
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация (ИСПРАВЛЕННАЯ ВЕРСИЯ) -->
                <?php if (isset($pager) && method_exists($pager, 'links')): ?>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Показано <?= count($restaurants) ?> из <?= $total_restaurants ?? 'неизвестно' ?> записей
                            </div>
                            <div>
                                <?= $pager->links() ?>
                            </div>
                        </div>
                    </div>
                <?php elseif (isset($pager) && is_array($pager)): ?>
                    <!-- Если пагинация пришла как массив (fallback) -->
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Показано <?= count($restaurants) ?> из <?= $total_restaurants ?? 'неизвестно' ?> записей
                            </div>
                            <div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm">
                                        <?php if ($pager['hasPrev']): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= current_url() ?>?page=<?= $pager['currentPage'] - 1 ?>&<?= http_build_query($filters) ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $pager['currentPage'] - 2); $i <= min($pager['totalPages'], $pager['currentPage'] + 2); $i++): ?>
                                            <li class="page-item <?= $i == $pager['currentPage'] ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= current_url() ?>?page=<?= $i ?>&<?= http_build_query($filters) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($pager['hasNext']): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= current_url() ?>?page=<?= $pager['currentPage'] + 1 ?>&<?= http_build_query($filters) ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Пустое состояние -->
                <div class="text-center py-5">
                    <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Рестораны не найдены</h5>
                    <p class="text-muted">
                        <?php if (!empty(array_filter($filters))): ?>
                            Попробуйте изменить параметры поиска или 
                            <a href="<?= base_url('admin/restaurants') ?>">сбросить фильтры</a>
                        <?php else: ?>
                            Начните с добавления ресторанов в систему
                        <?php endif; ?>
                    </p>
                    <div class="mt-3">
                        <a href="<?= base_url('admin/restaurants/add') ?>" class="btn btn-primary me-2">
                            <i class="fas fa-plus me-1"></i>Добавить ресторан
                        </a>
                        <a href="<?= base_url('admin/import') ?>" class="btn btn-info">
                            <i class="fas fa-download me-1"></i>Импорт данных
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Обработка выбора всех чекбоксов
    $('#selectAll').on('change', function() {
        $('.restaurant-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });
    
    // Обработка изменения отдельных чекбоксов
    $('.restaurant-checkbox').on('change', function() {
        updateBulkActions();
        updateSelectAllState();
    });
    
    // Автоматическая отправка формы при изменении фильтров
    $('#city, #status, #data_filter, #restaurant_type').on('change', function() {
        // Убираем параметр show_all при изменении фильтров
        const form = $('#filterForm');
        const currentAction = form.attr('action') || window.location.pathname;
        form.attr('action', currentAction.split('?')[0]);
        form.submit();
    });
    
    // Поиск по Enter
    $('#search').on('keypress', function(e) {
        if (e.which === 13) {
            $('#filterForm').submit();
        }
    });
    
    // Очистка поиска по Escape
    $('#search').on('keyup', function(e) {
        if (e.which === 27) { // Escape
            $(this).val('');
            $('#filterForm').submit();
        }
    });

    // Обработчик клика по бейджам типа ресторана
    $(document).on('click', '.restaurant-type-badge', function(e) {
        e.preventDefault();
        
        const restaurantId = $(this).data('restaurant-id');
        const currentType = $(this).data('current-type');
        
        // Показываем контекстное меню
        showTypeChangeMenu(restaurantId, currentType, e.pageX, e.pageY);
    });
    
    // Скрываем контекстное меню при клике вне его
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.type-context-menu').length) {
            $('.type-context-menu').remove();
        }
    });
});

// Показ контекстного меню для изменения типа
function showTypeChangeMenu(restaurantId, currentType, x, y) {
    // Удаляем существующие меню
    $('.type-context-menu').remove();
    
    const menu = $(`
        <div class="type-context-menu" style="position: fixed; top: ${y}px; left: ${x}px; z-index: 9999;">
            <div class="card shadow">
                <div class="card-body p-2">
                    <div class="btn-group-vertical w-100">
                        <button type="button" class="btn btn-sm btn-success" onclick="setRestaurantType(${restaurantId}, 'georgian')" ${currentType === 'georgian' ? 'disabled' : ''}>
                            <i class="fas fa-flag"></i> Грузинский
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="setRestaurantType(${restaurantId}, 'non_georgian')" ${currentType === 'non_georgian' ? 'disabled' : ''}>
                            <i class="fas fa-times"></i> Обычный
                        </button>
                        <button type="button" class="btn btn-sm btn-light" onclick="setRestaurantType(${restaurantId}, 'undetermined')" ${currentType === 'undetermined' ? 'disabled' : ''}>
                            <i class="fas fa-question-circle"></i> Не определен
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(menu);
    
    // Автоматически скрываем через 5 секунд
    setTimeout(() => {
        menu.fadeOut(() => menu.remove());
    }, 5000);
}

// Быстрое изменение типа ресторана
function setRestaurantType(restaurantId, type) {
    $('.type-context-menu').remove();
    
    $.ajax({
        url: `<?= base_url('admin/restaurants/set-type/') ?>${restaurantId}`,
        method: 'POST',
        data: {
            type: type,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('danger', response.message || 'Ошибка изменения типа');
            }
        },
        error: function() {
            showAlert('danger', 'Ошибка запроса');
        }
    });
}

// Автоматическое определение типов
function autoDetectTypes() {
    if (!confirm('Автоматически определить типы ресторанов на основе названий и категорий?')) {
        return;
    }
    
    $.ajax({
        url: '<?= base_url('admin/restaurants/auto-detect-types') ?>',
        method: 'POST',
        data: {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        beforeSend: function() {
            showAlert('info', 'Выполняется автоматическое определение типов...');
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                
                // Показываем детальную статистику
                if (response.stats) {
                    const stats = response.stats;
                    const details = `
                        <div class="mt-2">
                            <small>
                                Обработано: ${stats.total_processed}<br>
                                Обновлено: ${stats.updated}<br>
                                Найдено грузинских: ${stats.georgian_found}<br>
                                Осталось неопределенных: ${stats.remaining_undetermined}
                            </small>
                        </div>
                    `;
                    
                    $('.alert').last().append(details);
                }
                
                setTimeout(() => location.reload(), 3000);
            } else {
                showAlert('danger', response.message || 'Ошибка автоматического определения');
            }
        },
        error: function() {
            showAlert('danger', 'Ошибка запроса автоматического определения');
        }
    });
}

// Обновление состояния массовых операций
function updateBulkActions() {
    const selectedCount = $('.restaurant-checkbox:checked').length;
    $('#selectedCount').text(selectedCount);
    
    if (selectedCount > 0) {
        $('#bulkActions').removeClass('d-none');
    } else {
        $('#bulkActions').addClass('d-none');
    }
}

// Обновление состояния "Выбрать все"
function updateSelectAllState() {
    const totalCheckboxes = $('.restaurant-checkbox').length;
    const checkedCheckboxes = $('.restaurant-checkbox:checked').length;
    
    $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
    $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
}

// Очистка поиска
function clearSearch() {
    $('#search').val('');
    $('#filterForm').submit();
}

// Обновление результатов
function refreshResults() {
    location.reload();
}

// Экспорт результатов
function exportResults() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.open('?' + params.toString(), '_blank');
}

// Массовые операции
function bulkAction(action) {
    const selectedIds = $('.restaurant-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Выберите хотя бы один ресторан');
        return;
    }
    
    let confirmMessage = `Вы уверены, что хотите ${getActionName(action)} ${selectedIds.length} ресторан(ов)?`;
    
    if (action === 'delete') {
        confirmMessage = `Внимание! Это действие удалит ${selectedIds.length} ресторан(ов) навсегда. Продолжить?`;
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Отправляем AJAX запрос
    $.ajax({
        url: '<?= base_url('admin/restaurants/bulk') ?>',
        method: 'POST',
        data: {
            action: action,
            ids: selectedIds,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        beforeSend: function() {
            $('#bulkActions button').prop('disabled', true);
            showAlert('info', `Выполняется ${getActionName(action)}...`);
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message || 'Операция выполнена успешно');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', response.message || 'Произошла ошибка');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Ошибка выполнения запроса';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch (e) {
                // Используем стандартное сообщение
            }
            showAlert('danger', errorMessage);
        },
        complete: function() {
            $('#bulkActions button').prop('disabled', false);
        }
    });
}

// Получение названия действия
function getActionName(action) {
    const actions = {
        'activate': 'активировать',
        'deactivate': 'деактивировать', 
        'delete': 'удалить',
        'geocode': 'геокодировать'
    };
    return actions[action] || action;
}

// Удаление отдельного ресторана
function deleteRestaurant(id, name) {
    if (!confirm(`Вы уверены, что хотите удалить ресторан "${name}"?`)) {
        return;
    }
    
    $.ajax({
        url: `<?= base_url('admin/restaurants/delete/') ?>${id}`,
        method: 'DELETE',
        data: {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Ресторан удален');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('danger', response.message || 'Ошибка удаления');
            }
        },
        error: function() {
            showAlert('danger', 'Ошибка удаления ресторана');
        }
    });
}

// Импорт фотографий из Google
function importGooglePhotos(restaurantId) {
    $.ajax({
        url: `<?= base_url('admin/restaurants/') ?>${restaurantId}/import-google-photos`,
        method: 'POST',
        data: {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        beforeSend: function() {
            showAlert('info', 'Импорт фотографий...');
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                // Обновляем иконку фотографий в таблице
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('danger', response.message || 'Ошибка импорта');
            }
        },
        error: function() {
            showAlert('danger', 'Ошибка импорта фотографий');
        }
    });
}

// Показ уведомлений
function showAlert(type, message) {
    const alertClass = `alert-${type}`;
    const iconClass = type === 'success' ? 'check-circle' : 
                     type === 'danger' ? 'exclamation-circle' : 
                     type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;">
            <i class="fas fa-${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('body').append(alert);
    
    // Автоматическое скрытие через 5 секунд (кроме ошибок)
    if (type !== 'danger') {
        setTimeout(function() {
            $('.alert').last().fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
}
//по фильтрам
$(document).ready(function() {
    // Проверяем статус запоминания фильтров при загрузке
    checkRememberStatus();
    
    // Обработка переключателя "Запомнить фильтры"
    $('#rememberFilters').on('change', function() {
        const isChecked = this.checked;
        
        if (isChecked) {
            // Включаем запоминание - сохраняем текущие фильтры
            saveCurrentFilters();
            showAlert('success', 'Запоминание фильтров включено');
        } else {
            // Выключаем запоминание - очищаем сессию
            clearSavedFilters();
            showAlert('info', 'Запоминание фильтров отключено');
        }
    });
    
    // Автоматическое сохранение при изменении фильтров (если включено запоминание)
    $('#city, #status, #data_filter, #restaurant_type').on('change', function() {
        if ($('#rememberFilters').is(':checked')) {
            // Небольшая задержка для сохранения
            setTimeout(() => {
                saveCurrentFiltersQuietly();
            }, 100);
        }
        
        // Отправляем форму
        $('#filterForm').submit();
    });
    
    // Поиск по Enter с автосохранением
    $('#search').on('keypress', function(e) {
        if (e.which === 13) {
            if ($('#rememberFilters').is(':checked')) {
                saveCurrentFiltersQuietly();
            }
            $('#filterForm').submit();
        }
    });
    
    // Остальные обработчики...
    $('.restaurant-checkbox').on('change', function() {
        updateBulkActions();
        updateSelectAllState();
    });
    
    $('#selectAll').on('change', function() {
        $('.restaurant-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });
});

// Проверка статуса запоминания при загрузке
function checkRememberStatus() {
    // Проверяем есть ли сохраненные фильтры на сервере
    $.ajax({
        url: '<?= base_url('admin/filters/status') ?>',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.has_saved_filters) {
                $('#rememberFilters').prop('checked', true);
            }
        },
        error: function() {
            // Если ошибка API, проверяем локально
            const hasActiveFilters = <?= !empty(array_filter($filters)) ? 'true' : 'false' ?>;
            if (hasActiveFilters) {
                $('#rememberFilters').prop('checked', true);
            }
        }
    });
}

// Сохранение текущих фильтров
function saveCurrentFilters() {
    const filters = getFormFilters();
    
    $.ajax({
        url: '<?= base_url('admin/filters/save') ?>',
        method: 'POST',
        data: {
            filters: filters,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateFilterBadges();
                showAlert('success', 'Фильтры сохранены');
            }
        },
        error: function() {
            showAlert('danger', 'Ошибка сохранения фильтров');
        }
    });
}

// Тихое сохранение без уведомлений
function saveCurrentFiltersQuietly() {
    const filters = getFormFilters();
    
    $.ajax({
        url: '<?= base_url('admin/filters/save') ?>',
        method: 'POST',
        data: {
            filters: filters,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json'
        // Без обработки результата для тихого сохранения
    });
}

// Очистка сохраненных фильтров
function clearSavedFilters() {
    $.ajax({
        url: '<?= base_url('admin/filters/clear') ?>',
        method: 'POST',
        data: {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            updateFilterBadges();
        }
    });
}

// Получение текущих фильтров из формы
function getFormFilters() {
    return {
        search: $('#search').val(),
        city_id: $('#city').val(),
        restaurant_type: $('#restaurant_type').val(),
        status: $('#status').val(),
        data_filter: $('#data_filter').val()
    };
}

// Сброс фильтров
function resetFilters() {
    // Очищаем форму
    $('#filterForm')[0].reset();
    
    // Если включено запоминание, очищаем и сохраненные фильтры
    if ($('#rememberFilters').is(':checked')) {
        clearSavedFilters();
    }
    
    // Переходим на чистую страницу
    window.location.href = '<?= base_url('admin/restaurants') ?>';
}

// Полная очистка всего
function clearAllFilters() {
    if (confirm('Очистить все фильтры и отключить запоминание?')) {
        // Отключаем запоминание
        $('#rememberFilters').prop('checked', false);
        
        // Очищаем сохраненные данные
        clearSavedFilters();
        
        // Переходим на чистую страницу
        window.location.href = '<?= base_url('admin/restaurants') ?>';
    }
}

// Удаление конкретного фильтра
function removeFilter(filterKey) {
    const currentUrl = new URL(window.location);
    const params = new URLSearchParams(currentUrl.search);
    
    // Маппинг имен параметров
    const paramMapping = {
        'city_id': 'city',
        'restaurant_type': 'restaurant_type',
        'status': 'status',
        'data_filter': 'data_filter',
        'search': 'search'
    };
    
    const paramName = paramMapping[filterKey] || filterKey;
    params.delete(paramName);
    
    // Обновляем URL
    currentUrl.search = params.toString();
    window.location.href = currentUrl.toString();
}

// Обновление индикаторов фильтров
function updateFilterBadges() {
    // Добавляем/убираем бейдж "Сохранено"
    const hasSaved = $('#rememberFilters').is(':checked');
    const savedBadge = $('.badge:contains("Сохранено")');
    
    if (hasSaved && savedBadge.length === 0) {
        $('.badge').last().after('<span class="badge bg-success me-1" title="Фильтры сохранены в сессии"><i class="fas fa-check"></i> Сохранено</span>');
    } else if (!hasSaved && savedBadge.length > 0) {
        savedBadge.remove();
    }
}

// Очистка поиска
function clearSearch() {
    $('#search').val('');
    if ($('#rememberFilters').is(':checked')) {
        saveCurrentFiltersQuietly();
    }
    $('#filterForm').submit();
}

// Показ уведомлений
function showAlert(type, message) {
    const alertClass = `alert-${type}`;
    const iconClass = type === 'success' ? 'check-circle' : 
                     type === 'danger' ? 'exclamation-circle' : 
                     type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;">
            <i class="fas fa-${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('body').append(alert);
    
    // Автоматическое скрытие
    setTimeout(function() {
        $('.alert').last().fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}

</script>



<?= $this->endSection() ?>