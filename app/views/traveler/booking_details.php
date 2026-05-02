<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php if ($booking['status'] === 'cancelled'): ?>
    <div class="alert alert-warning border-0 rounded-4">
        <i class="bi bi-info-circle me-2"></i>
        This booking was cancelled on <?= date('M d, Y', strtotime($booking['cancelled_at'] ?? 'now')) ?>.
        No further action is needed.
    </div>
<?php endif; ?>

<a href="<?= BASE_URL ?>traveler/dashboard" class="btn btn-outline-success rounded-pill mb-4">
    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
</a>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold text-success mb-1">Booking #<?= $booking['booking_id'] ?></h4>
                    <p class="text-muted mb-0">
                        <?= date('F d, Y \a\t h:i A', strtotime($booking['start_time'])) ?> ·
                        <span class="badge bg-success"><?= ucfirst($booking['status']) ?></span>
                    </p>
                    <div class="mt-3">
                        <?php if (in_array($booking['status'], ['confirmed', 'pending'])): ?>
                            <a href="<?= BASE_URL ?>traveler/cancelBooking/<?= $booking['booking_id'] ?>"
                                class="btn btn-outline-danger rounded-pill">
                                <i class="bi bi-x-circle me-1"></i> Cancel Booking
                            </a>
                        <?php endif; ?>
                        <?php if ($booking['status'] === 'payment_pending'): ?>
                            <a href="<?= BASE_URL ?>traveler/payBooking/<?= $booking['booking_id'] ?>"
                                class="btn btn-success w-100 rounded-pill mb-2">
                                <i class="bi bi-lock me-2"></i> Complete Payment
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <i class="bi bi-calendar-check display-6 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
            <h5 class="fw-bold mb-3">Your Voucher</h5>
            <div id="qrcode" class="d-inline-block mb-3" style="margin: auto;"></div>
            <p class="text-muted small">Show this QR code to your guide</p>
            <p class="fw-bold mb-0">Booking ID: <?= $booking['booking_id'] ?></p>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-3">Trip Details</h5>
            <div class="row mb-3">
                <div class="col-sm-6">
                    <strong>Tour:</strong> <?= htmlspecialchars($tour['tour_name']) ?>
                </div>
                <div class="col-sm-6">
                    <strong>Version:</strong> <?= htmlspecialchars($version['version_name']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-6">
                    <strong>Start Date & Time:</strong><br>
                    <?= date('M d, Y \a\t h:i A', strtotime($booking['start_time'])) ?>
                </div>
                <div class="col-sm-6">
                    <strong>Total Paid:</strong> $<?= number_format($booking['total_price'], 2) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-6">
                    <strong>Location:</strong>
                    <?= htmlspecialchars($location['location_name'] ?? 'N/A') ?>,
                    <?= htmlspecialchars($location['country'] ?? '') ?>
                </div>
                <div class="col-sm-6">
                    <strong>Carbon Offset:</strong> $<?= number_format($booking['carbon_offset'], 2) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-6">
                    <strong>Travelers:</strong> <?= (int) ($booking['num_travelers'] ?? 1) ?>
                </div>
            </div>
            <?php if (!empty($addons)): ?>
                <div class="mb-3">
                    <strong>Add‑ons:</strong>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($addons as $addon): ?>
                            <li>
                                <?= htmlspecialchars($addon['name']) ?> ($<?= number_format($addon['price'], 2) ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($guide)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-3">Your Guide</h5>
                <div class="d-flex align-items-start gap-3">
                    <img src="<?= BASE_URL ?>assets/images/profile.png" class="rounded-circle" width="80" height="80"
                        alt="Guide photo" style="object-fit: cover;">
                    <div>
                        <h6 class="fw-bold mb-1">
                            <?= htmlspecialchars($guide['name'] ?? $tour['guide_name']) ?>
                        </h6>
                        <?php $guideBadges = json_decode($guide['badges'] ?? '[]', true); ?>
                        <?php if (!empty($guideBadges)): ?>
                            <div class="mb-2">
                                <?php foreach ($guideBadges as $badge): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success me-1">
                                        <?= htmlspecialchars($badge) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <p class="text-muted mb-2"><i class="bi bi-geo-alt-fill text-success me-1"></i>
                            <?= htmlspecialchars($guide['country_of_residence'] ?? '') ?>
                        </p>
                        <p>
                            <?= nl2br(htmlspecialchars($guide['bio'] ?? '')) ?>
                        </p>
                        <div class="d-flex gap-3 text-muted">
                            <span><i class="bi bi-briefcase me-1"></i>
                                <?= $guide['years_of_experience'] ?? '?' ?> years exp.
                            </span>
                            <span><i class="bi bi-star-fill text-warning me-1"></i>
                                <?= $guide['sustainability_score'] ?? '—' ?>% eco score
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-3">About the Tour</h5>
            <?php if (!empty($tour['tour_img_path'])): ?>
                <img src="<?= BASE_URL . ltrim($tour['tour_img_path'], '/') ?>" class="img-fluid rounded-3 mb-3"
                    alt="<?= htmlspecialchars($tour['tour_name']) ?>" style="max-height: 300px; object-fit: cover;">
            <?php endif; ?>
            <p>
                <?= nl2br(htmlspecialchars($tour['description'] ?? 'No description available.')) ?>
            </p>

            <ul class="list-unstyled">
                <li><strong>Type:</strong>
                    <?= ucfirst(str_replace('_', ' ', $tour['tour_type'] ?? 'N/A')) ?>
                </li>
                <li><strong>Waste Management:</strong>
                    <?= htmlspecialchars($tour['waste_management'] ?? 'N/A') ?>
                </li>
                <li><strong>Local Hiring:</strong>
                    <?= $tour['local_hiring'] ? 'Yes' : 'No' ?>
                </li>

            </ul>
            <?php if (!empty($tour['routes'])): ?>
                <?php $routeList = json_decode($tour['routes'], true); ?>
                <?php if (is_array($routeList)): ?>
                    <h6 class="fw-bold mt-3">Route / Stops</h6>
                    <ol class="list-group list-group-numbered mb-3">
                        <?php foreach ($routeList as $stop): ?>
                            <li class="list-group-item border-0 ps-0">
                                <?= htmlspecialchars($stop) ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>traveler/briefing/<?= $tour['tour_id'] ?>"
                class="btn btn-outline-success btn-sm rounded-pill">
                <i class="bi bi-file-earmark-pdf me-1"></i> Trip Briefing
            </a>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new QRCode(document.getElementById("qrcode"), {
            text: "<?= $booking['booking_id'] ?>",
            width: 150,
            height: 150,
            correctLevel: QRCode.CorrectLevel.M
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>