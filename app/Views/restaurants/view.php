<?php
// ОТЛАДКА: узнаем откуда пришли данные
echo "<!-- DEBUG: Called from method: " . debug_backtrace()[1]['function'] . " -->";
echo "<!-- DEBUG: Photos isset: " . (isset($photos) ? 'YES (' . count($photos) . ')' : 'NO') . " -->";
echo "<!-- DEBUG: MainPhoto isset: " . (isset($mainPhoto) ? 'YES' : 'NO') . " -->";
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// ОТЛАДКА: узнаем откуда пришли данные
echo "<!-- DEBUG: Called from method: " . debug_backtrace()[1]['function'] . " -->";
echo "<!-- DEBUG: Photos isset: " . (isset($photos) ? 'YES (' . count($photos) . ')' : 'NO') . " -->";
echo "<!-- DEBUG: MainPhoto isset: " . (isset($mainPhoto) ? 'YES' : 'NO') . " -->";
?>
<?php if (isset($restaurant) && !empty($restaurant)): ?>

<!-- Restaurant Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('georgian-restaurants-' . $restaurant['city_slug']) ?>"><?= esc($restaurant['city_name']) ?></a></li>
                        <li class="breadcrumb-item active"><?= esc($restaurant['name']) ?></li>
                    </ol>
                </nav>
                
                <div class="row">
                    <!-- Restaurant Image/Gallery -->
                    <div class="col-lg-6 mb-4">
                        <?php if (!empty($mainPhoto) || !empty($photos)): ?>
                            <!-- Main Photo -->
                            <div class="main-photo-container mb-3">
                                <?php $displayPhoto = $mainPhoto ?: $photos[0]; ?>
                                <img src="<?= base_url($displayPhoto['file_path']) ?>" 
                                     alt="<?= esc($displayPhoto['alt_text'] ?: $restaurant['name']) ?>" 
                                     class="img-fluid rounded shadow main-restaurant-photo"
                                     style="width: 100%; height: 400px; object-fit: cover; cursor: pointer;"
                                     onclick="openPhotoModal(0)">
                            </div>
                            
                            <!-- Photo Gallery Thumbnails -->
                            <?php if (count($photos) > 1): ?>
                                <div class="photo-gallery">
                                    <div class="row g-2">
                                        <?php foreach (array_slice($photos, 0, 6) as $index => $photo): ?>
                                            <div class="col-2">
                                                <img src="<?= base_url($photo['file_path']) ?>" 
                                                     alt="<?= esc($photo['alt_text'] ?: $restaurant['name'] . ' photo ' . ($index + 1)) ?>"
                                                     class="img-fluid rounded thumbnail-photo"
                                                     style="width: 100%; height: 60px; object-fit: cover; cursor: pointer;"
                                                     onclick="openPhotoModal(<?= $index ?>)">
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if (count($photos) > 6): ?>
                                            <div class="col-2">
                                                <div class="more-photos-indicator rounded d-flex align-items-center justify-content-center"
                                                     style="width: 100%; height: 60px; background: rgba(0,0,0,0.7); color: white; cursor: pointer;"
                                                     onclick="openPhotoModal(6)">
                                                    <small>+<?= count($photos) - 6 ?></small>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Placeholder if no photos -->
                            <div class="card">
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                                    <div class="text-center">
                                        <i class="fas fa-utensils fa-4x text-muted mb-3"></i>
                                        <p class="text-muted">No photos available</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Restaurant Info -->
                    <div class="col-lg-6">
                        <h1 class="display-5 fw-bold mb-3"><?= esc($restaurant['name']) ?></h1>
                        
                        <!-- Rating and Price -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="rating me-3">
                                <?php
                                $rating = floatval($restaurant['rating']);
                                $fullStars = floor($rating);
                                $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                ?>
                                
                                <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                    <i class="fas fa-star rating-stars"></i>
                                <?php endfor; ?>
                                
                                <?php if ($hasHalfStar): ?>
                                    <i class="fas fa-star-half-alt rating-stars"></i>
                                <?php endif; ?>
                                
                                <?php for ($i = 0; $i < (5 - ceil($rating)); $i++): ?>
                                    <i class="far fa-star rating-stars"></i>
                                <?php endfor; ?>
                                
                                <span class="ms-2 h5 mb-0"><?= number_format($rating, 1) ?></span>
                            </div>
                            
                            <div class="price-level h5 mb-0">
                                <?php
                                $priceLevel = intval($restaurant['price_level']);
                                for ($i = 0; $i < $priceLevel; $i++) {
                                    echo '$';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Location -->
                        <p class="h6 text-muted mb-3">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= esc($restaurant['address']) ?>
                        </p>
                        
                        <!-- Description -->
                        <p class="lead mb-4"><?= nl2br(esc($restaurant['description'])) ?></p>
                        
                        <!-- Quick Actions -->
                        <div class="d-flex gap-2 flex-wrap mb-4">
                            <?php if (!empty($restaurant['phone'])): ?>
                                <a href="tel:<?= esc($restaurant['phone']) ?>" class="btn btn-georgian">
                                    <i class="fas fa-phone"></i> Call Now
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($restaurant['website'])): ?>
                                <a href="<?= esc($restaurant['website']) ?>" target="_blank" rel="nofollow" class="btn btn-outline-dark">
                                    <i class="fas fa-globe"></i> Visit Website
                                </a>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-success" onclick="getDirections()">
                                <i class="fas fa-directions"></i> Get Directions
                            </button>
                        </div>

                        <!-- Restaurant Stats Card -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Quick Info</h5>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="d-flex justify-content-between">
                                            <span>Rating:</span>
                                            <span class="fw-bold"><?= number_format($rating, 1) ?>/5.0</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex justify-content-between">
                                            <span>Price:</span>
                                            <span class="fw-bold">
                                                <?php for ($i = 0; $i < $priceLevel; $i++) echo '$'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if (!empty($restaurant['phone'])): ?>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span>Phone:</span>
                                            <span class="fw-bold"><?= esc($restaurant['phone']) ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Restaurant Details -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-info-circle"></i> Contact Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Address</h6>
                                <p class="text-muted"><?= esc($restaurant['address']) ?></p>
                                
                                <?php if (!empty($restaurant['phone'])): ?>
                                    <h6>Phone</h6>
                                    <p class="text-muted">
                                        <a href="tel:<?= esc($restaurant['phone']) ?>" class="text-decoration-none">
                                            <?= esc($restaurant['phone']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <?php if (!empty($restaurant['website'])): ?>
                                    <h6>Website</h6>
                                    <p class="text-muted">
                                        <a href="<?= esc($restaurant['website']) ?>" target="_blank" rel="nofollow" class="text-decoration-none">
                                            Visit Website
                                        </a>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($restaurant['hours'])): ?>
                                    <h6>Hours</h6>
                                    <p class="text-muted"><?= esc($restaurant['hours']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Map -->
                <?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-map"></i> Location</h3>
                    </div>
                    <div class="card-body">
                        <div id="restaurantMap" style="height: 400px; background: #f8f9fa; border-radius: 8px;">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div class="text-center">
                                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Interactive map will load here</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Share Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Share this Restaurant</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-sm" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook"></i> Share on Facebook
                            </button>
                            <button class="btn btn-info btn-sm" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i> Share on Twitter
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="copyLink()">
                                <i class="fas fa-link"></i> Copy Link
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Photo Count -->
                <?php if (!empty($photos)): ?>
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="fas fa-camera"></i> Photo Gallery</h5>
                        <p class="card-text"><?= count($photos) ?> photo<?= count($photos) !== 1 ? 's' : '' ?> available</p>
                        <button class="btn btn-outline-primary btn-sm" onclick="openPhotoModal(0)">
                            View All Photos
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Similar Restaurants -->
<?php if (isset($similarRestaurants) && !empty($similarRestaurants)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold">More Restaurants in <?= esc($restaurant['city_name']) ?></h2>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($similarRestaurants as $similar): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card restaurant-card h-100">
                        <!-- Restaurant Photo -->
                        <?php if (!empty($similar['main_photo'])): ?>
                            <img src="<?= base_url($similar['main_photo']['file_path']) ?>" 
                                 alt="<?= esc($similar['main_photo']['alt_text'] ?: $similar['name']) ?>"
                                 class="card-img-top"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-utensils fa-2x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold"><?= esc($similar['name']) ?></h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rating small">
                                    <?php
                                    $simRating = floatval($similar['rating']);
                                    $simFullStars = floor($simRating);
                                    $simHasHalfStar = ($simRating - $simFullStars) >= 0.5;
                                    ?>
                                    
                                    <?php for ($i = 0; $i < $simFullStars; $i++): ?>
                                        <i class="fas fa-star rating-stars"></i>
                                    <?php endfor; ?>
                                    
                                    <?php if ($simHasHalfStar): ?>
                                        <i class="fas fa-star-half-alt rating-stars"></i>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 0; $i < (5 - ceil($simRating)); $i++): ?>
                                        <i class="far fa-star rating-stars"></i>
                                    <?php endfor; ?>
                                    
                                    <span class="ms-1"><?= number_format($simRating, 1) ?></span>
                                </div>
                                
                                <div class="price-level small">
                                    <?php
                                    $simPriceLevel = intval($similar['price_level']);
                                    for ($i = 0; $i < $simPriceLevel; $i++) {
                                        echo '$';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <p class="card-text small flex-grow-1">
                                <?= character_limiter(strip_tags($similar['description']), 80) ?>
                            </p>
                            
                            <div class="mt-auto">
                                <a href="<?= base_url($similar['seo_url']) ?>"
                                   class="btn btn-outline-dark btn-sm w-100">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Photo Modal -->
<?php if (!empty($photos)): ?>
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc($restaurant['name']) ?> - Photos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="photoCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($photos as $index => $photo): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="<?= base_url($photo['file_path']) ?>" 
                                     class="d-block w-100" 
                                     alt="<?= esc($photo['alt_text'] ?: $restaurant['name'] . ' photo ' . ($index + 1)) ?>"
                                     style="max-height: 70vh; object-fit: contain;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($photos) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#photoCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#photoCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                        
                        <div class="carousel-indicators">
                            <?php foreach ($photos as $index => $photo): ?>
                                <button type="button" data-bs-target="#photoCarousel" data-bs-slide-to="<?= $index ?>" 
                                        class="<?= $index === 0 ? 'active' : '' ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted">Photo <span id="currentPhotoNumber">1</span> of <?= count($photos) ?></span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<!-- Restaurant Not Found -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                <h2>Restaurant Not Found</h2>
                <p class="text-muted">The restaurant you're looking for doesn't exist or has been removed.</p>
                <a href="<?= base_url('restaurants') ?>" class="btn btn-georgian">
                    <i class="fas fa-arrow-left"></i> Back to Restaurants
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.thumbnail-photo:hover {
    opacity: 0.8;
    transform: scale(1.05);
    transition: all 0.2s;
}

.main-restaurant-photo:hover {
    transform: scale(1.02);
    transition: all 0.3s;
}

.rating-stars {
    color: #ffc107;
}

.btn-georgian {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-georgian:hover {
    background-color: #bb2d3b;
    border-color: #bb2d3b;
    color: white;
}

.restaurant-card {
    transition: transform 0.2s;
}

.restaurant-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.more-photos-indicator:hover {
    background: rgba(0,0,0,0.9) !important;
}
</style>

<script>
// Photo gallery functionality
function openPhotoModal(startIndex) {
    <?php if (!empty($photos)): ?>
    const modal = new bootstrap.Modal(document.getElementById('photoModal'));
    const carousel = bootstrap.Carousel.getOrCreateInstance(document.getElementById('photoCarousel'));
    
    // Go to specific slide
    carousel.to(startIndex);
    
    // Show modal
    modal.show();
    <?php endif; ?>
}

// Update photo counter in modal
<?php if (!empty($photos)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('photoCarousel');
    if (carousel) {
        carousel.addEventListener('slide.bs.carousel', function(event) {
            document.getElementById('currentPhotoNumber').textContent = event.to + 1;
        });
    }
});
<?php endif; ?>

// Map functionality
<?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
function initRestaurantMap() {
    const lat = <?= $restaurant['latitude'] ?>;
    const lng = <?= $restaurant['longitude'] ?>;
    
    const map = new google.maps.Map(document.getElementById('restaurantMap'), {
        zoom: 15,
        center: { lat: lat, lng: lng }
    });

    const marker = new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: map,
        title: '<?= esc($restaurant['name']) ?>',
        icon: {
            url: '/assets/images/khinkali-marker.png',
            scaledSize: new google.maps.Size(32, 32),
            anchor: new google.maps.Point(16, 32)
        }
    });

    const infoWindow = new google.maps.InfoWindow({
        content: 
            '<div style="max-width: 200px;">' +
            '<h6><?= esc($restaurant['name']) ?></h6>' +
            '<p class="mb-1 small"><?= esc($restaurant['address']) ?></p>' +
            <?php if (!empty($restaurant['phone'])): ?>
            '<p class="mb-0 small"><a href="tel:<?= esc($restaurant['phone']) ?>"><?= esc($restaurant['phone']) ?></a></p>' +
            <?php endif; ?>
            '</div>'
    });

    marker.addListener('click', function() {
        infoWindow.open(map, marker);
    });
}

