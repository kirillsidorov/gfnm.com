<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-edit me-2"></i>Редактирование ресторана
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0"><?= esc($restaurant['name']) ?></h3>
        <small class="text-muted">ID: <?= $restaurant['id'] ?></small>
    </div>
    <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Restaurants
    </a>
</div>

<!-- Edit Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Restaurant Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= current_url() ?>">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Restaurant Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= esc($restaurant['name']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="city_id" class="form-label">City *</label>
                            <select class="form-select" id="city_id" name="city_id" required>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= $city['id'] ?>" 
                                            <?= ($city['id'] == $restaurant['city_id']) ? 'selected' : '' ?>>
                                        <?= esc($city['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- NEW: SEO Fields Section -->
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning bg-opacity-10">
                            <h6 class="mb-0"><i class="fas fa-search"></i> SEO Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="slug" class="form-label">Slug *</label>
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="<?= esc($restaurant['slug']) ?>" required>
                                    <div class="form-text">
                                        URL-friendly version of name (e.g., "aragvi-restaurant")
                                        <br><small class="text-muted">Used in URLs: /restaurant/<strong>slug</strong></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="seo_url" class="form-label">SEO URL</label>
                                    <input type="text" class="form-control" id="seo_url" name="seo_url" 
                                           value="<?= esc($restaurant['seo_url']) ?>">
                                    <div class="form-text">
                                        Full SEO URL path (e.g., "aragvi-restaurant-manhattan")
                                        <br><small class="text-muted">Used for: <?= base_url() ?>/<strong>seo_url</strong></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mb-0">
                                <small>
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>SEO URL Structure:</strong> Usually follows the pattern "restaurant-name-city" 
                                    (e.g., "aragvi-restaurant-manhattan"). This creates user-friendly URLs that are better for SEO.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" 
                               value="<?= esc($restaurant['address']) ?>">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?= esc($restaurant['phone']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="website" name="website" 
                                   value="<?= esc($restaurant['website']) ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="rating" class="form-label">Rating</label>
                            <input type="number" class="form-control" id="rating" name="rating" 
                                   min="0" max="5" step="0.1" value="<?= $restaurant['rating'] ?>">
                            <div class="form-text">Rating from 0.0 to 5.0</div>
                        </div>
                        <div class="col-md-4">
                            <label for="price_level" class="form-label">Price Level</label>
                            <select class="form-select" id="price_level" name="price_level">
                                <option value="0" <?= ($restaurant['price_level'] == 0) ? 'selected' : '' ?>>Not specified</option>
                                <option value="1" <?= ($restaurant['price_level'] == 1) ? 'selected' : '' ?>>$ (Budget)</option>
                                <option value="2" <?= ($restaurant['price_level'] == 2) ? 'selected' : '' ?>>$$ (Moderate)</option>
                                <option value="3" <?= ($restaurant['price_level'] == 3) ? 'selected' : '' ?>>$$$ (Expensive)</option>
                                <option value="4" <?= ($restaurant['price_level'] == 4) ? 'selected' : '' ?>>$$$$ (Very Expensive)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1" <?= ($restaurant['is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($restaurant['is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?= esc($restaurant['description']) ?></textarea>
                        <div class="form-text">Brief description of the restaurant and its cuisine</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="button" class="btn btn-outline-info" id="generateSeoUrl">
                            <i class="fas fa-magic"></i> Generate SEO URL
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restaurant Info Sidebar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Restaurant Info</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td><?= $restaurant['id'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Current Slug:</strong></td>
                        <td><code><?= esc($restaurant['slug']) ?></code></td>
                    </tr>
                    <?php if (!empty($restaurant['seo_url'])): ?>
                    <tr>
                        <td><strong>Current SEO URL:</strong></td>
                        <td>
                            <a href="<?= base_url($restaurant['seo_url']) ?>" target="_blank" class="text-decoration-none">
                                <code><?= esc($restaurant['seo_url']) ?></code>
                                <i class="fas fa-external-link-alt small"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['google_place_id'])): ?>
                    <tr>
                        <td><strong>Google Place ID:</strong></td>
                        <td><code><?= esc($restaurant['google_place_id']) ?></code></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td><?= date('M j, Y H:i', strtotime($restaurant['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td><?= date('M j, Y H:i', strtotime($restaurant['updated_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- URL Preview -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-link"></i> URL Preview</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Restaurant page URL:</small>
                    <div class="bg-light p-2 rounded">
                        <code id="restaurant-url-preview"><?= base_url('restaurant/' . $restaurant['slug']) ?></code>
                    </div>
                </div>
                <div id="seo-url-preview-container" <?= empty($restaurant['seo_url']) ? 'style="display:none"' : '' ?>>
                    <small class="text-muted">SEO URL:</small>
                    <div class="bg-light p-2 rounded">
                        <code id="seo-url-preview"><?= base_url($restaurant['seo_url']) ?></code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('admin/restaurants/' . $restaurant['id'] . '/photos') ?>" 
                       class="btn btn-outline-info btn-sm">
                        <i class="fas fa-camera"></i> Manage Photos
                    </a>
                    
                    <?php if (!empty($restaurant['seo_url'])): ?>
                        <a href="<?= base_url($restaurant['seo_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye"></i> View on Site (SEO URL)
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('restaurant/' . $restaurant['slug']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye"></i> View on Site (Standard)
                    </a>
                    
                    <?php if (!empty($restaurant['website'])): ?>
                        <a href="<?= esc($restaurant['website']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-globe"></i> Visit Website
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($restaurant['google_place_id'])): ?>
                        <a href="https://www.google.com/maps/place/?q=place_id:<?= esc($restaurant['google_place_id']) ?>" 
                           target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-map-marker-alt"></i> View on Google Maps
                        </a>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <a href="<?= base_url('admin/restaurants/delete/' . $restaurant['id']) ?>" 
                       class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this restaurant? This action cannot be undone.')">
                        <i class="fas fa-trash"></i> Delete Restaurant
                    </a>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb"></i> SEO Tips</h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <ul class="mb-0 ps-3">
                        <li><strong>Slug:</strong> Keep it short and descriptive (e.g., "aragvi-manhattan")</li>
                        <li><strong>SEO URL:</strong> Follow pattern "restaurant-name-city" for best SEO</li>
                        <li>Use hyphens, not underscores or spaces</li>
                        <li>Keep URLs under 60 characters when possible</li>
                        <li>Avoid special characters and numbers</li>
                        <li>Make URLs readable and memorable</li>
                    </ul>
                </small>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SEO Helper utilities -->
<script src="<?= base_url('assets/js/admin/seo-helper.js') ?>"></script>

<script>
// Конфигурация для текущей страницы
const pageConfig = {
    restaurantId: <?= $restaurant['id'] ?>,
    baseUrl: '<?= base_url() ?>',
    cities: <?= json_encode($cities) ?>
};

// Auto-format phone number
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 6) {
        value = `(${value.substring(0,3)}) ${value.substring(3,6)}-${value.substring(6,10)}`;
    } else if (value.length >= 3) {
        value = `(${value.substring(0,3)}) ${value.substring(3)}`;
    }
    e.target.value = value;
});

// Validate rating
document.getElementById('rating').addEventListener('input', function(e) {
    const value = parseFloat(e.target.value);
    if (value > 5) e.target.value = 5;
    if (value < 0) e.target.value = 0;
});

// Auto-update description based on name
document.getElementById('name').addEventListener('input', function(e) {
    const description = document.getElementById('description');
    if (!description.value.trim()) {
        const name = e.target.value;
        if (name) {
            description.value = `Authentic Georgian restaurant ${name} serving traditional Georgian dishes like khachapuri, khinkali, and other delicious Georgian cuisine.`;
        }
    }
});

// Основные переменные для SEO функций
const baseUrl = pageConfig.baseUrl;
const restaurantId = pageConfig.restaurantId;
let slugCheckTimeout;
let seoUrlCheckTimeout;

// Slug generation and validation
function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-') // Replace spaces with hyphens
        .replace(/-+/g, '-') // Replace multiple hyphens with single
        .replace(/^-|-$/g, ''); // Remove leading/trailing hyphens
}

// Auto-generate slug from name (без проверки уникальности)
document.getElementById('name').addEventListener('input', function(e) {
    const slugField = document.getElementById('slug');
    if (!slugField.dataset.manual) {
        slugField.value = generateSlug(e.target.value);
        updateUrlPreviews();
        // Убрали checkSlugAvailability - slug уникальность не проверяем
    }
});

// Mark slug as manually edited (без проверки уникальности)
document.getElementById('slug').addEventListener('input', function(e) {
    e.target.dataset.manual = 'true';
    const newSlug = generateSlug(e.target.value);
    e.target.value = newSlug;
    updateUrlPreviews();
    
    // Убрали проверку уникальности slug
});

// Auto-generate SEO URL
document.getElementById('generateSeoUrl').addEventListener('click', function() {
    const name = document.getElementById('name').value;
    const citySelect = document.getElementById('city_id');
    const cityName = citySelect.options[citySelect.selectedIndex].text;
    
    if (name && cityName) {
        const restaurantSlug = generateSlug(name);
        const citySlug = generateSlug(cityName);
        const seoUrl = `${restaurantSlug}-restaurant-${citySlug}`;
        
        document.getElementById('seo_url').value = seoUrl;
        updateUrlPreviews();
        checkSeoUrlAvailability(seoUrl);
    } else {
        showAlert('Please fill in restaurant name and select city first', 'warning');
    }
});

// Update SEO URL on manual input
document.getElementById('seo_url').addEventListener('input', function(e) {
    const newSeoUrl = generateSlug(e.target.value);
    e.target.value = newSeoUrl;
    updateUrlPreviews();
    
    // Debounced availability check
    clearTimeout(seoUrlCheckTimeout);
    if (newSeoUrl) {
        seoUrlCheckTimeout = setTimeout(() => {
            checkSeoUrlAvailability(newSeoUrl);
        }, 500);
    } else {
        clearValidationMessage('seo_url');
    }
});

// Update URL previews
function updateUrlPreviews() {
    const slug = document.getElementById('slug').value;
    const seoUrl = document.getElementById('seo_url').value;
    
    // Update restaurant URL preview
    document.getElementById('restaurant-url-preview').textContent = `${baseUrl}restaurant/${slug}`;
    
    // Update SEO URL preview
    const seoUrlContainer = document.getElementById('seo-url-preview-container');
    const seoUrlPreview = document.getElementById('seo-url-preview');
    
    if (seoUrl) {
        seoUrlPreview.textContent = `${baseUrl}${seoUrl}`;
        seoUrlContainer.style.display = 'block';
    } else {
        seoUrlContainer.style.display = 'none';
    }
}

// Убираем функцию checkSlugAvailability - не нужна

// Check SEO URL availability via AJAX (оставляем только эту проверку)
function checkSeoUrlAvailability(seoUrl) {
    if (!seoUrl) {
        clearValidationMessage('seo_url');
        return;
    }
    
    showValidationMessage('seo_url', 'Checking availability...', 'info');
    
    fetch(`${baseUrl}admin/restaurants/check-seo-url-availability`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `seo_url=${encodeURIComponent(seoUrl)}&exclude_id=${restaurantId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.available) {
            showValidationMessage('seo_url', data.message, 'success');
        } else {
            showValidationMessage('seo_url', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error checking SEO URL availability:', error);
        showValidationMessage('seo_url', 'Error checking availability', 'error');
    });
}

// Show validation message
function showValidationMessage(fieldId, message, type) {
    const field = document.getElementById(fieldId);
    let messageDiv = field.parentNode.querySelector('.validation-message');
    
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.className = 'validation-message form-text';
        field.parentNode.appendChild(messageDiv);
    }
    
    // Remove existing classes
    messageDiv.className = 'validation-message form-text';
    
    // Add type-specific class
    switch (type) {
        case 'success':
            messageDiv.classList.add('text-success');
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            break;
        case 'error':
            messageDiv.classList.add('text-danger');
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            break;
        case 'info':
            messageDiv.classList.add('text-info');
            field.classList.remove('is-valid', 'is-invalid');
            break;
        default:
            field.classList.remove('is-valid', 'is-invalid');
    }
    
    messageDiv.textContent = message;
}

// Clear validation message
function clearValidationMessage(fieldId) {
    const field = document.getElementById(fieldId);
    const messageDiv = field.parentNode.querySelector('.validation-message');
    
    if (messageDiv) {
        messageDiv.remove();
    }
    
    field.classList.remove('is-valid', 'is-invalid');
}

// Show alert message
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of form
    const form = document.querySelector('form');
    form.insertBefore(alertDiv, form.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Form submission validation (убираем проверку slug)
document.querySelector('form').addEventListener('submit', function(e) {
    const seoUrlField = document.getElementById('seo_url');
    
    // Проверяем только SEO URL на ошибки валидации
    if (seoUrlField.classList.contains('is-invalid')) {
        e.preventDefault();
        showAlert('Please fix SEO URL validation errors before submitting', 'danger');
        return false;
    }
});

// Initialize URL previews
updateUrlPreviews();

// City change handler for auto-generating SEO URL
document.getElementById('city_id').addEventListener('change', function() {
    const seoUrlField = document.getElementById('seo_url');
    if (!seoUrlField.dataset.manual) {
        // Auto-regenerate SEO URL if it wasn't manually set
        document.getElementById('generateSeoUrl').click();
    }
});

// Initialize validation for existing values (только SEO URL)
document.addEventListener('DOMContentLoaded', function() {
    const seoUrlField = document.getElementById('seo_url');
    
    // Проверяем только SEO URL при загрузке
    if (seoUrlField.value) {
        checkSeoUrlAvailability(seoUrlField.value);
    }
});
</script>
<?= $this->endSection() ?>