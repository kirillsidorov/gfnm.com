<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <h1 class="display-4 fw-bold mb-4">Georgian Food Near Me</h1>
                <p class="lead mb-5">Find authentic Georgian restaurants, khachapuri, khinkali and traditional dishes near you</p>

                <!-- Mobile Optimized Search Box -->
                <div class="search-container mx-auto">
                    <form id="searchForm" method="GET" action="<?= base_url('search') ?>">
                        <div class="search-input-wrapper">
                            <!-- Search Input -->
                            <div class="search-input-group-mobile">
                                <div class="search-field-container">
                                    <i class="fas fa-search search-icon"></i>
                                    <input 
                                        type="text" 
                                        class="search-input-mobile" 
                                        id="searchInput"
                                        name="q" 
                                        placeholder="Search restaurants or city name..."
                                        autocomplete="off"
                                    >
                                </div>
                                
                                <!-- Search Button (компактная для мобильных) -->
                                <button type="submit" class="search-btn-mobile">
                                    <i class="fas fa-search d-md-none"></i>
                                    <span class="d-none d-md-inline">Find Food</span>
                                </button>
                            </div>
                            
                            <!-- Dropdown Suggestions (оптимизированы для мобильных) -->
                            <div class="search-suggestions-mobile" id="suggestions" style="display: none;"></div>
                        </div>
                    </form>
                    

                </div>
                
                <!-- Popular Cities Quick Access -->
                <div class="popular-cities mt-5">
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <a href="<?= base_url('georgian-restaurants-manhattan') ?>" class="city-quick-btn">
                                <i class="fas fa-building"></i>
                                Manhattan
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('georgian-restaurants-brooklyn') ?>" class="city-quick-btn">
                                <i class="fas fa-bridge"></i>
                                Brooklyn
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('georgian-restaurants-chicago') ?>" class="city-quick-btn">
                                <i class="fas fa-city"></i>
                                Chicago
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('georgian-restaurants-washington-dc') ?>" class="city-quick-btn">
                                <i class="fas fa-landmark"></i>
                                Washington DC
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('map') ?>" class="city-quick-btn special-btn">
                                <i class="fas fa-map-marked-alt"></i>
                                Interactive Map
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<?php if (isset($stats)): ?>
<section class="stats-section py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($stats['total_restaurants']) ?>+</div>
                    <div class="stat-label">Georgian Restaurants</div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($stats['total_cities']) ?></div>
                    <div class="stat-label">Cities Covered</div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="stat-card">
                    <div class="stat-number">4.5★</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Popular Georgian Dishes -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-6 fw-bold">Popular Georgian Dishes</h2>
                <p class="lead text-muted">Discover authentic flavors of Georgia</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <a href="<?= base_url('search?q=khachapuri') ?>" class="text-decoration-none">
                    <div class="dish-card">
                        <div class="dish-icon">
                            <i class="fas fa-bread-slice"></i>
                        </div>
                        <h4 class="dish-title">Khachapuri</h4>
                        <p class="dish-description">Traditional Georgian cheese-filled bread, available in various regional styles</p>
                        <span class="dish-link">Find Khachapuri <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 mb-4">
                <a href="<?= base_url('search?q=khinkali') ?>" class="text-decoration-none">
                    <div class="dish-card">
                        <div class="dish-icon">
                            <i class="fas fa-circle"></i>
                        </div>
                        <h4 class="dish-title">Khinkali</h4>
                        <p class="dish-description">Handmade Georgian dumplings filled with spiced meat and savory broth</p>
                        <span class="dish-link">Find Khinkali <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 mb-4">
                <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="text-decoration-none">
                    <div class="dish-card">
                        <div class="dish-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h4 class="dish-title">Georgian Cuisine</h4>
                        <p class="dish-description">Explore the full range of authentic Georgian dishes and flavors</p>
                        <span class="dish-link">Explore Cuisine <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Top Restaurants Section -->
