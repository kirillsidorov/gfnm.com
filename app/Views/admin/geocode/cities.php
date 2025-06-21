<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-city me-2"></i>Обновление координат городов
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Progress Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Прогресс обновления</h5>
                        <p class="text-muted mb-0">Обновляем координаты для всех городов</p>
                    </div>
                    <div class="text-end">
                        <button id="updateAllBtn" class="btn btn-primary">
                            <i class="fas fa-play me-2"></i>Обновить все города
                        </button>
                        <a href="<?= base_url('admin/geocode') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Назад
                        </a>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 20px;">
                    <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%">
                        0%
                    </div>
                </div>
                <div id="progressText" class="text-center mt-2">
                    <small class="text-muted">Готов к началу</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cities List -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>Города для обновления (<?= count($cities) ?>)
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($cities)): ?>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Город</th>
                            <th>Штат</th>
                            <th>Текущие координаты</th>
                            <th>Статус</th>
                            <th width="150">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cities as $index => $city): ?>
                            <tr id="city-row-<?= $city['id'] ?>">
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= esc($city['name']) ?></strong></td>
                                <td><?= esc($city['state']) ?></td>
                                <td class="coordinates-cell">
                                    <?php if ($city['latitude'] && $city['longitude']): ?>
                                        <small class="text-success">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?= number_format($city['latitude'], 6) ?>,<br>
                                            <?= number_format($city['longitude'], 6) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">Нет координат</span>
                                    <?php endif; ?>
                                </td>
                                <td class="status-cell">
                                    <?php if ($city['latitude'] && $city['longitude']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Готово
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Ожидание
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-cell">
                                    <button class="btn btn-sm btn-outline-primary update-single-btn" 
                                            data-city-id="<?= $city['id'] ?>"
                                            data-city-name="<?= esc($city['name']) ?>">
                                        <i class="fas fa-sync me-1"></i>Обновить
                                    </button>
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
                <p class="text-muted">Сначала добавьте города в систему</p>
                <a href="<?= base_url('admin/cities') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Добавить города
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Log Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list-alt me-2"></i>Лог обновления
        </h5>
    </div>
    <div class="card-body">
        <div id="logContainer" class="bg-light p-3 rounded" style="height: 300px; overflow-y: auto;">
            <p class="text-muted mb-0">Лог будет отображаться здесь...</p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateAllBtn = document.getElementById('updateAllBtn');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const logContainer = document.getElementById('logContainer');
    const cities = <?= json_encode($cities) ?>;
    
    let isUpdating = false;
    let currentCityIndex = 0;

    // Update All Cities
    updateAllBtn.addEventListener('click', function() {
        if (isUpdating) return;
        
        if (!confirm('Обновить координаты для всех городов? Это может занять некоторое время.')) {
            return;
        }
        
        startBulkUpdate();
    });

    // Update Single City
    document.querySelectorAll('.update-single-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (isUpdating) return;
            
            const cityId = this.dataset.cityId;
            const cityName = this.dataset.cityName;
            
            if (!confirm(`Обновить координаты для города ${cityName}?`)) {
                return;
            }
            
            updateSingleCity(cityId, cityName);
        });
    });

    function startBulkUpdate() {
        isUpdating = true;
        currentCityIndex = 0;
        updateAllBtn.disabled = true;
        updateAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Обновление...';
        
        logContainer.innerHTML = '<p class="text-info mb-2"><i class="fas fa-info-circle me-2"></i>Начинаем обновление всех городов...</p>';
        
        updateNextCity();
    }

    function updateNextCity() {
        if (currentCityIndex >= cities.length) {
            // Completed
            isUpdating = false;
            updateAllBtn.disabled = false;
            updateAllBtn.innerHTML = '<i class="fas fa-check me-2"></i>Обновление завершено';
            updateAllBtn.className = 'btn btn-success';
            
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
            progressBar.className = 'progress-bar bg-success';
            progressText.innerHTML = '<small class="text-success">Обновление завершено!</small>';
            
            logContainer.innerHTML += '<p class="text-success mb-2"><strong><i class="fas fa-check-circle me-2"></i>Все города обновлены!</strong></p>';
            
            setTimeout(() => {
                updateAllBtn.className = 'btn btn-primary';
                updateAllBtn.innerHTML = '<i class="fas fa-sync me-2"></i>Обновить заново';
            }, 3000);
            
            return;
        }

        const city = cities[currentCityIndex];
        const progress = Math.round(((currentCityIndex + 1) / cities.length) * 100);
        
        progressBar.style.width = progress + '%';
        progressBar.textContent = progress + '%';
        progressText.innerHTML = `<small class="text-muted">Обновляем ${city.name}... (${currentCityIndex + 1}/${cities.length})</small>`;
        
        updateCityCoordinates(city.id, city.name, () => {
            currentCityIndex++;
            setTimeout(updateNextCity, 1000); // 1 second delay between requests
        });
    }

    function updateSingleCity(cityId, cityName) {
        const btn = document.querySelector(`[data-city-id="${cityId}"]`);
        const originalHtml = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>...';
        
        logContainer.innerHTML += `<p class="text-info mb-2"><i class="fas fa-sync fa-spin me-2"></i>Обновляем ${cityName}...</p>`;
        logContainer.scrollTop = logContainer.scrollHeight;
        
        updateCityCoordinates(cityId, cityName, () => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }

    function updateCityCoordinates(cityId, cityName, callback) {
        fetch('<?= base_url('admin/geocode/update-city') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `city_id=${cityId}`
        })
        .then(response => response.json())
        .then(data => {
            const row = document.getElementById(`city-row-${cityId}`);
            const statusCell = row.querySelector('.status-cell');
            const coordinatesCell = row.querySelector('.coordinates-cell');
            
            if (data.success) {
                // Update status
                statusCell.innerHTML = '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Готово</span>';
                
                // Update coordinates
                coordinatesCell.innerHTML = `
                    <small class="text-success">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        ${parseFloat(data.data.latitude).toFixed(6)},<br>
                        ${parseFloat(data.data.longitude).toFixed(6)}
                    </small>
                `;
                
                // Add to log
                logContainer.innerHTML += `
                    <p class="text-success mb-2">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>${cityName}:</strong> Координаты обновлены
                        <br><small class="text-muted ms-4">
                            ${data.data.latitude}, ${data.data.longitude}
                            <br>Адрес: ${data.data.formatted_address}
                        </small>
                    </p>
                `;
            } else {
                // Update status
                statusCell.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Ошибка</span>';
                
                // Add to log
                logContainer.innerHTML += `
                    <p class="text-danger mb-2">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>${cityName}:</strong> Ошибка - ${data.message}
                    </p>
                `;
            }
            
            logContainer.scrollTop = logContainer.scrollHeight;
            
            if (callback) callback();
        })
        .catch(error => {
            console.error('Error:', error);
            
            const row = document.getElementById(`city-row-${cityId}`);
            const statusCell = row.querySelector('.status-cell');
            statusCell.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Ошибка</span>';
            
            logContainer.innerHTML += `
                <p class="text-danger mb-2">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>${cityName}:</strong> Ошибка соединения
                </p>
            `;
            logContainer.scrollTop = logContainer.scrollHeight;
            
            if (callback) callback();
        });
    }
});
</script>
<?= $this->endSection() ?>