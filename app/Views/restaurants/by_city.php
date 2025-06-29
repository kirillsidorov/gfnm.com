<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <!-- City Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center mb-4">
                <h1 class="display-5 fw-bold text-georgian">
                    <?= isset($city) ? 'Georgian Restaurants in ' . esc($city['name']) : 'Georgian Restaurants' ?>
                </h1>
                <p class="lead text-muted">
                    Discover authentic Georgian cuisine with traditional khachapuri, khinkali, and more
                </p>
            </div>
        </div>
    </div>

    <!-- Search Info (если есть каскадный поиск) -->
    <?php if (isset($search_info) && $search_info['method'] !== 'exact_city'): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <?= esc($search_info['message']) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- City Stats (БЕЗ ЦЕНЫ) -->
    <?php if (!empty($restaurants)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-6">
                                <h3 class="text-georgian fw-bold"><?= count($restaurants) ?></h3>
                                <p class="mb-0">Georgian Restaurants</p>
                            </div>
                            <div class="col-md-6">
                                <h3 class="text-warning fw-bold">
                                    <?php
                                    $avgRating = 0;
                                    if (!empty($restaurants)) {
                                        $ratingsWithValues = array_filter(array_column($restaurants, 'rating'), function($rating) {
                                            return $rating > 0;
                                        });
                                        if (!empty($ratingsWithValues)) {
                                            $avgRating = array_sum($ratingsWithValues) / count($ratingsWithValues);
                                        }
                                    }
                                    echo $avgRating > 0 ? number_format($avgRating, 1) . '★' : 'N/A';
                                    ?>
                                </h3>
                                <p class="mb-0">Average Rating</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
        
    <!-- Filter and Sort (БЕЗ ЦЕНЫ) -->
    <?php if (!empty($restaurants)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="<?= current_url() ?>">
                            <div class="row g-3 align-items-end">
                                <!-- Sort By (убрали price сортировку) -->
                                <div class="col-md-8">
                                    <label for="sort" class="form-label">Sort By</label>
                                    <select class="form-select" name="sort" id="sort">
                                        <option value="rating" <?= (isset($selectedSort) && $selectedSort == 'rating') ? 'selected' : '' ?>>Highest Rated</option>
                                        <option value="name" <?= (isset($selectedSort) && $selectedSort == 'name') ? 'selected' : '' ?>>Name A-Z</option>
                                        <?php if (isset($search_info) && in_array($search_info['method'], ['radius_30', 'radius_50', 'radius_100'])): ?>
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
                        <!-- Restaurant Image -->
                        <?php if (!empty($restaurant['main_photo'])): ?>
                            <div class="card-img-top restaurant-image" style="height: 200px; overflow: hidden;">
                                <img src="<?= base_url($restaurant['main_photo']['file_path']) ?>" 
                                     alt="<?= esc($restaurant['main_photo']['alt_text'] ?: $restaurant['name']) ?>" 
                                     class="img-fluid w-100 h-100" style="object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div class="card-img-top restaurant-image-placeholder d-flex align-items-center justify-content-center" 
                                 style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-utensils fa-3x text-white opacity-50"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <!-- Restaurant Name and Rating -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-0">
                                    <?= esc($restaurant['name']) ?>
                                </h5>
                                <?php if (!empty($restaurant['rating']) && $restaurant['rating'] > 0): ?>
                                    <div class="rating-badge">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star"></i> <?= number_format($restaurant['rating'], 1) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Location -->
                            <div class="location-info mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= esc($restaurant['city_name']) ?>
                                    <?php if (isset($restaurant['distance'])): ?>
                                        • <?= number_format($restaurant['distance'], 1) ?>km away
                                    <?php endif; ?>
                                </small>
                            </div>
                            
                            <!-- Description -->
                            <?php if (!empty($restaurant['description'])): ?>
                                <p class="card-text text-muted small mb-3">
                                    <?= character_limiter(strip_tags($restaurant['description']), 100) ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Action Button -->
                            <div class="mt-auto">
                                <?php 
                                // Генерируем правильный URL для ресторана
                                $restaurantUrl = '';
                                if (!empty($restaurant['seo_url'])) {
                                    $restaurantUrl = $restaurant['seo_url'];
                                } else {
                                    $citySlug = strtolower(str_replace([' ', ','], ['-', ''], $restaurant['city_name']));
                                    $restaurantUrl = $restaurant['slug'] . '-restaurant-' . $citySlug;
                                }
                                ?>
                                <a href="<?= base_url($restaurantUrl) ?>" 
                                   class="btn btn-georgian w-100">
                                    View Restaurant <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- No Results -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted opacity-50"></i>
                    </div>
                    <h3 class="text-muted">No Georgian restaurants found</h3>
                    <p class="text-muted">
                        <?= isset($city) ? 'Try exploring restaurants in nearby areas or ' : '' ?>
                        <a href="<?= base_url('restaurants') ?>" class="text-decoration-none">browse all restaurants</a>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Related Cities или Suggestions -->
    <?php if (isset($city) && !empty($restaurants)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h4 class="fw-bold mb-3">Explore More Georgian Food</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <a href="<?= base_url('restaurants') ?>" class="btn btn-outline-georgian">
                                    <i class="fas fa-list me-2"></i>All Restaurants
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-outline-georgian">
                                    <i class="fas fa-map-marker-alt me-2"></i>Near Me
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?= base_url() ?>" class="btn btn-outline-georgian">
                                    <i class="fas fa-home me-2"></i>Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- JSON-LD Schema (БЕЗ ЦЕНЫ) -->
<?php if (!empty($restaurants)): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Restaurant",
  "name": "Georgian Restaurants in <?= isset($city) ? esc($city['name']) : 'Multiple Locations' ?>",
  "description": "Find authentic Georgian restaurants serving traditional khachapuri, khinkali, and Georgian cuisine",
  "@graph": [
    <?php foreach ($restaurants as $index => $restaurant): ?>
        {
          "@type": "Restaurant",
          "name": "<?= esc($restaurant['name']) ?>",
          "description": "<?= esc(strip_tags($restaurant['description'] ?? 'Authentic Georgian restaurant')) ?>",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "<?= esc($restaurant['address']) ?>",
            "addressLocality": "<?= esc($restaurant['city_name']) ?>"
            <?php if (isset($city['state'])): ?>
            ,"addressRegion": "<?= esc($city['state']) ?>"
            <?php endif; ?>
            ,"addressCountry": "US"
          }
          <?php if (!empty($restaurant['rating']) && $restaurant['rating'] > 0): ?>
          ,"aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "<?= number_format($restaurant['rating'], 1) ?>",
            "bestRating": "5"
          }
          <?php endif; ?>
          ,"servesCuisine": "Georgian"
          <?php if (!empty($restaurant['phone'])): ?>
          ,"telephone": "<?= esc($restaurant['phone']) ?>"
          <?php endif; ?>
          <?php if (!empty($restaurant['website'])): ?>
          ,"url": "<?= esc($restaurant['website']) ?>"
          <?php endif; ?>
        }<?= ($index < count($restaurants) - 1) ? ',' : '' ?>
      <?php endforeach; ?>
  ]
}
</script>
<?php endif; ?>

<?= $this->endSection() ?>