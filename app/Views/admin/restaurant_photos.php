<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>
Управление фотографиями - <?= esc($restaurant['name']) ?>
<?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<div class="d-flex align-items-center">
    <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-outline-secondary me-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h2 class="mb-0">Управление фотографиями</h2>
        <small class="text-muted"><?= esc($restaurant['name']) ?></small>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .photo-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .photo-item {
        position: relative;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .photo-item:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
    
    .photo-item.main-photo {
        border-color: #28a745;
        border-width: 3px;
    }
    
    .photo-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
    }
    
    .photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .photo-item:hover .photo-overlay {
        opacity: 1;
    }
    
    .main-photo-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .upload-zone {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .upload-zone:hover {
        border-color: #667eea;
        background-color: #f8f9ff;
    }
    
    .upload-zone.dragover {
        border-color: #667eea;
        background-color: #f0f3ff;
    }
    
    .upload-preview {
        display: none;
        margin-top: 20px;
    }
    
    .upload-preview img {
        max-width: 200px;
        max-height: 150px;
        border-radius: 4px;
    }
    
    .progress {
        height: 6px;
        margin-top: 10px;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Стили для Google Photos */
    .google-photos-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .google-photos-card .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .google-photos-card .btn-outline-light {
        border-color: rgba(255, 255, 255, 0.3);
        color: white;
    }
    
    .google-photos-card .btn-outline-light:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: white;
    }

    .preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }

    .preview-item {
        position: relative;
        border-radius: 6px;
        overflow: hidden;
    }

    .preview-item img {
        width: 100%;
        height: 100px;
        object-fit: cover;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Информация о ресторане -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="card-title mb-1"><?= esc($restaurant['name']) ?></h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?= esc($restaurant['address']) ?>
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-images me-1"></i>
                            Всего фотографий: <strong><?= count($photos) ?></strong>
                            <?php if (!empty($restaurant['google_place_id'])): ?>
                                <span class="ms-3">
                                    <i class="fab fa-google me-1"></i>
                                    Google Place ID: <code><?= substr($restaurant['google_place_id'], 0, 20) ?>...</code>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="<?= base_url("admin/restaurants/edit/{$restaurant['id']}") ?>" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>
                            Редактировать ресторан
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Google Photos (левая колонка) -->
    <div class="col-md-4">
        <div class="card google-photos-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fab fa-google me-2"></i>
                    Google Photos
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($restaurant['google_place_id'])): ?>
                    <div class="d-grid gap-2">
                        <button onclick="previewGooglePhotos()" 
                                class="btn btn-outline-light" 
                                id="previewBtn">
                            <i class="fas fa-eye me-1"></i>
                            Превью фотографий
                        </button>
                        <button onclick="importGooglePhotos()" 
                                class="btn btn-light" 
                                id="importBtn">
                            <i class="fas fa-download me-1"></i>
                            Импорт фотографий
                        </button>
                    </div>
                    <div class="small mt-3 opacity-75">
                        <i class="fas fa-info-circle me-1"></i>
                        Автоматически загружаем фото из Google Places
                    </div>
                    <!-- Контейнер для превью -->
                    <div id="googlePreviewContainer" style="display: none;">
                        <hr class="my-3" style="border-color: rgba(255, 255, 255, 0.2);">
                        <div class="small mb-2">Превью Google Photos:</div>
                        <div id="googlePreviewGrid" class="preview-grid"></div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Google Place ID не установлен.<br>
                        <small>Установите Place ID в настройках ресторана для импорта фото.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Загрузка файлов (правая колонка) -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cloud-upload-alt me-2"></i>
                    Загрузить новые фотографии
                </h5>
            </div>
            <div class="card-body">
                <form id="photoUploadForm" enctype="multipart/form-data">
                    <div class="upload-zone" id="uploadZone">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <h5>Перетащите фотографии сюда</h5>
                        <p class="text-muted mb-3">или нажмите для выбора файлов</p>
                        <input type="file" id="photoInput" name="photos[]" multiple accept="image/*" class="form-control" style="display: none;">
                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('photoInput').click()">
                            <i class="fas fa-plus me-1"></i>
                            Выбрать файлы
                        </button>
                    </div>
                    
                    <div class="upload-preview" id="uploadPreview">
                        <div class="row" id="previewContainer"></div>
                    </div>
                    
                    <div class="progress" id="uploadProgress" style="display: none;">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    
                    <div class="text-center mt-3" id="uploadActions" style="display: none;">
                        <button type="submit" class="btn btn-success me-2" id="uploadBtn">
                            <i class="fas fa-upload me-1"></i>
                            Загрузить фотографии
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearUpload()">
                            <i class="fas fa-times me-1"></i>
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Текущие фотографии -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-images me-2"></i>
                    Текущие фотографии
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($photos)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Нет загруженных фотографий</h5>
                        <p class="text-muted">
                            Загрузите первую фотографию для этого ресторана
                            <?php if (!empty($restaurant['google_place_id'])): ?>
                                или импортируйте из Google Places
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="photo-gallery">
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-item <?= $photo['is_primary'] ? 'main-photo' : '' ?>" data-photo-id="<?= $photo['id'] ?>">
                                <?php if ($photo['is_primary']): ?>
                                    <div class="main-photo-badge">
                                        <i class="fas fa-star me-1"></i>
                                        Главное фото
                                    </div>
                                <?php endif; ?>
                                
                                <img src="<?= base_url($photo['file_path']) ?>">
                                     alt="Фото ресторана" 
                                     loading="lazy"
                                     onerror="this.src='<?= base_url('assets/images/no-image.svg') ?>'">
                                
                                <div class="photo-overlay">
                                    <div class="btn-group-vertical btn-group-sm">
                                        <?php if (!$photo['is_primary']): ?>
                                            <button type="button" class="btn btn-success" 
                                                    onclick="setMainPhoto(<?= $photo['id'] ?>)"
                                                    title="Сделать главным фото">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-primary" 
                                                onclick="viewPhoto('<?= base_url($photo['file_path']) ?>')"
                                                title="Просмотр">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-danger" 
                                                onclick="deletePhoto(<?= $photo['id'] ?>)"
                                                title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для просмотра фото -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Просмотр фотографии</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Фото ресторана" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для Google Photos превью -->
