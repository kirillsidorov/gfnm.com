<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

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
                        <li class="breadcrumb-item"><a href="<?= base_url('restaurants') ?>">Restaurants</a></li>
                        <li class="breadcrumb-item active"><?= esc($restaurant['name']) ?></li>
                    </ol>
                </nav>
                
                <div class="row">
                    <!-- Restaurant Image -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
                                <i class="fas fa-utensils fa-4x text-muted"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Restaurant Info -->
                    <div class="col-lg-8">
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
                                    echo '';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Location -->
                        <p class="h6 text-muted mb-3">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= esc($restaurant['city_name']) ?>
                        </p>
                        
                        <!-- Description -->
                        <p class="lead mb-4"><?= nl2br(esc($restaurant['description'])) ?></p>
                        
                        <!-- Quick Actions -->
                        <div class="d-flex gap-2 flex-wrap">
                            <?php if (!empty($restaurant['phone'])): ?>
                                <a href="tel:<?= esc($restaurant['phone']) ?>" class="btn btn-georgian">
                                    <i class="fas fa-phone"></i> Call Now
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($restaurant['website'])): ?>
                                <a href="<?= esc($restaurant['website']) ?>" target="_blank" class="btn btn-outline-dark">
                                    <i class="fas fa-globe"></i> Visit Website
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($restaurant['google_place_id'])): ?>
                                <a href="https://www.google.com/maps/place/?q=place_id:<?= esc($restaurant['google_place_id']) ?>" 
                                   target="_blank" class="btn btn-outline-success">
                                    <i class="fas fa-directions"></i> Get Directions
                                </a>
                            <?php endif; ?>
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
                                        <a href="<?= esc($restaurant['website']) ?>" target="_blank" class="text-decoration-none">
                                            <?= esc($restaurant['website']) ?>
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

                <!-- Map Section -->
                <?php if (!empty($restaurant['google_place_id'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-map"></i> Location</h3>
                    </div>
                    <div class="card-body">
                        <div id="map" style="height: 400px; background: #f8f9fa; border-radius: 8px;">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div class="text-center">
                                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Map will be loaded here</p>
                                    <a href="https://www.google.com/maps/place/?q=place_id:<?= esc($restaurant['google_place_id']) ?>" 
                                       target="_blank" class="btn btn-outline-primary">
                                        View on Google Maps
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Restaurant Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Rating:</span>
                            <span class="fw-bold"><?= number_format($rating, 1) ?>/5.0</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Price Level:</span>
                            <span class="price-level fw-bold">
                                <?php
                                $priceLevel = intval($restaurant['price_level']);
                                for ($i = 0; $i < $priceLevel; $i++) {
                                    echo '';
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span>City:</span>
                            <span class="fw-bold"><?= esc($restaurant['city_name']) ?></span>
                        </div>
                    </div>
                </div>

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
                        <div class="card-img-top bg-white d-flex align-items-center justify-content-center" style="height: 150px;">
                            <i class="fas fa-utensils fa-2x text-muted"></i>
                        </div>
                        
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
                                        echo '';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <p class="card-text small flex-grow-1">
                                <?= character_limiter(strip_tags($similar['description']), 80) ?>
                            </p>
                            
                            <div class="mt-auto">
                                <a href="<?= base_url('restaurants/view/' . $similar['id']) ?>" 
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

<script>
// Share functions
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent('Check out this Georgian restaurant: <?= esc($restaurant['name']) ?>');
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=600,height=400');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        alert('Link copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>

<?= $this->endSection() ?>