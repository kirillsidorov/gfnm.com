<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-4">Georgian Food Near Me</h1>
                <p class="lead mb-5">Find authentic Georgian restaurants, khachapuri, khinkali and traditional dishes near you</p>
                
                <!-- Search Box -->
                <div class="search-box">
                    <form method="GET" action="<?= base_url('search') ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-lg" name="q" 
                                       placeholder="Search for Georgian food, restaurants..." required>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select form-select-lg" name="city">
                                    <option value="">Near Me / All Cities</option>
                                    <?php if (isset($cities) && is_array($cities)): ?>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?= $city['id'] ?>"><?= esc($city['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-georgian btn-lg w-100">
                                    <i class="fas fa-search"></i> Find
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Quick Links -->
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <small class="text-muted">Popular searches: </small>
                            <a href="<?= base_url('khachapuri-near-me') ?>" class="badge bg-light text-dark text-decoration-none me-2">Khachapuri Near Me</a>
                            <a href="<?= base_url('khinkali-near-me') ?>" class="badge bg-light text-dark text-decoration-none me-2">Khinkali Near Me</a>
                            <a href="<?= base_url('georgian-restaurant-nyc') ?>" class="badge bg-light text-dark text-decoration-none">Georgian Restaurant NYC</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Georgian Dishes -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-6 fw-bold">Popular Georgian Dishes</h2>
                <p class="lead text-muted">Discover authentic flavors of Georgia</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <a href="<?= base_url('khachapuri') ?>" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body">
                            <div class="bg-georgian rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-bread-slice fa-2x text-white"></i>
                            </div>
                            <h4 class="fw-bold text-dark">Khachapuri</h4>
                            <p class="text-muted">Traditional Georgian cheese-filled bread, available in various regional styles</p>
                            <span class="btn btn-outline-dark">Find Khachapuri <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 mb-4">
                <a href="<?= base_url('khinkali') ?>" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body">
                            <div class="bg-georgian rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-circle fa-2x text-white"></i>
                            </div>
                            <h4 class="fw-bold text-dark">Khinkali</h4>
                            <p class="text-muted">Handmade Georgian dumplings filled with spiced meat and savory broth</p>
                            <span class="btn btn-outline-dark">Find Khinkali <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 mb-4">
                <a href="<?= base_url('georgian-cuisine') ?>" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body">
                            <div class="bg-georgian rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-utensils fa-2x text-white"></i>
                            </div>
                            <h4 class="fw-bold text-dark">Georgian Cuisine</h4>
                            <p class="text-muted">Explore the full range of authentic Georgian dishes and flavors</p>
                            <span class="btn btn-outline-dark">Explore Cuisine <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<?php if (isset($stats)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-6">
                <div class="card border-0 bg-transparent">
                    <div class="card-body">
                        <h2 class="display-4 text-georgian fw-bold"><?= number_format($stats['total_restaurants']) ?></h2>
                        <p class="lead">Georgian Restaurants</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 bg-transparent">
                    <div class="card-body">
                        <h2 class="display-4 text-georgian fw-bold"><?= number_format($stats['total_cities']) ?></h2>
                        <p class="lead">Cities Covered</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Top Restaurants Section -->
<?php if (isset($topRestaurants) && !empty($topRestaurants)): ?>
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold">Top Rated Georgian Restaurants</h2>
                <p class="lead text-muted">Discover the highest-rated authentic Georgian dining experiences</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($topRestaurants as $restaurant): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card restaurant-card h-100">
                        <!-- Restaurant Image Placeholder -->
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-utensils fa-3x text-muted"></i>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?= esc($restaurant['name']) ?></h5>
                            
                            <!-- Location -->
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= esc($restaurant['city_name']) ?>
                            </p>
                            
                            <!-- Rating and Price -->
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
                            
                            <!-- Description -->
                            <p class="card-text flex-grow-1">
                                <?= character_limiter(strip_tags($restaurant['description']), 120) ?>
                            </p>
                            
                            <!-- Contact Info -->
                            <div class="mb-3">
                                <?php if (!empty($restaurant['phone'])): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-phone"></i> <?= esc($restaurant['phone']) ?>
                                    </small><br>
                                <?php endif; ?>
                                
                                <?php if (!empty($restaurant['website'])): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-globe"></i> 
                                        <a href="<?= esc($restaurant['website']) ?>" target="_blank" class="text-decoration-none">
                                            Website
                                        </a>
                                    </small>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
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
        
        <div class="row">
            <div class="col-12 text-center">
                <a href="<?= base_url('restaurants') ?>" class="btn btn-outline-dark btn-lg">
                    View All Restaurants <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Popular Cities Section -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-6 fw-bold">Popular Cities for Georgian Food</h2>
                <p class="lead text-muted">Find Georgian restaurants in these popular destinations</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 col-6 mb-4">
                <a href="<?= base_url('georgian-restaurant-nyc') ?>" class="text-decoration-none">
                    <div class="card border-0 bg-light text-center h-100">
                        <div class="card-body">
                            <h5 class="fw-bold text-dark">New York</h5>
                            <small class="text-muted">Most restaurants</small>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3 col-6 mb-4">
                <a href="<?= base_url('georgian-restaurant-chicago') ?>" class="text-decoration-none">
                    <div class="card border-0 bg-light text-center h-100">
                        <div class="card-body">
                            <h5 class="fw-bold text-dark">Chicago</h5>
                            <small class="text-muted">Great selection</small>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3 col-6 mb-4">
                <a href="<?= base_url('georgian-restaurant-manhattan') ?>" class="text-decoration-none">
                    <div class="card border-0 bg-light text-center h-100">
                        <div class="card-body">
                            <h5 class="fw-bold text-dark">Manhattan</h5>
                            <small class="text-muted">Premium dining</small>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3 col-6 mb-4">
                <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="text-decoration-none">
                    <div class="card border-0 bg-primary text-white text-center h-100">
                        <div class="card-body">
                            <h5 class="fw-bold">Find Near Me</h5>
                            <small>Use location</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    .text-georgian {
        color: var(--georgian-red) !important;
    }
    
    .bg-georgian {
        background-color: var(--georgian-red) !important;
    }
    
    .search-box .badge:hover {
        background-color: var(--georgian-red) !important;
        color: white !important;
    }
</style>

<?= $this->endSection() ?>