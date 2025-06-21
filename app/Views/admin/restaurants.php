<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-utensils me-2"></i>Управление ресторанами
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= current_url() ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search restaurants..." value="<?= esc($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="city">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= $city['id'] ?>" <?= ($filters['city_id'] == $city['id']) ? 'selected' : '' ?>>
                                <?= esc($city['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('admin/restaurants') ?>" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="<?= base_url('admin/restaurants/bulk') ?>" id="bulkForm">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <select class="form-select" name="action" required>
                        <option value="">Select Action</option>
                        <option value="activate">Activate Selected</option>
                        <option value="deactivate">Deactivate Selected</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning w-100" onclick="return confirmBulk()">
                        <i class="fas fa-bolt"></i> Apply to Selected
                    </button>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">
                        Select restaurants using checkboxes, then choose an action above.
                    </small>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Restaurants Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($restaurants)): ?>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Restaurant</th>
                            <th>City</th>
                            <th>Rating</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Added</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($restaurants as $restaurant): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="restaurant_ids[]" 
                                           value="<?= $restaurant['id'] ?>" 
                                           class="form-check-input restaurant-checkbox"
                                           form="bulkForm">
                                </td>
                                <td>
                                    <div>
                                        <strong><?= esc($restaurant['name']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= character_limiter(esc($restaurant['address']), 50) ?>
                                        </small>
                                    </div>
                                </td>
                                <td><?= esc($restaurant['city_name']) ?></td>
                                <td>
                                    <?php if ($restaurant['rating']): ?>
                                        <span class="badge bg-warning">
                                            <?= number_format($restaurant['rating'], 1) ?> ⭐
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($restaurant['price_level']): ?>
                                        <?php for ($i = 0; $i < $restaurant['price_level']; $i++): ?>
                                            $
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($restaurant['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('M j', strtotime($restaurant['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('admin/restaurants/edit/' . $restaurant['id']) ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                       
                                        <a href="<?= base_url('admin/restaurants/' . $restaurant['id'] . '/photos') ?>" 
                                        class="btn btn-outline-info" title="Manage Photos">
                                            <i class="fas fa-images"></i>
                                        </a>
                                        
                                        <?php if (!empty($restaurant['seo_url'])): ?>
                                            <a href="<?= base_url($restaurant['seo_url']) ?>" 
                                               class="btn btn-outline-success" title="View" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?= base_url('admin/restaurants/delete/' . $restaurant['id']) ?>" 
                                           class="btn btn-outline-danger" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this restaurant?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                <h5>No restaurants found</h5>
                <p class="text-muted">Try adjusting your search filters or add some restaurants first.</p>
                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Restaurants
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.restaurant-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Bulk action confirmation
function confirmBulk() {
    const selected = document.querySelectorAll('.restaurant-checkbox:checked');
    const action = document.querySelector('select[name="action"]').value;
    
    if (selected.length === 0) {
        alert('Please select at least one restaurant.');
        return false;
    }
    
    if (action === 'delete') {
        return confirm(`Are you sure you want to delete ${selected.length} restaurant(s)? This action cannot be undone.`);
    }
    
    return confirm(`Are you sure you want to ${action} ${selected.length} restaurant(s)?`);
}
</script>
<?= $this->endSection() ?>