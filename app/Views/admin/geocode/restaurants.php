<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-utensils me-2"></i>Обновление координат ресторанов
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Info Alert -->
<?php if (count($restaurants) == 0): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Отлично!</strong> Все рестораны уже имеют координаты.
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Найдено <?= count($restaurants) ?> ресторанов</strong> без координат. 
        Геокодирование поможет пользователям найти их на карте.
    </div>
<?php endif; ?>

<!-- Progress Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Прогресс обновления</h5>
                        <p class="text-muted mb-0">Обновляем координаты через Google Geocoding API</p>
                    </div>
                    <div class="text-end">
                        <?php if (count($restaurants) > 0): ?>
                            <button id="updateAllBtn" class="btn btn-primary">
                                <i class="fas fa-play me-2"></i>Обновить все рестораны
                            </button>
                        <?php endif; ?>
                        <a href="<?= base_url('admin/geocode') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Назад
                        </a>
                    </div>
                </div>
                <?php if (count($restaurants) > 0): ?>
                    <div class="progress mt-3" style="height: 20px;">
                        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%">
                            0%
                        </div>
                    </div>
                    <div id="progressText" class="text-center mt-2">
                        <small class="text-muted">Готов к началу</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Restaurants List -->
<?php if (count($restaurants) > 0): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Рестораны без координат (<?= count($restaurants) ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Ресторан</th>
                            <th>Адрес</th>
                            <th>Город</th>
                            <th>Статус</th>
                            <th width="150">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($restaurants as $index => $restaurant): ?>
                            <tr id="restaurant-row-<?= $restaurant['id'] ?>">
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= esc($restaurant['name']) ?></strong>
                                    <?php if ($restaurant['rating']): ?>
                                        <br><small class="text-warning">
                                            <?= number_format($restaurant['rating'], 1) ?> ⭐
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= esc($restaurant['address']) ?></small>
                                </td>
                                <td>
                                    <?= esc($restaurant['city_name']) ?>, <?= esc($restaurant['state']) ?>
                                </td>
                                <td class="status-cell">
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>Ожидание
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <button class="btn btn-sm btn-outline-primary update-single-btn" 
                                            data-restaurant-id="<?= $restaurant['id'] ?>"
                                            data-restaurant-name="<?= esc($restaurant['name']) ?>">
                                        <i class="fas fa-sync me-1"></i>Обновить
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- No restaurants without coordinates -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
            <h4 class="text-success">Все рестораны геокодированы!</h4>
            <p class="text-muted mb-4">Все рестораны уже имеют координаты для отображения на карте.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('admin/geocode') ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Вернуться к статусу
                        </a>
                        <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-utensils me-2"></i>Управление ресторанами
                        </a>
                        <a href="<?= base_url('map') ?>" class="btn btn-outline-info" target="_blank">
                            <i class="fas fa-map me-2"></i>Проверить карту
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Log Section -->
<?php if (count($restaurants) > 0): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list-alt me-2"></i>Лог обновления
            </h5>
        </div>
        <div class="card-body">
            <div id="logContainer" class="bg-light p-3 rounded" style="height: 400px; overflow-y: auto;">
                <p class="text-muted mb-0">Лог будет отображаться здесь...</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (count($restaurants) > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateAllBtn = document.getElementById('updateAllBtn');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const logContainer = document.getElementById('logContainer');
    const restaurants = <?= json_encode($restaurants) ?>;
    
    let isUpdating = false;
    let currentRestaurantIndex = 0;
    let successCount = 0;
    let errorCount = 0;

    // Update All Restaurants
    updateAllBtn.addEventListener('click', function() {
        if (isUpdating) return;
        
        if (!confirm('Обновить координаты для всех ресторанов? Это может занять некоторое время (по 1 секунде на ресторан).')) {
            return;
        }
        
        startBulkUpdate();
    });

    // Update Single Restaurant
    document.querySelectorAll('.update-single-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (isUpdating) return;
            
            const restaurantId = this.dataset.restaurantId;
            const restaurantName = this.dataset.restaurantName;
            
            if (!confirm(`Обновить координаты для ресторана "${restaurantName}"?`)) {
                return;
            }
            
            updateSingleRestaurant(restaurantId, restaurantName);
        });
    });

    function startBulkUpdate() {
        isUpdating = true;
        currentRestaurantIndex = 0;
        successCount = 0;
        errorCount = 0;
        
        updateAllBtn.disabled = true;
        updateAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Обновление...';
        
        logContainer.innerHTML = `
            <p class="text-info mb-2">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Начинаем обновление ${restaurants.length} ресторанов...</strong>
            </p>
            <p class="text-muted mb-3">
                <small>Между запросами делается пауза в 1 секунду для соблюдения лимитов Google API</small>
            </p>
        `;
        
        updateNextRestaurant();
    }

    function updateNextRestaurant() {
        if (currentRestaurantIndex >= restaurants.length) {
            // Completed
            isUpdating = false;
            updateAllBtn.disabled = false;
            
            const isAllSuccess = errorCount === 0;
            updateAllBtn.innerHTML = isAllSuccess ? 
                '<i class="fas fa-check me-2"></i>Обновление завершено' : 
                '<i class="fas fa-exclamation-triangle me-2"></i>Завершено с ошибками';
            updateAllBtn.className = isAllSuccess ? 'btn btn-success' : 'btn btn-warning';
            
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
            progressBar.className = isAllSuccess ? 'progress-bar bg-success' : 'progress-bar bg-warning';
            progressText.innerHTML = `<small class="text-${isAllSuccess ? 'success' : 'warning'}">Обновление завершено!</small>`;
            
            logContainer.innerHTML += `
                <hr>
                <p class="text-${isAllSuccess ? 'success' : 'warning'} mb-2">
                    <strong><i class="fas fa-${isAllSuccess ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    Обновление завершено!</strong>
                </p>
                <p class="mb-1"><strong>Статистика:</strong></p>
                <ul class="mb-0">
                    <li class="text-success">✅ Успешно: ${successCount}</li>
                    <li class="text-danger">❌ Ошибок: ${errorCount}</li>
                    <li>📊 Процент успеха: ${Math.round((successCount / restaurants.length) * 100)}%</li>
                </ul>
            `;
            
            setTimeout(() => {
                updateAllBtn.className = 'btn btn-primary';
                updateAllBtn.innerHTML = '<i class="fas fa-sync me-2"></i>Обновить заново';
            }, 5000);
            
            return;
        }

        const restaurant = restaurants[currentRestaurantIndex];
        const progress = Math.round(((currentRestaurantIndex + 1) / restaurants.length) * 100);
        
        progressBar.style.width = progress + '%';
        progressBar.textContent = progress + '%';
        progressText.innerHTML = `<small class="text-muted">Обновляем ${restaurant.name}... (${currentRestaurantIndex + 1}/${restaurants.length})</small>`;
        
        updateRestaurantCoordinates(restaurant.id, restaurant.name, () => {
            currentRestaurantIndex++;
            setTimeout(updateNextRestaurant, 1000); // 1 second delay between requests
        });
    }

    function updateSingleRestaurant(restaurantId, restaurantName) {
        const btn = document.querySelector(`[data-restaurant-id="${restaurantId}"]`);
        const originalHtml = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>...';
        
        logContainer.innerHTML += `<p class="text-info mb-2"><i class="fas fa-sync fa-spin me-2"></i>Обновляем "${restaurantName}"...</p>`;
        logContainer.scrollTop = logContainer.scrollHeight;
        
        updateRestaurantCoordinates(restaurantId, restaurantName, () => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }

    function updateRestaurantCoordinates(restaurantId, restaurantName, callback) {
        fetch('<?= base_url('admin/geocode/update-restaurant') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `restaurant_id=${restaurantId}`
        })
        .then(response => response.json())
        .then(data => {
            const row = document.getElementById(`restaurant-row-${restaurantId}`);
            const statusCell = row.querySelector('.status-cell');
            
            if (data.success) {
                successCount++;
                
                // Update status
                statusCell.innerHTML = '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Готово</span>';
                
                // Add to log
                logContainer.innerHTML += `
                    <p class="text-success mb-2">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>"${restaurantName}":</strong> Координаты обновлены
                        <br><small class="text-muted ms-4">
                            ${data.data.latitude}, ${data.data.longitude}
                            <br>Адрес: ${data.data.formatted_address}
                        </small>
                    </p>
                `;
            } else {
                errorCount++;
                
                // Update status
                statusCell.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Ошибка</span>';
                
                // Add to log
                logContainer.innerHTML += `
                    <p class="text-danger mb-2">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>"${restaurantName}":</strong> ${data.message}
                    </p>
                `;
            }
            
            logContainer.scrollTop = logContainer.scrollHeight;
            
            if (callback) callback();
        })
        .catch(error => {
            console.error('Error:', error);
            errorCount++;
            
            const row = document.getElementById(`restaurant-row-${restaurantId}`);
            const statusCell = row.querySelector('.status-cell');
            statusCell.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Ошибка</span>';
            
            logContainer.innerHTML += `
                <p class="text-danger mb-2">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>"${restaurantName}":</strong> Ошибка соединения
                </p>
            `;
            logContainer.scrollTop = logContainer.scrollHeight;
            
            if (callback) callback();
        });
    }
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>