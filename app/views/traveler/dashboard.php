<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="fw-bold text-success">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
        <p class="text-muted">Here’s your travel overview.</p>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-success bg-opacity-25">
            <div class="d-flex align-items-center">
                <i class="bi bi-calendar-check fs-1 text-success me-3"></i>
                <div>
                    <h3 class="fw-bold mb-0"><?= $totalBookings ?? 0 ?></h3>
                    <small class="text-muted">Total Trips</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-success bg-opacity-25">
            <div class="d-flex align-items-center">
                <i class="bi bi-tree fs-1 text-success me-3"></i>
                <div>
                    <h3 class="fw-bold mb-0"><?= number_format($travelerData['total_carbon_offset'] ?? 0, 1) ?> kg</h3>
                    <small class="text-muted">CO₂ Offset</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-success bg-opacity-25">
            <div class="d-flex align-items-center">
                <i class="bi bi-globe2 fs-1 text-success me-3"></i>
                <div>
                    <h3 class="fw-bold mb-0">
                        <?= htmlspecialchars($travelerData['nationality'] ?? 'Unknown') ?>
                    </h3>
                    <small class="text-muted">Nationality</small>
                </div>
            </div>
        </div>
    </div>
</div>

<h4 class="fw-bold mb-3">Upcoming Adventures</h4>
<?php if (empty($upcoming)): ?>
    <div class="alert alert-success bg-opacity-25 border-0 rounded-4">
        You have no upcoming trips. <a href="<?= BASE_URL ?>guest/tours" class="fw-bold">Browse eco-tours now</a>!
    </div>
<?php else: ?>
    <div class="row g-4 mb-5">
        <?php foreach ($upcoming as $trip): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($trip['tour_name']) ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($trip['location_name']) ?>,
                                <?= htmlspecialchars($trip['country']) ?>
                            </p>
                            <p class="mb-0"><strong><?= date('M d, Y', strtotime($trip['trip_date'])) ?></strong> ·
                                <?= htmlspecialchars($trip['version_name']) ?>
                            </p>
                        </div>
                        <span class="badge bg-success">Confirmed</span>
                    </div>
                    <hr>
                    <a href="<?= BASE_URL ?>traveler/booking/<?= $trip['booking_id'] ?>"
                        class="btn btn-outline-success btn-sm rounded-pill">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12">
        <h5 class="fw-bold mb-3">Quick Actions</h5>
    </div>
    <div class="col-md-4">
        <a href="<?= BASE_URL ?>traveler/tours" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 card-hover" style="transition-duration: 0.5s;">
                <i class="bi bi-compass fs-3 text-success mb-2"></i>
                <h6 class="fw-bold">Browse Tours</h6>
                <small class="text-muted">Find your next eco‑adventure</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= BASE_URL ?>traveler/bookings" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 card-hover" style="transition-duration: 0.5s;">
                <i class="bi bi-journal-check fs-3 text-success mb-2"></i>
                <h6 class="fw-bold">My Bookings</h6>
                <small class="text-muted">View all reservations</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= BASE_URL ?>traveler/settings" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 card-hover" style="transition-duration: 0.5s;">
                <i class="bi bi-gear fs-3 text-success mb-2"></i>
                <h6 class="fw-bold">Account Settings</h6>
                <small class="text-muted">Manage your profile</small>
            </div>
        </a>
    </div>
</div>

<style>
    .card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>