<div class="modal fade" id="googlePhotosModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fab fa-google me-2"></i>
                    Превью Google Photos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="googleModalContent">
                    <!-- Контент будет загружен через JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-success" onclick="importFromModal()">
                    <i class="fas fa-download me-1"></i>
                    Импортировать все фото
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let selectedFiles = [];
let googlePhotosData = null;
const restaurantId = <?= $restaurant['id'] ?>;

// Инициализация drag & drop
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const photoInput = document.getElementById('photoInput');
    
    // Drag & Drop события
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        
        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });
    
    // Клик по зоне загрузки
    uploadZone.addEventListener('click', function(e) {
        if (e.target === uploadZone || e.target.closest('.upload-zone')) {
            photoInput.click();
        }
    });
    
    // Выбор файлов через input
    photoInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        handleFiles(files);
    });
    
    // Обработка формы загрузки
    document.getElementById('photoUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        uploadPhotos();
    });
});

// Обработка выбранных файлов
function handleFiles(files) {
    const imageFiles = files.filter(file => file.type.startsWith('image/'));
    
    if (imageFiles.length === 0) {
        showNotification('Пожалуйста, выберите изображения', 'warning');
        return;
    }
    
    selectedFiles = imageFiles;
    showPreview();
}

// Показать превью выбранных файлов
function showPreview() {
    const previewContainer = document.getElementById('previewContainer');
    previewContainer.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-md-3 mb-3';
            col.innerHTML = `
                <div class="card">
                    <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                    <div class="card-body p-2">
                        <small class="text-muted">${file.name}</small>
                        <button type="button" class="btn btn-sm btn-danger float-end" onclick="removeFile(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            previewContainer.appendChild(col);
        };
        reader.readAsDataURL(file);
    });
    
    document.getElementById('uploadPreview').style.display = 'block';
    document.getElementById('uploadActions').style.display = 'block';
}

// Удалить файл из превью
function removeFile(index) {
    selectedFiles.splice(index, 1);
    if (selectedFiles.length === 0) {
        clearUpload();
    } else {
        showPreview();
    }
}

// Очистить загрузку
function clearUpload() {
    selectedFiles = [];
    document.getElementById('uploadPreview').style.display = 'none';
    document.getElementById('uploadActions').style.display = 'none';
    document.getElementById('photoInput').value = '';
}

// Загрузить фотографии
async function uploadPhotos() {
    if (selectedFiles.length === 0) return;
    
    const formData = new FormData();
    selectedFiles.forEach(file => {
        formData.append('photos[]', file);
    });
    
    const uploadBtn = document.getElementById('uploadBtn');
    const progressBar = document.getElementById('uploadProgress');
    
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Загрузка...';
    progressBar.style.display = 'block';
    
    try {
        const response = await fetch(`<?= base_url('admin/restaurants') ?>/${restaurantId}/photos`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Фотографии успешно загружены!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('Ошибка загрузки: ' + (result.message || 'Неизвестная ошибка'), 'danger');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка загрузки фотографий', 'danger');
    } finally {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Загрузить фотографии';
        progressBar.style.display = 'none';
    }
}


// Google Photos функции
async function previewGooglePhotos() {
    const btn = document.getElementById('previewBtn');
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Загрузка...';
    
    try {
        // Используем правильный URL
        const response = await fetch(`<?= base_url('admin/google-photos/preview-photos/') ?>${restaurantId}`);
        const result = await response.json();
        
        if (result.success) {
            googlePhotosData = result;
            showGooglePhotosModal(result);
            showGooglePreview(result);
        } else {
            showNotification(result.message || 'Ошибка получения превью', 'warning');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка запроса к Google API', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

// Функция для показа превью в боковом блоке (если её нет)
function showGooglePreview(data) {
    const container = document.getElementById('googlePreviewContainer');
    const grid = document.getElementById('googlePreviewGrid');
    
    if (!grid) return; // Если элемент не найден
    
    grid.innerHTML = '';
    
    data.previews.slice(0, 4).forEach(preview => {
        const item = document.createElement('div');
        item.className = 'preview-item';
        item.innerHTML = `<img src="${preview.url}" alt="Google Photo" loading="lazy">`;
        grid.appendChild(item);
    });
    
    container.style.display = 'block';
}

// Функция для показа модального окна с превью (если её нет)
function showGooglePhotosModal(data) {
    const modalContent = document.getElementById('googleModalContent');
    
    let html = `
        <div class="mb-3">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Найдено <strong>${data.total_photos}</strong> фотографий в Google Places.
                Показаны первые ${data.previews.length}.
            </div>
        </div>
        <div class="row">
    `;
    
    data.previews.forEach((preview, index) => {
        html += `
            <div class="col-md-4 col-lg-3 mb-3">
                <div class="card">
                    <img src="${preview.url}" class="card-img-top" 
                         style="height: 200px; object-fit: cover;" 
                         alt="Google Photo ${index + 1}" loading="lazy">
                    <div class="card-body p-2">
                        <small class="text-muted">${preview.width}×${preview.height}</small>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    modalContent.innerHTML = html;
    
    const modal = new bootstrap.Modal(document.getElementById('googlePhotosModal'));
    modal.show();
}

