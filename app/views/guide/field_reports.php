<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 class="fw-bold text-success mb-4">Field Reports</h2>

<div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
    <h5 class="fw-bold mb-3">Post a Field Update</h5>
    <form method="POST" action="<?= BASE_URL ?>guide/fieldReports" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label fw-semibold">Report</label>
            <textarea name="content_text" class="form-control" rows="4"
                placeholder="Share current trail conditions, wildlife sightings..."></textarea>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tour (optional)</label>
                <select name="tour_id" class="form-select">
                    <option value="">General update</option>
                    <?php
                    $toursModel = new Tours();
                    $guideTours = $toursModel->getByGuide($_SESSION['user_id']);
                    foreach ($guideTours as $t): ?>
                        <option value="<?= $t['tour_id'] ?>">
                            <?= htmlspecialchars($t['tour_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Photo (optional)</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
        </div>
        <button type="submit" class="btn btn-success rounded-pill mt-3">
            <i class="bi bi-send me-2"></i> Post Report
        </button>
    </form>
</div>

<h5 class="fw-bold mb-3">Previous Reports</h5>
<?php if (empty($reports)): ?>
    <div class="alert alert-success bg-opacity-20 border-0 rounded-4">
        No field reports yet.
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($reports as $report): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <p>
                        <?= nl2br(htmlspecialchars($report['content_text'])) ?>
                    </p>
                    <?php if (!empty($report['photo_path'])): ?>
                        <img src="<?= BASE_URL . ltrim($report['photo_path'], '/') ?>" class="img-fluid rounded-3 mb-2"
                            style="max-height:200px;">
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <?= date('M d, Y \a\t h:i A', strtotime($report['created_at'])) ?>
                            <?php if ($report['tour_id']): ?>
                                · Tour #
                                <?= $report['tour_id'] ?>
                            <?php endif; ?>
                        </small>
                        <a href="<?= BASE_URL ?>guide/deleteFieldReport/<?= $report['report_id'] ?>" class="text-danger small"
                            onclick="return confirm('Delete this report?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>