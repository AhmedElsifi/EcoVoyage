<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="text-center mb-4">
                <i class="bi bi-exclamation-triangle text-warning display-1"></i>
                <h3 class="fw-bold mt-3">Cancel Booking #<?= $booking['booking_id'] ?>?</h3>
                <p class="text-muted">You are about to cancel this booking.</p>
            </div>

            <?php if ($isPaid): ?>
                <div class="alert alert-info border-0 rounded-4 mb-4">
                    <h6 class="fw-bold">Refund Summary</h6>
                    <p class="mb-1">Tour date: <strong><?= date('F d, Y', strtotime($booking['start_time'])) ?></strong></p>
                    <p class="mb-1">Days until tour: <strong><?= $daysLeft ?></strong></p>
                    <p class="mb-2">
                        Cancellation policy:
                        <?php if ($refundPercent == 100): ?>
                            <span class="badge bg-success">Full refund</span>
                        <?php elseif ($refundPercent == 50): ?>
                            <span class="badge bg-warning text-dark">50% refund</span>
                        <?php else: ?>
                            <span class="badge bg-danger">No refund</span>
                        <?php endif; ?>
                    </p>
                    <hr>
                    <p class="mb-0 fw-bold">
                        Refund amount: <span class="text-success">$<?= number_format($refundAmount, 2) ?></span>
                        <small class="text-muted">(<?= $refundPercent ?>% of
                            $<?= number_format($booking['total_price'], 2) ?>)</small>
                    </p>
                </div>
            <?php else: ?>
                <div class="alert alert-warning border-0 rounded-4 mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>No payment has been made yet.</strong> Cancelling will simply void the request.
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>traveler/cancelBooking/<?= $booking['booking_id'] ?>">
                <div class="d-flex gap-2 justify-content-center">
                    <a href="<?= BASE_URL ?>traveler/booking/<?= $booking['booking_id'] ?>"
                        class="btn btn-outline-success rounded-pill">Keep Booking</a>
                    <button type="submit" class="btn btn-danger rounded-pill">
                        <i class="bi bi-x-circle me-1"></i> Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>