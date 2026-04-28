<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-7 text-center">
        <h2 class="fw-bold text-success mb-4">Join EcoVoyage</h2>
        <p class="lead text-muted mb-5">Choose how you want to be part of the community</p>

        <div class="row g-4">
            <div class="col-md-4">
                <a href="<?= BASE_URL ?>auth/register/traveler" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 rounded-4 p-4 card-hover"
                        style="transition-duration: 0.5s;">
                        <div class="mb-3">
                            <i class="bi bi-backpack fs-1 text-success"></i>
                        </div>
                        <h5 class="fw-bold">Traveler</h5>
                        <p class="text-muted small">Explore eco‑tours, offset carbon, and book unforgettable adventures.
                        </p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="<?= BASE_URL ?>auth/register/guide" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 rounded-4 p-4 card-hover"
                        style="transition-duration: 0.5s;">
                        <div class="mb-3">
                            <i class="bi bi-map fs-1 text-success"></i>
                        </div>
                        <h5 class="fw-bold">Guide</h5>
                        <p class="text-muted small">Lead sustainable tours, set your own prices, and grow your
                            reputation.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="<?= BASE_URL ?>auth/register/auditor" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 rounded-4 p-4 card-hover"
                        style="transition-duration: 0.5s;">
                        <div class="mb-3">
                            <i class="bi bi-clipboard-check fs-1 text-success"></i>
                        </div>
                        <h5 class="fw-bold">Regional Auditor</h5>
                        <p class="text-muted small">Apply to verify guides and tours in your region. Help keep us green.
                        </p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>