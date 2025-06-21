<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('page_title') ?>
<i class="fas fa-file-csv me-2"></i>Import Restaurants from CSV
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <!-- Upload Section -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-upload"></i> Upload CSV File
                </h5>
            </div>
            <div class="card-body">
                <!-- Upload Form -->
                <form id="csvUploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Select CSV File *</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" 
                               accept=".csv" required>
                        <div class="form-text">
                            Maximum file size: 5MB. Required columns: Name, Address, Rating, Place ID
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-info" id="previewBtn" disabled>
                            <i class="fas fa-eye"></i> Preview
                        </button>
                        <button type="submit" class="btn btn-primary" id="importBtn" disabled>
                            <i class="fas fa-download"></i> Import Data
                        </button>
                        <button type="button" class="btn btn-warning" id="checkDuplicatesBtn">
                            <i class="fas fa-search-plus"></i> Check Duplicates
                        </button>
                        <button type="button" class="btn btn-danger" id="fixCitiesBtn">
                            <i class="fas fa-wrench"></i> Fix Cities
                        </button>
                    </div>
                </form>

                <!-- Progress Bar -->
                <div id="progressContainer" class="mt-3" style="display: none;">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted">Processing CSV file...</small>
                </div>

                <!-- Results -->
                <div id="importResults" class="mt-3" style="display: none;"></div>
            </div>
        </div>

        <!-- Preview Section -->
        <div id="previewSection" class="card mt-3" style="display: none;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-table"></i> CSV Preview
                </h6>
            </div>
            <div class="card-body">
                <div id="previewContent"></div>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="col-lg-4">
        <!-- Instructions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle"></i> Instructions
                </h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">CSV Format Requirements:</h6>
                <ul class="list-unstyled">
                    <li><strong>Name:</strong> Restaurant name</li>
                    <li><strong>Address:</strong> Full address</li>
                    <li><strong>Rating:</strong> Decimal rating (0.0-5.0)</li>
                    <li><strong>Place ID:</strong> Google Place ID</li>
                    <li><strong>User Ratings Total:</strong> (Optional)</li>
                </ul>
                
                <hr>
                
                <h6 class="text-success">What happens during import:</h6>
                <ul class="small">
                    <li>✅ Duplicate check by Place ID</li>
                    <li>✅ Auto-detect city from address</li>
                    <li>✅ Generate unique slug</li>
                    <li>✅ Create SEO-friendly URLs</li>
                    <li>✅ Generate basic descriptions</li>
                </ul>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar"></i> Current Database
                </h6>
            </div>
            <div class="card-body">
                <div id="dbStats">
                    <div class="d-flex justify-content-between">
                        <span>Total Restaurants:</span>
                        <strong id="totalRestaurants">-</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>With Place ID:</span>
                        <strong id="withPlaceId">-</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Without Place ID:</span>
                        <strong id="withoutPlaceId">-</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sample CSV -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-download"></i> Sample CSV
                </h6>
            </div>
            <div class="card-body">
                <p class="small">Download a sample CSV file with the correct format:</p>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadSampleBtn">
                    <i class="fas fa-file-csv"></i> Download Sample CSV
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Duplicates Modal -->
<div class="modal fade" id="duplicatesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    Potential Duplicates
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="duplicatesContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const baseUrl = '<?= base_url() ?>';

document.addEventListener('DOMContentLoaded', function() {
    const csvFileInput = document.getElementById('csv_file');
    const previewBtn = document.getElementById('previewBtn');
    const importBtn = document.getElementById('importBtn');
    const checkDuplicatesBtn = document.getElementById('checkDuplicatesBtn');
    const csvUploadForm = document.getElementById('csvUploadForm');
    
    // Load database statistics
    loadDbStats();
    
    // File input change handler
    csvFileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            previewBtn.disabled = false;
            importBtn.disabled = false;
            
            // Validate file
            if (!file.name.toLowerCase().endsWith('.csv')) {
                showAlert('Please select a CSV file', 'danger');
                this.value = '';
                previewBtn.disabled = true;
                importBtn.disabled = true;
            }
        } else {
            previewBtn.disabled = true;
            importBtn.disabled = true;
        }
    });
    
    // Preview button handler
    previewBtn.addEventListener('click', function() {
        const file = csvFileInput.files[0];
        if (!file) return;
        
        const formData = new FormData();
        formData.append('csv_file', file);
        
        fetch(`${baseUrl}admin/restaurants/preview-csv`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPreview(data.preview);
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Preview error:', error);
            showAlert('Error previewing file', 'danger');
        });
    });
    
    // Form submit handler
    csvUploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const file = csvFileInput.files[0];
        if (!file) {
            showAlert('Please select a file', 'warning');
            return;
        }
        
        const formData = new FormData();
        formData.append('csv_file', file);
        
        // Show progress
        showProgress();
        
        fetch(`${baseUrl}admin/restaurants/process-csv-import`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideProgress();
            
            if (data.success) {
                showResults(data.stats);
                loadDbStats(); // Refresh statistics
                csvUploadForm.reset();
                previewBtn.disabled = true;
                importBtn.disabled = true;
                hidePreview();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            hideProgress();
            console.error('Import error:', error);
            showAlert('Import failed', 'danger');
        });
    });
    
    // Check duplicates handler
    checkDuplicatesBtn.addEventListener('click', function() {
        fetch(`${baseUrl}admin/restaurants/check-duplicates`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDuplicatesModal(data.duplicates);
            } else {
                showAlert('Error checking duplicates', 'danger');
            }
        })
        .catch(error => {
            console.error('Duplicates check error:', error);
            showAlert('Error checking duplicates', 'danger');
        });
    });
    
    // Fix cities handler
    document.getElementById('fixCitiesBtn').addEventListener('click', function() {
        if (confirm('This will attempt to fix restaurants without cities. Continue?')) {
            fetch(`${baseUrl}admin/restaurants/fix-without-city`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(`Fixed ${data.fixed} restaurants out of ${data.total_without_city}`, 'success');
                    loadDbStats(); // Refresh statistics
                } else {
                    showAlert('Error fixing cities', 'danger');
                }
            })
            .catch(error => {
                console.error('Fix cities error:', error);
                showAlert('Error fixing cities', 'danger');
            });
        }
    });
    
    // Download sample CSV
    document.getElementById('downloadSampleBtn').addEventListener('click', function() {
        downloadSampleCsv();
    });
});

