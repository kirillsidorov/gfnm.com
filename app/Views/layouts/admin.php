<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Админские стили -->
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
    <style>
        <?= $this->renderSection('styles') ?>
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar p-0">
                <div class="position-sticky pt-3">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <h5 class="brand-logo">
                            <i class="fas fa-utensils me-2"></i>
                            Georgian Food Admin
                        </h5>
                    </div>

                    <!-- Navigation -->
                    <!-- Navigation -->
                    <ul class="nav flex-column px-3">
                        <li class="nav-item">
                            <a class="nav-link <?= (current_url() == base_url('admin') || current_url() == base_url('admin/dashboard')) ? 'active' : '' ?>" 
                               href="<?= base_url('admin/dashboard') ?>">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Дашборд
                            </a>
                        </li>
                        
                        <hr class="text-white-50 mx-2">
                        
                        <!-- ОСНОВНЫЕ РАЗДЕЛЫ -->
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos(current_url(), 'admin/restaurants') !== false) ? 'active' : '' ?>" 
                               href="<?= base_url('admin/restaurants') ?>">
                                <i class="fas fa-utensils me-2"></i>
                                Рестораны
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos(current_url(), 'admin/cities') !== false) ? 'active' : '' ?>" 
                               href="<?= base_url('admin/cities') ?>">
                                <i class="fas fa-city me-2"></i>
                                Города
                            </a>
                        </li>
                        
                        <hr class="text-white-50 mx-2">
                        
                        <!-- ИНСТРУМЕНТЫ ИМПОРТА И ДАННЫХ -->
                        <div class="text-white-50 small text-uppercase px-3 py-2 fw-bold">
                            Импорт и данные
                        </div>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos(current_url(), 'admin/dataforseo-import') !== false) ? 'active' : '' ?>" 
                               href="<?= base_url('admin/dataforseo-import') ?>">
                                <i class="fas fa-download me-2 text-warning"></i>
                                <span>DataForSEO Import</span>
                                <?php 
                                // Показываем бейдж если есть рестораны без Place ID
                                try {
                                    $restaurantModel = model('RestaurantModel');
                                    $withoutPlaceId = $restaurantModel->where('google_place_id IS NULL OR google_place_id =', '')->countAllResults();
                                    if ($withoutPlaceId > 0): 
                                ?>
                                    <span class="badge bg-warning rounded-pill ms-1 small"><?= $withoutPlaceId > 99 ? '99+' : $withoutPlaceId ?></span>
                                <?php endif; } catch (Exception $e) { /* ignore */ } ?>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos(current_url(), 'admin/google-photos') !== false) ? 'active' : '' ?>" 
                               href="<?= base_url('admin/google-photos') ?>">
                                <i class="fab fa-google me-2 text-info"></i>
                                Google Photos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos(current_url(), 'admin/geocode') !== false) ? 'active' : '' ?>" 
                               href="<?= base_url('admin/geocode') ?>">
                                <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                Геокодирование
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin/restaurants/import-csv') ?>">
                                <i class="fas fa-file-csv me-2 text-secondary"></i>
                                Import CSV
                            </a>
                        </li>
                        
                        <hr class="text-white-50 mx-2">
                        
                        <!-- СИСТЕМНЫЕ ИНСТРУМЕНТЫ -->
                        <div class="text-white-50 small text-uppercase px-3 py-2 fw-bold">
                            Система
                        </div>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= (strpos(current_url(), 'admin/sitemap') !== false) ? 'active' : '' ?>" 
                               href="<?= base_url('admin/sitemap') ?>">
                                <i class="fas fa-sitemap me-2"></i>
                                Sitemap
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin/export/csv') ?>">
                                <i class="fas fa-file-export me-2"></i>
                                Экспорт данных
                            </a>
                        </li>
                        
                        <hr class="text-white-50 mx-2">
                        
                        <!-- ПРОСМОТР САЙТА -->
                        <div class="text-white-50 small text-uppercase px-3 py-2 fw-bold">
                            Просмотр
                        </div>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url() ?>" target="_blank">
                                <i class="fas fa-globe me-2"></i>
                                Главная сайта
                                <i class="fas fa-external-link-alt ms-1 small"></i>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('map') ?>" target="_blank">
                                <i class="fas fa-map me-2"></i>
                                Карта ресторанов
                                <i class="fas fa-external-link-alt ms-1 small"></i>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('import-test') ?>" target="_blank">
                                <i class="fas fa-flask me-2 text-info"></i>
                                Тестовая страница
                                <i class="fas fa-external-link-alt ms-1 small"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin/restaurants/updater') ?>" target="_blank">
                                <i class="fas fa-flask me-2 text-info"></i>
                                Google Place API Update
                                <i class="fas fa-external-link-alt ms-1 small"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin/clear-cache') ?>">
                                <i class="fas fa-memory text-warning me-2"></i>
                                Cache Management
                            </a>
                        </li>
                        <hr class="text-white-50 mx-2">
                        
                        <!-- ВЫХОД -->
                        <li class="nav-item mt-auto">
                            <a class="nav-link text-danger" href="<?= base_url('admin/logout') ?>" 
                               onclick="return confirm('Вы уверены, что хотите выйти?')">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Выход
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-wrapper">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $this->renderSection('page_title') ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            <?= date('d.m.Y H:i') ?>
                        </small>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Page Content -->
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>