<?php if (isset($topRestaurants) && !empty($topRestaurants)): ?>
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold">Top Rated Georgian Restaurants</h2>
                <p class="lead text-muted">Discover the highest-rated authentic Georgian dining experiences</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($topRestaurants as $restaurant): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="restaurant-card">
                        <!-- ОБНОВЛЕНО: Restaurant Image с реальными фотографиями -->
                        <?php if (!empty($restaurant['main_photo'])): ?>
                            <div class="restaurant-image">
                                <img src="<?= base_url($restaurant['main_photo']['file_path']) ?>" 
                                     alt="<?= esc($restaurant['main_photo']['alt_text'] ?: $restaurant['name']) ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div class="restaurant-image">
                                <i class="fas fa-utensils fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="restaurant-content">
                            <h5 class="restaurant-name"><?= esc($restaurant['name']) ?></h5>
                            
                            <!-- Location -->
                            <p class="restaurant-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= esc($restaurant['city_name']) ?>
                            </p>
                            
                            <!-- Rating and Price -->
                            <div class="restaurant-meta">
                                <div class="rating">
                                    <?php
                                    $rating = floatval($restaurant['rating']);
                                    $fullStars = floor($rating);
                                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    ?>
                                    
                                    <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    
                                    <?php if ($hasHalfStar): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 0; $i < (5 - ceil($rating)); $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                    
                                    <span class="rating-text"><?= number_format($rating, 1) ?></span>
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
                            <p class="restaurant-description">
                                <?= character_limiter(strip_tags($restaurant['description']), 120) ?>
                            </p>
                            
                            <!-- ОБНОВЛЕНО: Action Button с правильным SEO URL -->
                            <?php 
                            // Генерируем правильный URL для ресторана
                            $restaurantUrl = '';
                            if (!empty($restaurant['seo_url'])) {
                                $restaurantUrl = $restaurant['seo_url'];
                            } else {
                                // Генерируем SEO URL из slug ресторана и города
                                $citySlug = strtolower(str_replace([' ', ','], ['-', ''], $restaurant['city_name']));
                                $restaurantUrl = $restaurant['slug'] . '-restaurant-' . $citySlug;
                            }
                            ?>
                            <a href="<?= base_url($restaurantUrl) ?>" 
                               class="restaurant-btn">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row">
            <div class="col-12 text-center">
                <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-outline-dark btn-lg">
                    View All Restaurants <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 70vh;
    display: flex;
    align-items: center;
    color: white;
    padding: 80px 0;
}

