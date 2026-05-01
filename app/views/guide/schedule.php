<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 class="fw-bold text-success mb-4">My Schedule</h2>

<?php if (empty($bookings)): ?>
    <div class="alert alert-success bg-opacity-20 border-0 rounded-4">
        No bookings yet.
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-success">
                    <tr>
                        <th>Booking ID</th>
                        <th>Tour</th>
                        <th>Traveler</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td class="fw-bold">#
                                <?= $b['booking_id'] ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($b['tour_name']) ?><br><small class="text-muted">
                                    <?= htmlspecialchars($b['version_name']) ?>
                                </small>
                            </td>
                            <td>
                                <?= htmlspecialchars($b['traveler_name']) ?>
                            </td>
                            <td>
                                <?= date('M d, Y \a\t H:i', strtotime($b['start_time'])) ?>
                            </td>
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
                                <span class="badge <?= $statusClass ?>">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>guide/booking/<?= $b['booking_id'] ?>"
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