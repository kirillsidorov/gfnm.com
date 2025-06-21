<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="hero-section-near-me">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <h1 class="display-4 fw-bold mb-4">Georgian Restaurant Near Me</h1>
                <p class="lead mb-4">Find authentic Georgian restaurants in your area with real-time location search</p>
                
                <!-- Location Status -->
                <div id="locationStatus" class="alert alert-info d-none mb-4">
                    <i class="fas fa-location-dot"></i> <span id="locationText">Getting your location...</span>
                </div>
                
                <!-- Search Form -->
                <div class="search-container">
                    <form id="nearMeSearchForm" class="search-form-near-me">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="searchQuery" class="form-label text-dark">Search for</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="searchQuery" 
                                       name="q"
                                       value="<?= esc($searchQuery ?? '') ?>"
                                       placeholder="Restaurant name or dish..."
                                       autocomplete="off">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="radiusSelect" class="form-label text-dark">Within</label>
                                <select class="form-select form-select-lg" id="radiusSelect" name="radius">
                                    <option value="5" <?= ($defaultRadius == 5) ? 'selected' : '' ?>>5 km</option>
                                    <option value="10" <?= ($defaultRadius == 10) ? 'selected' : '' ?>>10 km</option>
                                    <option value="25" <?= ($defaultRadius == 25) ? 'selected' : '' ?>>25 km</option>
                                    <option value="50" <?= ($defaultRadius == 50) ? 'selected' : '' ?>>50 km</option>
                                    <option value="100" <?= ($defaultRadius == 100) ? 'selected' : '' ?>>100 km</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <button type="button" id="getLocationBtn" class="btn btn-warning btn-lg w-100">
                                    <i class="fas fa-crosshairs"></i> Find Near Me
                                </button>
                            </div>
                            
                            <div class="col-md-2">
                                <button type="submit" id="searchBtn" class="btn btn-georgian btn-lg w-100" disabled>
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Quick Location Suggestions -->
                <div class="quick-locations mt-4">
                    <p class="text-white-50 mb-3">Or search in popular areas:</p>
                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        <a href="<?= base_url('georgian-restaurants-manhattan') ?>" class="btn btn-outline-light btn-sm">Manhattan</a>
                        <a href="<?= base_url('georgian-restaurants-brooklyn') ?>" class="btn btn-outline-light btn-sm">Brooklyn</a>
                        <a href="<?= base_url('georgian-restaurants-chicago') ?>" class="btn btn-outline-light btn-sm">Chicago</a>
                        <a href="<?= base_url('georgian-restaurants-nyc') ?>" class="btn btn-outline-light btn-sm">NYC</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Loading Section -->
<div id="loadingSection" class="py-5 d-none">
    <div class="container text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Searching for Georgian restaurants near you...</p>
    </div>
</div>

<!-- Results Section -->
<div id="resultsSection" class="py-5 d-none">
    <div class="container">
        <!-- Results Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 id="resultsTitle" class="h3 mb-3"></h2>
                <div id="resultsInfo" class="alert alert-success"></div>
            </div>
        </div>
        
        <!-- Interactive Map -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="map-container">
                    <div id="nearMeMap" style="height: 400px; border-radius: 15px; overflow: hidden; background: #f8f9fa;">
                        <div class="map-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading map...</span>
                            </div>
                            <p class="mt-2">Map will load after getting your location</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Restaurant Cards -->
        <div class="row" id="restaurantResults">
            <!-- Динамически заполняется JavaScript -->
        </div>
        
        <!-- No Results -->
        <div id="noResults" class="text-center py-5 d-none">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h3>No Georgian restaurants found nearby</h3>
            <p class="text-muted mb-4">Try expanding your search radius or search in a different area.</p>
            <div class="d-flex justify-content-center gap-3">
                <button class="btn btn-outline-primary" onclick="expandSearch()">
                    <i class="fas fa-expand-arrows-alt"></i> Expand Search
                </button>
                <a href="<?= base_url('restaurants') ?>" class="btn btn-georgian">
                    <i class="fas fa-list"></i> Browse All Restaurants
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section-near-me {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 100px 0 80px;
    position: relative;
    overflow: hidden;
}

.search-container {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
}

.restaurant-card-near-me {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    margin-bottom: 20px;
    cursor: pointer;
}

.restaurant-card-near-me:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.restaurant-card-near-me.active {
    border: 2px solid #007bff;
    box-shadow: 0 0 20px rgba(0, 123, 255, 0.3);
}

.distance-badge {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.map-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 1000;
}

@media (max-width: 768px) {
    .hero-section-near-me {
        padding: 80px 0 60px;
    }
    
    .search-container {
        padding: 20px;
        margin: 0 15px;
    }
}
</style>

<script>
// СКОПИРОВАНО И АДАПТИРОВАНО ИЗ index.php
let map;
let markers = [];
let currentInfoWindow = null;
let userLocation = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('Near Me page loaded, setting up...');
    setupEventListeners();
});

