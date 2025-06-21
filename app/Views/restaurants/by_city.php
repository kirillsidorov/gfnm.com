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
                    <li class="breadcrumb-item active"><?= esc($city['name']) ?></li>
                </ol>
            </nav>
            
            <!-- Dynamic Title Based on City and State -->
            <?php
            $pageTitle = 'Georgian Food in ' . esc($city['name']);
            $metaDescription = 'Find the Best Georgian restaurants in ' . esc($city['name']);
            ?>
            
            <h1 class="display-5 fw-bold"><?= $pageTitle ?></h1>
            
            <?php if (!empty($city['state'])): ?>
                <p class="lead text-muted">
                    Discover authentic Georgian restaurants in <?= esc($city['name']) ?>, <?= esc($city['state']) ?>
                </p>
            <?php else: ?>
                <p class="lead text-muted">Discover authentic Georgian restaurants in <?= esc($city['name']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- НОВОЕ: Информация о методе поиска -->
    <?php if (isset($search_info) && $search_info['method'] !== 'exact_city'): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        <div>
                            <strong><?= $search_info['message'] ?></strong>
                            <span class="badge bg-info ms-2"><?= $search_info['count'] ?> found</span>
                            
                            <?php if ($search_info['method'] === 'radius_50'): ?>
                                <small class="d-block text-muted mt-1">
                                    <i class="fas fa-location-dot"></i> 
                                    Showing restaurants within 50km radius for better results
                                </small>
                            <?php elseif ($search_info['method'] === 'radius_100'): ?>
                                <small class="d-block text-muted mt-1">
                                    <i class="fas fa-search"></i> 
                                    Expanded search to 100km radius to find more options
                                </small>
                            <?php elseif ($search_info['method'] === 'state' || $search_info['method'] === 'state_ny'): ?>
                                <small class="d-block text-muted mt-1">
                                    <i class="fas fa-map"></i> 
                                    Showing restaurants from across the state
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- City Stats -->
    <div class="row mb-4">
        <div class="col-12">
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
                                        <?php if (isset($search_info) && in_array($search_info['method'], ['radius_50', 'radius_100'])): ?>
                                            <option value="distance" <?= (isset($selectedSort) && $selectedSort == 'distance') ? 'selected' : '' ?>>Distance</option>
                                        <?php endif; ?>
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
                            
                            <!-- НОВОЕ: Показываем расстояние если доступно -->
                            <?php if (isset($restaurant['distance'])): ?>
                                <small class="text-primary mb-2">
                                    <i class="fas fa-location-dot"></i>
                                    <?= number_format($restaurant['distance'], 1) ?> km from <?= esc($city['name']) ?>
                                </small>
                            <?php endif; ?>
                            
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
                                <?php if (!empty($restaurant['city_name']) && $restaurant['city_name'] !== $city['name']): ?>
                                    <br><small class="text-primary"><?= esc($restaurant['city_name']) ?></small>
                                <?php endif; ?>
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
                            
                            <div class="mt-auto">
                                <?php 
                                // ИСПРАВЛЕНО: Генерируем правильный URL для ресторана
                                $restaurantUrl = '';
                                if (!empty($restaurant['seo_url'])) {
                                    $restaurantUrl = $restaurant['seo_url'];
                                } else {
                                    // Генерируем SEO URL из slug ресторана и города
                                    $citySlug = !empty($city['slug']) ? $city['slug'] : strtolower(str_replace([' ', ','], ['-', ''], $city['name']));
                                    $restaurantUrl = $restaurant['slug'] . '-restaurant-' . $citySlug;
                                }
                                ?>
                                <a href="<?= base_url($restaurantUrl) ?>" 
                                   class="btn btn-georgian w-100">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ОБНОВЛЕНО: Georgian Cuisine Info for City с учетом каскадного поиска -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <?php if (isset($search_info) && $search_info['method'] === 'exact_city'): ?>
                            <h3>About Georgian Food in <?= esc($city['name']) ?></h3>
                            <p>
                                Georgian cuisine in <?= esc($city['name']) ?> offers a unique blend of European and Asian influences, 
                                featuring signature dishes like khachapuri (cheese-filled bread), khinkali (soup dumplings), 
                                and mtsvadi (grilled meat skewers). The rich culinary tradition emphasizes fresh herbs, 
                                walnuts, and distinctive spice blends that create unforgettable flavors.
                            </p>
                        <?php else: ?>
                            <h3>Georgian Food Near <?= esc($city['name']) ?></h3>
                            <p>
                                While we've expanded our search to find Georgian restaurants near <?= esc($city['name']) ?>, 
                                you'll still experience the authentic flavors of Georgia. These restaurants specialize in 
                                traditional dishes like khachapuri, khinkali, and mtsvadi, maintaining the rich culinary 
                                heritage that makes Georgian cuisine unique.
                            </p>
                        <?php endif; ?>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-star"></i> Popular Georgian Dishes</h6>
                                <ul class="list-unstyled">
                                    <li>• Khachapuri - Traditional cheese-filled bread</li>
                                    <li>• Khinkali - Handmade soup dumplings</li>
                                    <li>• Mtsvadi - Georgian grilled meat</li>
                                    <li>• Lobio - Hearty bean stew</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($city['state'])): ?>
                                    <h6><i class="fas fa-map-marker-alt"></i> Other Cities in <?= esc($city['state']) ?></h6>
                                    <?php
                                    // Получаем другие города из того же штата
                                    $otherCities = [];
                                    if (isset($cities)) {
                                        foreach ($cities as $otherCity) {
                                            if ($otherCity['state'] == $city['state'] && $otherCity['id'] != $city['id']) {
                                                $otherCities[] = $otherCity;
                                            }
                                        }
                                    }
                                    ?>
                                    
                                    <?php if (!empty($otherCities)): ?>
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <?php foreach (array_slice($otherCities, 0, 4) as $otherCity): ?>
                                                <a href="<?= base_url('georgian-restaurants-' . $otherCity['slug']) ?>" 
                                                class="btn btn-outline-primary btn-sm">
                                                    <?= esc($otherCity['name']) ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small">Only city with Georgian restaurants in <?= esc($city['state']) ?></p>
                                    <?php endif; ?>
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
        <!-- ОБНОВЛЕНО: No Restaurants Found с лучшими предложениями -->
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                <h3>No Georgian Restaurants Found</h3>
                <p class="text-muted mb-4">
                    We searched extensively but couldn't find any Georgian restaurants near <?= esc($city['name']) ?>.
                    <?php if (!empty($city['state'])): ?>
                        <br>Try exploring other cities in <?= esc($city['state']) ?> or expand your search area.
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
                    
                    <a href="<?= base_url('search') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i> Search Nearby Cities
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ДОБАВЛЕНО: CSS стили -->
<style>
.rating-stars {
    color: #ffc107;
}

.btn-georgian {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-georgian:hover {
    background-color: #bb2d3b;
    border-color: #bb2d3b;
    color: white;
}

.restaurant-card {
    transition: transform 0.2s;
}

.restaurant-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.restaurant-image img {
    transition: transform 0.3s ease;
}

.restaurant-card:hover .restaurant-image img {
    transform: scale(1.05);
}

/* НОВОЕ: Стили для информационного блока */
.alert-info {
    background-color: #e7f3ff;
    border-left: 4px solid #0084ff;
}

.alert-info .badge {
    font-size: 0.75rem;
}
</style>

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