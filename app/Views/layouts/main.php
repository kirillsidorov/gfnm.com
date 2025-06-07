<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Georgian Food Near Me') ?></title>
    <meta name="description" content="<?= esc($meta_description ?? 'Find authentic Georgian restaurants near you') ?>">
    <meta name="keywords" content="georgian food, georgian restaurant, khachapuri, khinkali, georgian cuisine, georgian food near me">
    <meta name="author" content="Georgian Food Near Me">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Ahrefs Web Analytics -->
    <script src="https://analytics.ahrefs.com/analytics.js" data-key="9F0gGvB8+Lpfv+pgst3hmA" async></script>
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-02LE8DQ3ZB"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-02LE8DQ3ZB');
    </script>

    <!-- Open Graph -->
    <meta property="og:title" content="<?= esc($title ?? 'Georgian Food Near Me') ?>">
    <meta property="og:description" content="<?= esc($meta_description ?? 'Find authentic Georgian restaurants near you') ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Georgian Food Near Me">
    
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
        
        .navbar-brand {
            font-weight: bold;
            color: var(--georgian-red) !important;
        }
        
        .btn-georgian {
            background-color: var(--georgian-red);
            border-color: var(--georgian-red);
            color: white;
        }
        
        .btn-georgian:hover {
            background-color: #c82333;
            border-color: #bd2130;
            color: white;
        }
        
        .rating-stars {
            color: var(--georgian-gold);
        }
        
        .price-level {
            color: #28a745;
            font-weight: bold;
        }
        
        .restaurant-card {
            transition: transform 0.2s ease-in-out;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .restaurant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
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
        
        footer {
            background-color: var(--dark-bg);
            color: white;
            padding: 40px 0;
            margin-top: 50px;
        }
        
        .restaurant-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .text-georgian {
            color: var(--georgian-red) !important;
        }
        
        .bg-georgian {
            background-color: var(--georgian-red) !important;
        }
        
        .search-box .badge:hover {
            background-color: var(--georgian-red) !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand h4 mb-0" href="<?= base_url() ?>">
                <i class="fas fa-utensils"></i> Georgian Food Near Me
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('georgian-restaurant-near-me') ?>">All Restaurants</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dishesDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Georgian Dishes
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dishesDropdown">
                            <li><a class="dropdown-item" href="<?= base_url('khachapuri') ?>">Khachapuri</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('khinkali') ?>">Khinkali</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('georgian-cuisine') ?>">Georgian Cuisine</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= base_url('khachapuri-near-me') ?>">Khachapuri Near Me</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('khinkali-near-me') ?>">Khinkali Near Me</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="citiesDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Popular Cities
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="citiesDropdown">
                            <li><a class="dropdown-item" href="<?= base_url('georgian-restaurant-nyc') ?>">New York</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('georgian-restaurant-chicago') ?>">Chicago</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('georgian-restaurant-manhattan') ?>">Manhattan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= base_url('restaurants') ?>">Browse All Locations</a></li>
                        </ul>
                    </li>
                </ul>
                
                <!-- Quick Search in Navbar -->
                <form class="d-flex" method="GET" action="<?= base_url('search') ?>">
                    <input class="form-control me-2" type="search" name="q" placeholder="Search restaurants..." style="width: 200px;">
                    <button class="btn btn-georgian" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-utensils"></i> Georgian Food Near Me</h5>
                    <p>Discover authentic Georgian restaurants and traditional cuisine near you. From khachapuri to khinkali, find the best Georgian food experiences.</p>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= base_url() ?>" class="text-light">Home</a></li>
                        <li><a href="<?= base_url('georgian-restaurant-near-me') ?>" class="text-light">All Restaurants</a></li>
                        <li><a href="<?= base_url('search') ?>" class="text-light">Search</a></li>
                        <li><a href="<?= base_url('khachapuri') ?>" class="text-light">Khachapuri</a></li>
                        <li><a href="<?= base_url('khinkali') ?>" class="text-light">Khinkali</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Popular Cities</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= base_url('georgian-restaurant-nyc') ?>" class="text-light">New York</a></li>
                        <li><a href="<?= base_url('georgian-restaurant-chicago') ?>" class="text-light">Chicago</a></li>
                        <li><a href="<?= base_url('georgian-restaurant-manhattan') ?>" class="text-light">Manhattan</a></li>
                        <li><a href="<?= base_url('restaurants') ?>" class="text-light">Browse All Locations</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; <?= date('Y') ?> Georgian Food Near Me. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Price level display function
        function displayPriceLevel(level) {
            let dollarSigns = '';
            for(let i = 0; i < level; i++) {
                dollarSigns += '$';
            }
            return dollarSigns;
        }
        
        // Rating stars display function
        function displayRatingStars(rating) {
            let stars = '';
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 !== 0;
            
            for(let i = 0; i < fullStars; i++) {
                stars += '<i class="fas fa-star rating-stars"></i>';
            }
            
            if(hasHalfStar) {
                stars += '<i class="fas fa-star-half-alt rating-stars"></i>';
            }
            
            const emptyStars = 5 - Math.ceil(rating);
            for(let i = 0; i < emptyStars; i++) {
                stars += '<i class="far fa-star rating-stars"></i>';
            }
            
            return stars;
        }
    </script>
</body>
</html>