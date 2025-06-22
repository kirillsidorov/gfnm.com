<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-memory me-2 text-info"></i>
                Cache Management
            </h1>
            <p class="text-muted mb-0">Manage system cache and performance</p>
        </div>
        <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <div class="row">
        <!-- Cache Actions -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Cache Actions
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Clear All Cache -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Clear All Cache</h6>
                        <p class="text-muted small mb-3">
                            Clears all cached data including restaurant listings, city pages, and search results.
                            Use this after making changes to restaurant verification status.
                        </p>
                        <button type="button" class="btn btn-warning" id="clearAllCache">
                            <i class="fas fa-trash-alt me-1"></i>Clear All Cache
                        </button>
                    </div>

                    <hr>

                    <!-- Quick Actions -->
                    <div class="mb-3">
                        <h6 class="fw-bold">Quick Actions</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="clearRestaurantCache()">
                                <i class="fas fa-utensils me-1"></i>Restaurant Cache
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="clearCityCache()">
                                <i class="fas fa-city me-1"></i>City Cache
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="clearSearchCache()">
                                <i class="fas fa-search me-1"></i>Search Cache
                            </button>
                        </div>
                    </div>

                    <!-- Warning -->
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Clearing cache may temporarily slow down the website while cache rebuilds.
                    </div>
                </div>
            </div>
        </div>

        <!-- Cache Statistics -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Cache Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($cache_info)): ?>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="border rounded p-3 text-center">
                                    <div class="h4 text-primary mb-1">
                                        <?= $cache_info['total_files'] ?? 0 ?>
                                    </div>
                                    <small class="text-muted">Cache Files</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 text-center">
                                    <div class="h4 text-success mb-1">
                                        <?= $cache_info['total_size_formatted'] ?? '0 B' ?>
                                    </div>
                                    <small class="text-muted">Total Size</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="small">
                            <div class="mb-2">
                                <strong>Cache Driver:</strong> 
                                <span class="badge bg-info"><?= $cache_info['cache_driver'] ?? 'file' ?></span>
                            </div>
                            <div class="mb-2">
                                <strong>Cache Path:</strong> 
                                <code class="small"><?= $cache_info['cache_path'] ?? 'N/A' ?></code>
                            </div>
                            <div class="mb-2">
                                <strong>Status:</strong> 
                                <?php if ($cache_info['cache_enabled'] ?? false): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Disabled</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Cache Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div id="cacheActivity">
                        <p class="text-muted text-center">No recent cache activity</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="cacheMessages"></div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clear All Cache button
    document.getElementById('clearAllCache').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all cache? This may temporarily slow down the website.')) {
            clearCache();
        }
    });
});

function clearCache() {
    const button = document.getElementById('clearAllCache');
    const originalText = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Clearing...';
    
    fetch('<?= base_url('admin/clear-cache') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('success', data.message);
            addCacheActivity('All cache cleared', 'success');
            
            // Refresh page after 2 seconds to update stats
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showMessage('danger', data.message);
            addCacheActivity('Cache clear failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('danger', 'Error clearing cache: ' + error.message);
        addCacheActivity('Cache clear error: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function clearRestaurantCache() {
    showMessage('info', 'Restaurant cache clearing feature coming soon!');
}

function clearCityCache() {
    showMessage('info', 'City cache clearing feature coming soon!');
}

function clearSearchCache() {
    showMessage('info', 'Search cache clearing feature coming soon!');
}

function showMessage(type, message) {
    const messagesContainer = document.getElementById('cacheMessages');
    const alertClass = `alert-${type}`;
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        <i class="fas fa-info-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    messagesContainer.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

function addCacheActivity(action, type) {
    const activityContainer = document.getElementById('cacheActivity');
    const timestamp = new Date().toLocaleString();
    const iconClass = type === 'success' ? 'check-circle text-success' : 'exclamation-circle text-danger';
    
    // Clear "no activity" message
    if (activityContainer.querySelector('p.text-muted')) {
        activityContainer.innerHTML = '';
    }
    
    const activityItem = document.createElement('div');
    activityItem.className = 'border-bottom pb-2 mb-2 small';
    activityItem.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <i class="fas fa-${iconClass} me-1"></i>
                ${action}
            </div>
            <small class="text-muted">${timestamp}</small>
        </div>
    `;
    
    activityContainer.insertBefore(activityItem, activityContainer.firstChild);
    
    // Keep only last 5 activities
    const activities = activityContainer.querySelectorAll('div.border-bottom');
    if (activities.length > 5) {
        activities[5].remove();
    }
}
</script>
<?= $this->endSection() ?>