/* Search Container */
.search-container {
    max-width: 800px;
    background: white;
    border-radius: 20px;
    padding: 8px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.search-input-wrapper {
    position: relative;
}

/* Mobile Optimized Search Form */
.search-input-group-mobile {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 15px;
    padding: 4px;
    position: relative;
    gap: 8px;
}

.search-field-container {
    flex: 1;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    z-index: 2;
}

.search-input-mobile {
    width: 100%;
    border: none;
    padding: 18px 20px 18px 50px;
    font-size: 16px;
    border-radius: 12px;
    outline: none;
    background: transparent;
}

.search-input-mobile::placeholder {
    color: #999;
}

.search-input-mobile:focus {
    background: #f8f9fa;
}

.search-btn-mobile {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-btn-mobile:hover {
    background: #bb2d3b;
}

.search-btn-mobile:active {
    transform: scale(0.98);
}

/* Search Suggestions (Mobile Optimized) */
.search-suggestions-mobile {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    z-index: 1000;
    margin-top: 8px;
    border: 1px solid #eee;
    min-height: 0 !important;
    height: auto !important;
}

/* Убираем отступы у скрытых подсказок */
.search-suggestions-mobile:not([style*="display: block"]) {
    display: none !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Убираем лишние отступы у wrapper'а */
.search-input-wrapper {
    min-height: auto !important;
}

/* Корректируем отступы контейнера поиска */
.search-container {
    padding-bottom: 8px !important; /* Минимальный отступ снизу */
}
.suggestion-item {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background 0.2s;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
    border-radius: 0 0 15px 15px;
}

.suggestion-item:first-child {
    border-radius: 15px 15px 0 0;
}

.suggestion-type {
    font-size: 11px;
    color: #666;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 2px;
}

.suggestion-name {
    font-weight: 600;
    color: #333;
    margin: 2px 0;
    font-size: 15px;
}

.suggestion-location {
    font-size: 13px;
    color: #999;
}

/* Quick Links */
.quick-links {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
}

.quick-links-label {
    color: rgba(255,255,255,0.8);
    font-size: 14px;
    margin-right: 10px;
}

.quick-link {
    background: rgba(255,255,255,0.2);
    color: white;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    transition: all 0.2s;
    border: 1px solid rgba(255,255,255,0.3);
}

.quick-link:hover {
    background: rgba(255,255,255,0.3);
    color: white;
    text-decoration: none;
}

/* Popular Cities */
.popular-cities .row {
    gap: 15px;
}

.city-quick-btn {
    display: block;
    background: rgba(255,255,255,0.15);
    color: white;
    text-decoration: none;
    padding: 15px 25px;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s;
    border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}

.city-quick-btn:hover {
    background: rgba(255,255,255,0.25);
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
}

.city-quick-btn i {
    display: block;
    font-size: 24px;
    margin-bottom: 8px;
}

/* Stats Section */
.stats-section {
    background: #f8f9fa;
}

.stat-card {
    text-align: center;
    padding: 30px 20px;
}

.stat-number {
    font-size: 48px;
    font-weight: bold;
    color: #dc3545;
    line-height: 1;
    margin-bottom: 10px;
}

.stat-label {
    color: #666;
    font-size: 16px;
    font-weight: 500;
}

/* Dish Cards */
.dish-card {
    background: white;
    border-radius: 15px;
    padding: 40px 30px;
    text-align: center;
    height: 100%;
    transition: all 0.3s;
    border: 1px solid #eee;
}

.dish-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.dish-icon {
    width: 80px;
    height: 80px;
    background: #dc3545;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 24px;
}

.dish-title {
    color: #333;
    font-weight: bold;
    margin-bottom: 15px;
}

.dish-description {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.6;
}

.dish-link {
    color: #dc3545;
    font-weight: 600;
    text-decoration: none;
}

/* Restaurant Cards */
.restaurant-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    height: 100%;
    transition: all 0.3s;
    border: 1px solid #eee;
}

.restaurant-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.restaurant-image {
    height: 200px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.restaurant-content {
    padding: 25px;
    display: flex;
    flex-direction: column;
    height: calc(100% - 200px);
}

.restaurant-name {
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.restaurant-location {
    color: #666;
    margin-bottom: 15px;
    font-size: 14px;
}

.restaurant-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.rating {
    color: #ffc107;
}

.rating-text {
    color: #666;
    margin-left: 8px;
    font-size: 14px;
}

.price-level {
    color: #28a745;
    font-weight: bold;
}

.restaurant-description {
    color: #666;
    line-height: 1.6;
    flex-grow: 1;
    margin-bottom: 20px;
}

.restaurant-btn {
    background: #dc3545;
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
    transition: all 0.2s;
}

.restaurant-btn:hover {
    background: #bb2d3b;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}


/* ========================================
   RESPONSIVE STYLES
   ======================================== */

/* Desktop */
@media (min-width: 768px) {
    .search-btn-mobile {
        padding: 18px 30px;
        min-width: 120px;
    }
    
    .search-suggestions-mobile {
        max-height: 350px;
        overflow-y: auto;
    }

    .search-input-wrapper {
        margin-bottom: 0 !important;
    }
}

/* Mobile */
@media (max-width: 767px) {
    .search-input-wrapper {
        margin-bottom: 0 !important;
    }
    
    /* Убираем лишние отступы у quick links */
    .quick-links {
        margin-top: 20px !important; /* Было mt-4 = 24px */
    }
    
    /* Уменьшаем отступ между поиском и городами */
    .popular-cities {
        margin-top: 30px !important; /* Было mt-5 = 48px */
    }
    /* Скрываем параграф с описанием */
    .hero-section .lead {
        display: none !important;
    }
    
    /* Уменьшаем отступ после заголовка */
    .hero-section h1 {
        margin-bottom: 30px !important; /* Было mb-4 (24px), делаем меньше */
    }
    
    /* Уменьшаем отступ у контейнера поиска */
    .search-container {
        margin-top: 0 !important;
    }
    
    /* Уменьшаем общие отступы hero секции на мобильных */
    .hero-section {
        padding: 60px 0 !important; /* Было 80px */
    }

    /* Делаем форму поиска горизонтальной на мобильных */
    .search-input-group-mobile {
        flex-direction: row !important; /* Отменяем вертикальное расположение */
        gap: 6px !important;
    }
    
    /* Уменьшаем кнопку поиска */
    .search-btn-mobile {
        padding: 16px 12px !important;
        min-width: 50px !important;
        width: 50px !important;
        height: 50px !important;
        flex-shrink: 0 !important;
    }
    
    /* Поле ввода остается широким */
    .search-field-container {
        flex: 1 !important;
    }
    
    .search-input-mobile {
        padding: 16px 15px 16px 45px !important;
        width: 100% !important;
    }
    
    /* УБИРАЕМ все модальные штуки для подсказок */
    .search-suggestions-mobile {
        position: absolute !important; /* Возвращаем обычное позиционирование */
        top: 100% !important;
        left: 0 !important;
        right: 0 !important;
        bottom: auto !important; /* Убираем привязку к низу */
        margin-top: 8px !important;
        max-height: 250px !important; /* Достаточная высота */
        border-radius: 15px !important; /* Обычные углы */
    }
    
    /* Убираем все .show классы */
    .search-suggestions-mobile.show {
        position: absolute !important;
        top: 100% !important;
        bottom: auto !important;
        left: 0 !important;
        right: 0 !important;
        margin: 8px 0 0 0 !important;
        border-radius: 15px !important;
        max-height: 250px !important;
    }
}

/* Очень маленькие экраны */
@media (max-width: 480px) {
    .search-container {
        padding: 0 10px;
    }
    
    .search-suggestions-mobile {
        left: 5px;
        right: 5px;
        max-height: 35vh;
    }
    
    .suggestion-item {
        padding: 10px 12px;
    }
}

/* Ландшафт на мобильных */
@media (max-width: 767px) and (orientation: landscape) {
    .search-suggestions-mobile {
        max-height: 25vh;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsDiv = document.getElementById('suggestions');
    const searchForm = document.getElementById('searchForm');

    let searchTimeout;
    let isMobile = window.innerWidth <= 767;

    if (!searchInput || !suggestionsDiv) {
        console.error('Search elements not found');
        return;
    }

    // Обновляем isMobile при изменении размера окна
    window.addEventListener('resize', function() {
        isMobile = window.innerWidth <= 767;
    });

    // Автодополнение
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideSuggestions();
            return;
        }

        searchTimeout = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });

    // Специальная обработка фокуса на мобильных
    if (isMobile) {
        searchInput.addEventListener('focus', function() {
            // Небольшая задержка чтобы клавиатура появилась
            setTimeout(() => {
                adjustSuggestionsPosition();
            }, 300);
        });

        searchInput.addEventListener('blur', function() {
            // Задержка чтобы успеть кликнуть на подсказку
            setTimeout(() => {
                if (!document.activeElement.closest('.search-suggestions-mobile')) {
                    hideSuggestions();
                }
            }, 150);
        });
    }

    function fetchSuggestions(query) {
        const url = `<?= base_url('api/search/suggestions') ?>?q=${encodeURIComponent(query)}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.suggestions && data.suggestions.length > 0) {
                    displaySuggestions(data.suggestions);
                } else {
                    showStaticSuggestions(query);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                showStaticSuggestions(query);
            });
    }

    function showStaticSuggestions(query) {
        const staticSuggestions = [
            { type: 'restaurant', name: 'Old Tbilisi Garden', location: 'Manhattan, NY', url: 'old-tbilisi-garden-restaurant-manhattan' },
            { type: 'restaurant', name: 'Oda House', location: 'Manhattan, NY', url: 'oda-house-manhattan-restaurant-manhattan' },
            { type: 'restaurant', name: 'Aragvi', location: 'Chicago, IL', url: 'aragvi-restaurant-chicago' },
            { type: 'restaurant', name: 'Marani', location: 'Brooklyn, NY', url: 'marani-restaurant-brooklyn' },
            { type: 'restaurant', name: 'Supra', location: 'Washington, DC', url: 'supra-restaurant-washington-dc' },
            { type: 'city', name: 'Manhattan', location: 'New York, NY', url: 'georgian-restaurants-manhattan' },
            { type: 'city', name: 'Brooklyn', location: 'New York, NY', url: 'georgian-restaurants-brooklyn' },
            { type: 'city', name: 'Chicago', location: 'Illinois', url: 'georgian-restaurants-chicago' },
            { type: 'dish', name: 'Khachapuri', location: 'Cheese-filled bread', url: 'search?q=khachapuri' },
            { type: 'dish', name: 'Khinkali', location: 'Georgian dumplings', url: 'search?q=khinkali' }
        ];

        const filtered = staticSuggestions.filter(item => 
            item.name.toLowerCase().includes(query.toLowerCase())
        );

        if (filtered.length > 0) {
            // Ограничиваем количество подсказок на мобильных
            const maxSuggestions = isMobile ? 4 : 6;
            displaySuggestions(filtered.slice(0, maxSuggestions));
        } else {
            hideSuggestions();
        }
    }

    function displaySuggestions(suggestions) {
        let html = '';
        suggestions.forEach((item, index) => {
            html += `
                <div class="suggestion-item" onclick="goToUrl('${item.url}', '${item.name}')" data-index="${index}">
                    <div class="suggestion-type">${item.type}</div>
                    <div class="suggestion-name">${item.name}</div>
                    <div class="suggestion-location">${item.location}</div>
                </div>
            `;
        });

        suggestionsDiv.innerHTML = html;
        showSuggestions();
    }

    function showSuggestions() {
        suggestionsDiv.style.display = 'block';
        suggestionsDiv.classList.add('show');
        
        if (isMobile) {
            adjustSuggestionsPosition();
        }
    }

    function hideSuggestions() {
        suggestionsDiv.style.display = 'none';
        suggestionsDiv.classList.remove('show');
    }

    function adjustSuggestionsPosition() {
        if (!isMobile) return;

        const viewport = window.visualViewport || {
            height: window.innerHeight,
            offsetTop: 0
        };

        const availableHeight = viewport.height - viewport.offsetTop;
        const suggestionsMaxHeight = Math.min(availableHeight * 0.4, 200);
        
        suggestionsDiv.style.maxHeight = suggestionsMaxHeight + 'px';
    }

    // Обработка клавиатуры для навигации по подсказкам
    let selectedIndex = -1;

    searchInput.addEventListener('keydown', function(e) {
        const items = suggestionsDiv.querySelectorAll('.suggestion-item');
        
        if (items.length === 0) return;

        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection(items);
                break;
            case 'ArrowUp':
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection(items);
                break;
            case 'Enter':
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    items[selectedIndex].click();
                } else {
                    searchForm.submit();
                }
                break;
            case 'Escape':
                hideSuggestions();
                selectedIndex = -1;
                break;
        }
    });

    function updateSelection(items) {
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.style.background = '#e9ecef';
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.style.background = '';
            }
        });
    }

    // Сброс выбора при вводе
    searchInput.addEventListener('input', function() {
        selectedIndex = -1;
    });

    // Скрыть подсказки при клике вне
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-input-wrapper')) {
            hideSuggestions();
            selectedIndex = -1;
        }
    });

    // Обработка изменения ориентации
    window.addEventListener('orientationchange', function() {
        setTimeout(() => {
            isMobile = window.innerWidth <= 767;
            if (suggestionsDiv.style.display === 'block') {
                adjustSuggestionsPosition();
            }
        }, 500);
    });

    // Валидация формы
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchValue = searchInput.value.trim();
            if (!searchValue) {
                e.preventDefault();
                searchInput.focus();
                return false;
            }
            hideSuggestions();
        });
    }
});

// Переход по URL
function goToUrl(url, name) {
    const searchInput = document.getElementById('searchInput');
    const suggestionsDiv = document.getElementById('suggestions');
    
    if (searchInput) {
        searchInput.value = name;
        searchInput.blur(); // Скрываем клавиатуру на мобильных
    }
    if (suggestionsDiv) {
        suggestionsDiv.style.display = 'none';
        suggestionsDiv.classList.remove('show');
    }
    
    const fullUrl = '<?= base_url() ?>' + url;
    window.location.href = fullUrl;
}

// Добавляем поддержку Visual Viewport API для лучшей работы с виртуальной клавиатурой
if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', () => {
        const suggestionsDiv = document.getElementById('suggestions');
        if (suggestionsDiv && suggestionsDiv.style.display === 'block') {
            adjustSuggestionsPosition();
        }
    });
}
</script>

<?= $this->endSection() ?>