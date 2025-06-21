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
                    <li class="breadcrumb-item"><a href="<?= base_url(strtolower($dishName)) ?>"><?= esc($dishName) ?></a></li>
                    <li class="breadcrumb-item active"><?= esc($dishName) ?> Near Me</li>
                </ol>
            </nav>
            
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-georgian"><?= esc($dishName) ?> Near Me</h1>
                <p class="lead">Find the closest restaurants serving authentic <?= strtolower(esc($dishName)) ?> using your location</p>
                
                <div class="mt-4">
                    <button id="findNearMe" class="btn btn-georgian btn-lg me-3">
                        <i class="fas fa-location-arrow"></i> Find Near My Location
                    </button>
                    <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-outline-dark btn-lg">
                        Browse All Restaurants
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div id="locationStatus" class="alert alert-info text-center" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Finding your location...
            </div>
            <div id="locationResult" class="alert alert-success text-center" style="display: none;">
                <i class="fas fa-map-marker-alt"></i> <span id="userLocation"></span>
            </div>
        </div>
    </div>

    <!-- Quick Search -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">Or Search by City</h5>
                    <form method="GET" action="<?= base_url('search') ?>">
                        <div class="row g-3 justify-content-center">
                            <div class="col-md-6">
                                <input type="hidden" name="q" value="<?= strtolower(esc($dishName)) ?>">
                                <input type="text" class="form-control form-control-lg" 
                                       placeholder="Enter city name (e.g., New York, Chicago)" 
                                       name="location" id="locationInput">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-georgian btn-lg w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Cities -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="text-center mb-4">Popular Cities for <?= esc($dishName) ?></h3>
            <div class="row justify-content-center">
                <div class="col-md-3 col-6 mb-3">
                    <a href="<?= base_url('georgian-restaurant-nyc') ?>" class="text-decoration-none">
                        <div class="card border-0 bg-light text-center h-100">
                            <div class="card-body">
                                <h6 class="fw-bold text-dark">New York</h6>
                                <small class="text-muted">Most restaurants</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="<?= base_url('georgian-restaurant-chicago') ?>" class="text-decoration-none">
                        <div class="card border-0 bg-light text-center h-100">
                            <div class="card-body">
                                <h6 class="fw-bold text-dark">Chicago</h6>
                                <small class="text-muted">Great selection</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="<?= base_url('georgian-restaurant-manhattan') ?>" class="text-decoration-none">
                        <div class="card border-0 bg-light text-center h-100">
                            <div class="card-body">
                                <h6 class="fw-bold text-dark">Manhattan</h6>
                                <small class="text-muted">Premium dining</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="<?= base_url('search?q=' . strtolower($dishName)) ?>" class="text-decoration-none">
                        <div class="card border-0 bg-primary text-white text-center h-100">
                            <div class="card-body">
                                <h6 class="fw-bold">View All</h6>
                                <small>Find everywhere</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Restaurants List -->
    <div class="row" id="restaurantsList">
        <div class="col-12">
            <h3 class="text-center mb-4">Restaurants Serving <?= esc($dishName) ?></h3>
        </div>

        <?php if (!empty($restaurants)): ?>
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="col-lg-4 col-md-6 mb-4 restaurant-item" 
                     data-lat="<?= $restaurant['latitude'] ?? '' ?>" 
                     data-lng="<?= $restaurant['longitude'] ?? '' ?>">
                    <div class="card restaurant-card h-100">
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center restaurant-image">
                            <i class="fas fa-utensils fa-3x text-muted"></i>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?= esc($restaurant['name']) ?></h5>
                            
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= esc($restaurant['city_name']) ?>
                                <span class="distance-badge ms-2" style="display: none;">
                                    <small class="badge bg-primary"></small>
                                </span>
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
                                        echo ';
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
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h3>No restaurants found</h3>
                <p class="text-muted">We're working on adding more restaurants that serve <?= strtolower(esc($dishName)) ?>.</p>
                <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-georgian">
                    Browse All Georgian Restaurants
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tips Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h4>Tips for Finding <?= esc($dishName) ?> Near You</h4>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <h6><i class="fas fa-clock"></i> Best Times to Visit</h6>
                            <p class="small">
                                <?php if ($dishName === 'Khachapuri'): ?>
                                    Khachapuri is best enjoyed fresh and hot. Visit during lunch or dinner hours for the freshest preparation.
                                <?php else: ?>
                                    Khinkali are typically made fresh daily. Visit in the evening when most restaurants prepare their daily batch.
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-phone"></i> Call Ahead</h6>
                            <p class="small">
                                Some restaurants may run out of handmade <?= strtolower(esc($dishName)) ?> during busy times. 
                                Calling ahead ensures availability.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-star"></i> What to Look For</h6>
                            <p class="small">
                                Authentic Georgian restaurants often have Georgian staff and serve traditional accompaniments 
                                like Georgian wine or tarkhuna.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const findButton = document.getElementById('findNearMe');
    const locationStatus = document.getElementById('locationStatus');
    const locationResult = document.getElementById('locationResult');
    const userLocationSpan = document.getElementById('userLocation');
    
    findButton.addEventListener('click', function() {
        if (navigator.geolocation) {
            locationStatus.style.display = 'block';
            locationResult.style.display = 'none';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // Update UI
                    locationStatus.style.display = 'none';
                    locationResult.style.display = 'block';
                    userLocationSpan.textContent = `Your location found (${lat.toFixed(2)}, ${lng.toFixed(2)})`;
                    
                    // Calculate distances and sort restaurants
                    calculateDistances(lat, lng);
                },
                function(error) {
                    locationStatus.style.display = 'none';
                    alert('Unable to get your location. Please search by city instead.');
                }
            );
        } else {
            alert('Geolocation is not supported by this browser. Please search by city instead.');
        }
    });
    
    function calculateDistances(userLat, userLng) {
        const restaurantItems = document.querySelectorAll('.restaurant-item');
        const restaurantsWithDistance = [];
        
        restaurantItems.forEach(function(item) {
            const lat = parseFloat(item.dataset.lat);
            const lng = parseFloat(item.dataset.lng);
            
            if (lat && lng) {
                const distance = calculateDistance(userLat, userLng, lat, lng);
                restaurantsWithDistance.push({
                    element: item,
                    distance: distance
                });
                
                // Show distance badge
                const badge = item.querySelector('.distance-badge small');
                if (badge) {
                    badge.textContent = distance.toFixed(1) + ' miles';
                    item.querySelector('.distance-badge').style.display = 'inline';
                }
            }
        });
        
        // Sort by distance
        restaurantsWithDistance.sort((a, b) => a.distance - b.distance);
        
        // Reorder elements
        const container = document.getElementById('restaurantsList');
        const titleCol = container.querySelector('.col-12');
        
        restaurantsWithDistance.forEach(function(item) {
            container.appendChild(item.element);
        });
    }
    
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 3959; // Earth's radius in miles
        const dLat = toRad(lat2 - lat1);
        const dLng = toRad(lng2 - lng1);
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    function toRad(degrees) {
        return degrees * (Math.PI/180);
    }
});
</script>

<style>
    .text-georgian {
        color: var(--georgian-red) !important;
    }
    
    .distance-badge {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<?= $this->endSection() ?>