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
                    <li class="breadcrumb-item active">Georgian Cuisine</li>
                </ol>
            </nav>
            
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-georgian">Georgian Cuisine</h1>
                <p class="lead">
                    Discover the rich culinary traditions of Georgia, featuring unique flavors, 
                    ancient recipes, and authentic dishes that have been perfected over centuries.
                </p>
                <div class="mt-4">
                    <a href="#restaurants" class="btn btn-georgian btn-lg me-3">Find Restaurants</a>
                    <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-outline-dark btn-lg">Browse All</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Dishes -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Signature Georgian Dishes</h2>
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="<?= base_url('khachapuri') ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 text-center">
                            <div class="card-body">
                                <i class="fas fa-bread-slice fa-3x text-georgian mb-3"></i>
                                <h5 class="fw-bold text-dark">Khachapuri</h5>
                                <p class="small text-muted">Traditional cheese-filled bread</p>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="<?= base_url('khinkali') ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 text-center">
                            <div class="card-body">
                                <i class="fas fa-circle fa-3x text-georgian mb-3"></i>
                                <h5 class="fw-bold text-dark">Khinkali</h5>
                                <p class="small text-muted">Handmade soup dumplings</p>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-drumstick-bite fa-3x text-georgian mb-3"></i>
                            <h5 class="fw-bold text-dark">Mtsvadi</h5>
                            <p class="small text-muted">Georgian grilled meat</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-seedling fa-3x text-georgian mb-3"></i>
                            <h5 class="fw-bold text-dark">Lobio</h5>
                            <p class="small text-muted">Traditional bean stew</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Restaurant List -->
    <section id="restaurants">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center">Georgian Restaurants</h2>
                <p class="text-center text-muted">Authentic Georgian cuisine across the United States</p>
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
                    <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                    <h3>Loading restaurants...</h3>
                    <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-georgian">
                        Browse All Restaurants
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- About Georgian Cuisine -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h3>About Georgian Cuisine</h3>
                    <p>
                        Georgian cuisine is a unique culinary tradition that reflects the country's location at the crossroads 
                        of Europe and Asia. Known for its bold flavors, generous use of herbs and spices, and distinctive 
                        cooking techniques, Georgian food offers an unforgettable dining experience.
                    </p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <h6><i class="fas fa-leaf"></i> Fresh Herbs</h6>
                            <p class="small">
                                Georgian cuisine heavily features fresh herbs like cilantro, parsley, dill, and tarragon, 
                                which are used both in cooking and as accompaniments.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-pepper-hot"></i> Unique Spices</h6>
                            <p class="small">
                                Traditional spice blends like khmeli-suneli give Georgian dishes their distinctive flavor, 
                                combining coriander, fenugreek, cinnamon, and other aromatic spices.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-wine-glass"></i> Wine Culture</h6>
                            <p class="small">
                                Georgia is one of the world's oldest wine-producing regions, and Georgian wine is an 
                                integral part of the dining experience and cultural tradition.
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