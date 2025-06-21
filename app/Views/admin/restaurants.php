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
            </ul>
        </div>
    </div>

    <!-- Фильтры поиска -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Фильтры поиска
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" id="filterForm" class="row g-3">
                <!-- Поиск по тексту -->
                <div class="col-md-4">
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

                <!-- Статус активности -->
                <div class="col-md-2">
                    <label for="status" class="form-label">Статус</label>
                    <select class="form-select" name="status" id="status">
                        <option value="">Все</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>
                            Активные
                        </option>
                        <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                            Неактивные
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
                <div class="col-md-2 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>

            <!-- Быстрые фильтры -->
            <?php if (!empty(array_filter($filters))): ?>
                <div class="mt-3">
                    <span class="text-muted me-2">Активные фильтры:</span>
                    <?php foreach ($filters as $key => $value): ?>
                        <?php if (!empty($value)): ?>
                            <span class="badge bg-primary me-1">
                                <?= ucfirst(str_replace('_', ' ', $key)) ?>: <?= esc($value) ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="fas fa-times me-1"></i>Сбросить все
                    </a>
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
                    Список ресторанов 
                    <?php if (isset($total_restaurants)): ?>
                        (<?= count($restaurants) ?> из <?= $total_restaurants ?>)
                    <?php else: ?>
                        (<?= count($restaurants) ?>)
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
                                <th>Рейтинг</th>
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
                                        <?php if (!empty($restaurant['rating'])): ?>
                                            <?php 
                                            $rating = floatval($restaurant['rating']);
                                            $badge_class = $rating >= 4.5 ? 'success' : ($rating >= 4.0 ? 'warning' : 'secondary');
                                            ?>
                                            <span class="badge bg-<?= $badge_class ?>">
                                                <i class="fas fa-star"></i> <?= number_format($rating, 1) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
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

                                            <!-- Проверка фотографий (если есть доступ к модели) -->
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

                <!-- Пагинация (если есть) -->
                <?php if (isset($pager)): ?>
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
    $('#city, #status, #data_filter').on('change', function() {
        $('#filterForm').submit();
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
});

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
            ids: selectedIds
        },
        dataType: 'json',
        beforeSend: function() {
            $('#bulkActions button').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message || 'Операция выполнена успешно');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', response.message || 'Произошла ошибка');
            }
        },
        error: function() {
            showAlert('danger', 'Ошибка выполнения запроса');
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
        dataType: 'json',
        beforeSend: function() {
            showAlert('info', 'Импорт фотографий...');
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
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
                     type === 'danger' ? 'exclamation-circle' : 'info-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('body').append(alert);
    
    // Автоматическое скрытие через 5 секунд
    setTimeout(function() {
        $('.alert').last().fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}
</script>
<?= $this->endSection() ?>