// ТОЧНО КАК В index.php
function initGoogleMaps() {
    console.log('Google Maps API loaded, ready to initialize map...');
    // Карта будет инициализирована после получения местоположения
}

function setupEventListeners() {
    console.log('Setting up event listeners...');
    
    const getLocationBtn = document.getElementById('getLocationBtn');
    const searchBtn = document.getElementById('searchBtn');
    const nearMeForm = document.getElementById('nearMeSearchForm');
    const locationStatus = document.getElementById('locationStatus');
    const locationText = document.getElementById('locationText');

    // Обработчик кнопки геолокации
    getLocationBtn.addEventListener('click', function() {
        console.log('Find Near Me button clicked');
        findUserLocation();
    });

    // Обработчик формы поиска
    nearMeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (userLocation) {
            performSearch();
        } else {
            alert('Please get your location first by clicking "Find Near Me" button.');
        }
    });
}

// АДАПТИРОВАНО ИЗ index.php
function findUserLocation() {
    const locationStatus = document.getElementById('locationStatus');
    const locationText = document.getElementById('locationText');
    const getLocationBtn = document.getElementById('getLocationBtn');
    const searchBtn = document.getElementById('searchBtn');
    
    if (navigator.geolocation) {
        locationStatus.classList.remove('d-none');
        locationText.textContent = 'Getting your location...';
        getLocationBtn.disabled = true;
        getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Locating...';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                console.log('User location found:', userLocation);

                locationText.textContent = 'Location found! Now searching for nearby restaurants...';
                locationStatus.className = 'alert alert-success mb-4';
                
                searchBtn.disabled = false;
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="fas fa-check"></i> Located';
                getLocationBtn.className = 'btn btn-success btn-lg w-100';

                // Инициализируем карту
                initializeMap();
                
                // Выполняем поиск
                setTimeout(() => {
                    performSearch();
                }, 1000);
            },
            function(error) {
                console.error('Geolocation error:', error);
                let errorMessage = 'Location access denied or unavailable.';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Location access denied. Please enable location services.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Location information unavailable.';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Location request timed out.';
                        break;
                }
                
                locationText.textContent = errorMessage;
                locationStatus.className = 'alert alert-warning mb-4';
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Try Again';
            }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// СКОПИРОВАНО ИЗ index.php И АДАПТИРОВАНО
