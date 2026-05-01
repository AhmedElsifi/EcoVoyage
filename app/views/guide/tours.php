<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 class="fw-bold text-success mb-4">My Tours</h2>

<a href="<?= BASE_URL ?>guide/createTour" class="btn btn-success rounded-pill mb-3">
    <i class="bi bi-plus-circle me-2"></i> Create New Tour
</a>

<?php if (empty($tours)): ?>
    <div class="alert alert-success bg-opacity-20 border-0 rounded-4">
        You haven't created any tours yet.
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Tour Name</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tours as $t): ?>
                        <tr>
                            <td>#<?= $t['tour_id'] ?></td>
                            <td><?= htmlspecialchars($t['tour_name']) ?></td>
                            <td><?= htmlspecialchars($t['location_name'] ?? '') ?>, <?= htmlspecialchars($t['country'] ?? '') ?>
                            </td>
                            <td><?= ucfirst(str_replace('_', ' ', $t['tour_type'])) ?></td>
                            <td>
                                <?php
                                $statusClass = match ($t['status']) {
                                    'active' => 'bg-success',
                                    'pending' => 'bg-warning text-dark',
                                    'draft' => 'bg-secondary',
                                    'inactive' => 'bg-light text-dark',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-light text-dark'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($t['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>guide/editTour/<?= $t['tour_id'] ?>"
                                    class="btn btn-sm btn-outline-success rounded-pill me-1">Edit</a>
                                <a href="<?= BASE_URL ?>guide/deleteTour/<?= $t['tour_id'] ?>"
                                    class="btn btn-sm btn-outline-danger rounded-pill"
                                    onclick="return confirm('⚠️ Deleting this tour will issue a full refund to all travelers with confirmed bookings. Are you sure?')">
                                    Delete
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