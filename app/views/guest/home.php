<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="hero-section position-relative overflow-hidden text-white">
    <div class="position-absolute top-0 start-0 w-100 h-100"
        style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); z-index: 0; border-radius: 16px;">
    </div>
    <div class="position-relative z-1 py-5" style=" padding:20px;">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-7">
                <h1 class="display-3 fw-bold mb-3">Explore the World <br><span class="text-warning">Sustainably</span>
                </h1>
                <p class="lead mb-4">Discover eco‑friendly tours, connect with local guides, and travel responsibly.
                    Your adventure starts here.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>guest/tours"
                        class="btn btn-warning btn-lg fw-semibold px-4 py-2 rounded-pill">
                        <i class="bi bi-compass me-2"></i>Browse Tours
                    </a>
                    <a href="<?= BASE_URL ?>auth/register" class="btn btn-outline-light btn-lg px-4 py-2 rounded-pill">
                        <i class="bi bi-person-plus me-2"></i>Join Free
                    </a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <img src="https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=600&h=500&fit=crop&auto=format"
                    alt="Traveler" class="img-fluid rounded-4 shadow-lg">
            </div>
        </div>
    </div>
</section>

<div class="mb-5" style="margin-top: 30px;">
    <div class="text-center mb-5">
        <h2 class="display-5 fw-bold text-success">Popular Eco‑Tours</h2>
        <p class="text-muted lead">Handpicked sustainable adventures for you</p>
    </div>
    <div class="row g-4">
        <?php foreach ($featuredTours as $tour): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <img src="<?= BASE_URL . ltrim($tour['image'], '/') ?>" class=" card-img-top" alt="<?= $tour['name'] ?>"
                        style="height: 220px; object-fit: cover;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title fw-bold"><?= $tour['name'] ?></h5>
                            <span class="badge bg-success fs-6">$<?= $tour['price'] ?></span>
                        </div>
                        <p class="text-muted mb-3"><i
                                class="bi bi-geo-alt-fill text-success me-1"></i><?= $tour['location'] ?></p>
                        <a href="<?= BASE_URL ?>guest/tours" class="btn btn-outline-success rounded-pill w-100">View
                            Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
        <a href="<?= BASE_URL ?>guest/tours" class="btn btn-success btn-lg rounded-pill px-5">See All Tours</a>
    </div>
</div>

<div class="bg-success bg-opacity-10 py-5 mb-5" style="border-radius: 16px; padding: 20px;">
    <div>
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded-4 shadow-sm">
                    <h3 class="fw-bold text-success display-5"><?= $tours ?></h3>
                    <p class="text-muted mb-0">Tours Available</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded-4 shadow-sm">
                    <h3 class="fw-bold text-success display-5"><?= $travelers ?></h3>
                    <p class="text-muted mb-0">Happy Travelers</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded-4 shadow-sm">
                    <h3 class="fw-bold text-success display-5"><?= $guides ?></h3>
                    <p class="text-muted mb-0">Local Guides</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded-4 shadow-sm">
                    <h3 class="fw-bold text-success display-5"><?= $countries ?></h3>
                    <p class="text-muted mb-0">Countries</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-5">
    <div class="text-center mb-5">
        <h2 class="display-5 fw-bold text-success">Meet Our Eco‑Guides</h2>
        <p class="lead text-muted">Passionate locals who will make your trip unforgettable</p>
    </div>
    <div class="row g-4 justify-content-center">
        <?php foreach ($featuredGuides as $guide): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 rounded-4 p-4">
                    <div class="d-flex align-items-center">
                        <img src="<?= $guide['image'] ?? BASE_URL . 'assets/images/profile.png' ?>"
                            class="rounded-circle me-3" width="80" height="80" style="object-fit: cover;" alt="Guide photo">
                        <div>
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($guide['guide_name']) ?></h5>
                            <p class="text-muted mb-2">
                                <i
                                    class="bi bi-geo-alt-fill text-success me-1"></i><?= htmlspecialchars($guide['country_of_residence']) ?>
                            </p>
                            <p class="small mb-0"><?= htmlspecialchars($guide['bio'] ?? 'Eco‑travel expert') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>