function initializeMap() {
    if (!userLocation) {
        console.log('Cannot initialize map: no user location');
        return;
    }

    try {
        map = new google.maps.Map(document.getElementById('nearMeMap'), {
            zoom: 12,
            center: userLocation
        });

        console.log('Map initialized successfully');
        
        // Убираем лоадер
        const loadingElement = document.querySelector('.map-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }

        // Добавляем маркер пользователя (КАК В index.php)
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

    } catch (error) {
        console.error('Error initializing map:', error);
    }
}

// ПОИСК - ТОЧНО КАК В index.php НО С ДРУГИМ API
function performSearch() {
    if (!userLocation) return;

    const query = document.getElementById('searchQuery').value;
    const radius = document.getElementById('radiusSelect').value;

    console.log('Performing search:', { query, radius, userLocation });

    document.getElementById('loadingSection').classList.remove('d-none');
    document.getElementById('resultsSection').classList.add('d-none');

    // ИСПОЛЬЗУЕМ ТОТ ЖЕ ПОДХОД ЧТО И В index.php
    const formData = new FormData();
    formData.append('latitude', userLocation.lat);
    formData.append('longitude', userLocation.lng);
    formData.append('radius', radius);
    if (query) {
        formData.append('query', query);
    }

    const baseUrl = window.location.origin;
    fetch(baseUrl + '/api/map/nearby', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Search response:', data);
        document.getElementById('loadingSection').classList.add('d-none');
        
        if (data.success && data.restaurants && data.restaurants.length > 0) {
            displayResults(data.restaurants, query, radius);
        } else {
            showNoResults();
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        document.getElementById('loadingSection').classList.add('d-none');
        showNoResults();
    });
}

// ОТОБРАЖЕНИЕ РЕЗУЛЬТАТОВ - АДАПТИРОВАНО ИЗ index.php
function displayResults(restaurants, query, radius) {
    const resultsSection = document.getElementById('resultsSection');
    const resultsTitle = document.getElementById('resultsTitle');
    const resultsInfo = document.getElementById('resultsInfo');
    const restaurantResults = document.getElementById('restaurantResults');

    const searchText = query ? `"${query}"` : 'Georgian restaurants';
    resultsTitle.textContent = `${restaurants.length} ${searchText} found near you`;
    resultsInfo.innerHTML = `<i class="fas fa-location-dot"></i> Within ${radius}km of your location • Sorted by distance`;

    restaurantResults.innerHTML = '';
    clearMarkers();

    restaurants.forEach((restaurant, index) => {
        const restaurantCard = createRestaurantCard(restaurant, index);
        restaurantResults.appendChild(restaurantCard);
        
        if (restaurant.latitude && restaurant.longitude) {
            addMarkerToMap(restaurant, index);
        }
    });

    resultsSection.classList.remove('d-none');
    document.getElementById('noResults').classList.add('d-none');

    if (markers.length > 0 && map) {
        fitMapToMarkers();
    }

    resultsSection.scrollIntoView({ behavior: 'smooth' });
}

// СОЗДАНИЕ КАРТОЧКИ - АДАПТИРОВАНО
function createRestaurantCard(restaurant, index) {
    const col = document.createElement('div');
    col.className = 'col-lg-4 col-md-6 mb-4';

    const distance = parseFloat(restaurant.distance || 0);
    const rating = parseFloat(restaurant.rating || 4.0);
    const priceLevel = '$'.repeat(restaurant.price_level || 2);

    col.innerHTML = `
        <div class="restaurant-card-near-me" data-index="${index}" onclick="focusMarker(${index})">
            <div class="card-header p-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">${restaurant.name}</h5>
                ${distance > 0 ? `<span class="distance-badge">${distance.toFixed(1)} km</span>` : ''}
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="rating">
                            ${generateStars(rating)}
                            <span class="ms-2 text-muted">${rating.toFixed(1)}</span>
                        </div>
                        <span class="text-success fw-bold">${priceLevel}</span>
                    </div>
                </div>
                
                <p class="text-muted mb-3">
                    <i class="fas fa-map-marker-alt"></i> ${restaurant.address || 'Address not available'}
                    ${restaurant.city_name ? `, ${restaurant.city_name}` : ''}
                </p>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-georgian btn-sm" onclick="getDirections('${restaurant.name}', '${restaurant.address}')">
                        <i class="fas fa-directions"></i> Get Directions
                    </button>
                    ${restaurant.phone ? `<a href="tel:${restaurant.phone}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-phone"></i> ${restaurant.phone}
                    </a>` : ''}
                </div>
            </div>
        </div>
    `;

    return col;
}

// ФУНКЦИИ КАРТЫ - СКОПИРОВАНЫ ИЗ index.php
function addMarkerToMap(restaurant, index) {
    if (!map) return;
    
    const position = {
        lat: parseFloat(restaurant.latitude),
        lng: parseFloat(restaurant.longitude)
    };
    
    const marker = new google.maps.Marker({
        position: position,
        map: map,
        title: restaurant.name
    });

    const infoContent = 
        '<div class="marker-info">' +
        '<h6 class="mb-2">' + restaurant.name + '</h6>' +
        '<p class="mb-1 text-muted small">' + (restaurant.address || 'Address not available') + '</p>' +
        (restaurant.rating ? '<div class="rating mb-2">' + generateStars(restaurant.rating) + ' ' + restaurant.rating + '</div>' : '') +
        (restaurant.price_level ? '<div class="price-level">' + '$'.repeat(restaurant.price_level) + '</div>' : '') +
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

function focusMarker(index) {
    if (markers[index] && map) {
        map.setCenter(markers[index].getPosition());
        map.setZoom(16);
        google.maps.event.trigger(markers[index], 'click');
    }
}

function highlightSidebarCard(index) {
    document.querySelectorAll('.restaurant-card-near-me').forEach(function(card) {
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
    
    // Добавляем местоположение пользователя
    if (userLocation) {
        bounds.extend(userLocation);
    }
    
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
        stars += '<i class="fas fa-star text-warning"></i>';
    }
    
    if (hasHalfStar) {
        stars += '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    
    for (let i = 0; i < (5 - Math.ceil(rating)); i++) {
        stars += '<i class="far fa-star text-warning"></i>';
    }
    
    return stars;
}

function showNoResults() {
    document.getElementById('resultsSection').classList.remove('d-none');
    document.getElementById('restaurantResults').innerHTML = '';
    document.getElementById('noResults').classList.remove('d-none');
    document.getElementById('resultsTitle').textContent = 'No Results Found';
    document.getElementById('resultsInfo').innerHTML = '<i class="fas fa-info-circle"></i> Try expanding your search radius or searching in a different area.';
}

function getDirections(name, address) {
    const searchQuery = encodeURIComponent(name + ' ' + address);
    const url = `https://www.google.com/maps/search/${searchQuery}`;
    window.open(url, '_blank');
}

function expandSearch() {
    const radiusSelect = document.getElementById('radiusSelect');
    const currentRadius = parseInt(radiusSelect.value);
    
    if (currentRadius < 100) {
        radiusSelect.value = Math.min(currentRadius * 2, 100);
        performSearch();
    } else {
        window.location.href = '<?= base_url("restaurants") ?>';
    }
}
</script>

<!-- ИСПОЛЬЗУЕМ ТОТ ЖЕ ПОДХОД ЧТО И В index.php -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?= esc($google_maps_key) ?>&callback=initGoogleMaps&libraries=places"></script>

<?= $this->endSection() ?>