<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>DataForSEO Import - Georgian Food Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-download me-2 text-primary"></i>
                DataForSEO Import Center
            </h1>
            <p class="text-muted mb-0">Импорт и обновление данных ресторанов из DataForSEO API</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-info" onclick="checkApiStatus()">
                <i class="fas fa-plug me-1"></i>Проверить API
            </button>
            <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>К ресторанам
            </a>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-primary text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-75 small text-uppercase mb-1">Всего ресторанов</div>
                            <div class="h2 mb-0 font-weight-bold"><?= number_format($stats['total_restaurants']) ?></div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-utensils fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-75 small text-uppercase mb-1">С Place ID</div>
                            <div class="h2 mb-0 font-weight-bold"><?= number_format($stats['with_place_id']) ?></div>
                            <div class="small text-white-75">
                                <?= $stats['total_restaurants'] > 0 ? round(($stats['with_place_id'] / $stats['total_restaurants']) * 100, 1) : 0 ?>% готовы к импорту
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="fab fa-google fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-75 small text-uppercase mb-1">Без Place ID</div>
                            <div class="h2 mb-0 font-weight-bold"><?= number_format($stats['without_place_id']) ?></div>
                            <div class="small text-white-75">Требуют настройки</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-info text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-75 small text-uppercase mb-1">Последний импорт</div>
                            <div class="h5 mb-0 font-weight-bold">
                                <?php if ($stats['last_import']): ?>
                                    <?= date('d.m.Y', strtotime($stats['last_import'])) ?>
                                    <div class="small text-white-75">
                                        <?= date('H:i', strtotime($stats['last_import'])) ?>
                                    </div>
                                <?php else: ?>
                                    Никогда
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Главные операции -->
    <div class="row mb-4">
        <!-- Массовое обновление -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success bg-opacity-10">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-sync me-2"></i>Массовое обновление
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Обновить данные всех ресторанов, у которых есть Google Place ID</p>
                    
                    <form id="bulkUpdateForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="bulk_limit" class="form-label">Лимит обновлений</label>
                                <select class="form-select" id="bulk_limit" name="limit">
                                    <option value="10">10 ресторанов</option>
                                    <option value="25" selected>25 ресторанов</option>
                                    <option value="50">50 ресторанов</option>
                                    <option value="100">100 ресторанов</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Фильтр</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="only_outdated" name="only_outdated" checked>
                                    <label class="form-check-label" for="only_outdated">
                                        Только устаревшие (старше 30 дней)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Внимание:</strong> Массовое обновление может занять время и потратить кредиты API.
                        </div>

                        <button type="submit" class="btn btn-success" id="bulkUpdateBtn">
                            <i class="fas fa-sync me-1"></i>Начать массовое обновление
                        </button>
                    </form>

                    <div id="bulkUpdateResult" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>

        <!-- Поиск и импорт новых -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary bg-opacity-10">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-search me-2"></i>Поиск и импорт новых
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Найти и импортировать новые грузинские рестораны в выбранном городе</p>
                    
                    <form id="searchImportForm">
                        <div class="mb-3">
                            <label for="city_id" class="form-label">Город для поиска</label>
                            <select class="form-select" id="city_id" name="city_id" required>
                                <option value="">Выберите город</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= $city['id'] ?>" 
                                            data-lat="<?= $city['latitude'] ?>" 
                                            data-lng="<?= $city['longitude'] ?>">
                                        <?= esc($city['name']) ?>
                                        <?php if ($city['state']): ?>
                                            , <?= esc($city['state']) ?>
                                        <?php endif; ?>
                                        <?php if (!$city['latitude'] || !$city['longitude']): ?>
                                            <em>(нет координат)</em>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="search_query" class="form-label">Поисковый запрос</label>
                                <input type="text" class="form-control" id="search_query" name="search_query" 
                                       value="georgian restaurant" placeholder="georgian restaurant">
                            </div>
                            <div class="col-md-4">
                                <label for="search_limit" class="form-label">Лимит результатов</label>
                                <select class="form-select" id="search_limit" name="limit">
                                    <option value="10">10 результатов</option>
                                    <option value="20" selected>20 результатов</option>
                                    <option value="30">30 результатов</option>
                                    <option value="50">50 результатов</option>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Совет:</strong> Выберите город с координатами для лучших результатов поиска.
                        </div>

                        <button type="submit" class="btn btn-primary" id="searchImportBtn">
                            <i class="fas fa-search me-1"></i>Найти и импортировать
                        </button>
                    </form>

                    <div id="searchImportResult" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статус API и недавние операции -->
    <div class="row">
        <!-- Статус API -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plug me-2"></i>Статус API
                    </h6>
                </div>
                <div class="card-body">
                    <div id="apiStatus" class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Проверка...</span>
                        </div>
                        <div class="mt-2">Проверка подключения...</div>
                    </div>
                    
                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-outline-primary" onclick="checkApiStatus()">
                            <i class="fas fa-sync me-1"></i>Обновить статус
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Недавние операции -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Недавние операции
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_imports)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <thead>
                                    <tr>
                                        <th>Тип</th>
                                        <th>Результат</th>
                                        <th>Время</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_imports as $import): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $typeIcons = [
                                                    'single_restaurant' => 'fa-utensils',
                                                    'bulk_update' => 'fa-sync',
                                                    'search_and_import' => 'fa-search'
                                                ];
                                                $icon = $typeIcons[$import['import_type']] ?? 'fa-download';
                                                ?>
                                                <i class="fas <?= $icon ?> me-1"></i>
                                                <?= ucfirst(str_replace('_', ' ', $import['import_type'])) ?>
                                            </td>
                                            <td>
                                                <?php $result = json_decode($import['result_data'], true); ?>
                                                <small class="text-muted">
                                                    <?php if (isset($result['processed'])): ?>
                                                        Обработано: <?= $result['processed'] ?>
                                                    <?php elseif (isset($result['imported'])): ?>
                                                        Импортировано: <?= $result['imported'] ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d.m.Y H:i', strtotime($import['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($import['success']): ?>
                                                    <span class="badge bg-success">Успех</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Ошибка</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-2x text-muted mb-3"></i>
                            <p class="text-muted">Пока нет операций импорта</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Дополнительные инструменты -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools me-2"></i>Дополнительные инструменты
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="<?= base_url('admin/restaurants?data_filter=no_place_id') ?>" class="btn btn-outline-warning">
                                    <i class="fas fa-search mb-1"></i><br>
                                    <small>Рестораны без Place ID</small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="<?= base_url('admin/restaurants?data_filter=no_coords') ?>" class="btn btn-outline-info">
                                    <i class="fas fa-map-marker-alt mb-1"></i><br>
                                    <small>Без координат</small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-secondary" onclick="exportImportLog()">
                                    <i class="fas fa-file-export mb-1"></i><br>
                                    <small>Экспорт логов</small>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="<?= base_url('import-test') ?>" target="_blank" class="btn btn-outline-success">
                                    <i class="fas fa-flask mb-1"></i><br>
                                    <small>Тестовая страница</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Автоматическая проверка статуса API при загрузке
    checkApiStatus();
    
    // Обработчик массового обновления
    $('#bulkUpdateForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = $('#bulkUpdateBtn');
        const originalText = btn.html();
        
        if (!confirm('Начать массовое обновление? Это может занять время и потратить кредиты API.')) {
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Обновление...');
        $('#bulkUpdateResult').hide();
        
        $.ajax({
            url: '<?= base_url('admin/dataforseo-import/bulk-update') ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showImportResult('bulkUpdateResult', response, 'success');
                } else {
                    showImportResult('bulkUpdateResult', response, 'danger');
                }
            },
            error: function() {
                showImportResult('bulkUpdateResult', {message: 'Ошибка выполнения запроса'}, 'danger');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Обработчик поиска и импорта
    $('#searchImportForm').on('submit', function(e) {
        e.preventDefault();
        
        const citySelect = $('#city_id');
        const selectedOption = citySelect.find('option:selected');
        
        if (!citySelect.val()) {
            alert('Выберите город для поиска');
            return;
        }
        
        const lat = selectedOption.data('lat');
        const lng = selectedOption.data('lng');
        
        if (!lat || !lng) {
            if (!confirm('У выбранного города нет координат. Поиск может быть неточным. Продолжить?')) {
                return;
            }
        }
        
        const formData = new FormData(this);
        const btn = $('#searchImportBtn');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Поиск...');
        $('#searchImportResult').hide();
        
        $.ajax({
            url: '<?= base_url('admin/dataforseo-import/search-and-import') ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showImportResult('searchImportResult', response, 'success');
                } else {
                    showImportResult('searchImportResult', response, 'danger');
                }
            },
            error: function() {
                showImportResult('searchImportResult', {message: 'Ошибка выполнения запроса'}, 'danger');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});

// Проверка статуса API
function checkApiStatus() {
    $('#apiStatus').html(`
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Проверка...</span>
        </div>
        <div class="mt-2">Проверка подключения...</div>
    `);
    
    $.ajax({
        url: '<?= base_url('admin/dataforseo-import/check-api-status') ?>',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#apiStatus').html(`
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <div><strong>API подключен</strong></div>
                        <small class="text-muted">Доступно локаций: ${response.available_locations || 'N/A'}</small>
                    </div>
                `);
            } else {
                $('#apiStatus').html(`
                    <div class="text-danger">
                        <i class="fas fa-times-circle fa-2x mb-2"></i>
                        <div><strong>Ошибка API</strong></div>
                        <small class="text-muted">${response.message}</small>
                    </div>
                `);
            }
        },
        error: function() {
            $('#apiStatus').html(`
                <div class="text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <div><strong>Ошибка соединения</strong></div>
                    <small class="text-muted">Не удается проверить статус API</small>
                </div>
            `);
        }
    });
}

// Отображение результатов импорта
function showImportResult(elementId, response, type) {
    const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
    let content = `
        <div class="alert alert-${type} alert-dismissible fade show">
            <i class="fas fa-${icon} me-2"></i>
            <strong>${response.message}</strong>
    `;
    
    if (response.results) {
        const results = response.results;
        content += `
            <hr>
            <div class="small">
                <div class="row">
        `;
        
        if (results.found !== undefined) {
            content += `<div class="col-sm-3"><strong>Найдено:</strong> ${results.found}</div>`;
        }
        if (results.processed !== undefined) {
            content += `<div class="col-sm-3"><strong>Обработано:</strong> ${results.processed}</div>`;
        }
        if (results.imported !== undefined) {
            content += `<div class="col-sm-3"><strong>Импортировано:</strong> ${results.imported}</div>`;
        }
        if (results.updated !== undefined) {
            content += `<div class="col-sm-3"><strong>Обновлено:</strong> ${results.updated}</div>`;
        }
        if (results.errors !== undefined && results.errors > 0) {
            content += `<div class="col-sm-3"><strong class="text-danger">Ошибок:</strong> ${results.errors}</div>`;
        }
        
        content += `</div>`;
        
        if (results.error_details && results.error_details.length > 0) {
            content += `
                <div class="mt-2">
                    <strong>Детали ошибок:</strong>
                    <ul class="mb-0">
            `;
            results.error_details.slice(0, 5).forEach(error => {
                content += `<li>${error}</li>`;
            });
            if (results.error_details.length > 5) {
                content += `<li><em>И еще ${results.error_details.length - 5} ошибок...</em></li>`;
            }
            content += `</ul></div>`;
        }
        
        content += `</div>`;
    }
    
    content += `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $(`#${elementId}`).html(content).show();
}

// Экспорт логов импорта
function exportImportLog() {
    window.open('<?= base_url('admin/dataforseo-import/export-logs') ?>', '_blank');
}

// Обновление одного ресторана (вызывается из формы редактирования)
function updateSingleRestaurant(restaurantId) {
    if (!confirm('Обновить данные этого ресторана из DataForSEO?')) {
        return;
    }
    
    $.ajax({
        url: `<?= base_url('admin/dataforseo-import/update-restaurant/') ?>${restaurantId}`,
        method: 'POST',
        dataType: 'json',
        beforeSend: function() {
            showAlert('info', 'Обновление данных ресторана...');
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                // Перезагружаем страницу через 2 секунды
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'Ошибка обновления данных ресторана');
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