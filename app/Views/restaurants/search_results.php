<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <!-- Search Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active">Search Results</li>
                </ol>
            </nav>
            
            <h1 class="display-6 fw-bold">Search Results</h1>
            <p class="lead">
                Found <?= $totalFound ?> results for "<?= esc($searchQuery) ?>"
                <?php if (isset($selectedCity) && $selectedCity): ?>
                    in <?= esc($selectedCity['name']) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?= base_url('search') ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="q" class="form-label">Search Term</label>
                                <input type="text" class="form-control" name="q" id="q" 
                                       value="<?= esc($searchQuery) ?>" 
                                       placeholder="Restaurant name, dish, or cuisine...">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <select class="form-select" name="city" id="city">
                                    <option value="">All Cities</option>
                                    <?php if (isset($cities) && is_array($cities)): ?>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?= $city['id'] ?>" 
                                                    <?= (isset($selectedCity) && $selectedCity && $selectedCity['id'] == $city['id']) ? 'selected' : '' ?>>
                                                <?= esc($city['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-georgian w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <?php if (isset($restaurants) && !empty($restaurants)): ?>
        <div class="row">
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card restaurant-card h-100">
                        <!-- Restaurant Image Placeholder -->
                        <!-- ОБНОВЛЕНО: Restaurant Image с реальными фотографиями -->
                        <?php if (!empty($restaurant['main_photo'])): ?>
                            <div class="card-img-top restaurant-image" style="height: 200px; overflow: hidden;">
                                <img src="<?= base_url($restaurant['main_photo']['file_path']) ?>" 
                                     alt="<?= esc($restaurant['main_photo']['alt_text'] ?: $restaurant['name']) ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center restaurant-image" style="height: 200px;">
                                <i class="fas fa-utensils fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
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
                            
                            <!-- Address -->
                            <p class="small text-muted mb-3">
                                <i class="fas fa-location-dot"></i>
                                <?= esc($restaurant['address']) ?>
                            </p>
                            
                            <!-- Contact Info -->
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
                            
                            <!-- Action Buttons -->
                            <div class="mt-auto">
                                <a href="<?= base_url($restaurant['seo_url']) ?>"
                                   class="btn btn-georgian w-100">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Search Tips -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5><i class="fas fa-lightbulb"></i> Search Tips</h5>
                        <p class="mb-0">Try searching for specific dishes like "khachapuri", "khinkali", or restaurant names. You can also filter by city to find restaurants in your area.</p>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- No Results -->
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No restaurants found</h3>
                <p class="text-muted mb-4">
                    We couldn't find any restaurants matching "<?= esc($searchQuery) ?>"
                    <?php if (isset($selectedCity) && $selectedCity): ?>
                        in <?= esc($selectedCity['name']) ?>
                    <?php endif; ?>
                </p>
                
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="<?= base_url('search?q=' . urlencode($searchQuery)) ?>" class="btn btn-outline-primary">
                        <i class="fas fa-globe"></i> Search All Cities
                    </a>
                    <a href="<?= base_url('restaurants') ?>" class="btn btn-georgian">
                        <i class="fas fa-utensils"></i> Browse All Restaurants
                    </a>
                </div>
                
                <!-- Search Suggestions -->
                <div class="mt-4">
                    <h6>Try searching for:</h6>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="<?= base_url('search?q=khachapuri') ?>" class="badge bg-secondary text-decoration-none">khachapuri</a>
                        <a href="<?= base_url('search?q=khinkali') ?>" class="badge bg-secondary text-decoration-none">khinkali</a>
                        <a href="<?= base_url('search?q=mtsvadi') ?>" class="badge bg-secondary text-decoration-none">mtsvadi</a>
                        <a href="<?= base_url('search?q=lobio') ?>" class="badge bg-secondary text-decoration-none">lobio</a>
                        <a href="<?= base_url('search?q=georgian bread') ?>" class="badge bg-secondary text-decoration-none">georgian bread</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>