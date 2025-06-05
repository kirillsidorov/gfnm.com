<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('georgian-restaurant-near-me') ?>">Georgian Restaurants</a></li>
                    <li class="breadcrumb-item active"><?= esc($displayName) ?></li>
                </ol>
            </nav>
            
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">Georgian Restaurant <?= esc($displayName) ?></h1>
                <p class="lead">
                    Discover the best Georgian restaurants in <?= esc($displayName) ?> • 
                    <?= $totalRestaurants ?> authentic Georgian restaurants
                    <?php if (!empty($city['state'])): ?>
                        in <?= esc($city['state']) ?>
                    <?php endif; ?>
                </p>
                
                <div class="mt-4">
                    <a href="#restaurants" class="btn btn-georgian btn-lg me-3">
                        View Restaurants
                    </a>
                    <?php if (!empty($city['state'])): ?>
                        <a href="<?= base_url('restaurants/state/' . urlencode($city['state'])) ?>" 
                           class="btn btn-outline-primary btn-lg">
                            All <?= esc($city['state']) ?> Restaurants
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- City Stats -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h2 class="fw-bold"><?= $totalRestaurants ?></h2>
                            <p class="mb-0">Georgian Restaurants</p>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold"><?= number_format($avgRating, 1) ?></h2>
                            <p class="mb-0">Average Rating</p>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold"><?= $priceRange ?: 'N/A' ?></h2>
                            <p class="mb-0">Price Range</p>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold"><?= esc($city['state'] ?? 'USA') ?></h2>
                            <p class="mb-0">State</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Georgian Dishes -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Popular Georgian Dishes in <?= esc($displayName) ?></h2>
            <div class="row">
                <div class="col-md-3 col-6 mb-3">
                    <a href="<?= base_url('khachapuri') ?>" class="text-decoration-none">
                        <div class="card border-0 bg-light text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-bread-slice fa-2x text-georgian mb-2"></i>
                                <h6 class="fw-bold text-dark">Khachapuri</h6>
                                <small class="text-muted">Cheese-filled bread</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="<?= base_url('khinkali') ?>" class="text-decoration-none">
                        <div class="card border-0 bg-light text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-circle fa-2x text-georgian mb-2"></i>
                                <h6 class="fw-bold text-dark">Khinkali</h6>
                                <small class="text-muted">Georgian dumplings</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-0 bg-light text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-drumstick-bite fa-2x text-georgian mb-2"></i>
                            <h6 class="fw-bold text-dark">Mtsvadi</h6>
                            <small class="text-muted">Grilled meat</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-0 bg-light text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-seedling fa-2x text-georgian mb-2"></i>
                            <h6 class="fw-bold text-dark">Lobio</h6>
                            <small class="text-muted">Bean stew</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Similar Cities -->
    <?php if (!empty($similarCities)): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="text-center mb-4">
                Other Cities in <?= esc($city['state']) ?> with Georgian Restaurants
            </h3>
            <div class="row">
                <?php foreach ($similarCities as $similarCity): ?>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <a href="<?= base_url('restaurants/city/' . $similarCity['id']) ?>" class="text-decoration-none">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="fw-bold text-dark"><?= esc($similarCity['name']) ?></h6>
                                    <small class="text-muted">
                                        <?= $similarCity['restaurant_count'] ?> restaurant<?= $similarCity['restaurant_count'] != 1 ? 's' : '' ?>
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Restaurants List -->
    <section id="restaurants">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center">Georgian Restaurants in <?= esc($displayName) ?></h2>
                <p class="text-center text-muted">Authentic Georgian cuisine and traditional dishes</p>
            </div>
        </div>

        <?php if (!empty($restaurants)): ?>
            <div class="row">
                <?php foreach ($restaurants as $restaurant): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card restaurant-card h-100">
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center restaurant-image">
                                <i class="fas fa-utensils fa-3x text-muted"></i>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold"><?= esc($restaurant['name']) ?></h5>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="rating">
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
                                        
                                        <span class="ms-2 text-muted"><?= number_format($rating, 1) ?></span>
                                    </div>
                                    
                                    <div class="price-level">
                                        <?php
                                        $priceLevel = intval($restaurant['price_level']);
                                        for ($i = 0; $i < $priceLevel; $i++) {
                                            echo '$';
                                        }
                                        ?>
                                    </div>
                                </div>
                                
                                <p class="card-text flex-grow-1">
                                    <?= character_limiter(strip_tags($restaurant['description']), 120) ?>
                                </p>
                                
                                <p class="small text-muted mb-3">
                                    <i class="fas fa-location-dot"></i>
                                    <?= esc($restaurant['address']) ?>
                                </p>
                                
                                <div class="mb-3">
                                    <?php if (!empty($restaurant['phone'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-phone"></i> 
                                            <a href="tel:<?= esc($restaurant['phone']) ?>" class="text-decoration-none">
                                                <?= esc($restaurant['phone']) ?>
                                            </a>
                                        </small><br>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($restaurant['website'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-globe"></i> 
                                            <a href="<?= esc($restaurant['website']) ?>" target="_blank" class="text-decoration-none">
                                                Visit Website
                                            </a>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-auto">
                                    <a href="<?= base_url('restaurants/view/' . $restaurant['id']) ?>" 
                                       class="btn btn-georgian w-100">
                                        View Details <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="row">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                    <h3>No Georgian Restaurants Found</h3>
                    <p class="text-muted">
                        We haven't found any Georgian restaurants in <?= esc($displayName) ?> yet.
                        <?php if (!empty($city['state'])): ?>
                            <br>Try exploring other cities in <?= esc($city['state']) ?>.
                        <?php endif; ?>
                    </p>
                    
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <?php if (!empty($city['state'])): ?>
                            <a href="<?= base_url('restaurants/state/' . urlencode($city['state'])) ?>" class="btn btn-georgian">
                                <i class="fas fa-map"></i> View <?= esc($city['state']) ?> Restaurants
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-outline-dark">
                            <i class="fas fa-utensils"></i> Browse All Restaurants
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- About Georgian Food in City -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h3>About Georgian Food in <?= esc($displayName) ?></h3>
                    <p>
                        Georgian cuisine in <?= esc($displayName) ?> offers a unique blend of European and Asian influences, 
                        featuring signature dishes that have been perfected over centuries. The rich culinary tradition 
                        emphasizes fresh herbs, walnuts, and distinctive spice blends that create unforgettable flavors.
                    </p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <h6><i class="fas fa-star"></i> Signature Dishes</h6>
                            <ul class="list-unstyled">
                                <li>• <strong>Khachapuri</strong> - Traditional cheese-filled bread</li>
                                <li>• <strong>Khinkali</strong> - Handmade soup dumplings</li>
                                <li>• <strong>Mtsvadi</strong> - Georgian grilled meat</li>
                                <li>• <strong>Lobio</strong> - Hearty bean stew</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-wine-glass"></i> Traditional Drinks</h6>
                            <ul class="list-unstyled">
                                <li>• <strong>Georgian Wine</strong> - Ancient winemaking traditions</li>
                                <li>• <strong>Chacha</strong> - Georgian grape brandy</li>
                                <li>• <strong>Tarkhuna</strong> - Tarragon-flavored soda</li>
                                <li>• <strong>Georgian Tea</strong> - Traditional tea service</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-map-marker-alt"></i> Explore More</h6>
                            <p>
                                <a href="<?= base_url('khachapuri-near-me') ?>" class="text-decoration-none">
                                    Find Khachapuri Near You
                                </a><br>
                                <a href="<?= base_url('khinkali-near-me') ?>" class="text-decoration-none">
                                    Find Khinkali Near You
                                </a><br>
                                <?php if (!empty($city['state'])): ?>
                                <a href="<?= base_url('restaurants/state/' . urlencode($city['state'])) ?>" class="text-decoration-none">
                                    All <?= esc($city['state']) ?> Restaurants
                                </a>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-georgian {
        color: var(--georgian-red) !important;
    }
</style>

<?= $this->endSection() ?>