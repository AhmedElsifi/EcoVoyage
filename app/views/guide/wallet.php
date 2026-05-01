<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 class="fw-bold text-success mb-4">My Wallet</h2>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div>
                <?= htmlspecialchars($e) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-success bg-opacity-25">
            <div class="d-flex align-items-center">
                <i class="bi bi-wallet2 fs-1 text-success me-3"></i>
                <div>
                    <h3 class="fw-bold mb-0">$
                        <?= number_format($available, 2) ?>
                    </h3>
                    <small class="text-muted">Available Balance</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-warning bg-opacity-25">
            <div class="d-flex align-items-center">
                <i class="bi bi-hourglass-split fs-1 text-warning me-3"></i>
                <div>
                    <h3 class="fw-bold mb-0">$
                        <?= number_format($pending, 2) ?>
                    </h3>
                    <small class="text-muted">Pending Balance</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-secondary bg-opacity-25">
            <div class="d-flex align-items-center">
                <i class="bi bi-bank fs-1 text-secondary me-3"></i>
                <div>
                    <h3 class="fw-bold mb-0">$
                        <?= number_format($withdrawn, 2) ?>
                    </h3>
                    <small class="text-muted">Withdrawn</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
    <h5 class="fw-bold mb-3">Request Withdrawal</h5>
    <form method="POST" action="<?= BASE_URL ?>guide/processWithdrawal">
        <div class="mb-3">
            <label class="form-label fw-semibold">Amount ($)</label>
            <input type="number" name="amount" class="form-control" min="1" step="0.01" max="<?= $available ?>"
                placeholder="Enter amount" required>
            <small class="text-muted">Available: $<?= number_format($available, 2) ?></small>
        </div>

        <h6 class="fw-bold mt-4 mb-3">Payment Method</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Card Number</label>
                <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456"
                    maxlength="19" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Cardholder Name</label>
                <input type="text" name="cardholder_name" class="form-control" placeholder="John Doe" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Expiry (MM/YY)</label>
                <input type="text" name="expiry" class="form-control" placeholder="MM/YY" maxlength="5" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">CVV</label>
                <input type="text" name="cvv" class="form-control" placeholder="123" maxlength="4" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success rounded-pill mt-4">
            <i class="bi bi-send me-2"></i> Submit Withdrawal Request
        </button>
    </form>
</div>

<h5 class="fw-bold mb-3">Withdrawal History</h5>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-success">
                <tr>
                    <th>Request ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th>Processed</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No withdrawal requests yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td class="fw-bold">#
                                <?= $req['request_id'] ?>
                            </td>
                            <td>$
                                <?= number_format($req['amount'], 2) ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = match ($req['status']) {
                                    'pending' => 'bg-warning text-dark',
                                    'processed' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-light text-dark'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>">
                                    <?= ucfirst($req['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= date('M d, Y \a\t h:i A', strtotime($req['requested_at'])) ?>
                            </td>
                            <td>
                                <?= $req['processed_at'] ? date('M d, Y \a\t h:i A', strtotime($req['processed_at'])) : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>