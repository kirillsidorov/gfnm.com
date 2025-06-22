<?php
// ОТЛАДКА: узнаем откуда пришли данные
echo "<!-- DEBUG: Called from method: " . debug_backtrace()[1]['function'] . " -->";
echo "<!-- DEBUG: Photos isset: " . (isset($photos) ? 'YES (' . count($photos) . ')' : 'NO') . " -->";
echo "<!-- DEBUG: MainPhoto isset: " . (isset($mainPhoto) ? 'YES' : 'NO') . " -->";
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php if (isset($restaurant) && !empty($restaurant)): ?>

<!-- Restaurant Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <?php if (isset($breadcrumbs)): ?>
                            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                                <li class="breadcrumb-item <?= $index === count($breadcrumbs) - 1 ? 'active' : '' ?>">
                                    <?php if ($index === count($breadcrumbs) - 1): ?>
                                        <?= esc($breadcrumb['name']) ?>
                                    <?php else: ?>
                                        <a href="<?= esc($breadcrumb['url']) ?>"><?= esc($breadcrumb['name']) ?></a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('georgian-restaurants-' . ($restaurant['city_slug'] ?? 'city')) ?>"><?= esc($restaurant['city_name']) ?></a></li>
                            <li class="breadcrumb-item active"><?= esc($restaurant['name']) ?></li>
                        <?php endif; ?>
                    </ol>
                </nav>
                
                <div class="row">
                    <!-- Restaurant Image/Gallery -->
                    <div class="col-lg-6 mb-4">
                        <?php if (!empty($mainPhoto) || !empty($photos)): ?>
                            <!-- Main Photo -->
                            <div class="main-photo-container mb-3">
                                <?php $displayPhoto = $mainPhoto ?: (!empty($photos) ? $photos[0] : null); ?>
                                <?php if ($displayPhoto): ?>
                                    <img src="<?= base_url($displayPhoto['file_path']) ?>" 
                                         alt="<?= esc($displayPhoto['alt_text'] ?: $restaurant['name']) ?>" 
                                         class="img-fluid rounded shadow main-restaurant-photo"
                                         style="width: 100%; height: 400px; object-fit: cover; cursor: pointer;"
                                         onclick="openPhotoModal(0)">
                                <?php endif; ?>
                            </div>
                            
                            <!-- Photo Gallery Thumbnails -->
                            <?php if (!empty($photos) && count($photos) > 1): ?>
                                <div class="photo-gallery">
                                    <div class="row g-2">
                                        <?php foreach (array_slice($photos, 0, 6) as $index => $photo): ?>
                                            <div class="col-2">
                                                <img src="<?= base_url($photo['file_path']) ?>" 
                                                     alt="<?= esc($photo['alt_text'] ?: $restaurant['name'] . ' photo ' . ($index + 1)) ?>"
                                                     class="img-fluid rounded thumbnail-photo"
                                                     style="width: 100%; height: 60px; object-fit: cover; cursor: pointer;"
                                                     onclick="changeMainPhoto('<?= base_url($photo['file_path']) ?>', '<?= esc($photo['alt_text'] ?: '') ?>')">
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if (count($photos) > 6): ?>
                                            <div class="col-2">
                                                <div class="more-photos-indicator rounded d-flex align-items-center justify-content-center"
                                                     style="width: 100%; height: 60px; background: rgba(0,0,0,0.7); color: white; cursor: pointer;"
                                                     onclick="openPhotoModal(6)">
                                                    <small>+<?= count($photos) - 6 ?></small>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Placeholder if no photos -->
                            <div class="card">
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                                    <div class="text-center">
                                        <i class="fas fa-utensils fa-4x text-muted mb-3"></i>
                                        <p class="text-muted">No photos available</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Restaurant Info -->
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center mb-2">
                            <h1 class="display-5 fw-bold mb-0 me-3"><?= esc($restaurant['name']) ?></h1>
                            
                            <!-- Status Badge -->
                            <?php if (isset($restaurant['status_info'])): ?>
                                <span class="badge <?= $restaurant['status_info']['class'] ?>">
                                    <i class="<?= $restaurant['status_info']['icon'] ?>"></i> <?= $restaurant['status_info']['message'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Rating and Price -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="rating me-3">
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
                                
                                <span class="ms-2 h5 mb-0"><?= number_format($rating, 1) ?></span>
                                <?php if (!empty($restaurant['rating_count'])): ?>
                                    <small class="text-muted ms-1">(<?= $restaurant['rating_count'] ?> reviews)</small>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($restaurant['price_level'])): ?>
                                <div class="price-level h5 mb-0">
                                    <?php
                                    $priceLevel = intval($restaurant['price_level']);
                                    for ($i = 0; $i < $priceLevel; $i++) {
                                        echo '$';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Location -->
                        <p class="h6 text-muted mb-3">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= esc($restaurant['address']) ?>
                        </p>
                        
                        <!-- Description -->
                        <p class="lead mb-4"><?= nl2br(esc($restaurant['description'])) ?></p>
                        
                        <!-- Quick Actions -->
                        <div class="d-flex gap-2 flex-wrap mb-4">
                            <?php if (!empty($restaurant['phone'])): ?>
                                <a href="tel:<?= esc($restaurant['phone']) ?>" class="btn btn-georgian">
                                    <i class="fas fa-phone"></i> Call Now
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($restaurant['website'])): ?>
                                <a href="<?= esc($restaurant['website']) ?>" target="_blank" rel="nofollow" class="btn btn-outline-dark">
                                    <i class="fas fa-globe"></i> Visit Website
                                </a>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-success" onclick="getDirections()">
                                <i class="fas fa-directions"></i> Get Directions
                            </button>
                            
                            <button class="btn btn-outline-primary" onclick="toggleFavorite(<?= $restaurant['id'] ?>)">
                                <i class="far fa-heart" id="favorite-icon"></i> Save
                            </button>
                        </div>

                        <!-- Today's Hours -->
                        <?php if (isset($restaurant['hours_info']['today'])): ?>
                            <div class="card hours-today mb-3">
                                <div class="card-body py-2">
                                    <small class="text-muted">Today's Hours:</small>
                                    <span class="fw-bold ms-2"><?= $restaurant['hours_info']['today']['hours'] ?></span>
                                    <?php if (isset($restaurant['status_info']['next_change'])): ?>
                                        <small class="text-muted ms-2"><?= $restaurant['status_info']['next_change'] ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Restaurant Details -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                
                <!-- Service Options -->
                <?php if (!empty($restaurant['service_options']) && is_array($restaurant['service_options'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-concierge-bell text-georgian"></i> Service Options
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $serviceLabels = [
                            'has_seating_outdoor' => 'Outdoor Seating',
                            'has_curbside_pickup' => 'Curbside Pickup',
                            'has_delivery' => 'Delivery',
                            'has_takeout' => 'Takeout',
                            'serves_dine_in' => 'Dine In'
                        ];
                        ?>
                        <?php foreach ($restaurant['service_options'] as $option): ?>
                            <span class="badge attribute-badge">
                                <i class="fas fa-check"></i> <?= $serviceLabels[$option] ?? ucwords(str_replace('_', ' ', $option)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Dining Options -->
                <?php if (!empty($restaurant['dining_options']) && is_array($restaurant['dining_options'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-utensils text-georgian"></i> Dining Options
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $diningLabels = [
                            'serves_brunch' => 'Brunch',
                            'serves_lunch' => 'Lunch',
                            'serves_dinner' => 'Dinner',
                            'serves_alcohol' => 'Alcohol',
                            'serves_beer' => 'Beer',
                            'serves_wine' => 'Wine',
                            'serves_cocktails' => 'Cocktails',
                            'serves_coffee' => 'Coffee'
                        ];
                        ?>
                        <?php foreach ($restaurant['dining_options'] as $option): ?>
                            <span class="badge attribute-badge">
                                <i class="fas fa-check"></i> <?= $diningLabels[$option] ?? ucwords(str_replace('_', ' ', $option)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Accessibility -->
                <?php if (!empty($restaurant['accessibility_options']) && is_array($restaurant['accessibility_options'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-universal-access text-georgian"></i> Accessibility
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $accessibilityLabels = [
                            'has_wheelchair_accessible_entrance' => 'Wheelchair Accessible Entrance',
                            'has_wheelchair_accessible_restroom' => 'Wheelchair Accessible Restroom',
                            'has_wheelchair_accessible_seating' => 'Wheelchair Accessible Seating'
                        ];
                        ?>
                        <?php foreach ($restaurant['accessibility_options'] as $option): ?>
                            <span class="badge attribute-badge">
                                <i class="fas fa-check"></i> <?= $accessibilityLabels[$option] ?? ucwords(str_replace('_', ' ', $option)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Hours of Operation -->
                <?php if (!empty($restaurant['hours_info']['formatted'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-clock text-georgian"></i> Hours of Operation
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($restaurant['hours_info']['formatted'] as $dayInfo): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 <?= $dayInfo['is_today'] ? 'bg-light rounded px-3' : '' ?>">
                                <span class="fw-bold <?= $dayInfo['is_today'] ? 'text-primary' : '' ?>">
                                    <?= $dayInfo['day'] ?><?= $dayInfo['is_today'] ? ' (Today)' : '' ?>
                                </span>
                                <span class="<?= $dayInfo['is_today'] ? 'fw-bold' : '' ?>">
                                    <?= $dayInfo['hours'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Popular Times -->
                <?php if (!empty($restaurant['popular_times']) && is_array($restaurant['popular_times'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-chart-bar text-georgian"></i> Popular Times
                        </h3>
                        <small class="text-muted">Typical visit duration: 45-90 minutes</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="day-selector" class="form-label">Select Day:</label>
                                <select class="form-select" id="day-selector" onchange="updatePopularTimes()">
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday" selected>Friday</option>
                                    <option value="saturday">Saturday</option>
                                    <option value="sunday">Sunday</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3" id="popular-times-chart">
                            <!-- Заполняется JavaScript -->
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-info-circle text-georgian"></i> Contact Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Address</h6>
                                <p class="text-muted"><?= esc($restaurant['address']) ?></p>
                                
                                <?php if (!empty($restaurant['phone'])): ?>
                                    <h6>Phone</h6>
                                    <p class="text-muted">
                                        <a href="tel:<?= esc($restaurant['phone']) ?>" class="text-decoration-none">
                                            <?= esc($restaurant['phone']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <?php if (!empty($restaurant['website'])): ?>
                                    <h6>Website</h6>
                                    <p class="text-muted">
                                        <a href="<?= esc($restaurant['website']) ?>" target="_blank" rel="nofollow" class="text-decoration-none">
                                            Visit Website
                                        </a>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($restaurant['payment_options']) && is_array($restaurant['payment_options'])): ?>
                                    <h6>Payment Options</h6>
                                    <div>
                                        <?php
                                        $paymentLabels = [
                                            'credit_cards' => 'Credit Cards',
                                            'debit_cards' => 'Debit Cards',
                                            'cash' => 'Cash',
                                            'mobile_payments' => 'Mobile Payments'
                                        ];
                                        $paymentIcons = [
                                            'credit_cards' => 'fas fa-credit-card',
                                            'debit_cards' => 'fas fa-credit-card',
                                            'cash' => 'fas fa-money-bill-wave',
                                            'mobile_payments' => 'fas fa-mobile-alt'
                                        ];
                                        ?>
                                        <?php foreach ($restaurant['payment_options'] as $option): ?>
                                            <span class="badge bg-success me-2 mb-1">
                                                <i class="<?= $paymentIcons[$option] ?? 'fas fa-check' ?>"></i>
                                                <?= $paymentLabels[$option] ?? ucwords(str_replace('_', ' ', $option)) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Map -->
                <?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-map text-georgian"></i> Location</h3>
                    </div>
                    <div class="card-body">
                        <div id="restaurantMap" style="height: 400px; background: #f8f9fa; border-radius: 8px;">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div class="text-center">
                                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Interactive map will load here</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                
                <!-- Rating Breakdown -->
                <?php if (!empty($restaurant['rating_distribution']) && is_array($restaurant['rating_distribution'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star text-warning"></i> Rating Breakdown
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h2 class="display-4 mb-0"><?= number_format($restaurant['rating'], 1) ?></h2>
                            <div class="mb-2">
                                <?php for ($i = 0; $i < floor($restaurant['rating']); $i++): ?>
                                    <i class="fas fa-star rating-stars"></i>
                                <?php endfor; ?>
                                <?php if ($restaurant['rating'] - floor($restaurant['rating']) >= 0.5): ?>
                                    <i class="fas fa-star-half-alt rating-stars"></i>
                                <?php endif; ?>
                                <?php for ($i = 0; $i < (5 - ceil($restaurant['rating'])); $i++): ?>
                                    <i class="far fa-star rating-stars"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted">Based on <?= $restaurant['rating_count'] ?? 0 ?> reviews</small>
                        </div>
                        
                        <?php
                        $total = array_sum($restaurant['rating_distribution']);
                        for ($stars = 5; $stars >= 1; $stars--):
                            $count = $restaurant['rating_distribution'][$stars] ?? 0;
                            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                        ?>
                            <div class="rating-distribution d-flex align-items-center mb-2">
                                <div class="me-2" style="width: 20px;">
                                    <small><?= $stars ?></small>
                                </div>
                                <div class="me-2">
                                    <i class="fas fa-star rating-stars" style="font-size: 0.8rem;"></i>
                                </div>
                                <div class="flex-grow-1 me-2">
                                    <div class="rating-bar">
                                        <div class="rating-bar-fill" style="width: <?= $percentage ?>%;"></div>
                                    </div>
                                </div>
                                <div style="width: 40px;">
                                    <small class="text-muted"><?= $count ?></small>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Atmosphere -->
                <?php if (!empty($restaurant['atmosphere']) && is_array($restaurant['atmosphere'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-heart text-danger"></i> Atmosphere
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $atmosphereLabels = [
                            'casual' => 'Casual',
                            'cozy' => 'Cozy',
                            'romantic' => 'Romantic',
                            'family_friendly' => 'Family Friendly',
                            'groups' => 'Good for Groups'
                        ];
                        ?>
                        <?php foreach ($restaurant['atmosphere'] as $mood): ?>
                            <span class="badge attribute-badge">
                                <i class="fas fa-check"></i> <?= $atmosphereLabels[$mood] ?? ucwords(str_replace('_', ' ', $mood)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- People Also Search - ИСПРАВЛЕННАЯ ВЕРСИЯ -->
                <?php 
                // Правильно обрабатываем people_also_search
                $relatedSearches = [];

                // Если данные есть в restaurant_relations таблице - берем оттуда
                if (isset($restaurant['id'])) {
                    $db = \Config\Database::connect();
                    $relatedRestaurants = $db->table('restaurant_relations')
                                            ->where('restaurant_id', $restaurant['id'])
                                            ->where('relation_type', 'people_also_search')
                                            ->where('related_name !=', '')
                                            ->limit(5)
                                            ->get()
                                            ->getResultArray();
                    
                    foreach ($relatedRestaurants as $related) {
                        if (!empty($related['related_name']) && $related['related_name'] !== '') {
                            $relatedSearches[] = [
                                'name' => $related['related_name'],
                                'rating' => $related['related_rating'],
                                'rating_count' => $related['related_rating_count'],
                                'type' => 'restaurant'
                            ];
                        }
                    }
                }

                // Если нет данных из таблицы связей, пробуем из JSON поля
                if (empty($relatedSearches) && !empty($restaurant['people_also_search'])) {
                    $peopleAlsoSearchData = $restaurant['people_also_search'];
                    
                    // Если это строка JSON - декодируем
                    if (is_string($peopleAlsoSearchData)) {
                        $peopleAlsoSearchData = json_decode($peopleAlsoSearchData, true);
                    }
                    
                    if (is_array($peopleAlsoSearchData)) {
                        foreach ($peopleAlsoSearchData as $search) {
                            if (is_array($search) && !empty($search['title'])) {
                                $relatedSearches[] = [
                                    'name' => $search['title'],
                                    'rating' => $search['rating']['value'] ?? null,
                                    'rating_count' => $search['rating']['votes_count'] ?? null,
                                    'type' => 'restaurant'
                                ];
                            }
                        }
                    }
                }

                // Если всё равно нет данных, добавляем общие поисковые запросы
                if (empty($relatedSearches)) {
                    $cityName = $restaurant['city_name'] ?? 'your area';
                    $relatedSearches = [
                        [
                            'name' => 'Georgian restaurants in ' . $cityName,
                            'type' => 'search'
                        ],
                        [
                            'name' => 'Khachapuri near me',
                            'type' => 'search'
                        ],
                        [
                            'name' => 'Khinkali restaurants',
                            'type' => 'search'
                        ],
                        [
                            'name' => 'Caucasian cuisine ' . $cityName,
                            'type' => 'search'
                        ]
                    ];
                }
                ?>

                <?php if (!empty($relatedSearches)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-search text-info"></i> Related Searches
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($relatedSearches as $search): ?>
                            <?php if (!empty($search['name']) && strlen($search['name']) > 3): ?>
                                <a href="<?= base_url('search?q=' . urlencode($search['name'])) ?>" 
                                class="btn btn-outline-primary btn-sm me-2 mb-2">
                                    <i class="fas fa-search me-1"></i>
                                    <?= esc($search['name']) ?>
                                    
                                    <?php if (!empty($search['rating']) && $search['type'] === 'restaurant'): ?>
                                        <small class="text-muted ms-1">
                                            (★ <?= number_format($search['rating'], 1) ?>)
                                        </small>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Share Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-share-alt text-primary"></i> Share this Restaurant
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-sm" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook"></i> Share on Facebook
                            </button>
                            <button class="btn btn-info btn-sm" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i> Share on Twitter
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="copyLink()">
                                <i class="fas fa-link"></i> Copy Link
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-chart-line text-success"></i> Quick Stats
                        </h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h6 class="mb-1"><?= number_format($restaurant['rating'], 1) ?>/5.0</h6>
                                    <small class="text-muted">Rating</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h6 class="mb-1"><?= str_repeat('$', intval($restaurant['price_level'] ?? 1)) ?></h6>
                                    <small class="text-muted">Price</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h6 class="mb-1"><?= $restaurant['rating_count'] ?? 0 ?></h6>
                                    <small class="text-muted">Reviews</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h6 class="mb-1"><?= count($photos ?? []) ?></h6>
                                    <small class="text-muted">Photos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Similar Restaurants -->
<?php if (isset($similarRestaurants) && !empty($similarRestaurants)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold">More Restaurants in <?= esc($restaurant['city_name']) ?></h2>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($similarRestaurants as $similar): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card restaurant-card h-100">
                        <!-- Restaurant Photo -->
                        <?php if (!empty($similar['main_photo'])): ?>
                            <img src="<?= base_url($similar['main_photo']['file_path']) ?>" 
                                 alt="<?= esc($similar['main_photo']['alt_text'] ?: $similar['name']) ?>"
                                 class="card-img-top"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-utensils fa-2x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold"><?= esc($similar['name']) ?></h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rating small">
                                    <?php
                                    $simRating = floatval($similar['rating']);
                                    for ($i = 0; $i < floor($simRating); $i++): ?>
                                        <i class="fas fa-star rating-stars"></i>
                                    <?php endfor; ?>
                                    <?php if ($simRating - floor($simRating) >= 0.5): ?>
                                        <i class="fas fa-star-half-alt rating-stars"></i>
                                    <?php endif; ?>
                                    <?php for ($i = 0; $i < (5 - ceil($simRating)); $i++): ?>
                                        <i class="far fa-star rating-stars"></i>
                                    <?php endfor; ?>
                                    <span class="ms-1"><?= number_format($simRating, 1) ?></span>
                                </div>
                                
                                <div class="price-level small">
                                    <?= str_repeat('$', intval($similar['price_level'] ?? 1)) ?>
                                </div>
                            </div>
                            
                            <p class="card-text small flex-grow-1">
                                <?= character_limiter(strip_tags($similar['description']), 80) ?>
                            </p>
                            
                            <div class="mt-auto">
                                <a href="<?= base_url($similar['seo_url'] ?: 'restaurant/' . $similar['id']) ?>"
                                   class="btn btn-outline-dark btn-sm w-100">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Photo Modal -->
<?php if (!empty($photos)): ?>
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc($restaurant['name']) ?> - Photos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="photoCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($photos as $index => $photo): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="<?= base_url($photo['file_path']) ?>" 
                                     class="d-block w-100" 
                                     alt="<?= esc($photo['alt_text'] ?: $restaurant['name'] . ' photo ' . ($index + 1)) ?>"
                                     style="max-height: 70vh; object-fit: contain;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($photos) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#photoCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#photoCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                        
                        <div class="carousel-indicators">
                            <?php foreach ($photos as $index => $photo): ?>
                                <button type="button" data-bs-target="#photoCarousel" data-bs-slide-to="<?= $index ?>" 
                                        class="<?= $index === 0 ? 'active' : '' ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted">Photo <span id="currentPhotoNumber">1</span> of <?= count($photos) ?></span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<!-- Restaurant Not Found -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                <h2>Restaurant Not Found</h2>
                <p class="text-muted">The restaurant you're looking for doesn't exist or has been removed.</p>
                <a href="<?= base_url('restaurants') ?>" class="btn btn-georgian">
                    <i class="fas fa-arrow-left"></i> Back to Restaurants
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Structured Data -->
<?php if (isset($structuredData)): ?>
<script type="application/ld+json">
<?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
<?php endif; ?>

<style>
:root {
    --georgian-red: #dc3545;
    --georgian-red-dark: #bb2d3b;
    --georgian-gold: #ffc107;
}

.thumbnail-photo:hover {
    opacity: 0.8;
    transform: scale(1.05);
    transition: all 0.2s;
}

.main-restaurant-photo:hover {
    transform: scale(1.02);
    transition: all 0.3s;
}

.rating-stars {
    color: var(--georgian-gold);
}

.btn-georgian {
    background-color: var(--georgian-red);
    border-color: var(--georgian-red);
    color: white;
}

.btn-georgian:hover {
    background-color: var(--georgian-red-dark);
    border-color: var(--georgian-red-dark);
    color: white;
}

.restaurant-card {
    transition: transform 0.2s;
}

.restaurant-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.attribute-badge {
    background: linear-gradient(135deg, var(--georgian-red), #e85d75);
    color: white;
    border: none;
    font-size: 0.85rem;
    padding: 0.4rem 0.8rem;
    margin: 0.2rem;
    border-radius: 20px;
}

.rating-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.rating-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--georgian-gold), #f39c12);
    border-radius: 4px;
}

.status-open {
    color: #28a745;
    font-weight: bold;
}

.status-closed {
    color: #dc3545;
    font-weight: bold;
}

.status-temporarily-closed {
    color: #ffc107;
    font-weight: bold;
}

.hours-today {
    background: linear-gradient(135deg, #e8f5e8, #d4edda);
    border-left: 4px solid #28a745;
}

.popular-time-bar {
    height: 20px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.popular-time-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
}

.text-georgian {
    color: var(--georgian-red) !important;
}
</style>

<script>
// JavaScript для динамического контента
const restaurantData = <?= json_encode($restaurant) ?>;
const popularTimesData = <?= json_encode($restaurant['popular_times'] ?? []) ?>;

// Функция смены главного фото
function changeMainPhoto(src, alt) {
    const mainPhoto = document.querySelector('.main-restaurant-photo');
    if (mainPhoto) {
        mainPhoto.src = src;
        mainPhoto.alt = alt;
    }
}

// Функция открытия модала фотографий
function openPhotoModal(startIndex) {
    <?php if (!empty($photos)): ?>
    const modal = new bootstrap.Modal(document.getElementById('photoModal'));
    const carousel = bootstrap.Carousel.getOrCreateInstance(document.getElementById('photoCarousel'));
    carousel.to(startIndex);
    modal.show();
    <?php endif; ?>
}

// Обновление популярных времен
function updatePopularTimes() {
    const selectedDay = document.getElementById('day-selector').value;
    const container = document.getElementById('popular-times-chart');
    
    if (!popularTimesData || !popularTimesData[selectedDay]) {
        container.innerHTML = '<p class="text-muted"><i class="fas fa-info-circle"></i> Popular times data not available for this day</p>';
        return;
    }
    
    const times = popularTimesData[selectedDay];
    const hours = ['6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM'];
    
    container.innerHTML = '';
    times.forEach((popularity, index) => {
        if (index < hours.length) {
            let busyLevel = 'Not busy';
            let busyColor = '#28a745';
            if (popularity > 75) {
                busyLevel = 'Very busy';
                busyColor = '#dc3545';
            } else if (popularity > 50) {
                busyLevel = 'Busy';
                busyColor = '#ffc107';
            } else if (popularity > 25) {
                busyLevel = 'Moderately busy';
                busyColor = '#fd7e14';
            }
            
            const hourDiv = document.createElement('div');
            hourDiv.className = 'd-flex align-items-center mb-2';
            hourDiv.innerHTML = `
                <div class="me-3" style="width: 60px;">
                    <small class="text-muted">${hours[index]}</small>
                </div>
                <div class="flex-grow-1 me-3">
                    <div class="popular-time-bar">
                        <div class="popular-time-fill" style="width: ${popularity}%; background-color: ${busyColor};"></div>
                    </div>
                </div>
                <div style="width: 100px;">
                    <small class="text-muted">${busyLevel}</small>
                </div>
            `;
            container.appendChild(hourDiv);
        }
    });
}

// Добавление/удаление из избранного
function toggleFavorite(restaurantId) {
    fetch(`<?= base_url('restaurantsenhance/toggleFavorite/') ?>${restaurantId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = document.getElementById('favorite-icon');
            if (data.is_favorite) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                icon.style.color = '#dc3545';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                icon.style.color = '';
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

// Функция получения направлений
function getDirections() {
    <?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const origin = position.coords.latitude + ',' + position.coords.longitude;
            const destination = '<?= $restaurant['latitude'] ?>,<?= $restaurant['longitude'] ?>';
            const url = `https://www.google.com/maps/dir/${origin}/${destination}`;
            window.open(url, '_blank');
        }, function() {
            const address = encodeURIComponent('<?= esc($restaurant['address']) ?>');
            window.open(`https://www.google.com/maps/search/${address}`, '_blank');
        });
    } else {
        const address = encodeURIComponent('<?= esc($restaurant['address']) ?>');
        window.open(`https://www.google.com/maps/search/${address}`, '_blank');
    }
    <?php else: ?>
    const address = encodeURIComponent('<?= esc($restaurant['address']) ?>');
    window.open(`https://www.google.com/maps/search/${address}`, '_blank');
    <?php endif; ?>
}

// Функции поделиться
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(`Check out ${restaurantData.name} - Georgian restaurant in ${restaurantData.city_name}!`);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=600,height=400');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-secondary');
        }, 2000);
    }, function(err) {
        console.error('Could not copy text: ', err);
        alert('Failed to copy link');
    });
}

// Инициализация популярных времен при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    if (popularTimesData && Object.keys(popularTimesData).length > 0) {
        updatePopularTimes();
    }
    
    // Обновление счетчика фотографий в модале
    <?php if (!empty($photos)): ?>
    const carousel = document.getElementById('photoCarousel');
    if (carousel) {
        carousel.addEventListener('slide.bs.carousel', function(event) {
            const currentPhotoNumber = document.getElementById('currentPhotoNumber');
            if (currentPhotoNumber) {
                currentPhotoNumber.textContent = event.to + 1;
            }
        });
    }
    <?php endif; ?>
});

// Google Maps инициализация
<?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
function initRestaurantMap() {
    const lat = <?= $restaurant['latitude'] ?>;
    const lng = <?= $restaurant['longitude'] ?>;
    
    const map = new google.maps.Map(document.getElementById('restaurantMap'), {
        zoom: 15,
        center: { lat: lat, lng: lng }
    });

    const marker = new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: map,
        title: '<?= esc($restaurant['name']) ?>',
        icon: {
            url: '/assets/images/khinkali-marker.png',
            scaledSize: new google.maps.Size(32, 32),
            anchor: new google.maps.Point(16, 32)
        }
    });

    const infoWindow = new google.maps.InfoWindow({
        content: 
            '<div style="max-width: 200px;">' +
            '<h6><?= esc($restaurant['name']) ?></h6>' +
            '<p class="mb-1 small"><?= esc($restaurant['address']) ?></p>' +
            <?php if (!empty($restaurant['phone'])): ?>
            '<p class="mb-0 small"><a href="tel:<?= esc($restaurant['phone']) ?>"><?= esc($restaurant['phone']) ?></a></p>' +
            <?php endif; ?>
            '</div>'
    });

    marker.addListener('click', function() {
        infoWindow.open(map, marker);
    });
}

// Загрузка карты при готовности страницы
document.addEventListener('DOMContentLoaded', function() {
    if (typeof google !== 'undefined') {
        initRestaurantMap();
    }
});
<?php endif; ?>
</script>

<!-- Load Google Maps if coordinates are available -->
<?php if (!empty($restaurant['latitude']) && !empty($restaurant['longitude'])): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= env('GOOGLE_MAPS_API_KEY') ?>&callback=initRestaurantMap"></script>
<?php endif; ?>

<?= $this->endSection() ?>