<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="display-5 fw-bold text-center">Browse Georgian Restaurants</h1>
            <p class="lead text-center text-muted">
                Explore authentic Georgian cuisine in <?= $totalCities ?> cities worldwide
            </p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <a href="<?= base_url('georgian-restaurant-near-me') ?>" class="btn btn-georgian btn-lg me-3">
                <i class="fas fa-search"></i> Find Restaurants with Filters
            </a>
            <a href="<?= base_url('search') ?>" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-map-marker-alt"></i> Search by Location
            </a>
        </div>
    </div>

    <!-- Global Stats -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h2 class="fw-bold"><?= $totalRestaurants ?></h2>
                            <p class="mb-0">Georgian Restaurants</p>
                        </div>
                        <div class="col-md-4">
                            <h2 class="fw-bold"><?= $totalCities ?></h2>
                            <p class="mb-0">Cities</p>
                        </div>
                        <div class="col-md-4">
                            <h2 class="fw-bold"><?= $totalCountries ?></h2>
                            <p class="mb-0">Countries</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Countries and Cities -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="mb-4">Browse by Location</h2>
            
            <?php foreach ($countriesData as $countryCode => $countryData): ?>
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-globe"></i> <?= esc($countryData['name']) ?>
                            </h4>
                            <span class="badge bg-primary fs-6">
                                <?= $countryData['total_restaurants'] ?> restaurants in <?= $countryData['total_cities'] ?> cities
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($countryData['cities'] as $city): ?>
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <a href="<?= base_url('georgian-restaurants-' . $city['slug']) ?>" 
                                    class="text-decoration-none">
                                        <div class="card border-0 bg-light h-100 city-card">
                                            <div class="card-body text-center">
                                                <h6 class="card-title fw-bold mb-1">
                                                    <?= esc($city['name']) ?>
                                                </h6>
                                                <?php if (!empty($city['state'])): ?>
                                                    <p class="text-muted small mb-2">
                                                        <?= esc($city['state']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <span class="badge bg-success">
                                                    <?= $city['restaurant_count'] ?> restaurant<?= $city['restaurant_count'] != 1 ? 's' : '' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<style>
.city-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.city-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<?= $this->endSection() ?>