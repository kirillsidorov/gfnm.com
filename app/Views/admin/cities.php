<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-city me-2"></i>Управление городами
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <!-- Add New City -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus"></i> Add New City</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= current_url() ?>" id="cityForm">
                    <input type="hidden" id="edit_city_id" name="edit_city_id" value="">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">City Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="e.g., New York" required>
                    </div>

                    <div class="mb-3">
                        <label for="state" class="form-label">State/Province</label>
                        <input type="text" class="form-control" id="state" name="state" 
                               placeholder="e.g., NY">
                    </div>

                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <select class="form-select" id="country" name="country">
                            <option value="USA" selected>United States</option>
                            <option value="Canada">Canada</option>
                            <option value="UK">United Kingdom</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="Georgia">Georgia (Country)</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- NEW: Slug field -->
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug *</label>
                        <input type="text" class="form-control" id="slug" name="slug" 
                               placeholder="e.g., manhattan" required>
                        <div class="form-text">
                            URL-friendly version (lowercase, hyphens only)
                            <br><small class="text-muted">Preview: <?= base_url() ?>georgian-restaurants-<span id="slugPreview">slug</span></small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" id="submitBtn">
                            <i class="fas fa-plus"></i> <span id="submitText">Add City</span>
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelEdit" style="display: none;">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> City Guidelines</h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <ul class="mb-0 ps-3">
                        <li>Use official city names</li>
                        <li>For US cities, include state abbreviation</li>
                        <li>Examples: "Manhattan", "Brooklyn", "Chicago"</li>
                        <li><strong>Slug must be unique</strong> and URL-friendly</li>
                        <li>Good slugs: "manhattan", "new-york", "los-angeles"</li>
                    </ul>
                </small>
            </div>
        </div>
    </div>

    <!-- Cities List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list"></i> Existing Cities</h5>
                <button type="button" class="btn btn-sm btn-warning" id="generateMissingSlugs">
                    <i class="fas fa-magic"></i> Generate Missing Slugs
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($cities)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>City</th>
                                    <th>State/Province</th>
                                    <th>Country</th>
                                    <th>Restaurants</th>
                                    <th>Slug</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cities as $city): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($city['name']) ?></strong>
                                        </td>
                                        <td>
                                            <?= esc($city['state']) ?: '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= esc($city['country']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($city['restaurant_count'] > 0): ?>
                                                <a href="<?= base_url('admin/restaurants?city=' . $city['id']) ?>" 
                                                   class="badge bg-primary text-decoration-none">
                                                    <?= $city['restaurant_count'] ?> restaurants
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No restaurants</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($city['slug'])): ?>
                                                <code class="text-success"><?= esc($city['slug']) ?></code>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Missing</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <!-- Edit button -->
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editCity(<?= htmlspecialchars(json_encode($city)) ?>)" 
                                                        title="Edit City">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <?php if (!empty($city['slug'])): ?>
                                                    <a href="<?= base_url('georgian-restaurants-' . $city['slug']) ?>" 
                                                       class="btn btn-outline-info" title="View City Page" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="<?= base_url('admin/geocode') ?>" 
                                                   class="btn btn-outline-warning" title="Geocode">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </a>

                                                <?php if ($city['restaurant_count'] == 0): ?>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteCity(<?= $city['id'] ?>)" title="Delete City">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-city fa-3x text-muted mb-3"></i>
                        <h5>No cities found</h5>
                        <p class="text-muted">Add your first city using the form on the left.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Popular Cities -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-star"></i> Quick Add Popular Cities</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Click to quickly add common cities where Georgian restaurants are found:</p>
                
                <div class="d-flex flex-wrap gap-2">
                    <?php 
                    $popularCities = [
                        ['name' => 'Manhattan', 'state' => 'NY', 'country' => 'USA', 'slug' => 'manhattan'],
                        ['name' => 'Brooklyn', 'state' => 'NY', 'country' => 'USA', 'slug' => 'brooklyn'],
                        ['name' => 'Queens', 'state' => 'NY', 'country' => 'USA', 'slug' => 'queens'],
                        ['name' => 'Chicago', 'state' => 'IL', 'country' => 'USA', 'slug' => 'chicago'],
                        ['name' => 'Los Angeles', 'state' => 'CA', 'country' => 'USA', 'slug' => 'los-angeles'],
                        ['name' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'slug' => 'san-francisco'],
                        ['name' => 'Washington', 'state' => 'DC', 'country' => 'USA', 'slug' => 'washington-dc'],
                        ['name' => 'Miami', 'state' => 'FL', 'country' => 'USA', 'slug' => 'miami'],
                        ['name' => 'Atlanta', 'state' => 'GA', 'country' => 'USA', 'slug' => 'atlanta'],
                        ['name' => 'Toronto', 'state' => 'ON', 'country' => 'Canada', 'slug' => 'toronto'],
                        ['name' => 'London', 'state' => '', 'country' => 'UK', 'slug' => 'london'],
                        ['name' => 'Tbilisi', 'state' => '', 'country' => 'Georgia', 'slug' => 'tbilisi']
                    ];
                    
                    // Получаем уже существующие города
                    $existingCities = array_column($cities, 'name');
                    ?>
                    
                    <?php foreach ($popularCities as $popular): ?>
                        <?php if (!in_array($popular['name'], $existingCities)): ?>
                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                    onclick="addQuickCity('<?= esc($popular['name']) ?>', '<?= esc($popular['state']) ?>', '<?= esc($popular['country']) ?>', '<?= esc($popular['slug']) ?>')">
                                <?= esc($popular['name']) ?>
                                <?= $popular['state'] ? ', ' . esc($popular['state']) : '' ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const baseUrl = '<?= base_url() ?>';

