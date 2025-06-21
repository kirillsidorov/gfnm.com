<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-sitemap me-2"></i>Sitemap Generator
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-utensils fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['restaurants_count'] ?></h5>
                        <p class="card-text mb-0">Активных ресторанов</p>
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
                        <i class="fas fa-city fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0"><?= $stats['cities_count'] ?></h5>
                        <p class="card-text mb-0">Городов</p>
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
                        <i class="fas fa-file-code fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0">
                            <?= $stats['sitemap_exists'] ? 'Есть' : 'Нет' ?>
                        </h5>
                        <p class="card-text mb-0">Sitemap.xml</p>
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
                        <i class="fas fa-robot fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0">
                            <?= $stats['robots_exists'] ? 'Есть' : 'Нет' ?>
                        </h5>
                        <p class="card-text mb-0">Robots.txt</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Current Sitemap Status -->
<?php if ($stats['sitemap_exists']): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-info-circle me-2"></i>Текущий sitemap
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <p class="mb-1"><strong>Размер файла:</strong> <?= number_format($stats['sitemap_size']) ?> байт</p>
                <p class="mb-1"><strong>Последнее обновление:</strong> 
                    <?= $stats['sitemap_modified'] ? date('d.m.Y H:i:s', $stats['sitemap_modified']) : 'Неизвестно' ?>
                </p>
                <p class="mb-0"><strong>URL:</strong> 
                    <a href="<?= base_url('sitemap.xml') ?>" target="_blank" class="text-decoration-none">
                        <?= base_url('sitemap.xml') ?> <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-outline-primary btn-sm me-2" onclick="validateSitemap()">
                    <i class="fas fa-check-circle me-1"></i>Проверить
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="deleteSitemap()">
                    <i class="fas fa-trash me-1"></i>Удалить
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Sitemap Generation Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-cogs me-2"></i>Настройки генерации
        </h5>
    </div>
    <div class="card-body">
        <form id="sitemapForm">
            <div class="row">
                <!-- Включаемые разделы -->
                <div class="col-md-4">
                    <h6>Включить в sitemap:</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="include_restaurants" 
                               name="include_restaurants" <?= $settings['include_restaurants'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="include_restaurants">
                            Рестораны (<?= $stats['restaurants_count'] ?>)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="include_cities" 
                               name="include_cities" <?= $settings['include_cities'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="include_cities">
                            Города (<?= $stats['cities_count'] ?>)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="include_static_pages" 
                               name="include_static_pages" <?= $settings['include_static_pages'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="include_static_pages">
                            Статические страницы
                        </label>
                    </div>
                </div>

                <!-- Приоритеты -->
                <div class="col-md-4">
                    <h6>Приоритет (priority):</h6>
                    <div class="mb-2">
                        <label for="restaurants_priority" class="form-label small">Рестораны:</label>
                        <select class="form-select form-select-sm" id="restaurants_priority" name="restaurants_priority">
                            <option value="1.0" <?= $settings['restaurants_priority'] == '1.0' ? 'selected' : '' ?>>1.0 (Высокий)</option>
                            <option value="0.8" <?= $settings['restaurants_priority'] == '0.8' ? 'selected' : '' ?>>0.8</option>
                            <option value="0.6" <?= $settings['restaurants_priority'] == '0.6' ? 'selected' : '' ?>>0.6 (Средний)</option>
                            <option value="0.4" <?= $settings['restaurants_priority'] == '0.4' ? 'selected' : '' ?>>0.4</option>
                            <option value="0.2" <?= $settings['restaurants_priority'] == '0.2' ? 'selected' : '' ?>>0.2 (Низкий)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="cities_priority" class="form-label small">Города:</label>
                        <select class="form-select form-select-sm" id="cities_priority" name="cities_priority">
                            <option value="1.0" <?= $settings['cities_priority'] == '1.0' ? 'selected' : '' ?>>1.0 (Высокий)</option>
                            <option value="0.8" <?= $settings['cities_priority'] == '0.8' ? 'selected' : '' ?>>0.8</option>
                            <option value="0.6" <?= $settings['cities_priority'] == '0.6' ? 'selected' : '' ?>>0.6 (Средний)</option>
                            <option value="0.4" <?= $settings['cities_priority'] == '0.4' ? 'selected' : '' ?>>0.4</option>
                            <option value="0.2" <?= $settings['cities_priority'] == '0.2' ? 'selected' : '' ?>>0.2 (Низкий)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="static_priority" class="form-label small">Статические:</label>
                        <select class="form-select form-select-sm" id="static_priority" name="static_priority">
                            <option value="1.0" <?= $settings['static_priority'] == '1.0' ? 'selected' : '' ?>>1.0 (Высокий)</option>
                            <option value="0.8" <?= $settings['static_priority'] == '0.8' ? 'selected' : '' ?>>0.8</option>
                            <option value="0.6" <?= $settings['static_priority'] == '0.6' ? 'selected' : '' ?>>0.6 (Средний)</option>
                            <option value="0.4" <?= $settings['static_priority'] == '0.4' ? 'selected' : '' ?>>0.4</option>
                            <option value="0.2" <?= $settings['static_priority'] == '0.2' ? 'selected' : '' ?>>0.2 (Низкий)</option>
                        </select>
                    </div>
                </div>

                <!-- Частота обновлений -->
                <div class="col-md-4">
                    <h6>Частота обновлений (changefreq):</h6>
                    <div class="mb-2">
                        <label for="restaurants_changefreq" class="form-label small">Рестораны:</label>
                        <select class="form-select form-select-sm" id="restaurants_changefreq" name="restaurants_changefreq">
                            <option value="always" <?= $settings['restaurants_changefreq'] == 'always' ? 'selected' : '' ?>>Always</option>
                            <option value="hourly" <?= $settings['restaurants_changefreq'] == 'hourly' ? 'selected' : '' ?>>Hourly</option>
                            <option value="daily" <?= $settings['restaurants_changefreq'] == 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $settings['restaurants_changefreq'] == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= $settings['restaurants_changefreq'] == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $settings['restaurants_changefreq'] == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                            <option value="never" <?= $settings['restaurants_changefreq'] == 'never' ? 'selected' : '' ?>>Never</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="cities_changefreq" class="form-label small">Города:</label>
                        <select class="form-select form-select-sm" id="cities_changefreq" name="cities_changefreq">
                            <option value="always" <?= $settings['cities_changefreq'] == 'always' ? 'selected' : '' ?>>Always</option>
                            <option value="hourly" <?= $settings['cities_changefreq'] == 'hourly' ? 'selected' : '' ?>>Hourly</option>
                            <option value="daily" <?= $settings['cities_changefreq'] == 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $settings['cities_changefreq'] == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= $settings['cities_changefreq'] == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $settings['cities_changefreq'] == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                            <option value="never" <?= $settings['cities_changefreq'] == 'never' ? 'selected' : '' ?>>Never</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="static_changefreq" class="form-label small">Статические:</label>
                        <select class="form-select form-select-sm" id="static_changefreq" name="static_changefreq">
                            <option value="always" <?= $settings['static_changefreq'] == 'always' ? 'selected' : '' ?>>Always</option>
                            <option value="hourly" <?= $settings['static_changefreq'] == 'hourly' ? 'selected' : '' ?>>Hourly</option>
                            <option value="daily" <?= $settings['static_changefreq'] == 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $settings['static_changefreq'] == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= $settings['static_changefreq'] == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $settings['static_changefreq'] == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                            <option value="never" <?= $settings['static_changefreq'] == 'never' ? 'selected' : '' ?>>Never</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-md-8">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="button" class="btn btn-primary" onclick="generateSitemap()">
                            <i class="fas fa-cogs me-2"></i>Сгенерировать Sitemap
                        </button>
                        <button type="button" class="btn btn-info" onclick="generateIndexSitemap()">
                            <i class="fas fa-list me-2"></i>Индексный Sitemap
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="previewSitemap()">
                            <i class="fas fa-eye me-2"></i>Предпросмотр
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-end">
                        <small class="text-muted">
                            Предполагаемое количество URL: <span id="estimatedUrls">0</span>
                        </small>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Progress Section -->
<div class="card mb-4" id="progressCard" style="display: none;">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-spinner fa-spin me-2"></i>Генерация Sitemap
        </h5>
    </div>
    <div class="card-body">
        <div class="progress mb-3" style="height: 20px;">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" style="width: 0%">0%</div>
        </div>
        <div id="progressText" class="text-center">
            <small class="text-muted">Подготовка к генерации...</small>
        </div>
    </div>
</div>

<!-- Result Section -->
<div class="card" id="resultCard" style="display: none;">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-check-circle me-2 text-success"></i>Результат генерации
        </h5>
    </div>
    <div class="card-body">
        <div id="resultContent">
            <!-- Результат будет добавлен сюда через JavaScript -->
        </div>
    </div>
</div>

<!-- SEO Tips -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-lightbulb me-2"></i>SEO Советы
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>📈 Оптимизация Sitemap:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Обновляйте sitemap при добавлении новых ресторанов</li>
                    <li><i class="fas fa-check text-success me-2"></i>Устанавливайте правильные приоритеты страниц</li>
                    <li><i class="fas fa-check text-success me-2"></i>Указывайте реальную частоту обновлений</li>
                    <li><i class="fas fa-check text-success me-2"></i>Включайте только активные рестораны</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>🚀 После генерации:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-arrow-right text-primary me-2"></i>Отправьте sitemap в Google Search Console</li>
                    <li><i class="fas fa-arrow-right text-primary me-2"></i>Добавьте ссылку в robots.txt (автоматически)</li>
                    <li><i class="fas fa-arrow-right text-primary me-2"></i>Проверьте индексацию через 1-2 недели</li>
                    <li><i class="fas fa-arrow-right text-primary me-2"></i>Настройте автоматическое обновление</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    updateEstimatedUrls();
    
    // Обновляем оценку количества URL при изменении настроек
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateEstimatedUrls);
    });
});

