<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-images me-2"></i>Google Photos Management
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="mb-0"><?= number_format($stats['total_restaurants']) ?></h4>
                        <p class="mb-0">Всего ресторанов</p>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-utensils fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="mb-0"><?= number_format($stats['with_place_id']) ?></h4>
                        <p class="mb-0">С Place ID</p>
                        <small><?= $stats['place_id_percentage'] ?>%</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-map-marker-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="mb-0"><?= number_format($stats['with_photos']) ?></h4>
                        <p class="mb-0">С фотографиями</p>
                        <small><?= $stats['photos_percentage'] ?>%</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-images fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h4 class="mb-0"><?= number_format($stats['total_photos']) ?></h4>
                        <p class="mb-0">Всего фото</p>
                        <small>Google: <?= $stats['google_photos'] ?></small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-camera fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Массовые операции -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-cogs me-2"></i>
            Массовые операции
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Заполнение Place ID -->
            <div class="col-md-6 mb-3">
                <div class="border rounded p-3 h-100">
                    <h6 class="text-primary">
                        <i class="fas fa-search me-2"></i>Заполнить Place ID
                    </h6>
                    <p class="text-muted small mb-3">Найти и установить Google Place ID для ресторанов без них</p>
                    
                    <div class="mb-3">
                        <label class="form-label small">Количество ресторанов:</label>
                        <input type="number" id="placeIdLimit" value="10" min="1" max="50" 
                               class="form-control form-control-sm" style="width: 100px; display: inline-block;">
                    </div>
                    
                    <button onclick="fillPlaceIds()" 
                            class="btn btn-primary btn-sm" id="fillPlaceIdsBtn">
                        <i class="fas fa-search me-1"></i>Заполнить Place ID
                    </button>
                </div>
            </div>

            <!-- Импорт фотографий -->
            <div class="col-md-6 mb-3">
                <div class="border rounded p-3 h-100">
                    <h6 class="text-success">
                        <i class="fas fa-download me-2"></i>Импорт фотографий
                    </h6>
                    <p class="text-muted small mb-3">Скачать фотографии из Google Places для ресторанов с Place ID</p>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small">Ресторанов:</label>
                            <input type="number" id="photosLimit" value="5" min="1" max="20" 
                                   class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Фото на ресторан:</label>
                            <input type="number" id="photosPerRestaurant" value="3" min="1" max="10" 
                                   class="form-control form-control-sm">
                        </div>
                    </div>
                    
                    <button onclick="importPhotos()" 
                            class="btn btn-success btn-sm" id="importPhotosBtn">
                        <i class="fas fa-download me-1"></i>Импорт фотографий
                    </button>
                </div>
            </div>
        </div>

        <!-- Полное обновление -->
        <div class="text-center pt-3 border-top">
            <h6 class="text-purple mb-2">
                <i class="fas fa-bolt me-2"></i>Полное обновление
            </h6>
            <p class="text-muted small mb-3">Последовательно заполнить Place ID и импортировать фотографии</p>
            
            <button onclick="fullUpdate()" 
                    class="btn btn-warning" id="fullUpdateBtn">
                <i class="fas fa-bolt me-1"></i>Полное обновление
            </button>
        </div>
    </div>
</div>

<!-- Лог операций -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list-alt me-2"></i>Лог операций
        </h5>
        <button onclick="clearLog()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-trash me-1"></i>Очистить
        </button>
    </div>
    <div class="card-body">
        <div id="operationLog" class="bg-light p-3 rounded" style="height: 250px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 0.9em;">
            <div class="text-muted">Операции будут отображаться здесь...</div>
        </div>
    </div>
</div>

