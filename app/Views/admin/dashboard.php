<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dashboard - Georgian Food Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <!-- Заголовок страницы -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                Панель управления
            </h1>
            <p class="text-muted mb-0">Georgian Food Near Me - Административная панель</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Последнее обновление: <?= date('d.m.Y H:i') ?></small>
        </div>
    </div>

    <!-- Основная статистика - КРАСИВЫЕ КАРТОЧКИ -->
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
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= base_url('admin/restaurants') ?>">
                        Управление ресторанами
                    </a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-75 small text-uppercase mb-1">Активные</div>
                            <div class="h2 mb-0 font-weight-bold"><?= number_format($stats['active_restaurants']) ?></div>
                            <div class="small text-white-75">
                                <?= $stats['total_restaurants'] > 0 ? round(($stats['active_restaurants'] / $stats['total_restaurants']) * 100, 1) : 0 ?>% от общего числа
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= base_url('admin/restaurants?status=active') ?>">
                        Активные рестораны
                    </a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-info text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-75 small text-uppercase mb-1">Города</div>
                            <div class="h2 mb-0 font-weight-bold"><?= number_format($stats['total_cities']) ?></div>
                            <div class="small text-white-75">
                                С координатами: <?= $stats['cities_with_coordinates'] ?>
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-map-marker-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= base_url('admin/cities') ?>">
                        Управление городами
                    </a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning text-white shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-75 small text-uppercase mb-1">За неделю</div>
                            <div class="h2 mb-0 font-weight-bold">+<?= number_format($stats['recent_additions']) ?></div>
                            <div class="small text-white-75">Новых ресторанов</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-calendar-week fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">За последние 7 дней</span>
                    <div class="small text-white"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Дополнительная статистика данных -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                С координатами
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['restaurants_with_coordinates'] ?>
                            </div>
                            <div class="mt-2 mb-0">
                                <div class="progress">
                                    <?php $coordsPercent = $stats['total_restaurants'] > 0 ? ($stats['restaurants_with_coordinates'] / $stats['total_restaurants']) * 100 : 0; ?>
                                    <div class="progress-bar bg-success" style="width: <?= $coordsPercent ?>%"></div>
                                </div>
                                <small class="text-muted"><?= round($coordsPercent, 1) ?>% от общего числа</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-pin fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                С фотографиями
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                // Подсчитаем рестораны с фотографиями (если есть доступ к PhotoModel)
                                $restaurantsWithPhotos = 0;
                                try {
                                    $photoModel = new \App\Models\RestaurantPhotoModel();
                                    $restaurantsWithPhotos = $photoModel->select('restaurant_id')
                                        ->groupBy('restaurant_id')
                                        ->countAllResults();
                                } catch (Exception $e) {
                                    // Если модели нет, покажем 0
                                }
                                echo $restaurantsWithPhotos;
                                ?>
                            </div>
                            <div class="mt-2 mb-0">
                                <div class="progress">
                                    <?php $photosPercent = $stats['total_restaurants'] > 0 ? ($restaurantsWithPhotos / $stats['total_restaurants']) * 100 : 0; ?>
                                    <div class="progress-bar bg-info" style="width: <?= $photosPercent ?>%"></div>
                                </div>
                                <small class="text-muted"><?= round($photosPercent, 1) ?>% от общего числа</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-images fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                С Google Place ID
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                // Подсчитаем рестораны с Google Place ID
                                $restaurantsWithPlaceId = 0;
                                try {
                                    $restaurantModel = model('RestaurantModel');
                                    $restaurantsWithPlaceId = $restaurantModel
                                        ->where('google_place_id IS NOT NULL')
                                        ->where('google_place_id !=', '')
                                        ->countAllResults();
                                } catch (Exception $e) {
                                    // Если ошибка, покажем 0
                                }
                                echo $restaurantsWithPlaceId;
                                ?>
                            </div>
                            <div class="mt-2 mb-0">
                                <div class="progress">
                                    <?php $placeIdPercent = $stats['total_restaurants'] > 0 ? ($restaurantsWithPlaceId / $stats['total_restaurants']) * 100 : 0; ?>
                                    <div class="progress-bar bg-warning" style="width: <?= $placeIdPercent ?>%"></div>
                                </div>
                                <small class="text-muted"><?= round($placeIdPercent, 1) ?>% от общего числа</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fab fa-google fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Проблемы требующие внимания -->
    <?php 
    $issues = [];
    $noCoords = $stats['total_restaurants'] - $stats['restaurants_with_coordinates'];
    $noPhotos = $stats['total_restaurants'] - $restaurantsWithPhotos;
    $noPlaceId = $stats['total_restaurants'] - $restaurantsWithPlaceId;
    $inactive = $stats['total_restaurants'] - $stats['active_restaurants'];
    
    if ($noCoords > 0) $issues[] = ['type' => 'warning', 'icon' => 'map-marker', 'text' => "Без координат: $noCoords"];
    if ($noPhotos > 0) $issues[] = ['type' => 'info', 'icon' => 'images', 'text' => "Без фотографий: $noPhotos"];
    if ($noPlaceId > 0) $issues[] = ['type' => 'secondary', 'icon' => 'google', 'text' => "Без Place ID: $noPlaceId", 'brand' => true];
    if ($inactive > 0) $issues[] = ['type' => 'danger', 'icon' => 'eye-slash', 'text' => "Неактивные: $inactive"];
    ?>

    <?php if (!empty($issues)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-warning shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Требует внимания
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($issues as $issue): ?>
                            <div class="col-md-6 col-lg-3 mb-2">
                                <div class="alert alert-<?= $issue['type'] ?> mb-0 py-2">
                                    <i class="fa<?= isset($issue['brand']) ? 'b' : 's' ?> fa-<?= $issue['icon'] ?> me-2"></i>
                                    <?= $issue['text'] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Последние рестораны и быстрые действия -->
    <div class="row">
        <!-- Последние рестораны -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Последние рестораны
                    </h5>
                    <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list me-1"></i>Все рестораны
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recent_restaurants)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Название</th>
                                        <th>Город</th>
                                        <th>Статус</th>
                                        <th>Данные</th>
                                        <th>Добавлен</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recent_restaurants, 0, 8) as $restaurant): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($restaurant['name']) ?></strong>
                                                <?php if (!empty($restaurant['address'])): ?>
                                                    <br><small class="text-muted"><?= esc(substr($restaurant['address'], 0, 40)) ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($restaurant['city_name'] ?? 'Не указан') ?></td>
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
                                                <div class="small d-flex gap-1">
                                                    <?php if ($restaurant['latitude'] && $restaurant['longitude']): ?>
                                                        <i class="fas fa-map-marker-alt text-success" title="Есть координаты"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-map-marker-alt text-muted" title="Нет координат"></i>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($restaurant['google_place_id']): ?>
                                                        <i class="fab fa-google text-success" title="Есть Place ID"></i>
                                                    <?php else: ?>
                                                        <i class="fab fa-google text-muted" title="Нет Place ID"></i>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($restaurant['website']): ?>
                                                        <i class="fas fa-globe text-info" title="Есть сайт"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d.m.Y', strtotime($restaurant['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= base_url('admin/restaurants/edit/' . $restaurant['id']) ?>" 
                                                       class="btn btn-outline-primary btn-sm" title="Редактировать">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if (!empty($restaurant['seo_url'])): ?>
                                                        <a href="<?= base_url($restaurant['seo_url']) ?>" target="_blank"
                                                           class="btn btn-outline-info btn-sm" title="Просмотр">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Нет недавно добавленных ресторанов</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Быстрые действия и SEO -->
        <div class="col-lg-4">
            <!-- Быстрые действия -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Быстрые действия
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Основные разделы -->
                        <div class="col-md-2 mb-3">
                            <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-utensils mb-2"></i>
                                <br>Рестораны
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="<?= base_url('admin/cities') ?>" class="btn btn-info btn-block">
                                <i class="fas fa-city mb-2"></i>
                                <br>Города
                            </a>
                        </div>
                        
                        <!-- DataForSEO Import - выделяем как важный -->
                        <div class="col-md-2 mb-3">
                            <a href="<?= base_url('admin/dataforseo-import') ?>" class="btn btn-warning btn-block position-relative">
                                <i class="fas fa-download mb-2"></i>
                                <br>DataForSEO
                                <br><small>Import</small>
                                <?php 
                                // Показываем уведомление если есть рестораны без Place ID
                                try {
                                    $restaurantModel = model('RestaurantModel');
                                    $withoutPlaceId = $restaurantModel->where('google_place_id IS NULL OR google_place_id =', '')->countAllResults();
                                    if ($withoutPlaceId > 0): 
                                ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $withoutPlaceId > 99 ? '99+' : $withoutPlaceId ?>
                                        <span class="visually-hidden">ресторанов без Place ID</span>
                                    </span>
                                <?php endif; } catch (Exception $e) { /* ignore */ } ?>
                            </a>
                        </div>
                        
                        <!-- Другие инструменты -->
                        <div class="col-md-2 mb-3">
                            <a href="<?= base_url('admin/google-photos') ?>" class="btn btn-success btn-block">
                                <i class="fab fa-google mb-2"></i>
                                <br>Google Photos
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="<?= base_url('admin/geocode') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-map-marker-alt mb-2"></i>
                                <br>Геокодирование
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="<?= base_url('admin/sitemap') ?>" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-sitemap mb-2"></i>
                                <br>Sitemap
                            </a>
                        </div>
                    </div>
                    
                    <!-- Дополнительная строка с менее важными действиями -->
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="<?= base_url('admin/export/csv') ?>" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-file-export me-1"></i>Экспорт данных
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= base_url('admin/restaurants/import-csv') ?>" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-file-csv me-1"></i>Import CSV
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= base_url('map') ?>" target="_blank" class="btn btn-outline-info btn-sm w-100">
                                <i class="fas fa-map me-1"></i>Карта сайта
                                <i class="fas fa-external-link-alt ms-1 small"></i>
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= base_url('import-test') ?>" target="_blank" class="btn btn-outline-success btn-sm w-100">
                                <i class="fas fa-flask me-1"></i>Тестирование
                                <i class="fas fa-external-link-alt ms-1 small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Status -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-search me-2"></i>SEO Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                            <span>
                                <?php 
                                $sitemapExists = file_exists(FCPATH . '../writable/uploads/sitemap.xml');
                                ?>
                                <i class="fas fa-sitemap me-2 text-<?= $sitemapExists ? 'success' : 'danger' ?>"></i>
                                Sitemap.xml
                            </span>
                            <span class="badge bg-<?= $sitemapExists ? 'success' : 'danger' ?>">
                                <?= $sitemapExists ? 'Есть' : 'Нет' ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                            <span>
                                <?php 
                                $robotsExists = file_exists(FCPATH . 'robots.txt');
                                ?>
                                <i class="fas fa-robot me-2 text-<?= $robotsExists ? 'success' : 'danger' ?>"></i>
                                Robots.txt
                            </span>
                            <span class="badge bg-<?= $robotsExists ? 'success' : 'danger' ?>">
                                <?= $robotsExists ? 'Есть' : 'Нет' ?>
                            </span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <?php if ($sitemapExists): ?>
                            <a href="<?= base_url('sitemap.xml') ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>Просмотр Sitemap
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('map') ?>" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-map me-1"></i>Просмотр карты
                        </a>
                        <a href="<?= base_url() ?>" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-globe me-1"></i>Перейти на сайт
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-xs {
    font-size: 0.7rem;
}

.progress {
    height: 8px;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.75) !important;
}

.text-white-50 {
    color: rgba(255, 255, 255, 0.5) !important;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.stretched-link::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
    content: "";
}
</style>
<?= $this->endSection() ?>