function updateEstimatedUrls() {
    let count = 1; // Главная страница всегда включается
    
    if (document.getElementById('include_restaurants').checked) {
        count += <?= $stats['restaurants_count'] ?>;
    }
    
    if (document.getElementById('include_cities').checked) {
        count += <?= $stats['cities_count'] ?>;
    }
    
    if (document.getElementById('include_static_pages').checked) {
        count += 5; // Примерное количество статических страниц
    }
    
    document.getElementById('estimatedUrls').textContent = count;
}

function generateSitemap() {
    const form = document.getElementById('sitemapForm');
    const formData = new FormData(form);
    
    // Конвертируем checkbox в правильные значения
    formData.set('include_restaurants', document.getElementById('include_restaurants').checked);
    formData.set('include_cities', document.getElementById('include_cities').checked);
    formData.set('include_static_pages', document.getElementById('include_static_pages').checked);
    
    showProgress('Генерируем sitemap...');
    
    fetch('<?= base_url('admin/sitemap/generate') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        
        if (data.success) {
            showResult(data, 'success');
            // Обновляем страницу через 2 секунды для отображения нового статуса
            setTimeout(() => location.reload(), 2000);
        } else {
            showResult(data, 'error');
        }
    })
    .catch(error => {
        hideProgress();
        showResult({message: 'Ошибка соединения: ' + error.message}, 'error');
    });
}

