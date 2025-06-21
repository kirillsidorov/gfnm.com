
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 fw-bold">All Georgian Restaurants</h1>
            <p class="lead text-muted">Discover authentic Georgian cuisine across different cities</p>
        </div>
    </div>

    <!-- Filters and Sorting -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?= current_url() ?>">
                        <div class="row g-3 align-items-end">
                            <!-- City Filter -->
                            <div class="col-md-3">
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

                            <!-- Price Filter -->
                            <div class="col-md-3">
                                <label for="price" class="form-label">Price Level</label>
                                <select class="form-select" name="price" id="price">
                                    <option value="">All Prices</option>
                                    <option value="1" <?= (isset($selectedPrice) && $selectedPrice == 1) ? 'selected' : '' ?>>$ - Budget</option>
                                    <option value="2" <?= (isset($selectedPrice) && $selectedPrice == 2) ? 'selected' : '' ?>>$$ - Moderate</option>
                                    <option value="3" <?= (isset($selectedPrice) && $selectedPrice == 3) ? 'selected' : '' ?>>$$$ - Expensive</option>
                                    <option value="4" <?= (isset($selectedPrice) && $selectedPrice == 4) ? 'selected' : '' ?>>$$$$ - Very Expensive</option>
                                </select>
                            </div>

                            <!-- Sort By -->
                            <div class="col-md-3">
                                <label for="sort" class="form-label">Sort By</label>
                                <select class="form-select" name="sort" id="sort">
                                    <option value="rating" <?= (isset($selectedSort) && $selectedSort == 'rating') ? 'selected' : '' ?>>Highest Rated</option>
                                    <option value="name" <?= (isset($selectedSort) && $selectedSort == 'name') ? 'selected' : '' ?>>Name A-Z</option>
                                    <option value="price_low" <?= (isset($selectedSort) && $selectedSort == 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
                                    <option value="price_high" <?= (isset($selectedSort) && $selectedSort == 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
                                </select>
                            </div>

                            <!-- Filter Button -->
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-georgian w-100">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Count -->
    <?php if (isset($restaurants) && is_array($restaurants)): ?>
        <div class="row mb-3">
            <div class="col-12">
                <p class="text-muted">
                    Showing <?= count($restaurants) ?> restaurants
                    <?php if (isset($selectedCity) && $selectedCity): ?>
                        in <?= esc($selectedCity['name']) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Restaurants Grid -->
    <?php if (isset($restaurants) && !empty($restaurants)): ?>
        <div class="row">
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card restaurant-card h-100">
                        <!-- Restaurant Image Placeholder -->
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center restaurant-image">
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

        <!-- Pagination -->
        <?php if (isset($pager)): ?>
            <div class="row mt-5">
                <div class="col-12 d-flex justify-content-center">
                    <?= $pager->links() ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No Results -->
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No restaurants found</h3>
                <p class="text-muted">Try adjusting your filters or search criteria.</p>
                <a href="<?= base_url('restaurants') ?>" class="btn btn-georgian">
                    <i class="fas fa-refresh"></i> Clear Filters
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>