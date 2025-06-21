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
                    <li class="breadcrumb-item active"><?= esc($state) ?></li>
                </ol>
            </nav>
            
            <h1 class="display-4 fw-bold">Georgian Food in <?= esc($state) ?></h1>
            <p class="lead text-muted">
                Discover authentic Georgian restaurants throughout <?= esc($state) ?> • 
                <?= $totalRestaurants ?> restaurants in <?= count($cities) ?> cities
            </p>
        </div>
    </div>

    <!-- State Stats -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h2 class="fw-bold"><?= $totalRestaurants ?></h2>
                            <p class="mb-0">Georgian Restaurants</p>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold"><?= count($cities) ?></h2>
                            <p class="mb-0">Cities</p>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold">
                                <?php
                                $avgRating = 0;
                                if (!empty($restaurants)) {
                                    $totalRating = array_sum(array_column($restaurants, 'rating'));
                                    $avgRating = $totalRating / count($restaurants);
                                }
                                echo number_format($avgRating, 1);
                                ?>
                            </h2>
                            <p class="mb-0">Average Rating</p>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold">
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
                            </h2>
                            <p class="mb-0">Price Range</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cities in State -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-map-marker-alt"></i> Cities with Georgian Restaurants in <?= esc($state) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($cities as $city): ?>
                            <?php
                            // Count restaurants in this city
                            $cityRestaurantCount = 0;
                            foreach ($restaurants as $restaurant) {
                                if ($restaurant['city_name'] == $city['name']) {
                                    $cityRestaurantCount++;
                                }
                            }
                            ?>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <a href="<?= base_url('restaurants/city/' . $city['id']) ?>" 
                                   class="text-decoration-none">
                                    <div class="card border-0 bg-light h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title fw-bold"><?= esc($city['name']) ?></h6>
                                            <p class="text-muted mb-0">
                                                <?= $cityRestaurantCount ?> restaurant<?= $cityRestaurantCount != 1 ? 's' : '' ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
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
                                <!-- City Filter -->
                                <div class="col-md-3">
                                    <label for="city" class="form-label">City</label>
                                    <select class="form-select" name="city" id="city">
                                        <option value="">All Cities</option>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?= $city['id'] ?>" 
                                                    <?= (isset($selectedCity) && $selectedCity == $city['id']) ? 'selected' : '' ?>>
                                                <?= esc($city['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Price Filter -->
                                <div class="col-md-3">
                                    <label for="price" class="form-label">Price Level</label>
                                    <select class="form-select" name="price" id="price">
                                        <option value="">All Prices</option>
                                        <option value="1" <?= (isset($selectedPrice) && $selectedPrice == 1) ? 'selected' : '' ?>>$ - Budget</option>
                                        <option value="2" <?= (isset($selectedPrice) && $selectedPrice == 2) ? 'selected' : '' ?>>$ - Moderate</option>
                                        <option value="3" <?= (isset($selectedPrice) && $selectedPrice == 3) ? 'selected' : '' ?>>$$ - Expensive</option>
                                        <option value="4" <?= (isset($selectedPrice) && $selectedPrice == 4) ? 'selected' : '' ?>>$$ - Very Expensive</option>
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
                            
                            <!-- Location -->
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= esc($restaurant['city_name']) ?>, <?= esc($state) ?>
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

        <!-- Georgian Cuisine Info for State -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h3>About Georgian Food in <?= esc($state) ?></h3>
                        <p>
                            Georgian cuisine in <?= esc($state) ?> brings the rich culinary traditions of the Caucasus region 
                            to America. From the cheese-filled khachapuri bread to savory khinkali dumplings, 
                            Georgian restaurants in <?= esc($state) ?> offer authentic flavors using traditional recipes 
                            and cooking methods passed down through generations.
                        </p>
                        
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <h6><i class="fas fa-bread-slice"></i> Signature Dishes</h6>
                                <ul class="list-unstyled">
                                    <li>• Khachapuri (cheese bread)</li>
                                    <li>• Khinkali (soup dumplings)</li>
                                    <li>• Mtsvadi (grilled meats)</li>
                                    <li>• Lobio (bean stew)</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-wine-glass"></i> Traditional Drinks</h6>
                                <ul class="list-unstyled">
                                    <li>• Georgian wine</li>
                                    <li>• Chacha (grape brandy)</li>
                                    <li>• Tarkhuna (tarragon soda)</li>
                                    <li>• Georgian tea</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-pepper-hot"></i> Key Ingredients</h6>
                                <ul class="list-unstyled">
                                    <li>• Walnuts</li>
                                    <li>• Fresh herbs</li>
                                    <li>• Georgian spices</li>
                                    <li>• Local cheeses</li>
                                </ul>
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
                <h3>No Georgian Restaurants in <?= esc($state) ?> Yet</h3>
                <p class="text-muted mb-4">
                    We haven't found any Georgian restaurants in <?= esc($state) ?> at the moment.
                    <br>Check back soon as we're always adding new locations!
                </p>
                
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="<?= base_url('restaurants') ?>" class="btn btn-georgian">
                        <i class="fas fa-utensils"></i> Browse All Restaurants
                    </a>
                    <a href="<?= base_url('search') ?>" class="btn btn-outline-dark">
                        <i class="fas fa-search"></i> Search Restaurants
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
  "name": "Georgian Restaurants in <?= esc($state) ?>",
  "description": "Discover authentic Georgian restaurants throughout <?= esc($state) ?>",
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
            "addressLocality": "<?= esc($restaurant['city_name']) ?>",
            "addressRegion": "<?= esc($state) ?>",
            "addressCountry": "US"
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