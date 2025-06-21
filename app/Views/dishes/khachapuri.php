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
                    <li class="breadcrumb-item active">Khachapuri</li>
                </ol>
            </nav>
            
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-georgian">Khachapuri</h1>
                <p class="lead">
                    <?= esc($dishDescription) ?>
                </p>
                <div class="mt-4">
                    <a href="#restaurants" class="btn btn-georgian btn-lg me-3">Find Khachapuri Near Me</a>
                    <a href="<?= base_url('khachapuri-near-me') ?>" class="btn btn-outline-dark btn-lg">Use My Location</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Khachapuri Types -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Types of Khachapuri</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-bread-slice fa-2x text-georgian"></i>
                            </div>
                            <h5 class="fw-bold">Adjarian Khachapuri</h5>
                            <p class="text-muted">Boat-shaped khachapuri topped with egg and butter, originating from Adjara region.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-circle fa-2x text-georgian"></i>
                            </div>
                            <h5 class="fw-bold">Imeruli Khachapuri</h5>
                            <p class="text-muted">Round, flat bread filled with Imeruli cheese, the most traditional form of khachapuri.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-layer-group fa-2x text-georgian"></i>
                            </div>
                            <h5 class="fw-bold">Megrelian Khachapuri</h5>
                            <p class="text-muted">Similar to Imeruli but with cheese on top as well, from the Samegrelo region.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Cities for Khachapuri -->
    <?php if (!empty($topCities)): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Top Cities for Khachapuri</h2>
            <div class="row">
                <?php foreach ($topCities as $city): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="<?= base_url('restaurants/city/' . $city['id']) ?>" class="text-decoration-none">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <h6 class="fw-bold text-dark"><?= esc($city['name']) ?></h6>
                                    <small class="text-muted"><?= $city['restaurant_count'] ?> restaurants</small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Restaurants Serving Khachapuri -->
    <section id="restaurants">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center">Best Restaurants for Khachapuri</h2>
                <p class="text-center text-muted">Authentic Georgian restaurants serving traditional khachapuri</p>
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
                                
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= esc($restaurant['city_name']) ?>
                                </p>
                                
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
                                
                                <div class="mt-auto">
                                    <a href="<?= base_url('restaurants/view/' . $restaurant['id']) ?>" 
                                       class="btn btn-georgian w-100">
                                        View Restaurant <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-outline-dark btn-lg">
                        View All Georgian Restaurants
                    </a>
                </div>
            </div>

        <?php else: ?>
            <div class="row">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h3>No restaurants found</h3>
                    <p class="text-muted">We're working on adding more restaurants that serve khachapuri.</p>
                    <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-georgian">
                        Browse All Restaurants
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- About Khachapuri -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h3>About Khachapuri</h3>
                    <p>
                        Khachapuri is a traditional Georgian dish of cheese-filled bread that serves as both a staple food and a beloved national symbol. 
                        The dish varies by region, with each area of Georgia having its own unique preparation method and cheese blend.
                    </p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6><i class="fas fa-history"></i> History</h6>
                            <p class="small">
                                Dating back centuries, khachapuri has been a cornerstone of Georgian cuisine, traditionally baked in tone ovens 
                                and served at every meal. Each region developed its own variation, reflecting local ingredients and preferences.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-utensils"></i> How to Eat</h6>
                            <p class="small">
                                Adjarian khachapuri is traditionally eaten by mixing the egg and butter into the cheese, then tearing off pieces 
                                of bread to dip into the mixture. It's best enjoyed while hot and fresh from the oven.
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