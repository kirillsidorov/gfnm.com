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
                    <li class="breadcrumb-item active">Khinkali</li>
                </ol>
            </nav>
            
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-georgian">Khinkali</h1>
                <p class="lead">
                    <?= esc($dishDescription) ?>
                </p>
                <div class="mt-4">
                    <a href="#restaurants" class="btn btn-georgian btn-lg me-3">Find Khinkali Near Me</a>
                    <a href="<?= base_url('khinkali-near-me') ?>" class="btn btn-outline-dark btn-lg">Use My Location</a>
                </div>
            </div>
        </div>
    </div>

    <!-- How to Eat Khinkali -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">How to Eat Khinkali</h2>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <span class="h3 text-georgian fw-bold">1</span>
                            </div>
                            <h6 class="fw-bold">Hold by the Top</h6>
                            <p class="small text-muted">Grab the khinkali by its twisted top knob, never use a fork.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <span class="h3 text-georgian fw-bold">2</span>
                            </div>
                            <h6 class="fw-bold">Make a Small Bite</h6>
                            <p class="small text-muted">Carefully bite a small hole to release the hot broth inside.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <span class="h3 text-georgian fw-bold">3</span>
                            </div>
                            <h6 class="fw-bold">Sip the Broth</h6>
                            <p class="small text-muted">Suck out the delicious broth carefully to avoid burns.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <span class="h3 text-georgian fw-bold">4</span>
                            </div>
                            <h6 class="fw-bold">Eat the Rest</h6>
                            <p class="small text-muted">Consume the meat and dough, leaving the tough top knob.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Cities for Khinkali -->
    <?php if (!empty($topCities)): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Top Cities for Khinkali</h2>
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

    <!-- Restaurants Serving Khinkali -->
    <section id="restaurants">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center">Best Restaurants for Khinkali</h2>
                <p class="text-center text-muted">Authentic Georgian restaurants serving handmade khinkali</p>
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
                    <p class="text-muted">We're working on adding more restaurants that serve khinkali.</p>
                    <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-georgian">
                        Browse All Restaurants
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- About Khinkali -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h3>About Khinkali</h3>
                    <p>
                        Khinkali are traditional Georgian dumplings that originated in the mountainous regions of Pshavi, Mtiuleti, and Khevsureti. 
                        These hand-twisted dumplings are filled with seasoned meat and aromatic herbs, creating a burst of flavor with every bite.
                    </p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <h6><i class="fas fa-meat"></i> Traditional Filling</h6>
                            <p class="small">
                                Classic khinkali are filled with a mixture of pork and beef, seasoned with cilantro, onions, 
                                and Georgian spices that create the signature flavor.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-hands"></i> Handmade Tradition</h6>
                            <p class="small">
                                Each khinkali is individually hand-twisted with exactly 19 pleats, a skill passed down through 
                                generations of Georgian cooks.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-wine-glass"></i> Perfect Pairing</h6>
                            <p class="small">
                                Khinkali are traditionally served with Georgian wine or chacha (Georgian brandy) and eaten 
                                with your hands, never with utensils.
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