<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-tachometer-alt me-2"></i>Дашборд
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Statistics Cards -->
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
                        <h4 class="mb-0"><?= number_format($stats['active_restaurants']) ?></h4>
                        <p class="mb-0">Активных</p>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-check-circle fa-2x"></i>
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
                        <h4 class="mb-0"><?= number_format($stats['total_cities']) ?></h4>
                        <p class="mb-0">Городов</p>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-city fa-2x"></i>
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
                        <h4 class="mb-0"><?= number_format($stats['recent_additions']) ?></h4>
                        <p class="mb-0">За неделю</p>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-calendar-week fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SEO & Technical Status -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sitemap me-2"></i>SEO Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <?php 
                                $sitemapExists = file_exists(FCPATH . '../writable/uploads/sitemap.xml');
                                $iconClass = $sitemapExists ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
                                ?>
                                <i class="<?= $iconClass ?> fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Sitemap.xml</h6>
                                <small class="text-muted">
                                    <?php if ($sitemapExists): ?>
                                        <?= date('d.m.Y H:i', filemtime(FCPATH . '../writable/uploads/sitemap.xml')) ?>
                                    <?php else: ?>
                                        Не создан
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <?php 
                                $robotsExists = file_exists(FCPATH . 'robots.txt');
                                $iconClass = $robotsExists ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
                                ?>
                                <i class="<?= $iconClass ?> fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Robots.txt</h6>
                                <small class="text-muted">
                                    <?= $robotsExists ? 'Создан' : 'Не создан' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-grid gap-2 d-md-flex">
                    <a href="<?= base_url('admin/sitemap') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-cogs me-1"></i>Управление sitemap
                    </a>
                    <?php if ($sitemapExists): ?>
                        <a href="<?= base_url('sitemap.xml') ?>" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i>Просмотр
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Геокодирование
                </h5>
            </div>
            <div class="card-body">
                <?php
                $restaurantsWithCoords = $stats['restaurants_with_coordinates'] ?? 0;
                $citiesWithCoords = $stats['cities_with_coordinates'] ?? 0;
                $geoPercentage = $stats['total_restaurants'] > 0 ? 
                    round(($restaurantsWithCoords / $stats['total_restaurants']) * 100, 1) : 0;
                ?>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary mb-1"><?= $restaurantsWithCoords ?></h4>
                            <small class="text-muted">Ресторанов с координатами</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success mb-1"><?= $citiesWithCoords ?></h4>
                            <small class="text-muted">Городов с координатами</small>
                        </div>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" 
                         style="width: <?= $geoPercentage ?>%" 
                         aria-valuenow="<?= $geoPercentage ?>" 
                         aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted"><?= $geoPercentage ?>% геокодировано</small>
                </div>
                <hr>
                <div class="d-grid">
                    <a href="<?= base_url('admin/geocode') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-map-marker-alt me-1"></i>Управление координатами
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-bolt me-2"></i>Быстрые действия
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-2">
                <div class="d-grid">
                    <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-utensils d-block mb-1"></i>
                        <small>Рестораны</small>
                    </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="d-grid">
                    <a href="<?= base_url('admin/cities') ?>" class="btn btn-outline-info">
                        <i class="fas fa-city d-block mb-1"></i>
                        <small>Города</small>
                    </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="d-grid">
                    <a href="<?= base_url('admin/sitemap') ?>" class="btn btn-outline-success">
                        <i class="fas fa-sitemap d-block mb-1"></i>
                        <small>Sitemap</small>
                    </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="d-grid">
                    <a href="<?= base_url('admin/geocode') ?>" class="btn btn-outline-warning">
                        <i class="fas fa-map-marker-alt d-block mb-1"></i>
                        <small>Координаты</small>
                    </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="d-grid">
                    <a href="<?= base_url('map') ?>" target="_blank" class="btn btn-outline-secondary">
                        <i class="fas fa-map d-block mb-1"></i>
                        <small>Карта</small>
                    </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="d-grid">
                    <a href="<?= base_url() ?>" target="_blank" class="btn btn-outline-dark">
                        <i class="fas fa-external-link-alt d-block mb-1"></i>
                        <small>Сайт</small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Restaurants -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-search me-2"></i>
            Поиск и добавление ресторанов
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('admin/search') ?>">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="city_name" 
                           placeholder="Введите название города (например: Нью-Йорк, Чикаго, Лос-Анджелес)" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Найти грузинские рестораны
                    </button>
                </div>
            </div>
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Поиск производится через Google Places API
            </small>
        </form>
    </div>