// Quick add city function
function addQuickCity(name, state, country, slug) {
    document.getElementById('name').value = name;
    document.getElementById('state').value = state;
    document.getElementById('country').value = country;
    document.getElementById('slug').value = slug;
    
    updateSlugPreview();
    
    // Scroll to form
    document.getElementById('name').scrollIntoView({ behavior: 'smooth' });
    document.getElementById('name').focus();
}

// Edit city function
function editCity(city) {
    document.getElementById('edit_city_id').value = city.id;
    document.getElementById('name').value = city.name;
    document.getElementById('state').value = city.state || '';
    document.getElementById('country').value = city.country || 'USA';
    document.getElementById('slug').value = city.slug || '';
    
    // Update UI for editing
    document.getElementById('submitText').textContent = 'Update City';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> <span id="submitText">Update City</span>';
    document.getElementById('cancelEdit').style.display = 'block';
    
    updateSlugPreview();
    
    // Scroll to form
    document.getElementById('name').scrollIntoView({ behavior: 'smooth' });
    document.getElementById('name').focus();
}

// Cancel edit function
document.getElementById('cancelEdit').addEventListener('click', function() {
    resetForm();
});

function resetForm() {
    document.getElementById('cityForm').reset();
    document.getElementById('edit_city_id').value = '';
    document.getElementById('submitText').textContent = 'Add City';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus"></i> <span id="submitText">Add City</span>';
    document.getElementById('cancelEdit').style.display = 'none';
    document.getElementById('country').value = 'USA'; // Reset to default
    updateSlugPreview();
}

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function(e) {
    // Only auto-generate if not editing or if slug is empty
    const editId = document.getElementById('edit_city_id').value;
    const currentSlug = document.getElementById('slug').value;
    
    if (!editId || !currentSlug) {
        const name = e.target.value;
        const slug = generateSlug(name);
        document.getElementById('slug').value = slug;
    }
    
    updateSlugPreview();
});

// Manual slug editing with live validation
document.getElementById('slug').addEventListener('input', function(e) {
    const originalValue = e.target.value;
    const cleanValue = generateSlug(originalValue);
    
    if (originalValue !== cleanValue) {
        e.target.value = cleanValue;
    }
    
    updateSlugPreview();
    
    // Live availability check
    if (cleanValue.length >= 2) {
        checkSlugAvailability(cleanValue);
    } else {
        clearSlugValidation();
    }
});

// Check slug availability
function checkSlugAvailability(slug) {
    const editId = document.getElementById('edit_city_id').value;
    
    fetch(`${baseUrl}admin/cities/check-slug-availability`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `slug=${encodeURIComponent(slug)}&exclude_id=${editId}`
    })
    .then(response => response.json())
    .then(data => {
        showSlugValidation(data.available, data.message);
    })
    .catch(error => {
        console.error('Slug check error:', error);
    });
}

// Show slug validation message
function showSlugValidation(isAvailable, message) {
    const slugField = document.getElementById('slug');
    let messageDiv = document.querySelector('.slug-validation');
    
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.className = 'slug-validation form-text';
        slugField.parentNode.appendChild(messageDiv);
    }
    
    if (isAvailable) {
        messageDiv.className = 'slug-validation form-text text-success';
        messageDiv.innerHTML = `<i class="fas fa-check"></i> ${message}`;
        slugField.classList.remove('is-invalid');
        slugField.classList.add('is-valid');
    } else {
        messageDiv.className = 'slug-validation form-text text-danger';
        messageDiv.innerHTML = `<i class="fas fa-times"></i> ${message}`;
        slugField.classList.remove('is-valid');
        slugField.classList.add('is-invalid');
    }
}

// Clear slug validation
function clearSlugValidation() {
    const slugField = document.getElementById('slug');
    const messageDiv = document.querySelector('.slug-validation');
    
    if (messageDiv) {
        messageDiv.remove();
    }
    
    slugField.classList.remove('is-valid', 'is-invalid');
}

function generateSlug(text) {
    return text.toLowerCase()
               .replace(/[^a-z0-9 -]/g, '')
               .replace(/\s+/g, '-')
               .replace(/-+/g, '-')
               .replace(/^-|-$/g, '');
}

function updateSlugPreview() {
    const slug = document.getElementById('slug').value;
    document.getElementById('slugPreview').textContent = slug || 'slug';
}

// Delete city function
function deleteCity(cityId) {
    if (confirm('Are you sure you want to delete this city? This action cannot be undone.')) {
        fetch(`${baseUrl}admin/cities/delete/${cityId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting city: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('Error deleting city');
        });
    }
}

// Generate missing slugs
document.getElementById('generateMissingSlugs').addEventListener('click', function() {
    if (confirm('Generate slugs for cities that don\'t have them?')) {
        fetch(`${baseUrl}admin/cities/generate-slugs`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Generated ${data.updated} slugs`);
                location.reload();
            } else {
                alert('Error generating slugs: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Generate slugs error:', error);
            alert('Error generating slugs');
        });
    }
});

// Form submission
document.getElementById('cityForm').addEventListener('submit', function(e) {
    const slug = document.getElementById('slug').value;
    
    // Validate slug
    if (!slug || slug.length < 2) {
        e.preventDefault();
        alert('Please enter a valid slug (at least 2 characters)');
        document.getElementById('slug').focus();
        return false;
    }
    
    if (!/^[a-z0-9-]+$/.test(slug)) {
        e.preventDefault();
        alert('Slug can only contain lowercase letters, numbers, and hyphens');
        document.getElementById('slug').focus();
        return false;
    }
});

// Initialize
updateSlugPreview();
</script>
<?= $this->endSection() ?>