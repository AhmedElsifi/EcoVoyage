<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 class="fw-bold text-success mb-4">Eco‑Certifications</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err): ?>
            <div>
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
    <h5 class="fw-bold mb-3">Add New Certification</h5>
    <form method="POST" action="<?= BASE_URL ?>guide/certifications" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Certification Type</label>
                <input type="text" name="cert_type" class="form-control"
                    placeholder="e.g., Leave No Trace, Rainforest Alliance" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Issued Date</label>
                <input type="date" name="issued_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">File</label>
                <input type="file" name="cert_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
        </div>
        <button type="submit" class="btn btn-success rounded-pill mt-3">
            <i class="bi bi-upload me-2"></i> Submit Certification
        </button>
    </form>
</div>

<h5 class="fw-bold mb-3">My Certifications</h5>
<?php if (empty($certs)): ?>
    <div class="alert alert-success bg-opacity-20 border-0 rounded-4">
        No certifications submitted yet.
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-success">
                    <tr>
                        <th>Type</th>
                        <th>Issued</th>
                        <th>Expires</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certs as $cert): ?>
                        <tr>
                            <td class="fw-bold">
                                <?= htmlspecialchars($cert['doc_type']) ?>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($cert['issued_date'])) ?>
                            </td>
                            <td>
                                <?= $cert['expiry_date']
                                    ? date('M d, Y', strtotime($cert['expiry_date']))
                                    : '—' ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL . ltrim($cert['file_path'], '/') ?>" target="_blank">
                                    View
                                </a>
                            </td>
                            <td>
                                <?php
                                $statusClass = match ($cert['status']) {
                                    'approved' => 'bg-success',
                                    'pending' => 'bg-warning text-dark',
                                    'expired' => 'bg-danger',
                                    'rejected' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>">
                                    <?= ucfirst($cert['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($cert['status'] === 'expired'): ?>
                                    <a href="#" class="btn btn-sm btn-outline-success rounded-pill" data-bs-toggle="modal"
                                        data-bs-target="#renewModal" data-doc-id="<?= $cert['doc_id'] ?>">
                                        Renew
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="renewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Renew Certification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>To renew, upload a new certificate of the same type. This will replace the expired one.</p>
                <a href="" id="renewLink" class="btn btn-success rounded-pill">Go to Upload Form</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-doc-id]').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('renewLink').href = '<?= BASE_URL ?>guide/certifications';
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>