</div>

<!-- System Health & Notifications -->
<div class="row mb-4">
    <div class="col-md-8">
        <!-- Recent Restaurants -->
        <div class="card">
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
                                    <th>Рейтинг</th>
                                    <th>Статус</th>
                                    <th>Добавлен</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_restaurants as $restaurant): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($restaurant['name']) ?></strong>
                                            <?php if (!empty($restaurant['address'])): ?>
                                                <br><small class="text-muted"><?= esc(substr($restaurant['address'], 0, 50)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($restaurant['city_name'] ?? 'Неизвестно') ?></td>
                                        <td>
                                            <?php if (!empty($restaurant['rating'])): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <?= number_format($restaurant['rating'], 1) ?> ⭐
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($restaurant['is_active']): ?>
                                                <span class="badge bg-success">Активен</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Неактивен</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= !empty($restaurant['created_at']) ? date('d.m.Y', strtotime($restaurant['created_at'])) : '-' ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('admin/restaurants/edit/' . $restaurant['id']) ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                        <h5>Ресторанов пока нет</h5>
                        <p class="text-muted">Начните с поиска ресторанов в каком-нибудь городе!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- System Alerts -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-bell me-2"></i>Уведомления
                </h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php
                    $alerts = [];
                    
                    // Проверяем различные условия
                    if (($stats['total_restaurants'] - $restaurantsWithCoords) > 0) {
                        $missingCoords = $stats['total_restaurants'] - $restaurantsWithCoords;
                        $alerts[] = [
                            'type' => 'warning',
                            'icon' => 'fas fa-map-marker-alt',
                            'message' => "{$missingCoords} ресторанов без координат",
                            'action' => 'admin/geocode/restaurants'
                        ];
                    }
                    
                    if (!$sitemapExists) {
                        $alerts[] = [
                            'type' => 'info',
                            'icon' => 'fas fa-sitemap',
                            'message' => 'Sitemap не создан',
                            'action' => 'admin/sitemap'
                        ];
                    } elseif (filemtime(FCPATH . '../writable/uploads/sitemap.xml') < strtotime('-7 days')) {
                        $alerts[] = [
                            'type' => 'warning',
                            'icon' => 'fas fa-clock',
                            'message' => 'Sitemap устарел (>7 дней)',
                            'action' => 'admin/sitemap'
                        ];
                    }
                    
                    if ($stats['total_restaurants'] > 0 && $stats['active_restaurants'] < $stats['total_restaurants']) {
                        $inactive = $stats['total_restaurants'] - $stats['active_restaurants'];
                        $alerts[] = [
                            'type' => 'secondary',
                            'icon' => 'fas fa-eye-slash',
                            'message' => "{$inactive} неактивных ресторанов",
                            'action' => 'admin/restaurants?status=inactive'
                        ];
                    }
                    ?>
                    
                    <?php if (empty($alerts)): ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                            <p class="text-muted mb-0">Все в порядке!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($alerts as $alert): ?>
                            <div class="list-group-item list-group-item-action border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="<?= $alert['icon'] ?> text-<?= $alert['type'] ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small><?= $alert['message'] ?></small>
                                    </div>
                                    <div>
                                        <a href="<?= base_url($alert['action']) ?>" class="btn btn-sm btn-outline-<?= $alert['type'] ?>">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Быстрая статистика
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h5 class="text-primary mb-0"><?= number_format(($stats['active_restaurants'] / max($stats['total_restaurants'], 1)) * 100, 1) ?>%</h5>
                            <small class="text-muted">Активных</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h5 class="text-success mb-0"><?= $geoPercentage ?>%</h5>
                            <small class="text-muted">С координатами</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h5 class="text-info mb-0"><?= $stats['total_restaurants'] > 0 ? number_format($stats['total_restaurants'] / $stats['total_cities'], 1) : 0 ?></h5>
                            <small class="text-muted">Ресторанов/город</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h5 class="text-warning mb-0"><?= $stats['recent_additions'] ?></h5>
                            <small class="text-muted">За неделю</small>
                        </div>
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

.list-group-item {
    border: none !important;
    padding: 0.5rem 0;
}

.border {
    border-color: #e9ecef !important;
}

.progress {
    border-radius: 10px;
}

.btn {
    border-radius: 8px;
}

.quick-action-card {
    transition: all 0.2s ease;
    cursor: pointer;
}

.quick-action-card:hover {
    background-color: var(--bs-light);
    transform: translateY(-2px);
}
<?= $this->endSection() ?>