function showPreview(preview) {
    const previewSection = document.getElementById('previewSection');
    const previewContent = document.getElementById('previewContent');
    
    let html = '<div class="table-responsive">';
    html += '<table class="table table-sm table-bordered">';
    
    // Header
    html += '<thead class="table-light"><tr>';
    preview.header.forEach(col => {
        html += `<th>${col}</th>`;
    });
    html += '</tr></thead>';
    
    // Rows
    html += '<tbody>';
    preview.rows.forEach(row => {
        html += '<tr>';
        row.forEach(cell => {
            html += `<td>${cell || ''}</td>`;
        });
        html += '</tr>';
    });
    html += '</tbody></table></div>';
    
    html += `<p class="text-muted small">Showing first ${preview.rows.length} rows</p>`;
    
    previewContent.innerHTML = html;
    previewSection.style.display = 'block';
}

function hidePreview() {
    document.getElementById('previewSection').style.display = 'none';
}

function showProgress() {
    document.getElementById('progressContainer').style.display = 'block';
    document.getElementById('importBtn').disabled = true;
}

function hideProgress() {
    document.getElementById('progressContainer').style.display = 'none';
    document.getElementById('importBtn').disabled = false;
}

function showResults(stats) {
    const resultsDiv = document.getElementById('importResults');
    
    let html = '<div class="alert alert-success">';
    html += '<h6><i class="fas fa-check-circle"></i> Import Completed</h6>';
    html += `<div class="row">`;
    html += `<div class="col-md-3"><strong>Total Rows:</strong> ${stats.total_rows}</div>`;
    html += `<div class="col-md-3"><strong>Processed:</strong> ${stats.processed}</div>`;
    html += `<div class="col-md-3"><strong>Inserted:</strong> ${stats.inserted}</div>`;
    html += `<div class="col-md-3"><strong>Updated:</strong> ${stats.updated}</div>`;
    html += `</div>`;
    
    if (stats.skipped > 0) {
        html += `<div class="mt-2"><strong>Skipped:</strong> ${stats.skipped}</div>`;
    }
    
    if (stats.errors.length > 0) {
        html += '<div class="mt-2"><strong>Errors:</strong>';
        html += '<ul class="mb-0">';
        stats.errors.slice(0, 5).forEach(error => {
            html += `<li class="small">${error}</li>`;
        });
        if (stats.errors.length > 5) {
            html += `<li class="small">... and ${stats.errors.length - 5} more errors</li>`;
        }
        html += '</ul></div>';
    }
    
    html += '</div>';
    
    resultsDiv.innerHTML = html;
    resultsDiv.style.display = 'block';
}

function showDuplicatesModal(duplicates) {
    const modal = new bootstrap.Modal(document.getElementById('duplicatesModal'));
    const content = document.getElementById('duplicatesContent');
    
    if (duplicates.length === 0) {
        content.innerHTML = '<p class="text-success"><i class="fas fa-check"></i> No potential duplicates found!</p>';
    } else {
        let html = '<div class="table-responsive">';
        html += '<table class="table table-sm">';
        html += '<thead><tr><th>ID</th><th>Name</th><th>Address</th><th>City</th><th>Has Place ID</th></tr></thead>';
        html += '<tbody>';
        
        duplicates.forEach(restaurant => {
            html += '<tr>';
            html += `<td>${restaurant.id}</td>`;
            html += `<td>${restaurant.name}</td>`;
            html += `<td>${restaurant.address || 'N/A'}</td>`;
            html += `<td>${restaurant.city || 'N/A'}</td>`;
            html += `<td>${restaurant.has_place_id ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>'}</td>`;
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        content.innerHTML = html;
    }
    
    modal.show();
}

function loadDbStats() {
    fetch(`${baseUrl}admin/restaurants/db-stats`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totalRestaurants').textContent = data.stats.total;
            document.getElementById('withPlaceId').textContent = data.stats.with_place_id;
            document.getElementById('withoutPlaceId').textContent = data.stats.without_place_id;
        }
    })
    .catch(error => {
        console.error('Error loading stats:', error);
    });
}

function downloadSampleCsv() {
    const csvContent = `Name,Address,Rating,User Ratings Total,Place ID
"Aragvi Restaurant","123 Main St, New York, NY 10001",4.5,150,"ChIJXXXXXXXXXXXX"
"Tbilisi Garden","456 Oak Ave, Brooklyn, NY 11201",4.2,89,"ChIJYYYYYYYYYYYY"
"Georgian House","789 Pine Rd, Manhattan, NY 10014",4.7,234,"ChIJZZZZZZZZZZZZ"`;
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'sample_restaurants.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of the form
    const form = document.getElementById('csvUploadForm');
    form.insertBefore(alertDiv, form.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
<?= $this->endSection() ?>