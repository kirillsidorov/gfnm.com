<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-map-marker-alt me-2"></i>Статус геокодирования
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-city fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0"><?= count($cities) ?></h5>
                        <p class="card-text mb-0">Всего городов</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0"><?= $citiesWithCoordinates ?></h5>
                        <p class="card-text mb-0">Городов с координатами</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-utensils fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0"><?= $totalRestaurants ?></h5>
                        <p class="card-text mb-0">Всего ресторанов</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0"><?= $restaurantsWithoutCoordinates ?></h5>
                        <p class="card-text mb-0">Без координат</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tools me-2"></i>Действия геокодирования
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="<?= base_url('admin/geocode/cities') ?>" class="btn btn-primary w-100">
                            <i class="fas fa-city me-2"></i>
                            Обновить координаты городов
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= base_url('admin/geocode/restaurants') ?>" class="btn btn-success w-100">
                            <i class="fas fa-utensils me-2"></i>
                            Обновить координаты ресторанов
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= base_url('admin/geocode/test') ?>" class="btn btn-info w-100">
                            <i class="fas fa-vial me-2"></i>
                            Тестировать геокодирование
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cities Status -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-city me-2"></i>Статус городов
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($cities)): ?>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Город</th>
                            <th>Штат</th>
                            <th>Широта</th>
                            <th>Долгота</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cities as $city): ?>
                            <tr>
                                <td><strong><?= esc($city['name']) ?></strong></td>
                                <td><?= esc($city['state']) ?></td>
                                <td>
                                    <?php if ($city['latitude']): ?>
                                        <?= number_format($city['latitude'], 6) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($city['longitude']): ?>
                                        <?= number_format($city['longitude'], 6) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($city['latitude'] && $city['longitude']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Есть координаты
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Нет координат
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-city fa-3x text-muted mb-3"></i>
                <h5>Города не найдены</h5>
                <p class="text-muted">Добавьте города в систему</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Restaurant Progress -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-utensils me-2"></i>Прогресс ресторанов
        </h5>
    </div>
    <div class="card-body">
        <?php 
        $percentage = $totalRestaurants > 0 ? round(($restaurantsWithCoordinates / $totalRestaurants) * 100, 1) : 0;
        ?>
        
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="mb-2">
                    <strong><?= $restaurantsWithCoordinates ?></strong> из <strong><?= $totalRestaurants ?></strong> 
                    ресторанов имеют координаты
                </p>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar <?= $percentage == 100 ? 'bg-success' : 'bg-primary' ?>" 
                         role="progressbar" 
                         style="width: <?= $percentage ?>%"
                         aria-valuenow="<?= $percentage ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        <?= $percentage ?>%
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <?php if ($restaurantsWithoutCoordinates > 0): ?>
                    <a href="<?= base_url('admin/geocode/restaurants') ?>" class="btn btn-warning">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Обновить <?= $restaurantsWithoutCoordinates ?> ресторанов
                    </a>
                <?php else: ?>
                    <span class="badge bg-success fs-6 p-2">
                        <i class="fas fa-check me-1"></i>Все рестораны геокодированы
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($percentage < 100): ?>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Совет:</strong> Геокодирование поможет пользователям найти рестораны на карте и получить точные направления.
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>