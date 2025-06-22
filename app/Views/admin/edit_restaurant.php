<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Заголовок страницы -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2 text-primary"></i>
                <?= isset($restaurant) ? 'Редактировать ресторан' : 'Добавить ресторан' ?>
            </h1>
            <?php if (isset($restaurant)): ?>
                <p class="text-muted mb-0">
                    ID: <?= $restaurant['id'] ?> | 
                    Создан: <?= date('d.m.Y H:i', strtotime($restaurant['created_at'])) ?>
                    <?php if ($restaurant['updated_at'] !== $restaurant['created_at']): ?>
                        | Обновлен: <?= date('d.m.Y H:i', strtotime($restaurant['updated_at'])) ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="btn-group">
            <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Назад к списку
            </a>
            <?php if (isset($restaurant) && !empty($restaurant['seo_url'])): ?>
                <a href="<?= base_url($restaurant['seo_url']) ?>" target="_blank" class="btn btn-outline-info">
                    <i class="fas fa-external-link-alt me-1"></i>Просмотр
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Основная форма -->
        <div class="col-lg-8">
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Ошибки валидации:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" id="restaurantForm">
                <!-- Основная информация -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Основная информация
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">
                                    Название ресторана <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= old('name', $restaurant['name'] ?? '') ?>" 
                                       placeholder="Введите название ресторана" required>
                                <div class="form-text">Полное название ресторана</div>
                            </div>
                            <div class="col-md-4">
                                <label for="is_active" class="form-label">Статус</label>
                                <select class="form-select" id="is_active" name="is_active" required>
                                    <option value="1" <?= old('is_active', $restaurant['is_active'] ?? '1') == '1' ? 'selected' : '' ?>>
                                        <i class="fas fa-check"></i> Активен
                                    </option>
                                    <option value="0" <?= old('is_active', $restaurant['is_active'] ?? '1') == '0' ? 'selected' : '' ?>>
                                        <i class="fas fa-pause"></i> Неактивен
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city_id" class="form-label">
                                    Город <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="city_id" name="city_id" required>
                                    <option value="">Выберите город</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= $city['id'] ?>" 
                                                data-state="<?= esc($city['state'] ?? '') ?>"
                                                <?= old('city_id', $restaurant['city_id'] ?? '') == $city['id'] ? 'selected' : '' ?>>
                                            <?= esc($city['name']) ?>
                                            <?php if (!empty($city['state'])): ?>
                                                , <?= esc($city['state']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="rating" class="form-label">Рейтинг</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-star text-warning"></i></span>
                                    <input type="number" class="form-control" id="rating" name="rating" 
                                           min="0" max="5" step="0.1"
                                           value="<?= old('rating', $restaurant['rating'] ?? '') ?>" 
                                           placeholder="4.5">
                                </div>
                                <div class="form-text">От 0.0 до 5.0</div>
                            </div>
                            <div class="col-md-3">
                                <label for="price_level" class="form-label">Уровень цен</label>
                                <select class="form-select" id="price_level" name="price_level">
                                    <option value="0" <?= old('price_level', $restaurant['price_level'] ?? '0') == '0' ? 'selected' : '' ?>>
                                        Не указан
                                    </option>
                                    <option value="1" <?= old('price_level', $restaurant['price_level'] ?? '0') == '1' ? 'selected' : '' ?>>
                                        $ - Бюджетный
                                    </option>
                                    <option value="2" <?= old('price_level', $restaurant['price_level'] ?? '0') == '2' ? 'selected' : '' ?>>
                                        $$ - Средний
                                    </option>
                                    <option value="3" <?= old('price_level', $restaurant['price_level'] ?? '0') == '3' ? 'selected' : '' ?>>
                                        $$$ - Дорогой
                                    </option>
                                    <option value="4" <?= old('price_level', $restaurant['price_level'] ?? '0') == '4' ? 'selected' : '' ?>>
                                        $$$$ - Очень дорогой
                                    </option>
                                </select>
                            </div>
                            <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="is_georgian" class="form-label">
                                    <strong>🇬🇪 Грузинский ресторан?</strong>
                                </label>
                                <select name="is_georgian" id="is_georgian" class="form-select">
                                    <option value="">-- Не проверен --</option>
                                    <option value="1" <?= old('is_georgian', $restaurant['is_georgian'] ?? '') == '1' ? 'selected' : '' ?>>
                                        ✅ Да, грузинский ресторан
                                    </option>
                                    <option value="0" <?= old('is_georgian', $restaurant['is_georgian'] ?? '') == '0' ? 'selected' : '' ?>>
                                        ❌ Нет, не грузинский
                                    </option>
                                </select>
                                <div class="form-text">
                                    Отметьте, является ли этот ресторан действительно грузинским
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Можно оставить пустым или добавить дополнительную информацию -->
                                <?php if (isset($restaurant) && $restaurant['is_georgian'] !== null): ?>
                                    <label class="form-label">Статус верификации</label>
                                    <div class="mt-2">
                                        <?php if ($restaurant['is_georgian'] == 1): ?>
                                            <span class="badge bg-success fs-6">
                                                <i class="fas fa-check-circle me-1"></i>Подтвержден как грузинский
                                            </span>
                                        <?php elseif ($restaurant['is_georgian'] == 0): ?>
                                            <span class="badge bg-danger fs-6">
                                                <i class="fas fa-times-circle me-1"></i>Отмечен как НЕ грузинский
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-4">
                                        <span class="badge bg-warning fs-6">
                                            <i class="fas fa-question-circle me-1"></i>Требует проверки
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Краткое описание ресторана, кухни, атмосферы..."><?= old('description', $restaurant['description'] ?? '') ?></textarea>
                            <div class="form-text">
                                Максимум 2000 символов. <span id="charCount" class="text-muted">0 символов</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO настройки -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-search me-2"></i>SEO настройки
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="slug" class="form-label">
                                    Slug <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="<?= old('slug', $restaurant['slug'] ?? '') ?>" 
                                           placeholder="restaurant-name" required>
                                    <button type="button" class="btn btn-outline-secondary" id="generateSlug">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    URL-дружественное имя. Только строчные буквы, цифры и дефисы.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="seo_url" class="form-label">SEO URL</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="seo_url" name="seo_url" 
                                           value="<?= old('seo_url', $restaurant['seo_url'] ?? '') ?>" 
                                           placeholder="restaurant-name-city">
                                    <button type="button" class="btn btn-outline-info" id="generateSeoUrl">
                                        <i class="fas fa-wand-magic-sparkles"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Полный SEO URL. Оставьте пустым для использования slug.
                                </div>
                            </div>
                        </div>

                        <!-- URL Preview -->
                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Предпросмотр URL:</strong><br>
                                <span class="text-muted">Страница ресторана:</span> 
                                <code id="restaurant-url-preview"><?= base_url() ?>/restaurant/<span id="slug-preview"><?= $restaurant['slug'] ?? 'slug' ?></span></code><br>
                                <span class="text-muted">SEO URL:</span> 
                                <code id="seo-url-preview"><?= base_url() ?>/<span id="seo-url-preview-text"><?= $restaurant['seo_url'] ?? 'seo-url' ?></span></code>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Контактная информация -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-address-book me-2"></i>Контактная информация
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="address" class="form-label">Адрес</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?= old('address', $restaurant['address'] ?? '') ?>" 
                                       placeholder="Полный адрес ресторана">
                                <button type="button" class="btn btn-outline-info" id="geocodeAddress" title="Получить координаты">
                                    <i class="fas fa-map-pin"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Телефон</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= old('phone', $restaurant['phone'] ?? '') ?>" 
                                           placeholder="+1 (555) 123-4567">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="website" class="form-label">Веб-сайт</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    <input type="url" class="form-control" id="website" name="website" 
                                           value="<?= old('website', $restaurant['website'] ?? '') ?>" 
                                           placeholder="https://restaurant.com">
                                    <?php if (!empty($restaurant['website'])): ?>
                                        <a href="<?= esc($restaurant['website']) ?>" target="_blank" class="btn btn-outline-info">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label">Широта</label>
                                <input type="number" class="form-control" id="latitude" name="latitude" 
                                       step="any" 
                                       value="<?= old('latitude', $restaurant['latitude'] ?? '') ?>" 
                                       placeholder="40.7580">
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label">Долгота</label>
                                <input type="number" class="form-control" id="longitude" name="longitude" 
                                       step="any"
                                       value="<?= old('longitude', $restaurant['longitude'] ?? '') ?>" 
                                       placeholder="-73.9855">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="google_place_id" class="form-label">Google Place ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-google"></i></span>
                                <input type="text" class="form-control" id="google_place_id" name="google_place_id" 
                                       value="<?= old('google_place_id', $restaurant['google_place_id'] ?? '') ?>" 
                                       placeholder="ChIJN1t_tDeuEmsRUsoyG83frY4">
                                <button type="button" class="btn btn-outline-success" id="findPlaceId" title="Найти Place ID">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                ID места в Google Places API для импорта фотографий и данных
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-1"></i>
                                    <?= isset($restaurant) ? 'Обновить ресторан' : 'Создать ресторан' ?>
                                </button>
                                <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-secondary ms-2">
                                    <i class="fas fa-times me-1"></i>Отмена
                                </a>
                            </div>
                            
                            <?php if (isset($restaurant)): ?>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-tools me-1"></i>Дополнительно
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('admin/restaurants/' . $restaurant['id'] . '/photos') ?>">
                                                <i class="fas fa-images me-1"></i>Управление фотографиями
                                            </a>
                                        </li>
                                        <?php if (!empty($restaurant['google_place_id'])): ?>
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="importGooglePhotos()">
                                                    <i class="fab fa-google me-1"></i>Импорт фото из Google
                                                </button>
                                            </li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" onclick="deleteRestaurant()">
                                                <i class="fas fa-trash me-1"></i>Удалить ресторан
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <?php if (isset($restaurant)): ?>
                <!-- Информация о ресторане -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Информация
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="h4 font-weight-bold text-primary"><?= $restaurant['id'] ?></div>
                                <div class="text-xs text-uppercase text-muted">ID</div>
                            </div>
                            <div class="col-4">
                                <div class="h4 font-weight-bold text-success">
                                    <?= $restaurant['rating'] ? number_format($restaurant['rating'], 1) : '—' ?>
                                </div>
                                <div class="text-xs text-uppercase text-muted">Рейтинг</div>
                            </div>
                            <div class="col-4">
                                <div class="h4 font-weight-bold text-info">
                                    <?php 
                                    try {
                                        $photoModel = new \App\Models\RestaurantPhotoModel();
                                        $photosCount = $photoModel->where('restaurant_id', $restaurant['id'])->countAllResults();
                                        echo $photosCount;
                                    } catch (Exception $e) {
                                        echo '0';
                                    }
                                    ?>
                                </div>
                                <div class="text-xs text-uppercase text-muted">Фото</div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="small">
                            <div class="mb-2"><strong>Статус данных:</strong></div>
                            <div class="row">
                                <div class="col-6">
                                    <?php if ($restaurant['latitude'] && $restaurant['longitude']): ?>
                                        <i class="fas fa-check text-success"></i> Координаты
                                    <?php else: ?>
                                        <i class="fas fa-times text-danger"></i> Координаты
                                    <?php endif; ?>
                                </div>
                                <div class="col-6">
                                    <?php if ($restaurant['google_place_id']): ?>
                                        <i class="fas fa-check text-success"></i> Place ID
                                    <?php else: ?>
                                        <i class="fas fa-times text-danger"></i> Place ID
                                    <?php endif; ?>
                                </div>
                                <div class="col-6">
                                    <?php if ($restaurant['website']): ?>
                                        <i class="fas fa-check text-success"></i> Веб-сайт
                                    <?php else: ?>
                                        <i class="fas fa-times text-danger"></i> Веб-сайт
                                    <?php endif; ?>
                                </div>
                                <div class="col-6">
                                    <?php if ($restaurant['phone']): ?>
                                        <i class="fas fa-check text-success"></i> Телефон
                                    <?php else: ?>
                                        <i class="fas fa-times text-danger"></i> Телефон
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Быстрые действия -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bolt me-2"></i>Быстрые действия
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="findOnMap()">
                                <i class="fas fa-map-marker-alt me-1"></i>Найти на карте
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="validateData()">
                                <i class="fas fa-check-circle me-1"></i>Проверить данные
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="generateFullSeo()">
                                <i class="fas fa-magic me-1"></i>Генерировать SEO
                            </button>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Подсказки для нового ресторана -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-lightbulb me-2"></i>Подсказки
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <div class="mb-3">
                                <strong>Обязательные поля:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Название ресторана</li>
                                    <li>Город</li>
                                    <li>Slug</li>
                                </ul>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Рекомендуется:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Адрес и координаты</li>
                                    <li>Телефон и веб-сайт</li>
                                    <li>Рейтинг и уровень цен</li>
                                    <li>Описание ресторана</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-info p-2 mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                После создания можно добавить Google Place ID и фотографии
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($restaurant)): ?>
                <!-- После существующих быстрых действий -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-sync me-2"></i>DataForSEO Импорт
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($restaurant['google_place_id'])): ?>
                            <div class="d-grid gap-2 mb-3">
                                <button type="button" class="btn btn-warning" onclick="updateSingleRestaurant(<?= $restaurant['id'] ?>)">
                                    <i class="fas fa-sync me-1"></i>Обновить из DataForSEO
                                </button>
                            </div>
                            
                            <div class="small text-muted mb-3">
                                <div class="mb-1">
                                    <strong>Google Place ID:</strong><br>
                                    <code class="small"><?= esc(substr($restaurant['google_place_id'], 0, 20)) ?>...</code>
                                </div>
                                <?php if (!empty($restaurant['last_updated_api'])): ?>
                                    <div class="mb-1">
                                        <strong>Последнее обновление:</strong><br>
                                        <?= date('d.m.Y H:i', strtotime($restaurant['last_updated_api'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="alert alert-info p-2 mb-0">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    Обновит: рейтинг, часы работы, атрибуты, описание и другие данные из Google Places
                                </small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning p-2 mb-3">
                                <small>
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Для импорта нужно добавить Google Place ID
                                </small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-info" onclick="findPlaceIdForRestaurant()">
                                    <i class="fas fa-search me-1"></i>Найти Place ID
                                </button>
                                <a href="<?= base_url('admin/dataforseo-import') ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-cogs me-1"></i>Импорт центр
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <!-- Карта локации (если есть координаты) -->
            <?php if (isset($restaurant) && $restaurant['latitude'] && $restaurant['longitude']): ?>
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-map me-2"></i>Расположение
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div id="restaurantMap" style="height: 200px; background-color: #f8f9fa;">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div class="text-center">
                                    <i class="fas fa-map-marker-alt fa-2x text-muted mb-2"></i>
                                    <div class="small text-muted">
                                        <?= number_format($restaurant['latitude'], 6) ?>,<br>
                                        <?= number_format($restaurant['longitude'], 6) ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="openInGoogleMaps()">
                                        <i class="fas fa-external-link-alt me-1"></i>Google Maps
                                    </button>
                                </div>
                            </div>
                        </div>
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
    // Счетчик символов для описания
    $('#description').on('input', function() {
        const length = $(this).val().length;
        const maxLength = 2000;
        const remaining = maxLength - length;
        
        let color = 'text-muted';
        if (remaining < 100) color = 'text-danger';
        else if (remaining < 200) color = 'text-warning';
        
        $('#charCount').removeClass('text-muted text-warning text-danger').addClass(color);
        $('#charCount').text(`${length} символов (осталось: ${remaining})`);
    });
    
    // Инициализация счетчика
    $('#description').trigger('input');
    
    // Автогенерация slug из названия
    $('#name').on('input', function() {
        if (!$('#slug').data('manual')) {
            const slug = generateSlug($(this).val());
            $('#slug').val(slug);
            updatePreviews();
        }
    });
    
    // Отметить slug как ручной при редактировании
    $('#slug').on('input', function() {
        $(this).data('manual', true);
        updatePreviews();
    });
    
    // Обновление SEO URL превью
    $('#seo_url').on('input', updatePreviews);
    
    // Кнопка генерации slug
    $('#generateSlug').on('click', function() {
        const name = $('#name').val();
        if (name) {
            const slug = generateSlug(name);
            $('#slug').val(slug).data('manual', false);
            updatePreviews();
        } else {
            alert('Сначала введите название ресторана');
        }
    });
    
    // Кнопка генерации SEO URL
    $('#generateSeoUrl').on('click', function() {
        const name = $('#name').val();
        const citySelect = $('#city_id option:selected');
        
        if (!name) {
            alert('Сначала введите название ресторана');
            return;
        }
        
        if (!citySelect.val()) {
            alert('Сначала выберите город');
            return;
        }
        
        const cityName = citySelect.text().split(',')[0].trim(); // Берем только название города
        const seoUrl = generateSlug(name + ' ' + cityName);
        $('#seo_url').val(seoUrl);
        updatePreviews();
    });
    
    // Геокодирование адреса
    $('#geocodeAddress').on('click', function() {
        const address = $('#address').val();
        if (!address) {
            alert('Введите адрес для геокодирования');
            return;
        }
        
        const button = $(this);
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        // Простое геокодирование через Google Maps API
        geocodeAddress(address, function(lat, lng) {
            $('#latitude').val(lat);
            $('#longitude').val(lng);
            showAlert('success', 'Координаты получены успешно');
            button.html(originalHtml).prop('disabled', false);
        }, function(error) {
            showAlert('danger', 'Ошибка геокодирования: ' + error);
            button.html(originalHtml).prop('disabled', false);
        });
    });
    
    // Поиск Google Place ID
    $('#findPlaceId').on('click', function() {
        const name = $('#name').val();
        const address = $('#address').val();
        
        if (!name && !address) {
            alert('Введите название или адрес ресторана');
            return;
        }
        
        const button = $(this);
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        // Поиск Place ID (требует реализации на бэкенде)
        findPlaceId(name, address, function(placeId) {
            $('#google_place_id').val(placeId);
            showAlert('success', 'Google Place ID найден');
            button.html(originalHtml).prop('disabled', false);
        }, function(error) {
            showAlert('warning', 'Place ID не найден: ' + error);
            button.html(originalHtml).prop('disabled', false);
        });
    });
    
    // Валидация координат
    $('#latitude, #longitude').on('input', function() {
        const lat = parseFloat($('#latitude').val());
        const lng = parseFloat($('#longitude').val());
        
        if (lat && (lat < -90 || lat > 90)) {
            showAlert('warning', 'Широта должна быть от -90 до 90');
        }
        
        if (lng && (lng < -180 || lng > 180)) {
            showAlert('warning', 'Долгота должна быть от -180 до 180');
        }
    });
    
    // Проверка формата Google Place ID
    $('#google_place_id').on('input', function() {
        const placeId = $(this).val();
        if (placeId && !placeId.match(/^ChIJ/)) {
            $(this).addClass('is-warning');
            showAlert('info', 'Google Place ID обычно начинается с "ChIJ"');
        } else {
            $(this).removeClass('is-warning');
        }
    });
    
    // Валидация формы перед отправкой
    $('#restaurantForm').on('submit', function(e) {
        const name = $('#name').val().trim();
        const slug = $('#slug').val().trim();
        const cityId = $('#city_id').val();
        
        if (!name) {
            e.preventDefault();
            showAlert('danger', 'Введите название ресторана');
            $('#name').focus();
            return false;
        }
        
        if (!slug) {
            e.preventDefault();
            showAlert('danger', 'Введите slug');
            $('#slug').focus();
            return false;
        }
        
        if (!cityId) {
            e.preventDefault();
            showAlert('danger', 'Выберите город');
            $('#city_id').focus();
            return false;
        }
        
        // Проверка корректности slug
        if (!slug.match(/^[a-z0-9-]+$/)) {
            e.preventDefault();
            showAlert('danger', 'Slug может содержать только строчные буквы, цифры и дефисы');
            $('#slug').focus();
            return false;
        }
        
        return true;
    });
});

// Генерация slug из текста
function generateSlug(text) {
    return text.toLowerCase()
               .replace(/[^a-z0-9\s-]/g, '')
               .replace(/\s+/g, '-')
               .replace(/-+/g, '-')
               .replace(/^-|-$/g, '');
}

// Обновление превью URL
function updatePreviews() {
    const slug = $('#slug').val() || 'slug';
    const seoUrl = $('#seo_url').val() || slug;
    
    $('#slug-preview').text(slug);
    $('#seo-url-preview-text').text(seoUrl);
}

// Геокодирование адреса
function geocodeAddress(address, successCallback, errorCallback) {
    // Здесь должен быть запрос к Google Geocoding API
    // Пока заглушка
    setTimeout(() => {
        errorCallback('Геокодирование не настроено');
    }, 1000);
}

// Поиск Google Place ID
function findPlaceId(name, address, successCallback, errorCallback) {
    // Здесь должен быть запрос к Google Places API
    // Пока заглушка
    setTimeout(() => {
        errorCallback('Поиск Place ID не настроен');
    }, 1000);
}

// Боковые функции
function findOnMap() {
    const lat = $('#latitude').val();
    const lng = $('#longitude').val();
    const name = $('#name').val();
    
    if (lat && lng) {
        const url = `https://www.google.com/maps/@${lat},${lng},15z`;
        window.open(url, '_blank');
    } else if (name) {
        const url = `https://www.google.com/maps/search/${encodeURIComponent(name)}`;
        window.open(url, '_blank');
    } else {
        showAlert('warning', 'Укажите координаты или название для поиска на карте');
    }
}

function openInGoogleMaps() {
    <?php if (isset($restaurant) && $restaurant['latitude'] && $restaurant['longitude']): ?>
        const url = `https://www.google.com/maps/@<?= $restaurant['latitude'] ?>,<?= $restaurant['longitude'] ?>,15z`;
        window.open(url, '_blank');
    <?php endif; ?>
}

function validateData() {
    const issues = [];
    
    if (!$('#latitude').val() || !$('#longitude').val()) {
        issues.push('Нет координат');
    }
    
    if (!$('#google_place_id').val()) {
        issues.push('Нет Google Place ID');
    }
    
    if (!$('#phone').val()) {
        issues.push('Нет телефона');
    }
    
    if (!$('#website').val()) {
        issues.push('Нет веб-сайта');
    }
    
    if (!$('#description').val()) {
        issues.push('Нет описания');
    }
    
    if (issues.length === 0) {
        showAlert('success', 'Все данные заполнены корректно!');
    } else {
        showAlert('warning', 'Недостающие данные: ' + issues.join(', '));
    }
}

function generateFullSeo() {
    $('#generateSlug').click();
    setTimeout(() => {
        $('#generateSeoUrl').click();
        showAlert('success', 'SEO данные сгенерированы');
    }, 100);
}

function importGooglePhotos() {
    <?php if (isset($restaurant)): ?>
        const placeId = $('#google_place_id').val();
        if (!placeId) {
            showAlert('warning', 'Сначала добавьте Google Place ID');
            return;
        }
        
        if (confirm('Импортировать фотографии из Google Places?')) {
            $.ajax({
                url: '<?= base_url('admin/restaurants/' . $restaurant['id'] . '/import-google-photos') ?>',
                method: 'POST',
                dataType: 'json',
                beforeSend: function() {
                    showAlert('info', 'Импорт фотографий...');
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'Ошибка импорта фотографий');
                }
            });
        }
    <?php endif; ?>
}

