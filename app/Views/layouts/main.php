<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- УЛУЧШЕНО: Более детальные title и description -->
    <title><?= esc($title ?? 'Georgian Food Near Me - Find Authentic Georgian Restaurants') ?></title>
    <meta name="description" content="<?= esc($meta_description ?? 'Find authentic Georgian restaurants near you. Discover khachapuri, khinkali, and traditional Georgian cuisine. Real reviews, photos, and locations.') ?>">
    
    <!-- ДОБАВЛЕНО: Расширенные meta теги -->
    <meta name="keywords" content="georgian food, georgian restaurant, khachapuri, khinkali, georgian cuisine, georgian food near me, authentic georgian, traditional georgian dishes">
    <meta name="author" content="Georgian Food Near Me">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="theme-color" content="#dc3545">
    
    <!-- УЛУЧШЕНО: Canonical URL с проверкой -->
    <?php if (isset($canonical_url)): ?>
        <link rel="canonical" href="<?= esc($canonical_url) ?>">
    <?php else: ?>
        <link rel="canonical" href="<?= current_url() ?>">
    <?php endif; ?>
    
    <!-- ДОБАВЛЕНО: Preconnect для внешних ресурсов (критично для Core Web Vitals) -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    <link rel="preconnect" href="https://analytics.ahrefs.com" crossorigin>
    
    <!-- УЛУЧШЕНО: Favicon и иконки -->
    <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>">
    <link rel="shortcut icon" href="<?= base_url('favicon.svg') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= base_url('site.webmanifest') ?>">
    
    <!-- УЛУЧШЕНО: Open Graph с большим количеством свойств -->
    <meta property="og:title" content="<?= esc($title ?? 'Georgian Food Near Me - Find Authentic Georgian Restaurants') ?>">
    <meta property="og:description" content="<?= esc($meta_description ?? 'Find authentic Georgian restaurants near you. Discover khachapuri, khinkali, and traditional Georgian cuisine.') ?>">
    <meta property="og:url" content="<?= isset($canonical_url) ? esc($canonical_url) : current_url() ?>">
    <meta property="og:type" content="<?= isset($og_type) ? esc($og_type) : 'website' ?>">
    <meta property="og:site_name" content="Georgian Food Near Me">
    <meta property="og:locale" content="en_US">
    
    <!-- ДОБАВЛЕНО: Open Graph изображения -->
    <?php if (isset($og_image)): ?>
        <meta property="og:image" content="<?= esc($og_image) ?>">
        <meta property="og:image:width" content="<?= esc($og_image_width ?? '1200') ?>">
        <meta property="og:image:height" content="<?= esc($og_image_height ?? '630') ?>">
        <meta property="og:image:alt" content="<?= esc($og_image_alt ?? $title) ?>">
    <?php else: ?>
        <meta property="og:image" content="<?= base_url('images/og-default.jpg') ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="Georgian Food Near Me - Find Authentic Georgian Restaurants">
    <?php endif; ?>
    
    <!-- ДОБАВЛЕНО: Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($title ?? 'Georgian Food Near Me') ?>">
    <meta name="twitter:description" content="<?= esc($meta_description ?? 'Find authentic Georgian restaurants near you') ?>">
    <meta name="twitter:image" content="<?= isset($og_image) ? esc($og_image) : base_url('images/og-default.jpg') ?>">
    <meta name="twitter:image:alt" content="<?= esc($og_image_alt ?? $title ?? 'Georgian Food Near Me') ?>">
    <!-- <meta name="twitter:site" content="@GeorgianFoodNearMe"> -->
    
    <!-- ДОБАВЛЕНО: Дополнительные meta для лучшего SEO -->
    <?php if (isset($article_author)): ?>
        <meta name="author" content="<?= esc($article_author) ?>">
    <?php endif; ?>
    
    <?php if (isset($published_time)): ?>
        <meta property="article:published_time" content="<?= esc($published_time) ?>">
    <?php endif; ?>
    
    <?php if (isset($modified_time)): ?>
        <meta property="article:modified_time" content="<?= esc($modified_time) ?>">
    <?php endif; ?>
    
    <!-- Analytics (оставляем как есть) -->
    <script src="https://analytics.ahrefs.com/analytics.js" data-key="9F0gGvB8+Lpfv+pgst3hmA" async></script>
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-02LE8DQ3ZB"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-02LE8DQ3ZB');
    </script>
    
    <!-- УЛУЧШЕНО: CSS с preload для критических ресурсов -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"></noscript>
    
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <!-- ДОБАВЛЕНО: Критический CSS inline для Core Web Vitals -->
    <style>
        /* Критические стили для первой отрисовки */
        :root {
            --georgian-red: #dc3545;
            --georgian-red-dark: #bb2d3b;
            --georgian-red-light: #f8d7da;
            --georgian-gold: #ffc107;
            --dark-bg: #2c3e50;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        /* Критические стили навигации */
        .navbar-brand {
            font-weight: bold;
            color: var(--georgian-red) !important;
        }
        
        .btn-georgian {
            background-color: var(--georgian-red);
            border-color: var(--georgian-red);
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-georgian:hover {
            background-color: var(--georgian-red-dark);
            border-color: var(--georgian-red-dark);
            color: white;
        }
        
        /* Skip navigation для доступности */
        .visually-hidden-focusable {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }
        
        .visually-hidden-focusable:focus {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 9999;
            padding: 10px 15px;
            background: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            width: auto !important;
            height: auto !important;
            clip: auto !important;
            overflow: visible !important;
            white-space: normal !important;
        }
        
        /* Основные стили */
        .text-georgian {
            color: var(--georgian-red) !important;
        }
        
        .bg-georgian {
            background-color: var(--georgian-red) !important;
        }
        
        .rating-stars {
            color: var(--georgian-gold);
        }
    </style>
</head>
<body>
    <!-- ДОБАВЛЕНО: Skip navigation для доступности -->
    <a class="visually-hidden-focusable" href="#main-content">Skip to main content</a>
    
    <!-- УЛУЧШЕНО: Navigation с лучшей семантикой -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" role="navigation" aria-label="Main navigation">
            <div class="container">
                <a class="navbar-brand h4 mb-0" href="<?= base_url() ?>" aria-label="Georgian Food Near Me - Home">
                    <i class="fas fa-utensils" aria-hidden="true"></i> Georgian Food Near Me
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url() ?>" 
                            <?= (current_url() === base_url()) ? 'aria-current="page"' : '' ?>>
                                Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('georgian-restaurant-near-me') ?>">
                                <i class="fas fa-location-dot" aria-hidden="true"></i> Near Me
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('restaurants') ?>">All Restaurants</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('map') ?>">
                                <i class="fas fa-map" aria-hidden="true"></i> 
                                <span>Map</span>
                            </a>
                        </li>
                        
                        <!-- УЛУЧШЕНО: Dropdown с лучшей доступностью -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dishesDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                                Georgian Dishes
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dishesDropdown" role="menu">
                                <li role="none"><a class="dropdown-item" href="<?= base_url('khachapuri') ?>" role="menuitem">Khachapuri</a></li>
                                <li role="none"><a class="dropdown-item" href="<?= base_url('khinkali') ?>" role="menuitem">Khinkali</a></li>
                                <li role="none"><a class="dropdown-item" href="<?= base_url('georgian-cuisine') ?>" role="menuitem">Georgian Cuisine</a></li>
                                <li role="none"><hr class="dropdown-divider"></li>
                                <li role="none"><a class="dropdown-item" href="<?= base_url('khachapuri-near-me') ?>" role="menuitem">Khachapuri Near Me</a></li>
                                <li role="none"><a class="dropdown-item" href="<?= base_url('khinkali-near-me') ?>" role="menuitem">Khinkali Near Me</a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="citiesDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                                Popular Cities
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="citiesDropdown" role="menu">
                                <li role="none"><a class="dropdown-item" href="<?= base_url('georgian-restaurants-nyc') ?>" role="menuitem">New York</a></li>
                                <li role="none"><a class="dropdown-item" href="<?= base_url('georgian-restaurants-chicago') ?>" role="menuitem">Chicago</a></li>
                                <li role="none"><a class="dropdown-item" href="<?= base_url('georgian-restaurants-manhattan') ?>" role="menuitem">Manhattan</a></li>
                                <li role="none"><hr class="dropdown-divider"></li>
                                <li role="none"><a class="dropdown-item" href="<?= base_url('georgian-restaurant') ?>" role="menuitem">Browse All Locations</a></li>
                            </ul>
                        </li>
                    </ul>
                    
                    <!-- УЛУЧШЕНО: Search с лучшими лейблами -->
                    <form class="d-flex" method="GET" action="<?= base_url('search') ?>" role="search" aria-label="Search restaurants">
                        <label for="navbar-search" class="visually-hidden">Search restaurants</label>
                        <input class="form-control me-2" type="search" name="q" id="navbar-search" 
                               placeholder="Search restaurants..." style="width: 200px;" 
                               aria-label="Search restaurants">
                        <button class="btn btn-georgian" type="submit" aria-label="Submit search">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <span class="visually-hidden">Search</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <!-- УЛУЧШЕНО: Flash Messages с лучшей семантикой -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" aria-live="polite">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close success message"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" aria-live="assertive">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close error message"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert" aria-live="polite">
            <?= session()->getFlashdata('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close message"></button>
        </div>
    <?php endif; ?>

    <!-- УЛУЧШЕНО: Main Content с семантическими тегами -->
    <main id="main-content" role="main" tabindex="-1">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- УЛУЧШЕНО: Footer с лучшей семантикой и микроразметкой -->
    <footer role="contentinfo" itemscope itemtype="https://schema.org/WPFooter">
        <div class="footer-content" style="background-color: var(--dark-bg); color: white; padding: 40px 0; margin-top: 50px;">
            <div class="container">
                <div class="row">
                    <div class="col-md-6" itemscope itemtype="https://schema.org/Organization">
                        <h2 class="h5" itemprop="name">
                            <i class="fas fa-utensils" aria-hidden="true"></i> Georgian Food Near Me
                        </h2>
                        <p itemprop="description">
                            Discover authentic Georgian restaurants and traditional cuisine near you. 
                            From khachapuri to khinkali, find the best Georgian food experiences.
                        </p>
                        <meta itemprop="url" content="<?= base_url() ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <h3 class="h6">Quick Links</h3>
                        <nav aria-label="Footer quick links">
                            <ul class="list-unstyled">
                                <li><a href="<?= base_url() ?>" class="text-light">Home</a></li>
                                <li><a href="<?= base_url('georgian-restaurant-near-me') ?>" class="text-light">Near Me Search</a></li>
                                <li><a href="<?= base_url('restaurants') ?>" class="text-light">All Restaurants</a></li>
                                <li><a href="<?= base_url('search') ?>" class="text-light">Search</a></li>
                                <li><a href="<?= base_url('about') ?>" class="text-light">About</a></li>
                                <li><a href="<?= base_url('khachapuri') ?>" class="text-light">Khachapuri</a></li>
                                <li><a href="<?= base_url('khinkali') ?>" class="text-light">Khinkali</a></li>
                            </ul>
                        </nav>
                    </div>
                    
                    <div class="col-md-3">
                        <h3 class="h6">Popular Cities</h3>
                        <nav aria-label="Footer popular cities">
                            <ul class="list-unstyled">
                                <li><a href="<?= base_url('georgian-restaurants-nyc') ?>" class="text-light">New York</a></li>
                                <li><a href="<?= base_url('georgian-restaurants-chicago') ?>" class="text-light">Chicago</a></li>
                                <li><a href="<?= base_url('georgian-restaurants-manhattan') ?>" class="text-light">Manhattan</a></li>
                                <li><a href="<?= base_url('georgian-restaurants-brooklyn') ?>" class="text-light">Brooklyn</a></li>
                                <li><a href="<?= base_url('georgian-restaurant') ?>" class="text-light">Browse All Locations</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <p class="mb-0">
                            &copy; <?= date('Y') ?> Georgian Food Near Me. All rights reserved.
                            <span class="d-none d-md-inline">| 
                                <a href="<?= base_url('privacy') ?>" class="text-light">Privacy Policy</a> | 
                                <a href="<?= base_url('terms') ?>" class="text-light">Terms of Service</a>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <small class="text-muted">
                            Made with <i class="fas fa-heart text-danger" aria-hidden="true"></i> for Georgian food lovers
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Bug Report Widget - Add this to your main layout file -->
    <div id="bug-report-widget" class="bug-widget">
        <a href="<?= base_url('bug-report') ?>" class="bug-btn" title="Found a bug? Report it!" data-bs-toggle="tooltip">
            <i class="fas fa-bug"></i>
            <span class="bug-text">Bug?</span>
        </a>
    </div>
    <!-- УЛУЧШЕНО: JavaScript с defer для лучшей производительности -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js" defer></script>
    
    <!-- УЛУЧШЕНО: Custom JS -->
    <script defer>
        // ДОБАВЛЕНО: Улучшенная функция цены
        function displayPriceLevel(level) {
            const dollarSigns = '$'.repeat(Math.max(1, Math.min(4, level)));
            return dollarSigns;
        }
        
        // ДОБАВЛЕНО: Улучшенная функция рейтинга
        function displayRatingStars(rating, showNumber = true) {
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 >= 0.5;
            const emptyStars = 5 - Math.ceil(rating);
            
            let html = '';
            
            // Полные звезды
            for(let i = 0; i < fullStars; i++) {
                html += '<i class="fas fa-star rating-stars" aria-hidden="true"></i>';
            }
            
            // Половинная звезда
            if(hasHalfStar) {
                html += '<i class="fas fa-star-half-alt rating-stars" aria-hidden="true"></i>';
            }
            
            // Пустые звезды
            for(let i = 0; i < emptyStars; i++) {
                html += '<i class="far fa-star rating-stars" aria-hidden="true"></i>';
            }
            
            if (showNumber) {
                html += `<span class="ms-2 rating-number" aria-label="Rating: ${rating} out of 5 stars">${rating.toFixed(1)}</span>`;
            }
            
            return html;
        }
        
        // ДОБАВЛЕНО: Обработка ошибок изображений
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    this.src = '<?= base_url("images/placeholder-restaurant.jpg") ?>';
                    this.alt = 'Image not available';
                });
            });
            
            // Плавная прокрутка для якорных ссылок
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
        
        // ДОБАВЛЕНО: Улучшенная аналитика для кликов
        function trackRestaurantClick(restaurantName, location) {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'restaurant_click', {
                    'restaurant_name': restaurantName,
                    'location': location,
                    'event_category': 'engagement'
                });
            }
        }
        
        // ДОБАВЛЕНО: Отслеживание поиска
        function trackSearch(query, resultCount) {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'search', {
                    'search_term': query,
                    'result_count': resultCount,
                    'event_category': 'search'
                });
            }
        }
    </script>
    
    <!-- ДОБАВЛЕНО: Структурированные данные -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Georgian Food Near Me",
        "url": "<?= base_url() ?>",
        "description": "Find authentic Georgian restaurants, khachapuri, khinkali and traditional dishes near you",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "<?= base_url('search?q={search_term_string}') ?>"
            },
            "query-input": "required name=search_term_string"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Georgian Food Near Me",
            "url": "<?= base_url() ?>"
        }
    }
    </script>

    <!-- ДОБАВЛЕНО: Breadcrumb Schema для внутренних страниц -->
    <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
            {
                "@type": "ListItem",
                "position": <?= $index + 1 ?>,
                "name": "<?= esc($breadcrumb['name']) ?>",
                "item": "<?= esc($breadcrumb['url']) ?>"
            }<?= $index < count($breadcrumbs) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ]
    }
    </script>
    <?php endif; ?>
     <!-- Bug Widget -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltip
        const bugBtn = document.querySelector('.bug-btn');
        if (bugBtn && typeof bootstrap !== 'undefined') {
            new bootstrap.Tooltip(bugBtn);
        }
        
        // Add pulse effect occasionally to draw attention
        setInterval(function() {
            if (Math.random() < 0.1) { // 10% chance every interval
                bugBtn.classList.add('pulse');
                setTimeout(() => bugBtn.classList.remove('pulse'), 4000);
            }
        }, 30000); // Check every 30 seconds
        
        // Hide widget on bug report page
        if (window.location.pathname.includes('bug-report')) {
            document.body.classList.add('bug-report-page');
        }
    });
    </script>
    <!-- ДОБАВЛЕНО: Дополнительный CSS с остальными стилями -->
    <style>
        /* Остальные стили, которые не критичны для первой отрисовки */
        .price-level {
            color: #28a745;
            font-weight: bold;
        }
        
        .restaurant-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .restaurant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--georgian-red), #e74c3c);
            color: white;
            padding: 80px 0;
        }
        
        .search-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .restaurant-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px 8px 0 0;
        }
        
        .search-box .badge:hover {
            background-color: var(--georgian-red) !important;
            color: white !important;
        }
        
        .special-map-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            border: 1px solid rgba(255,255,255,0.3) !important;
        }

        .special-map-btn:hover {
            background: linear-gradient(135deg, #218838 0%, #1da88a 100%) !important;
            transform: translateY(-3px);
        }
        
        /* ДОБАВЛЕНО: Responsive improvements */
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .restaurant-card {
                margin-bottom: 20px;
            }
            
            .navbar-nav {
                text-align: center;
            }
        }
        
        /* ДОБАВЛЕНО: Print styles */
        @media print {
            .navbar, footer, .alert {
                display: none !important;
            }
            
            .container {
                max-width: none !important;
            }
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .btn-georgian {
                border: 2px solid;
            }
            
            .restaurant-card {
                border: 1px solid #000;
            }
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .restaurant-card,
            .btn-georgian,
            * {
                transition: none !important;
                animation: none !important;
            }
        }

        /*Bug widget*/
        .bug-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
        }

        .bug-btn {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 12px 16px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }

        .bug-btn:hover {
            background: linear-gradient(135deg, #c82333, #a71e2a);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            text-decoration: none;
        }

        .bug-btn i {
            font-size: 16px;
            margin-right: 8px;
            animation: wiggle 2s infinite;
        }

        .bug-text {
            font-size: 13px;
            font-weight: 600;
        }

        /* Bug animation */
        @keyframes wiggle {
            0%, 50%, 100% { transform: rotate(0deg); }
            10%, 30% { transform: rotate(-3deg); }
            20%, 40% { transform: rotate(3deg); }
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .bug-widget {
                bottom: 80px; /* Above mobile navigation if you have one */
                right: 15px;
            }
            
            .bug-btn {
                padding: 10px 14px;
                font-size: 13px;
            }
            
            .bug-text {
                display: none; /* Hide text on very small screens */
            }
            
            .bug-btn i {
                margin-right: 0;
                font-size: 18px;
            }
        }

        /* Hide on bug report page itself */
        body.bug-report-page .bug-widget {
            display: none;
        }

        /* Pulse effect for attention */
        .bug-btn.pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); }
            50% { box-shadow: 0 4px 12px rgba(220, 53, 69, 0.6); }
            100% { box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); }
        }
    </style>
</body>
</html>