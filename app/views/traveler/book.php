<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<a href="javascript:history.back()" class="btn btn-outline-success rounded-pill mb-4">
    <i class="bi bi-arrow-left me-2"></i>Back
</a>

<h2 class="fw-bold text-success mb-4">Confirm Your Booking</h2>

<div class="row g-5">
    <div>
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold"><?= htmlspecialchars($tour['tour_name']) ?></h5>
            <p class="text-muted mb-2"><?= htmlspecialchars($version['version_name']) ?></p>
            <ul class="list-unstyled">
                <li><strong>Location:</strong> <?= htmlspecialchars($tour['location_name'] ?? '') ?>,
                    <?= htmlspecialchars($tour['country'] ?? '') ?>
                </li>
                <li><strong>Guide:</strong> <?= htmlspecialchars($tour['guide_name'] ?? '') ?></li>
                <li><strong>Travelers:</strong> <?= $numTravelers ?> person(s)</li>
                <li><strong>Start:</strong> <?= date('F d, Y \a\t H:i', strtotime($startDateTime)) ?></li>
            </ul>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold mb-3">Price Breakdown</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>Base price ( <?= htmlspecialchars($version['version_name']) ?> × <?= $numTravelers ?> )</span>
                    <strong><?= $currency ?> <?= number_format($basePricePerPerson * $numTravelers, 2) ?></strong>
                </li>
                <?php if ($discountPercent > 0): ?>
                    <li class="list-group-item d-flex justify-content-between text-success">
                        <span>Group discount (<?= $discountPercent ?>%)</span>
                        <span>-<?= $currency ?>     <?= number_format($discountAmount, 2) ?></span>
                    </li>
                <?php endif; ?>
                <?php if (!empty($addons)): ?>
                    <?php foreach ($addons as $addon): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($addon['name']) ?> (×<?= $numTravelers ?>)</span>
                            <span><?= $currency ?>         <?= number_format($addon['price'] * $numTravelers, 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if ($offsetCost > 0): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Carbon Offset – <?= htmlspecialchars($offsetProject['name'] ?? '') ?>
                            (×<?= $numTravelers ?>)</span>
                        <span><?= $currency ?>     <?= number_format($offsetCost, 2) ?></span>
                    </li>
                <?php endif; ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Subtotal</span>
                    <strong><?= $currency ?> <?= number_format($subtotal, 2) ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Local Tax (<?= $taxPercent ?>%)</span>
                    <span><?= $currency ?> <?= number_format($taxAmount, 2) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between fw-bold fs-5">
                    <span>Total You Pay</span>
                    <span class="text-success"><?= $currency ?> <?= number_format($totalTravelerPays, 2) ?></span>
                </li>
            </ul>
        </div>

        <div class="alert alert-success bg-opacity-20 border-0 rounded-4 small">
            <p class="mb-1"><i class="bi bi-cash-coin me-1"></i> <strong>Guide earnings:</strong> <?= $currency ?>
                <?= number_format($guideEarnings, 2) ?> (after <?= $platformFeePct ?>% platform fee)
            </p>
            <p class="mb-0">Platform fee (<?= $platformFeePct ?>%): <?= $currency ?>
                <?= number_format($platformFeeAmount, 2) ?>
            </p>
        </div>

        <form method="POST"
            action="<?= BASE_URL ?>traveler/book?tour_id=<?= $tourId ?>&version_id=<?= $versionId ?><?php if (!empty($addonIds)): ?><?php foreach ($addonIds as $a): ?>&addons[]=<?= $a ?><?php endforeach; ?><?php endif; ?>&offset_project=<?= $offsetProjId ?? '' ?>">
            <input type="hidden" name="start_time" value="<?= htmlspecialchars($startDateTime) ?>">
            <input type="hidden" name="num_travelers" value="<?= $numTravelers ?>">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err):
                        echo htmlspecialchars($err) . '<br>';
                    endforeach; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-success w-100 rounded-pill btn-lg">
                <i class="bi bi-lock me-2"></i> Confirm & Pay
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>