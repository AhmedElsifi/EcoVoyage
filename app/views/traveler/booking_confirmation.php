<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="text-center py-5">
    <i class="bi bi-check-circle text-success display-1 mb-3"></i>
    <h2 class="fw-bold text-success">Booking Confirmed!</h2>
    <p class="lead">Your eco‑adventure is booked. You’ll receive an email shortly.</p>

    <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 mx-auto" style="max-width: 500px;">
        <h5 class="fw-bold">
            <?= htmlspecialchars($tour['tour_name']) ?>
        </h5>
        <p class="text-muted">Start date:
            <?= htmlspecialchars($booking['start_time']) ?>
        </p>
        <p class="text-muted">Total paid: $
            <?= number_format($booking['total_price'], 2) ?>
        </p>
        <p class="text-muted">Status: <span class="badge bg-success">Confirmed</span></p>
        <a href="<?= BASE_URL ?>traveler/dashboard" class="btn btn-success rounded-pill mt-3">Go to Dashboard</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>