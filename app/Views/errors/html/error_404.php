<?php
// Определяем переменные для layout
$title = '404 - Restaurant Not Found | Georgian Food Near Me';
$meta_description = 'The requested Georgian restaurant or page was not found. Search for authentic Georgian restaurants and cuisine near you.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <meta name="description" content="<?= esc($meta_description) ?>">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="<?= base_url() ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --georgian-red: #dc3545;
            --georgian-gold: #ffc107;
            --dark-bg: #2c3e50;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: #333;
        }
        
        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
        }
        
        .error-icon {
            color: var(--georgian-red);
            opacity: 0.7;
            margin-bottom: 2rem;
        }
        
        .error-number {
            font-size: 6rem;
            font-weight: bold;
            color: var(--georgian-red);
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .btn-georgian {
            background-color: var(--georgian-red);
            border-color: var(--georgian-red);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-georgian:hover {
            background-color: #c82333;
            border-color: #bd2130;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        .btn-outline-georgian {
            border: 2px solid var(--georgian-red);
            color: var(--georgian-red);
            background: transparent;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-outline-georgian:hover {
            background: var(--georgian-red);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        .quick-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin: 2rem 0;
        }
        
        .search-form {
            max-width: 500px;
            margin: 2rem auto;
        }
        
        .search-form .input-group {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .search-form input {
            border: none;
            padding: 15px 20px;
            font-size: 16px;
        }
        
        .search-form input:focus {
            box-shadow: none;
            outline: none;
        }
        
        .search-form button {
            border: none;
            padding: 15px 25px;
        }
        
        .popular-cities {
            margin: 2rem 0;
        }
        
        .city-link {
            display: inline-block;
            background: #f8f9fa;
            color: #333;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            margin: 5px;
            transition: all 0.3s;
            border: 1px solid #dee2e6;
        }
        
        .city-link:hover {
            background: var(--georgian-red);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .error-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
            
            .error-number {
                font-size: 4rem;
            }
            
            .quick-links {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-georgian,
            .btn-outline-georgian {
                width: 100%;
                max-width: 300px;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="error-container">
                    <!-- Error Icon -->
                    <div class="error-icon">
                        <i class="fas fa-utensils fa-4x"></i>
                    </div>
                    
                    <!-- Error Number -->
                    <div class="error-number">404</div>
                    
                    <!-- Error Message -->
                    <h1 class="h2 mb-3">Restaurant Not Found</h1>
                    <p class="lead text-muted mb-4">
                        Oops! The Georgian restaurant or page you're looking for seems to have disappeared. 
                        But don't worry - we have plenty of other delicious options waiting for you!
                    </p>
                    
                    <!-- Search Form -->
                    <div class="search-form">
                        <form method="GET" action="<?= base_url('search') ?>">
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       name="q" 
                                       placeholder="Search for restaurants or cities..."
                                       aria-label="Search restaurants">
                                <button class="btn btn-georgian" type="submit">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Popular Cities -->
                    <div class="popular-cities">
                        <h3 class="h5 mb-3">Popular Destinations</h3>
                        <div>
                            <a href="<?= base_url('georgian-restaurants-nyc') ?>" class="city-link">
                                <i class="fas fa-building"></i> New York
                            </a>
                            <a href="<?= base_url('georgian-restaurants-manhattan') ?>" class="city-link">
                                <i class="fas fa-city"></i> Manhattan
                            </a>
                            <a href="<?= base_url('georgian-restaurants-brooklyn') ?>" class="city-link">
                                <i class="fas fa-bridge"></i> Brooklyn
                            </a>
                            <a href="<?= base_url('georgian-restaurants-chicago') ?>" class="city-link">
                                <i class="fas fa-wind"></i> Chicago
                            </a>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="quick-links">
                        <a href="<?= base_url() ?>" class="btn btn-georgian">
                            <i class="fas fa-home"></i> Back to Homepage
                        </a>
                        <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-outline-georgian">
                            <i class="fas fa-utensils"></i> All Restaurants
                        </a>
                        <a href="<?= base_url('map') ?>" class="btn btn-outline-georgian">
                            <i class="fas fa-map"></i> Restaurant Map
                        </a>
                    </div>
                    
                    <!-- Help Text -->
                    <div class="mt-4">
                        <small class="text-muted">
                            Lost? Try searching above or browse our 
                            <a href="<?= base_url('georgian-restaurant') ?>" class="text-decoration-none">popular locations</a>.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Google Analytics для 404 страниц -->
    <?php if (ENVIRONMENT === 'production'): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-02LE8DQ3ZB"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-02LE8DQ3ZB');
        
        // Отслеживаем 404 ошибки
        gtag('event', 'page_view', {
            'page_title': '404 Error',
            'page_location': window.location.href,
            'custom_map': {'error_type': '404'}
        });
    </script>
    <?php endif; ?>

    <!-- Structured Data для 404 -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Page Not Found - Georgian Food Near Me",
        "description": "<?= esc($meta_description) ?>",
        "url": "<?= current_url() ?>",
        "mainEntity": {
            "@type": "SearchAction",
            "target": "<?= base_url('search?q={search_term_string}') ?>",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
</body>
</html>