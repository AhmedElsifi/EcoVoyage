<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 class="fw-bold text-success mb-4">My Bookings</h2>

<?php if (empty($bookings)): ?>
    <div class="alert alert-success bg-opacity-20 border-0 rounded-4">
        You have no bookings yet. <a href="<?= BASE_URL ?>traveler/tours">Browse tours</a> to get started.
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-success">
                    <tr>
                        <th>Booking ID</th>
                        <th>Tour</th>
                        <th>Location</th>
                        <th>Start Date</th>
                        <th>Travelers</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td class="fw-bold">#<?= $b['booking_id'] ?></td>
                            <td>
                                <?= htmlspecialchars($b['tour_name']) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($b['version_name']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($b['location_name'] ?? '') ?>,
                                <?= htmlspecialchars($b['country'] ?? '') ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($b['trip_date'] ?? $b['start_time'])) ?></td>
                            <td><?= (int) ($b['num_travelers'] ?? 1) ?></td>
                            <td>$<?= number_format($b['total_price'], 2) ?></td>
                            <td>
                                <?php
                                $statusClass = match ($b['status']) {
                                    'confirmed' => 'bg-success',
                                    'pending' => 'bg-warning text-dark',
                                    'cancelled' => 'bg-danger',
                                    'declined' => 'bg-secondary',
                                    'completed' => 'bg-info text-dark',
                                    default => 'bg-light text-dark'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($b['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>traveler/booking/<?= $b['booking_id'] ?>"
                                    class="btn btn-outline-success btn-sm rounded-pill">
                                    Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>