function generateIndexSitemap() {
    showProgress('Создаем индексный sitemap...');
    
    fetch('<?= base_url('admin/sitemap/generate-index') ?>', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        showResult(data, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => location.reload(), 2000);
        }
    })
    .catch(error => {
        hideProgress();
        showResult({message: 'Ошибка: ' + error.message}, 'error');
    });
}

function validateSitemap() {
    fetch('<?= base_url('admin/sitemap/validate') ?>', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Sitemap валиден!\n\nКоличество URL: ${data.data.url_count}\nРазмер файла: ${data.data.file_size_formatted}`);
        } else {
            alert('❌ Ошибка валидации: ' + data.message);
        }
    })
    .catch(error => {
        alert('Ошибка проверки: ' + error.message);
    });
}

function deleteSitemap() {
    if (!confirm('Вы уверены, что хотите удалить sitemap.xml?')) {
        return;
    }
    
    fetch('<?= base_url('admin/sitemap/delete') ?>', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        alert('Ошибка удаления: ' + error.message);
    });
}

function previewSitemap() {
    // Открываем sitemap в новой вкладке
    window.open('<?= base_url('sitemap.xml') ?>', '_blank');
}

function showProgress(message) {
    document.getElementById('progressCard').style.display = 'block';
    document.getElementById('resultCard').style.display = 'none';
    document.getElementById('progressText').innerHTML = `<small class="text-muted">${message}</small>`;
    
    // Анимация прогресс-бара
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        
        const progressBar = document.getElementById('progressBar');
        progressBar.style.width = progress + '%';
        progressBar.textContent = Math.round(progress) + '%';
    }, 200);
    
    // Сохраняем interval для остановки
    window.progressInterval = interval;
}

function hideProgress() {
    if (window.progressInterval) {
        clearInterval(window.progressInterval);
    }
    
    // Завершаем прогресс-бар
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = '100%';
    progressBar.textContent = '100%';
    
    setTimeout(() => {
        document.getElementById('progressCard').style.display = 'none';
    }, 500);
}

function showResult(data, type) {
    const resultCard = document.getElementById('resultCard');
    const resultContent = document.getElementById('resultContent');
    
    let html = '';
    let iconClass = type === 'success' ? 'fas fa-check-circle text-success' : 'fas fa-exclamation-circle text-danger';
    let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    
    html += `<div class="alert ${alertClass}">`;
    html += `<i class="${iconClass} me-2"></i>`;
    html += `<strong>${data.message}</strong>`;
    html += `</div>`;
    
    if (data.success && data.data) {
        html += `<div class="row">`;
        html += `<div class="col-md-6">`;
        html += `<h6>Детали файла:</h6>`;
        html += `<ul class="list-unstyled">`;
        
        if (data.data.url_count) {
            html += `<li><strong>Количество URL:</strong> ${data.data.url_count}</li>`;
        }
        if (data.data.file_size) {
            html += `<li><strong>Размер файла:</strong> ${data.data.file_size_formatted || data.data.file_size + ' байт'}</li>`;
        }
        if (data.data.file_path) {
            html += `<li><strong>URL файла:</strong> <a href="${data.data.file_path}" target="_blank">${data.data.file_path}</a></li>`;
        }
        
        html += `</ul>`;
        html += `</div>`;
        html += `</div>`;
    }
    
    resultContent.innerHTML = html;
    resultCard.style.display = 'block';
}
</script>
<?= $this->endSection() ?>