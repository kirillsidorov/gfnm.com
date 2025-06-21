<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="map-page">
    <!-- Search Header -->
    <section class="map-search-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-0">Find Georgian Restaurants Near You</h1>
                    <p class="text-muted mb-0">Explore our verified Georgian restaurant collection</p>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary" id="findMyLocation">
                        <i class="fas fa-location-arrow"></i> Find My Location
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Controls -->
    <section class="map-controls bg-light border-bottom">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <form id="mapSearchForm" class="d-flex gap-3 align-items-center py-3">
                        <!-- Search Input -->
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="mapSearchInput"
                                    placeholder="Search for Georgian restaurants..."
                                    autocomplete="off"
                                >
                            </div>
                        </div>

                        <!-- City Filter -->
                        <div class="col-auto">
                            <select class="form-select" id="cityFilter">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= $city['id'] ?>"><?= esc($city['name']) ?>, <?= esc($city['state']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Search Button -->
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Container -->
    <section class="map-container">
        <div class="container-fluid p-0">
            <div class="row g-0">
                <!-- Map -->
                <div class="col-lg-8">
                    <div id="restaurantMap" class="restaurant-map">
                        <div class="map-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading map...</span>
                            </div>
                            <p class="mt-2">Loading map...</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="map-sidebar">
                        <!-- Results Header -->
                        <div class="sidebar-header">
                            <h5 class="mb-0">Search Results</h5>
                            <span class="text-muted" id="resultsCount">0 restaurants found</span>
                        </div>

                        <!-- Filters -->
                        <div class="sidebar-filters">
                            <div class="row g-2">
                                <div class="col-6">
                                    <select class="form-select form-select-sm" id="ratingFilter">
                                        <option value="">Any Rating</option>
                                        <option value="4">4+ Stars</option>
                                        <option value="3">3+ Stars</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select class="form-select form-select-sm" id="priceFilter">
                                        <option value="">Any Price</option>
                                        <option value="1">$</option>
                                        <option value="2">$$</option>
                                        <option value="3">$$$</option>
                                        <option value="4">$$$$</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Results List -->
                        <div class="sidebar-results" id="sidebarResults">
                            <div class="no-results text-center py-5">
                                <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Search for restaurants or use your location to see nearby Georgian restaurants</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.map-page {
    margin-top: -20px;
}

.map-search-header {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    padding: 20px 0;
}

.map-controls {
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.map-container {
    height: calc(100vh - 280px);
    position: relative;
    max-height: 600px;
}

.restaurant-map {
    height: 100%;
    width: 100%;
    position: relative;
}

.map-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 1000;
}

.map-sidebar {
    height: 100%;
    background: #fff;
    border-left: 1px solid #dee2e6;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
}

.sidebar-filters {
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
}

.sidebar-results {
    flex: 1;
    overflow-y: auto;
    padding: 0;
    max-height: calc(100vh - 400px);
}

.restaurant-card-map {
    border: none;
    border-bottom: 1px solid #dee2e6;
    border-radius: 0;
    padding: 20px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.restaurant-card-map:hover {
    background-color: #f8f9fa;
}

.restaurant-card-map.active {
    background-color: #e7f3ff;
    border-left: 4px solid #0d6efd;
}

.restaurant-name-map {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.restaurant-address-map {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.restaurant-meta-map {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.rating-map {
    color: #ffc107;
    font-size: 14px;
}

.price-level-map {
    color: #28a745;
    font-weight: 600;
}

.no-results {
    padding: 40px 20px !important;
}

@media (max-width: 991px) {
    .map-container {
        height: auto;
    }
    
    .restaurant-map {
        height: 400px;
        margin-bottom: 20px;
    }
    
    .sidebar-results {
        max-height: 400px;
    }
}
</style>

<script>
let map;
let markers = [];
let currentInfoWindow = null;
let userLocation = null;

document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    console.log('DOM loaded, waiting for Google Maps...');
});

function initGoogleMaps() {
    console.log('Google Maps API loaded, initializing map...');
    initializeMap();
    loadInitialData();
}

function initializeMap() {
    const defaultCenter = { lat: 40.7589, lng: -73.9851 };
    
    try {
        map = new google.maps.Map(document.getElementById('restaurantMap'), {
            zoom: 10,
            center: defaultCenter
        });

        console.log('Map initialized successfully');
        
        const loadingElement = document.querySelector('.map-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    } catch (error) {
        console.error('Error initializing map:', error);
    }
}

function setupEventListeners() {
    console.log('Setting up event listeners...');
    
    const searchForm = document.getElementById('mapSearchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
    }

    const locationBtn = document.getElementById('findMyLocation');
    if (locationBtn) {
        locationBtn.addEventListener('click', function() {
            findUserLocation();
        });
    }

    const cityFilter = document.getElementById('cityFilter');
    if (cityFilter) {
        cityFilter.addEventListener('change', function() {
            loadInitialData();
        });
    }

    const ratingFilter = document.getElementById('ratingFilter');
    const priceFilter = document.getElementById('priceFilter');
    
    if (ratingFilter) {
        ratingFilter.addEventListener('change', applyFilters);
    }
    if (priceFilter) {
        priceFilter.addEventListener('change', applyFilters);
    }
}

function loadInitialData() {
    console.log('Loading initial data...');
    
    const cityElement = document.getElementById('cityFilter');
    const cityId = cityElement ? cityElement.value : '';
    
    const baseUrl = window.location.origin;
    const url = baseUrl + '/api/map/data?city_id=' + cityId;
    
    console.log('Fetching data from:', url);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                displayRestaurants(data.restaurants || []);
                updateResultsCount(data.total || 0);
            } else {
                console.error('API returned error:', data.message);
                updateResultsCount(0);
            }
        })
        .catch(error => {
            console.error('Error loading data:', error);
            updateResultsCount(0);
        });
}