async function importGooglePhotos() {
    const btn = document.getElementById('importBtn');
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Импорт...';
    
    try {
        // Используем правильный URL
        const response = await fetch(`<?= base_url('admin/google-photos/import-photos/') ?>${restaurantId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ max_photos: 5 })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(`Успешно импортировано ${result.imported_count} фотографий!`, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showNotification(result.message || 'Не удалось импортировать фотографии', 'warning');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка импорта фотографий', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

function importFromModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('googlePhotosModal'));
    if (modal) modal.hide();
    
    importGooglePhotos();
}

// Также исправляем функцию setMainPhoto:
async function setMainPhoto(photoId) {
    if (!confirm('Сделать это фото главным?')) return;
    
    try {
        // ИСПРАВЛЕНО: используем правильный URL согласно маршрутам  
        const response = await fetch(`<?= base_url('admin/restaurants/photos') ?>/${photoId}/set-main`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Главное фото установлено!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Ошибка: ' + (result.message || 'Не удалось установить главное фото'), 'danger');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка при установке главного фото: ' + error.message, 'danger');
    }
}

// Удалить фото
async function deletePhoto(photoId) {
    if (!confirm('Удалить это фото? Действие нельзя будет отменить.')) return;
    
    try {
        // ИСПРАВЛЕНО: используем правильный URL согласно маршрутам
        const response = await fetch(`<?= base_url('admin/restaurants/photos') ?>/${photoId}/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Удалить элемент из DOM с анимацией
            const photoElement = document.querySelector(`[data-photo-id="${photoId}"]`);
            if (photoElement) {
                // Добавляем анимацию исчезновения
                photoElement.style.transition = 'all 0.3s ease';
                photoElement.style.opacity = '0';
                photoElement.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    photoElement.remove();
                    
                    // Проверяем, остались ли фотографии
                    const remainingPhotos = document.querySelectorAll('.photo-item');
                    if (remainingPhotos.length === 0) {
                        // Показываем сообщение "нет фотографий"
                        const galleryContainer = document.querySelector('.photo-gallery').parentElement;
                        galleryContainer.innerHTML = `
                            <div class="text-center py-5">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Нет загруженных фотографий</h5>
                                <p class="text-muted">
                                    Загрузите первую фотографию для этого ресторана
                                    <?php if (!empty($restaurant['google_place_id'])): ?>
                                        или импортируйте из Google Places
                                    <?php endif; ?>
                                </p>
                            </div>
                        `;
                    }
                }, 300);
            }
            
            showNotification('Фото успешно удалено', 'success');
        } else {
            showNotification('Ошибка: ' + (result.message || 'Не удалось удалить фото'), 'danger');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка при удалении фото: ' + error.message, 'danger');
    }
}

// Просмотр фото в модальном окне
function viewPhoto(photoUrl) {
    document.getElementById('modalImage').src = photoUrl;
    const modal = new bootstrap.Modal(document.getElementById('photoModal'));
    modal.show();
}

// Показать уведомление
function showNotification(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'danger': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const iconClass = {
        'success': 'fas fa-check-circle',
        'danger': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    }[type] || 'fas fa-info-circle';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        <i class="${iconClass} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Автоматически скрыть через 5 секунд
    setTimeout(() => {
        if (alertDiv && alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
<?= $this->endSection() ?>