function deleteRestaurant() {
    <?php if (isset($restaurant)): ?>
        if (confirm('Вы уверены, что хотите удалить ресторан "<?= esc($restaurant['name']) ?>"?\n\nЭто действие нельзя отменить.')) {
            $.ajax({
                url: '<?= base_url('admin/restaurants/delete/' . $restaurant['id']) ?>',
                method: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Ресторан удален');
                        setTimeout(() => {
                            window.location.href = '<?= base_url('admin/restaurants') ?>';
                        }, 1500);
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'Ошибка удаления ресторана');
                }
            });
        }
    <?php endif; ?>
}

// Показ уведомлений
function showAlert(type, message) {
    const alertClass = `alert-${type}`;
    const iconClass = type === 'success' ? 'check-circle' : 
                     type === 'danger' ? 'exclamation-circle' : 
                     type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
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

// Инициализация превью при загрузке
updatePreviews();
function updateSingleRestaurant(restaurantId) {
    const placeId = document.getElementById('google_place_id')?.value;
    const restaurantName = document.getElementById('name')?.value || '';
    const restaurantAddress = document.getElementById('address')?.value || '';
    
    if (!restaurantId) {
        showDataForSeoAlert('danger', 'Ошибка: ID ресторана не найден');
        return;
    }
    
    if (!placeId) {
        if (!confirm('Google Place ID не заполнен. Попробовать обновить данные без него?')) {
            return;
        }
    }
    
    // Находим кнопку DataForSEO
    const button = document.querySelector(`button[onclick*="updateSingleRestaurant(${restaurantId})"]`) ||
                   document.querySelector('button[onclick*="DataForSEO"]');
    
    const originalText = button ? button.innerHTML : '';
    
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Обновление...';
    }
    
    // Показываем уведомление о начале процесса
    showDataForSeoAlert('info', 'Начинаем обновление данных из DataForSEO...', 3000);
    
    // Выполняем запрос к правильному роуту
    fetch(`<?= base_url() ?>admin/restaurants/update-from-dataforseo/${restaurantId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            current_place_id: placeId,
            restaurant_name: restaurantName,
            restaurant_address: restaurantAddress
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDataForSeoAlert('success', data.message || 'Данные успешно обновлены из DataForSEO!', 5000);
            
            // Обновляем поля формы если данные изменились
            if (data.updated_data) {
                updateFormFieldsDataForSeo(data.updated_data);
            }
            
            // Предлагаем перезагрузить страницу для отображения всех изменений
            setTimeout(() => {
                if (confirm('Данные обновлены! Перезагрузить страницу для отображения всех изменений?')) {
                    window.location.reload();
                }
            }, 2000);
            
        } else {
            showDataForSeoAlert('danger', data.message || 'Ошибка при обновлении данных', 8000);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showDataForSeoAlert('danger', 'Ошибка при выполнении запроса: ' + error.message, 8000);
    })
    .finally(() => {
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    });
}

// Функция обновления полей формы (переименована чтобы не конфликтовать)
function updateFormFieldsDataForSeo(updatedData) {
    const fieldsMap = {
        'name': 'name',
        'description': 'description', 
        'rating': 'rating',
        'phone': 'phone',
        'website': 'website',
        'address': 'address',
        'google_place_id': 'google_place_id',
        'latitude': 'latitude',
        'longitude': 'longitude'
    };
    
    for (const [apiField, formField] of Object.entries(fieldsMap)) {
        if (updatedData[apiField] !== undefined) {
            const element = document.getElementById(formField);
            if (element) {
                const oldValue = element.value;
                const newValue = updatedData[apiField];
                
                if (oldValue !== newValue) {
                    element.value = newValue;
                    // Подсвечиваем обновленное поле
                    element.classList.add('border-success');
                    setTimeout(() => {
                        element.classList.remove('border-success');
                    }, 3000);
                }
            }
        }
    }
}

// Специальная функция для уведомлений DataForSEO (чтобы не конфликтовать с основной)
function showDataForSeoAlert(type, message, duration = 5000) {
    // Удаляем предыдущие уведомления DataForSEO
    const existingAlerts = document.querySelectorAll('.dataforseo-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertClass = `alert-${type}`;
    const iconClass = type === 'success' ? 'check-circle' : 
                     type === 'danger' ? 'exclamation-circle' : 
                     type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    const alertId = 'dataforseo-alert-' + Date.now();
    const alert = document.createElement('div');
    alert.id = alertId;
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed dataforseo-alert`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 350px; max-width: 500px;';
    
    alert.innerHTML = `
        <div class="d-flex align-items-start">
            <i class="fas fa-${iconClass} me-2 mt-1"></i>
            <div class="flex-grow-1">
                <strong>DataForSEO:</strong> ${message}
            </div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(alert);
    
    // Автоматическое скрытие
    setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            alertElement.classList.remove('show');
            setTimeout(() => alertElement.remove(), 300);
        }
    }, duration);
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('DataForSEO integration loaded');
});
</script>

<?= $this->endSection() ?>