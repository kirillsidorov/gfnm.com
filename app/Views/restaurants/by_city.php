<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('restaurants') ?>">Restaurants</a></li>
                    <li class="breadcrumb-item active"><?= esc($city['name']) ?></li>
                </ol>
            </nav>
            
            <!-- Dynamic Title Based on City and State -->
            <?php
            $pageTitle = 'Georgian Food in ' . esc($city['name']);
            $metaDescription = 'Find the best Georgian restaurants in ' . esc($city['name']);
            
            // If state exists, create state-specific content
            if (!empty($city['state'])) {
                $stateTitle = 'Georgian Food in ' . esc($city['state']);
                $stateDescription = 'Discover authentic Georgian cuisine throughout ' . esc($city['state']);
            }
            ?>
            
            <h1 class="display-5 fw-bold"><?= $pageTitle ?></h1>
            
            <?php if (!empty($city['state'])): ?>
                <p class="lead text-muted">
                    Discover authentic Georgian restaurants in <?= esc($city['name']) ?>, <?= esc($city['state']) ?>
                    • <a href="<?= base_url('restaurants/state/' . urlencode($city['state'])) ?>" class="text-decoration-none">
                        View all Georgian restaurants in <?= esc($city['state']) ?>
                    </a>
                </p>
            <?php else: ?>
                <p class="lead text-muted">Discover authentic Georgian restaurants in <?= esc($city['name']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- City Stats -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3 class="text-primary fw-bold"><?= $totalRestaurants ?></h3>
                            <p class="mb-0">Georgian Restaurants</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-success fw-bold">
                                <?php
                                $avgRating = 0;
                                if (!empty($restaurants)) {
                                    $totalRating = array_sum(array_column($restaurants, 'rating'));
                                    $avgRating = $totalRating / count($restaurants);
                                }
                                echo number_format($avgRating, 1);
                                ?>
                            </h3>
                            <p class="mb-0">Average Rating</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-warning fw-bold">
                                <?php
                                $priceRange = '';
                                if (!empty($restaurants)) {
                                    $prices = array_column($restaurants, 'price_level');
                                    $minPrice = min($prices);
                                    $maxPrice = max($prices);
                                    
                                    for ($i = 0; $i < $minPrice; $i++) $priceRange .= '$';
                                    if ($minPrice != $maxPrice) {
                                        $priceRange .= ' - ';
                                        for ($i = 0; $i < $maxPrice; $i++) $priceRange .= '$';
                                    }
                                }
                                echo $priceRange ?: 'N/A';
                                ?>
                            </h3>
                            <p class="mb-0">Price Range</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="d-grid gap-2">
                <a href="<?= base_url('search?city=' . $city['id']) ?>" class="btn btn-georgian">
                    <i class="fas fa-search"></i> Search in <?= esc($city['name']) ?>
                </a>
                
                <?php if (!empty($city['state'])): ?>
                    <a href="<?= base_url('restaurants/state/' . urlencode($city['state'])) ?>" class="btn btn-outline-primary">
                        <i class="fas fa-map"></i> All <?= esc($city['state']) ?> Restaurants
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter and Sort -->
    <?php if (!empty($restaurants)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="<?= current_url() ?>">
                            <div class="row g-3 align-items-end">
                                <!-- Price Filter -->
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <label for="sort" class="form-label">Sort By</label>
                                    <select class="form-select" name="sort" id="sort">
                                        <option value="rating" <?= (isset($selectedSort) && $selectedSort == 'rating') ? 'selected' : '' ?>>Highest Rated</option>
                                        <option value="name" <?= (isset($selectedSort) && $selectedSort == 'name') ? 'selected' : '' ?>>Name A-Z</option>
                                        <option value="price_low" <?= (isset($selectedSort) && $selectedSort == 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
                                        <option value="price_high" <?= (isset($selectedSort) && $selectedSort == 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
                                    </select>
                                </div>

                                <!-- Filter Button -->
                                <div class="col-md-4">
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
    <?php endif; ?>

    <!-- Restaurants Grid -->
    <?php if (!empty($restaurants)): ?>
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

        <!-- Georgian Cuisine Info for City -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h3>About Georgian Food in <?= esc($city['name']) ?></h3>
                        <p>
                            Georgian cuisine in <?= esc($city['name']) ?> offers a unique blend of European and Asian influences, 
                            featuring signature dishes like khachapuri (cheese-filled bread), khinkali (soup dumplings), 
                            and mtsvadi (grilled meat skewers). The rich culinary tradition emphasizes fresh herbs, 
                            walnuts, and distinctive spice blends that create unforgettable flavors.
                        </p>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-star"></i> Popular Dishes in <?= esc($city['name']) ?></h6>
                                <ul class="list-unstyled">
                                    <li>• Khachapuri - Traditional cheese-filled bread</li>
                                    <li>• Khinkali - Handmade soup dumplings</li>
                                    <li>• Mtsvadi - Georgian grilled meat</li>
                                    <li>• Lobio - Hearty bean stew</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($city['state'])): ?>
                                    <h6><i class="fas fa-map-marker-alt"></i> Explore More</h6>
                                    <p>
                                        <a href="<?= base_url('restaurants/state/' . urlencode($city['state'])) ?>" class="text-decoration-none">
                                            <strong>Georgian Food in <?= esc($city['state']) ?></strong>
                                        </a><br>
                                        <small class="text-muted">Discover restaurants throughout the state</small>
                                    </p>
                                <?php endif; ?>
                                
                                <p>
                                    <a href="<?= base_url('search?city=' . $city['id']) ?>" class="text-decoration-none">
                                        <strong>Search in <?= esc($city['name']) ?></strong>
                                    </a><br>
                                    <small class="text-muted">Find specific dishes or restaurant types</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- No Restaurants Found -->
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                <h3>No Georgian Restaurants in <?= esc($city['name']) ?> Yet</h3>
                <p class="text-muted mb-4">
                    We haven't found any Georgian restaurants in <?= esc($city['name']) ?> at the moment.
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
                    
                    <a href="<?= base_url('restaurants') ?>" class="btn btn-outline-dark">
                        <i class="fas fa-utensils"></i> Browse All Restaurants
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Structured Data for SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Georgian Restaurants in <?= esc($city['name']) ?>",
  "description": "<?= $metaDescription ?>",
  "numberOfItems": <?= $totalRestaurants ?>,
  "itemListElement": [
    <?php if (!empty($restaurants)): ?>
      <?php foreach ($restaurants as $index => $restaurant): ?>
        {
          "@type": "Restaurant",
          "position": <?= $index + 1 ?>,
          "name": "<?= esc($restaurant['name']) ?>",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "<?= esc($restaurant['address']) ?>",
            "addressLocality": "<?= esc($city['name']) ?>"
            <?php if (!empty($city['state'])): ?>
            ,"addressRegion": "<?= esc($city['state']) ?>"
            <?php endif; ?>
            ,"addressCountry": "US"
          },
          "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "<?= number_format($restaurant['rating'], 1) ?>",
            "bestRating": "5"
          },
          "priceRange": "<?php 
            $priceLevel = intval($restaurant['price_level']);
            for ($i = 0; $i < $priceLevel; $i++) echo '$';
          ?>",
          "servesCuisine": "Georgian"
          <?php if (!empty($restaurant['phone'])): ?>
          ,"telephone": "<?= esc($restaurant['phone']) ?>"
          <?php endif; ?>
          <?php if (!empty($restaurant['website'])): ?>
          ,"url": "<?= esc($restaurant['website']) ?>"
          <?php endif; ?>
        }<?= ($index < count($restaurants) - 1) ? ',' : '' ?>
      <?php endforeach; ?>
    <?php endif; ?>
  ]
}
</script>

<?= $this->endSection() ?>