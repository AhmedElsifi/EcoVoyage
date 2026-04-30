<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 class="fw-bold text-success mb-4">Guide Dashboard</h2>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-success bg-opacity-25">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle fs-1 text-success me-3"></i>
                <div>
                    <h3 class="fw-bold mb-0">
                        <?= $approvedTours ?>
                    </h3>
                    <small class="text-muted">Approved Tours</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-success bg-opacity-25">
            <div class="d-flex align-items-center">
                <i class="bi bi-calendar-event fs-1 text-success me-3"></i>
                <div>
                    <h5 class="fw-bold mb-0">
                        <?php if ($nextBooking): ?>
                            <?= date('M d, Y', strtotime($nextBooking['start_time'])) ?>
                        <?php else: ?>
                            No upcoming tour
                        <?php endif; ?>
                    </h5>
                    <small class="text-muted">Next Tour</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-success bg-opacity-25">
            <div class="d-flex align-items-center">
                <i class="bi bi-wallet2 fs-1 text-success me-3"></i>
                <div>
                    <h3 class="fw-bold mb-0">$
                        <?= number_format($totalBalance, 2) ?>
                    </h3>
                    <small class="text-muted">
                        Available: $
                        <?= number_format($availableBalance, 2) ?> | Pending: $
                        <?= number_format($pendingBalance, 2) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<h5 class="fw-bold mb-3">My Tours</h5>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-success">
                <tr>
                    <th>Tour ID</th>
                    <th>Tour Name</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($latestTours)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No tours yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($latestTours as $t): ?>
                        <tr>
                            <td>#
                                <?= $t['tour_id'] ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['tour_name']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['location_name'] ?? '') ?>,
                                <?= htmlspecialchars($t['country'] ?? '') ?>
                            </td>
                            <td>
                                <?= ucfirst(str_replace('_', ' ', $t['tour_type'] ?? 'N/A')) ?>
                            </td>
                            <td>
                                <span class="badge <?= $t['status'] == 'active' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= ucfirst($t['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>