<!-- Управление ресторанами -->
<div class="row">
    <!-- Рестораны без Place ID -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Рестораны без Place ID
                </h6>
            </div>
            <div class="card-body">
                <button onclick="loadRestaurantsWithoutPlaceId()" 
                        class="btn btn-outline-primary btn-sm mb-3">
                    <i class="fas fa-sync me-1"></i>Загрузить список
                </button>
                <div id="restaurantsWithoutPlaceId">
                    <div class="text-center text-muted">
                        <i class="fas fa-mouse-pointer"></i> Нажмите кнопку выше
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Рестораны без фотографий -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-image text-info me-2"></i>
                    Рестораны без фотографий
                </h6>
            </div>
            <div class="card-body">
                <button onclick="loadRestaurantsWithoutPhotos()" 
                        class="btn btn-outline-info btn-sm mb-3">
                    <i class="fas fa-sync me-1"></i>Загрузить список
                </button>
                <div id="restaurantsWithoutPhotos">
                    <div class="text-center text-muted">
                        <i class="fas fa-mouse-pointer"></i> Нажмите кнопку выше
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease;
}
.card:hover {
    transform: translateY(-2px);
}
.text-purple {
    color: #6f42c1 !important;
}
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
#operationLog .text-success { color: #198754 !important; }
#operationLog .text-danger { color: #dc3545 !important; }
#operationLog .text-warning { color: #fd7e14 !important; }
#operationLog .text-info { color: #0dcaf0 !important; }
#operationLog .text-muted { color: #6c757d !important; }

/* Анимация для кнопок */
.btn {
    transition: all 0.3s ease;
}
.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Стиль для спиннеров */
.fa-spin {
    animation: fa-spin 1s infinite linear;
}

/* Стиль для логов */
#operationLog div {
    padding: 2px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
#operationLog div:last-child {
    border-bottom: none;
}

/* Стиль для карточек с операциями */
.border.rounded.p-3 {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid #e9ecef !important;
    transition: all 0.3s ease;
}
.border.rounded.p-3:hover {
    border-color: #007bff !important;
    box-shadow: 0 4px 12px rgba(0,123,255,0.15);
}

/* Компактный стиль для списков ресторанов */
.restaurant-item {
    transition: background-color 0.2s ease;
}
.restaurant-item:hover {
    background-color: #f8f9fa;
}