function performSearch() {
    const queryElement = document.getElementById('mapSearchInput');
    const cityElement = document.getElementById('cityFilter');
    
    if (!queryElement) {
        console.error('Search elements not found');
        return;
    }
    
    const query = queryElement.value.trim();
    const cityId = cityElement ? cityElement.value : '';
    
    if (query.length < 2) {
        alert('Please enter at least 2 characters');
        return;
    }

    console.log('Performing search:', query);

    const formData = new FormData();
    formData.append('query', query);
    if (cityId) {
        formData.append('city_id', cityId);
    }

    const baseUrl = window.location.origin;
    fetch(baseUrl + '/api/map/search', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Search result:', data);
        if (data.success) {
            displayRestaurants(data.restaurants || []);
            updateResultsCount(data.total || 0);
        } else {
            console.error('Search failed:', data.message);
            alert('Search failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error searching:', error);
        alert('Search error: ' + error.message);
    });
}

function findUserLocation() {
    console.log('Finding user location...');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                console.log('User location found:', userLocation);
                
                if (map) {
                    map.setCenter(userLocation);
                    map.setZoom(12);
                    
                    new google.maps.Marker({
                        position: userLocation,
                        map: map,
                        title: 'Your Location',
                        icon: {
                            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
                                '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                '<circle cx="10" cy="10" r="8" fill="#007bff" stroke="#fff" stroke-width="2"/>' +
                                '</svg>'
                            ),
                            scaledSize: new google.maps.Size(20, 20)
                        }
                    });
                    
                    searchNearby();
                }
            },
            function(error) {
                console.error('Error getting location:', error);
                alert('Unable to get your location. Please search manually.');
            }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

function searchNearby() {
    if (!userLocation) {
        console.log('No user location available');
        return;
    }
    
    console.log('Searching nearby restaurants...');
    
    const formData = new FormData();
    formData.append('latitude', userLocation.lat);
    formData.append('longitude', userLocation.lng);
    formData.append('radius', 50);

    const baseUrl = window.location.origin;
    fetch(baseUrl + '/api/map/nearby', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Nearby search result:', data);
        if (data.success) {
            displayRestaurants(data.restaurants || []);
            updateResultsCount(data.total || 0);
        } else {
            console.error('Nearby search failed:', data.message);
        }
    })
    .catch(error => {
        console.error('Error searching nearby:', error);
    });
}

function displayRestaurants(restaurants) {
    console.log('Displaying restaurants:', restaurants.length);
    
    clearMarkers();
    
    const sidebar = document.getElementById('sidebarResults');
    if (!sidebar) {
        console.error('Sidebar element not found');
        return;
    }
    
    sidebar.innerHTML = '';
    
    if (!restaurants || restaurants.length === 0) {
        sidebar.innerHTML = 
            '<div class="no-results text-center py-5">' +
            '<i class="fas fa-search fa-3x text-muted mb-3"></i>' +
            '<p class="text-muted">No restaurants found. Try adjusting your search or filters.</p>' +
            '</div>';
        return;
    }

    restaurants.forEach(function(restaurant, index) {
        if (restaurant.latitude && restaurant.longitude) {
            addMarkerToMap(restaurant, index);
        }
        addCardToSidebar(restaurant, index);
    });
    
    if (markers.length > 0 && map) {
        fitMapToMarkers();
    }
}

function addMarkerToMap(restaurant, index) {
    if (!map) {
        console.log('Map not ready, skipping marker');
        return;
    }
    
    const position = {
        lat: parseFloat(restaurant.latitude),
        lng: parseFloat(restaurant.longitude)
    };
    
    const marker = new google.maps.Marker({
        position: position,
        map: map,
        title: restaurant.name,
        icon: {
            url: '/assets/images/khinkali-marker.png', // Путь к вашей картинке
            scaledSize: new google.maps.Size(64, 64), // Размер иконки
            anchor: new google.maps.Point(16, 32) // Точка привязки (центр низа)
        }
    });

    const infoContent = 
        '<div class="marker-info">' +
        '<h6 class="mb-2">' + restaurant.name + '</h6>' +
        '<p class="mb-1 text-muted small">' + (restaurant.address || 'Address not available') + '</p>' +
        (restaurant.rating ? '<div class="rating mb-2">' + generateStars(restaurant.rating) + ' ' + restaurant.rating + '</div>' : '') +
        (restaurant.price_level ? '<div class="price-level">' + '$'.repeat(restaurant.price_level) + '</div>' : '') +
        (restaurant.seo_url ? '<div class="mt-2"><a href="' + restaurant.seo_url + '" target="_blank" class="btn btn-sm btn-primary">View Details</a></div>' : '') +
        '</div>';

    const infoWindow = new google.maps.InfoWindow({
        content: infoContent
    });

    marker.addListener('click', function() {
        if (currentInfoWindow) {
            currentInfoWindow.close();
        }
        infoWindow.open(map, marker);
        currentInfoWindow = infoWindow;
        
        highlightSidebarCard(index);
    });

    markers.push(marker);
}