// Load map when page is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof google !== 'undefined') {
        initRestaurantMap();
    }
});
<?php endif; ?>

// Get directions
function getDirections() {
    <?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const origin = position.coords.latitude + ',' + position.coords.longitude;
            const destination = '<?= $restaurant['latitude'] ?>,<?= $restaurant['longitude'] ?>';
            const url = `https://www.google.com/maps/dir/${origin}/${destination}`;
            window.open(url, '_blank');
        }, function() {
            // If geolocation fails, just use restaurant address
            const address = encodeURIComponent('<?= esc($restaurant['address']) ?>');
            window.open(`https://www.google.com/maps/search/${address}`, '_blank');
        });
    } else {
        const address = encodeURIComponent('<?= esc($restaurant['address']) ?>');
        window.open(`https://www.google.com/maps/search/${address}`, '_blank');
    }
    <?php else: ?>
    const address = encodeURIComponent('<?= esc($restaurant['address']) ?>');
    window.open(`https://www.google.com/maps/search/${address}`, '_blank');
    <?php endif; ?>
}

// Share functions
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent('Check out <?= esc($restaurant['name']) ?> - Georgian restaurant in <?= esc($restaurant['city_name']) ?>!');
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=600,height=400');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        // Show success message
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-secondary');
        }, 2000);
    }, function(err) {
        console.error('Could not copy text: ', err);
        alert('Failed to copy link');
    });
}
</script>

<!-- Load Google Maps if coordinates are available -->
<?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= env('GOOGLE_MAPS_API_KEY') ?>&callback=initRestaurantMap"></script>
<?php endif; ?>

<?= $this->endSection() ?>