<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<meta name="description" content="<?= esc($meta_description) ?>">
<link rel="canonical" href="<?= esc($canonical_url) ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="display-4 text-center mb-4 text-primary">About Georgian Food Near Me</h1>
                    
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <img src="<?= base_url('assets/images/georgian-cuisine.jpg') ?>" 
                                 alt="Traditional Georgian Food" 
                                 class="img-fluid rounded shadow-sm mb-3">
                        </div>
                        <div class="col-md-6">
                            <h2 class="h4 text-primary mb-3">🇬🇪 Our Mission</h2>
                            <p class="lead">
                                Georgian Food Near Me is your ultimate guide to discovering authentic Georgian restaurants and culinary experiences in your area.
                            </p>
                            <p>
                                We're passionate about connecting food lovers with the rich, diverse flavors of Georgian cuisine - from savory khinkali dumplings to aromatic khachapuri cheese bread.
                            </p>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-12">
                            <h2 class="h3 text-primary mb-4">🍽️ What We Offer</h2>
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <div class="text-center">
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                                        </div>
                                        <h3 class="h5 mt-3">Location-Based Search</h3>
                                        <p class="text-muted">Find Georgian restaurants near your current location or any address.</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="text-center">
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <i class="fas fa-star fa-2x text-primary"></i>
                                        </div>
                                        <h3 class="h5 mt-3">Verified Reviews</h3>
                                        <p class="text-muted">Read authentic reviews and ratings from fellow Georgian food enthusiasts.</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="text-center">
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <i class="fas fa-utensils fa-2x text-primary"></i>
                                        </div>
                                        <h3 class="h5 mt-3">Detailed Information</h3>
                                        <p class="text-muted">Get complete details about menus, hours, contact info, and specialties.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light p-4 rounded mb-5">
                        <h2 class="h3 text-primary mb-3">🥟 About Georgian Cuisine</h2>
                        <p>
                            Georgian cuisine is one of the world's most distinctive and flavorful culinary traditions. 
                            Located at the crossroads of Europe and Asia, Georgia has developed a unique food culture 
                            that combines influences from Mediterranean, Middle Eastern, and European cooking styles.
                        </p>
                        <p class="mb-0">
                            From the iconic cheese-filled khachapuri to the hearty meat dumplings called khinkali, 
                            Georgian food is known for its bold flavors, fresh herbs, and generous use of walnuts, 
                            pomegranate, and distinctive spices like blue fenugreek.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-4">🤝 Our Commitment</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h4 class="h6">Accurate Information</h4>
                                        <p class="text-muted mb-0">We regularly update restaurant information to ensure accuracy.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h4 class="h6">Community Driven</h4>
                                        <p class="text-muted mb-0">Built by and for Georgian food lovers and restaurant owners.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h4 class="h6">Free to Use</h4>
                                        <p class="text-muted mb-0">Our platform is completely free for food lovers and diners.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h4 class="h6">Support Local Business</h4>
                                        <p class="text-muted mb-0">We help promote authentic Georgian restaurants worldwide.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <h2 class="h3 text-primary mb-4">📧 Get in Touch</h2>
                        <p class="mb-4">
                            Have questions, suggestions, or want to add your Georgian restaurant to our directory? 
                            We'd love to hear from you!
                        </p>
                        <a href="mailto:info@georgianfoodnearme.com" class="btn btn-primary btn-lg">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>