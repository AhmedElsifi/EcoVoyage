<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<a href="javascript:history.back()" class="btn btn-outline-success rounded-pill mb-4">
    <i class="bi bi-arrow-left me-2"></i>Back
</a>

<h2 class="fw-bold text-success mb-4">Payment Details</h2>

<div class="row">
    <div>
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold">Order Summary</h5>
            <p><strong>
                    <?= htmlspecialchars($tour['tour_name']) ?>
                </strong> –
                <?= htmlspecialchars($version['version_name']) ?>
            </p>
            <p>Total: <strong>
                    <?= $currency ?>
                    <?= number_format($totalTravelerPays, 2) ?>
                </strong></p>
            <form method="POST" action="<?= $paymentAction ?? BASE_URL . 'traveler/processPayment' ?>">
                <input type="hidden" name="tour_id" value="<?= $tourId ?>">
                <input type="hidden" name="version_id" value="<?= $versionId ?>">
                <?php if (!empty($addonIds)): ?>
                    <?php foreach ($addonIds as $aId): ?>
                        <input type="hidden" name="addons[]" value="<?= $aId ?>">
                    <?php endforeach; ?>
                <?php endif; ?>
                <input type="hidden" name="offset_project" value="<?= $offsetProjId ?? '' ?>">
                <input type="hidden" name="start_date" value="<?= $startDate ?>">
                <input type="hidden" name="total_price" value="<?= $totalTravelerPays ?>">
                <input type="hidden" name="subtotal" value="<?= $subtotal ?>">
                <input type="hidden" name="tax_amount" value="<?= $taxAmount ?>">
                <input type="hidden" name="platform_fee_amount" value="<?= $platformFeeAmount ?>">
                <input type="hidden" name="guide_earnings" value="<?= $guideEarnings ?>">
                <input type="hidden" name="num_travelers" value="<?= $numTravelers ?? 1 ?>">
                <div class="mb-3">
                    <label for="card_number" class="form-label fw-semibold">Card Number</label>
                    <input type="text" name="card_number" id="card_number" class="form-control"
                        placeholder="1234 5678 9012 3456" maxlength="19" required>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label for="expiry" class="form-label fw-semibold">Expiry (MM/YY)</label>
                        <input type="text" name="expiry" id="expiry" class="form-control" placeholder="MM/YY"
                            maxlength="5" required>
                    </div>
                    <div class="col-6">
                        <label for="cvv" class="form-label fw-semibold">CVV</label>
                        <input type="text" name="cvv" id="cvv" class="form-control" placeholder="123" maxlength="4"
                            required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="cardholder_name" class="form-label fw-semibold">Cardholder Name</label>
                    <input type="text" name="cardholder_name" id="cardholder_name" class="form-control"
                        placeholder="John Doe" required>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $err): ?>
                            <div>
                                <?= htmlspecialchars($err) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-success w-100 rounded-pill btn-lg">
                    <i class="bi bi-lock me-2"></i> Pay Securely
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>