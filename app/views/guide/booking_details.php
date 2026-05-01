<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<a href="<?= BASE_URL ?>guide/schedule" class="btn btn-outline-success rounded-pill mb-4">
    <i class="bi bi-arrow-left me-2"></i>Back to Schedule
</a>

<h3 class="fw-bold text-success mb-4">Booking #
    <?= $booking['booking_id'] ?>
</h3>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold">
                <?= htmlspecialchars($tour['tour_name']) ?>
            </h5>
            <p class="text-muted mb-1">
                <?= htmlspecialchars($version['version_name']) ?>
            </p>
            <p>
                <strong>Traveler:</strong>
                <?= htmlspecialchars($user['name']) ?><br>
                <strong>Email:</strong>
                <?= htmlspecialchars($user['email']) ?><br>
                <strong>Phone:</strong>
                <?= htmlspecialchars($user['phone'] ?? '—') ?><br>
                <strong>Nationality:</strong>
                <?= htmlspecialchars($traveler['nationality'] ?? '—') ?>
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
            <h6 class="fw-bold">Actions</h6>
            <?php if ($booking['status'] === 'pending'): ?>
                <form method="POST" action="<?= BASE_URL ?>guide/updateBookingStatus/<?= $booking['booking_id'] ?>">
                    <input type="hidden" name="action" value="accept">
                    <button type="submit" class="btn btn-success w-100 rounded-pill mb-2">Accept Booking</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>guide/updateBookingStatus/<?= $booking['booking_id'] ?>">
                    <input type="hidden" name="action" value="decline">
                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">Decline</button>
                </form>
            <?php elseif ($booking['status'] === 'confirmed'): ?>
                <form method="POST" action="<?= BASE_URL ?>guide/updateBookingStatus/<?= $booking['booking_id'] ?>">
                    <input type="hidden" name="action" value="complete">
                    <button type="submit" class="btn btn-success w-100 rounded-pill">Mark as Completed</button>
                </form>
            <?php else: ?>
                <p class="text-muted">No actions available</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-3">Trip Details</h5>
            <p><strong>Start:</strong>
                <?= date('F d, Y \a\t h:i A', strtotime($booking['start_time'])) ?>
            </p>
            <p><strong>Travelers:</strong>
                <?= $booking['num_travelers'] ?? 1 ?>
            </p>
            <p><strong>Total Paid:</strong> $
                <?= number_format($booking['total_price'], 2) ?>
            </p>
            <?php if (!empty($addons)): ?>
                <strong>Add‑ons:</strong>
                <ul>
                    <?php foreach ($addons as $addon): ?>
                        <li>
                            <?= htmlspecialchars($addon['name']) ?> ($
                            <?= number_format($addon['price'], 2) ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-3">Tour Info</h5>
            <?php if (!empty($tour['tour_img_path'])): ?>
                <img src="<?= BASE_URL . ltrim($tour['tour_img_path'], '/') ?>" class="img-fluid rounded-3 mb-3"
                    alt="<?= htmlspecialchars($tour['tour_name']) ?>" style="max-height:200px; object-fit:cover;">
            <?php endif; ?>
            <p>
                <?= htmlspecialchars($tour['description'] ?? 'No description') ?>
            </p>
            <p><strong>Location:</strong>
                <?= htmlspecialchars($tour['location_name'] ?? '') ?>,
                <?= htmlspecialchars($tour['country'] ?? '') ?>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>