/* Статусные индикаторы */
.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}
.status-success { background-color: #198754; }
.status-warning { background-color: #ffc107; }
.status-danger { background-color: #dc3545; }
.status-info { background-color: #0dcaf0; }
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Глобальные переменные
    let isProcessing = false;
    
    function logMessage(message, type = 'info') {
        const log = document.getElementById('operationLog');
        const timestamp = new Date().toLocaleTimeString();
        const colorClass = {
            'info': 'text-info',
            'success': 'text-success', 
            'error': 'text-danger',
            'warning': 'text-warning',
            'muted': 'text-muted'
        }[type] || 'text-muted';
        
        // Добавляем статусный индикатор
        const statusClass = {
            'success': 'status-success',
            'error': 'status-danger', 
            'warning': 'status-warning',
            'info': 'status-info'
        }[type];
        
        const logEntry = document.createElement('div');
        logEntry.className = `${colorClass} restaurant-item`;
        logEntry.innerHTML = `
            ${statusClass ? `<span class="status-indicator ${statusClass}"></span>` : ''}
            [${timestamp}] ${message}
        `;
        
        log.appendChild(logEntry);
        log.scrollTop = log.scrollHeight;
        
        // Ограничиваем количество записей в логе
        const entries = log.children;
        if (entries.length > 100) {
            log.removeChild(entries[0]);
        }
    }

    function clearLog() {
        document.getElementById('operationLog').innerHTML = '<div class="text-muted">Лог очищен...</div>';
    }

    function setProcessing(processing) {
        isProcessing = processing;
        const buttons = ['fillPlaceIdsBtn', 'importPhotosBtn', 'fullUpdateBtn'];
        buttons.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.disabled = processing;
            }
        });
    }

    function fillPlaceIds() {
        if (isProcessing) return;
        
        const btn = document.getElementById('fillPlaceIdsBtn');
        const limit = document.getElementById('placeIdLimit').value;
        
        setProcessing(true);
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Обработка...';
        
        logMessage(`🔍 Начинаем заполнение Place ID для ${limit} ресторанов...`, 'info');
        
        $.ajax({
            url: '<?= base_url('admin/google-photos/bulk-fill-place-ids') ?>',
            method: 'POST',
            data: { limit: limit },
            timeout: 300000, // 5 минут
            success: function(response) {
                if (response.success) {
                    logMessage(`✅ ${response.message}`, 'success');
                    
                    // Показываем детали
                    if (response.details && response.details.details) {
                        response.details.details.forEach(detail => {
                            const status = detail.success ? '✓' : '✗';
                            const type = detail.success ? 'success' : 'error';
                            logMessage(`${status} ${detail.restaurant} (${detail.city}) - ${detail.message}`, type);
                        });
                    }
                    
                    // Показываем уведомление
                    showNotification('Заполнение Place ID завершено!', 'success');
                    
                    // Обновляем статистику через 3 секунды
                    setTimeout(() => {
                        logMessage('🔄 Обновление статистики...', 'info');
                        location.reload();
                    }, 3000);
                } else {
                    logMessage('❌ Ошибка: ' + response.message, 'error');
                    showNotification('Ошибка при заполнении Place ID', 'error');
                }
            },
            error: function(xhr, status, error) {
                logMessage('❌ Ошибка выполнения запроса: ' + error, 'error');
                showNotification('Ошибка сетевого запроса', 'error');
            },
            complete: function() {
                setProcessing(false);
                btn.innerHTML = '<i class="fas fa-search me-1"></i>Заполнить Place ID';
            }
        });
    }

    function importPhotos() {
        if (isProcessing) return;
        
        const btn = document.getElementById('importPhotosBtn');
        const limit = document.getElementById('photosLimit').value;
        const photosPerRestaurant = document.getElementById('photosPerRestaurant').value;
        
        setProcessing(true);
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Импорт...';
        
        logMessage(`📸 Начинаем импорт фотографий для ${limit} ресторанов (по ${photosPerRestaurant} фото)...`, 'info');
        
        $.ajax({
            url: '<?= base_url('admin/google-photos/bulk-import-photos') ?>',
            method: 'POST',
            data: { 
                limit: limit,
                photos_per_restaurant: photosPerRestaurant 
            },
            timeout: 600000, // 10 минут
            success: function(response) {
                if (response.success) {
                    logMessage(`✅ ${response.message}`, 'success');
                    
                    // Показываем детали
                    if (response.details && response.details.details) {
                        response.details.details.forEach(detail => {
                            const status = detail.success ? '✓' : '✗';
                            const type = detail.success ? 'success' : 'error';
                            const photos = detail.photos_imported || 0;
                            logMessage(`${status} ${detail.restaurant} - ${photos} фото - ${detail.message}`, type);
                        });
                    }
                    
                    // Показываем уведомление
                    showNotification('Импорт фотографий завершен!', 'success');
                    
                    // Обновляем статистику
                    setTimeout(() => {
                        logMessage('🔄 Обновление статистики...', 'info');
                        location.reload();
                    }, 3000);
                } else {
                    logMessage('❌ Ошибка: ' + response.message, 'error');
                    showNotification('Ошибка при импорте фотографий', 'error');
                }
            },
            error: function(xhr, status, error) {
                logMessage('❌ Ошибка выполнения запроса: ' + error, 'error');
                showNotification('Ошибка сетевого запроса', 'error');
            },
            complete: function() {
                setProcessing(false);
                btn.innerHTML = '<i class="fas fa-download me-1"></i>Импорт фотографий';
            }
        });
    }

    function fullUpdate() {
        if (isProcessing) return;
        
        const btn = document.getElementById('fullUpdateBtn');
        
        setProcessing(true);
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Выполняется...';
        
        logMessage('🚀 Начинаем полное обновление...', 'info');
        
        // Этап 1: Place ID
        const placeIdLimit = document.getElementById('placeIdLimit').value;
        
        $.ajax({
            url: '<?= base_url('admin/google-photos/bulk-fill-place-ids') ?>',
            method: 'POST',
            data: { limit: placeIdLimit },
            timeout: 300000,
            success: function(response) {
                logMessage('📍 Этап 1/2 (Place ID): ' + response.message, response.success ? 'success' : 'warning');
                
                // Пауза перед следующим этапом
                setTimeout(() => {
                    // Этап 2: Фотографии
                    const photosLimit = document.getElementById('photosLimit').value;
                    const photosPerRestaurant = document.getElementById('photosPerRestaurant').value;
                    
                    $.ajax({
                        url: '<?= base_url('admin/google-photos/bulk-import-photos') ?>',
                        method: 'POST',
                        data: { 
                            limit: photosLimit,
                            photos_per_restaurant: photosPerRestaurant 
                        },
                        timeout: 600000,
                        success: function(response2) {
                            logMessage('📸 Этап 2/2 (Фото): ' + response2.message, response2.success ? 'success' : 'warning');
                            logMessage('🎉 Полное обновление завершено!', 'success');
                            
                            showNotification('Полное обновление завершено!', 'success');
                            
                            setTimeout(() => {
                                logMessage('🔄 Обновление статистики...', 'info');
                                location.reload();
                            }, 3000);
                        },
                        error: function() {
                            logMessage('❌ Ошибка на этапе импорта фотографий', 'error');
                            showNotification('Ошибка на этапе импорта фотографий', 'error');
                        },
                        complete: function() {
                            setProcessing(false);
                            btn.innerHTML = '<i class="fas fa-bolt me-1"></i>Полное обновление';
                        }
                    });
                }, 2000); // 2 секунды между этапами
            },
            error: function() {
                logMessage('❌ Ошибка на этапе заполнения Place ID', 'error');
                showNotification('Ошибка на этапе заполнения Place ID', 'error');
                setProcessing(false);
                btn.innerHTML = '<i class="fas fa-bolt me-1"></i>Полное обновление';
            }
        });
    }

    function loadRestaurantsWithoutPlaceId() {
        logMessage('📋 Загружаем рестораны без Place ID...', 'info');
        
        const btn = event.target;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Загрузка...';
        
        $.ajax({
            url: '<?= base_url('admin/google-photos/restaurants-without-place-id') ?>',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const container = document.getElementById('restaurantsWithoutPlaceId');
                    
                    if (response.restaurants.length === 0) {
                        container.innerHTML = '<div class="alert alert-success"><i class="fas fa-check me-2"></i>Все рестораны имеют Google Place ID!</div>';
                        logMessage('✅ Все рестораны уже имеют Place ID', 'success');
                        return;
                    }
                    
                    let html = `<div class="small text-muted mb-2">Найдено: <strong>${response.restaurants.length}</strong> ресторанов</div>`;
                    html += '<div style="max-height: 300px; overflow-y: auto;">';
                    
                    response.restaurants.forEach(restaurant => {
                        html += `
                            <div class="d-flex align-items-center justify-content-between p-2 border-bottom restaurant-item">
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">${restaurant.name}</div>
                                    <div class="text-muted" style="font-size: 0.8em;">${restaurant.city_name}</div>
                                </div>
                                <button onclick="setPlaceIdForRestaurant(${restaurant.id})" 
                                        class="btn btn-primary btn-sm" id="placeIdBtn${restaurant.id}">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    container.innerHTML = html;
                    logMessage(`✅ Загружено ${response.restaurants.length} ресторанов без Place ID`, 'success');
                } else {
                    logMessage('❌ Ошибка загрузки: ' + response.message, 'error');
                }
            },
            error: function() {
                logMessage('❌ Ошибка загрузки списка ресторанов', 'error');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }

    function loadRestaurantsWithoutPhotos() {
        logMessage('🖼️ Загружаем рестораны без фотографий...', 'info');
        
        const btn = event.target;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Загрузка...';
        
        $.ajax({
            url: '<?= base_url('admin/google-photos/restaurants-without-photos') ?>',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const container = document.getElementById('restaurantsWithoutPhotos');
                    
                    if (response.restaurants.length === 0) {
                        container.innerHTML = '<div class="alert alert-success"><i class="fas fa-check me-2"></i>Все рестораны имеют фотографии!</div>';
                        logMessage('✅ Все рестораны уже имеют фотографии', 'success');
                        return;
                    }
                    
                    let html = `<div class="small text-muted mb-2">Найдено: <strong>${response.restaurants.length}</strong> ресторанов</div>`;
                    html += '<div style="max-height: 300px; overflow-y: auto;">';
                    
                    response.restaurants.forEach(restaurant => {
                        html += `
                            <div class="d-flex align-items-center justify-content-between p-2 border-bottom restaurant-item">
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">${restaurant.name}</div>
                                    <div class="text-muted" style="font-size: 0.8em;">${restaurant.city_name}</div>
                                </div>
                                <div>
                                    <button onclick="previewPhotos(${restaurant.id})" 
                                            class="btn btn-outline-secondary btn-sm me-1" id="previewBtn${restaurant.id}" title="Превью фотографий">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="importPhotosForRestaurant(${restaurant.id})" 
                                            class="btn btn-success btn-sm" id="importBtn${restaurant.id}" title="Импортировать фотографии">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    container.innerHTML = html;
                    logMessage(`✅ Загружено ${response.restaurants.length} ресторанов без фотографий`, 'success');
                } else {
                    logMessage('❌ Ошибка загрузки: ' + response.message, 'error');
                }
            },
            error: function() {
                logMessage('❌ Ошибка загрузки списка ресторанов', 'error');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }

    function setPlaceIdForRestaurant(restaurantId) {
        const btn = document.getElementById(`placeIdBtn${restaurantId}`);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        logMessage(`🔍 Поиск Place ID для ресторана ID ${restaurantId}...`, 'info');
        
        $.ajax({
            url: `<?= base_url('admin/google-photos/set-place-id/') ?>${restaurantId}`,
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    logMessage(`✅ Place ID установлен для ресторана ID ${restaurantId}`, 'success');
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    btn.className = 'btn btn-success btn-sm';
                    btn.title = 'Place ID установлен';
                } else {
                    logMessage(`❌ Ошибка для ресторана ID ${restaurantId}: ${response.message}`, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-redo"></i>';
                    btn.title = 'Повторить попытку';
                }
            },
            error: function() {
                logMessage(`❌ Ошибка запроса для ресторана ID ${restaurantId}`, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo"></i>';
                btn.title = 'Повторить попытку';
            }
        });
    }

    function previewPhotos(restaurantId) {
        const btn = document.getElementById(`previewBtn${restaurantId}`);
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        $.ajax({
            url: `<?= base_url('admin/google-photos/preview-photos/') ?>${restaurantId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    logMessage(`👁️ Найдено ${response.total_photos} фотографий для ресторана ID ${restaurantId}`, 'info');
                    
                    // Показываем модальное окно с превью
                    showPhotoPreviewModal(response.previews, response.total_photos);
                } else {
                    logMessage(`❌ Превью для ресторана ID ${restaurantId}: ${response.message}`, 'error');
                    showNotification('Не удалось получить превью фотографий', 'error');
                }
            },
            error: function() {
                logMessage(`❌ Ошибка получения превью для ресторана ID ${restaurantId}`, 'error');
                showNotification('Ошибка сетевого запроса', 'error');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }

    function importPhotosForRestaurant(restaurantId) {
        const btn = document.getElementById(`importBtn${restaurantId}`);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        logMessage(`📥 Импорт фотографий для ресторана ID ${restaurantId}...`, 'info');
        
        $.ajax({
            url: `<?= base_url('admin/google-photos/import-photos/') ?>${restaurantId}`,
            method: 'POST',
            data: { max_photos: 5 },
            success: function(response) {
                if (response.success) {
                    logMessage(`✅ Импортировано ${response.imported_count} фотографий для ресторана ID ${restaurantId}`, 'success');
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    btn.className = 'btn btn-secondary btn-sm';
                    btn.title = 'Фотографии импортированы';
                    
                    showNotification(`Импортировано ${response.imported_count} фотографий`, 'success');
                } else {
                    logMessage(`❌ Ошибка импорта для ресторана ID ${restaurantId}: ${response.message}`, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-redo"></i>';
                    btn.title = 'Повторить импорт';
                    
                    showNotification('Ошибка импорта фотографий', 'error');
                }
            },
            error: function() {
                logMessage(`❌ Ошибка запроса импорта для ресторана ID ${restaurantId}`, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo"></i>';
                btn.title = 'Повторить импорт';
                
                showNotification('Ошибка сетевого запроса', 'error');
            }
        });
    }

    // Функция для показа уведомлений в стиле Bootstrap
    function showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const iconClass = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle', 
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        }[type] || 'fas fa-info-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="${iconClass} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').append(alertHtml);
        
        // Автоматическое скрытие через 5 секунд
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Функция для показа модального окна с превью фотографий
    function showPhotoPreviewModal(previews, totalPhotos) {
        let modalHtml = `
            <div class="modal fade" id="photoPreviewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-images me-2"></i>Превью фотографий
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">Найдено ${totalPhotos} фотографий в Google Places. Показаны первые ${previews.length}:</p>
                            <div class="row">
        `;
        
        previews.forEach((preview, index) => {
            modalHtml += `
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <img src="${preview.url}" class="card-img-top" style="height: 200px; object-fit: cover;" 
                             alt="Preview ${index + 1}" loading="lazy">
                        <div class="card-body p-2">
                            <small class="text-muted">${preview.width}x${preview.height}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        modalHtml += `
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Удаляем предыдущие модальные окна
        $('#photoPreviewModal').remove();
        
        // Добавляем новое модальное окно
        $('body').append(modalHtml);
        
        // Показываем модальное окно
        const modal = new bootstrap.Modal(document.getElementById('photoPreviewModal'));
        modal.show();
        
        // Удаляем модальное окно после закрытия
        document.getElementById('photoPreviewModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    // Проверка статуса API при загрузке страницы
    $(document).ready(function() {
        logMessage('🔌 Проверка подключения к Google Places API...', 'info');
        
        $.ajax({
            url: '<?= base_url('admin/google-photos/check-api-status') ?>',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    logMessage('✅ Google Places API работает корректно', 'success');
                } else {
                    logMessage('⚠️ Проблема с Google Places API: ' + response.message, 'error');
                    showNotification('Проблема с Google Places API', 'warning');
                }
            },
            error: function() {
                logMessage('⚠️ Не удалось проверить статус Google Places API', 'warning');
                showNotification('Не удалось проверить API', 'warning');
            }
        });
        
        // Инициализация подсказок для кнопок
        $('[title]').tooltip();
        
        // Автообновление времени в логах
        setInterval(function() {
            const timeElements = document.querySelectorAll('#operationLog [data-time]');
            timeElements.forEach(el => {
                const time = new Date(el.dataset.time);
                const now = new Date();
                const diff = Math.floor((now - time) / 1000);
                
                if (diff < 60) {
                    el.textContent = `${diff}с назад`;
                } else if (diff < 3600) {
                    el.textContent = `${Math.floor(diff/60)}м назад`;
                }
            });
        }, 30000); // каждые 30 секунд
    });

    // Горячие клавиши
    $(document).keydown(function(e) {
        // Ctrl+1 - Заполнить Place ID
        if (e.ctrlKey && e.which === 49) {
            e.preventDefault();
            if (!isProcessing) fillPlaceIds();
        }
        
        // Ctrl+2 - Импорт фотографий
        if (e.ctrlKey && e.which === 50) {
            e.preventDefault();
            if (!isProcessing) importPhotos();
        }
        
        // Ctrl+3 - Полное обновление
        if (e.ctrlKey && e.which === 51) {
            e.preventDefault();
            if (!isProcessing) fullUpdate();
        }
        
        // Ctrl+L - Очистить лог
        if (e.ctrlKey && e.which === 76) {
            e.preventDefault();
            clearLog();
        }
    });

    // Показываем подсказку о горячих клавишах
    setTimeout(() => {
        logMessage('💡 Горячие клавиши: Ctrl+1 (Place ID), Ctrl+2 (Фото), Ctrl+3 (Полное), Ctrl+L (Очистить лог)', 'muted');
    }, 2000);
</script>
<?= $this->endSection() ?>