function addCardToSidebar(restaurant, index) {
    const sidebar = document.getElementById('sidebarResults');
    if (!sidebar) return;
    
    const cardHtml = 
        '<div class="restaurant-card-map" data-index="' + index + '" onclick="focusMarker(' + index + ')">' +
        '<div class="restaurant-name-map">' + restaurant.name + '</div>' +
        '<div class="restaurant-address-map">' + (restaurant.address || 'Address not available') + '</div>' +
        '<div class="restaurant-meta-map">' +
        '<div>' +
        (restaurant.rating ? '<span class="rating-map">' + generateStars(restaurant.rating) + ' ' + restaurant.rating + '</span>' : '') +
        '</div>' +
        '<div>' +
        (restaurant.price_level ? '<span class="price-level-map">' + '$'.repeat(restaurant.price_level) + '</span>' : '') +
        '</div>' +
        '</div>' +
        (restaurant.distance ? '<div class="text-muted small mt-1">' + restaurant.distance + ' km away</div>' : '') +
        '</div>';
    
    sidebar.insertAdjacentHTML('beforeend', cardHtml);
}

function focusMarker(index) {
    if (markers[index] && map) {
        map.setCenter(markers[index].getPosition());
        map.setZoom(16);
        
        google.maps.event.trigger(markers[index], 'click');
    }
}

function highlightSidebarCard(index) {
    document.querySelectorAll('.restaurant-card-map').forEach(function(card) {
        card.classList.remove('active');
    });
    
    const card = document.querySelector('[data-index="' + index + '"]');
    if (card) {
        card.classList.add('active');
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function clearMarkers() {
    markers.forEach(function(marker) {
        marker.setMap(null);
    });
    markers = [];
    
    if (currentInfoWindow) {
        currentInfoWindow.close();
        currentInfoWindow = null;
    }
}

function fitMapToMarkers() {
    if (markers.length === 0 || !map) return;
    
    const bounds = new google.maps.LatLngBounds();
    markers.forEach(function(marker) {
        bounds.extend(marker.getPosition());
    });
    
    map.fitBounds(bounds);
    
    if (markers.length === 1) {
        map.setZoom(12);
    } else {
        google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
            if (map.getZoom() > 14) {
                map.setZoom(14);
            }
        });
    }
}

function generateStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = (rating - fullStars) >= 0.5;
    let stars = '';
    
    for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star"></i>';
    }
    
    if (hasHalfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
    }
    
    for (let i = 0; i < (5 - Math.ceil(rating)); i++) {
        stars += '<i class="far fa-star"></i>';
    }
    
    return stars;
}

function updateResultsCount(count) {
    const countElement = document.getElementById('resultsCount');
    if (countElement) {
        countElement.textContent = count + ' restaurant' + (count !== 1 ? 's' : '') + ' found';
    }
}

function applyFilters() {
    const ratingFilter = document.getElementById('ratingFilter');
    const priceFilter = document.getElementById('priceFilter');
    
    const ratingValue = ratingFilter ? ratingFilter.value : '';
    const priceValue = priceFilter ? priceFilter.value : '';
    
    document.querySelectorAll('.restaurant-card-map').forEach(function(card, index) {
        let show = true;
        
        if (ratingValue) {
            const ratingElement = card.querySelector('.rating-map');
            if (ratingElement) {
                const cardRating = parseFloat(ratingElement.textContent.split(' ').pop());
                if (cardRating < parseFloat(ratingValue)) {
                    show = false;
                }
            } else {
                show = false;
            }
        }
        
        if (priceValue) {
            const priceElement = card.querySelector('.price-level-map');
            if (priceElement) {
                const cardPrice = priceElement.textContent.length;
                if (cardPrice !== parseInt(priceValue)) {
                    show = false;
                }
            } else {
                show = false;
            }
        }
        
        card.style.display = show ? 'block' : 'none';
        if (markers[index]) {
            markers[index].setVisible(show);
        }
    });
    
    const visibleCards = document.querySelectorAll('.restaurant-card-map:not([style*="display: none"])');
    updateResultsCount(visibleCards.length);
}
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= esc($google_maps_key) ?>&callback=initGoogleMaps&libraries=places"></script>

<?= $this->endSection() ?>