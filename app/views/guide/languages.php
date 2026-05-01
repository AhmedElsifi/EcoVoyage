<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 class="fw-bold text-success mb-4">Language Certificates</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $err): ?>
            <div><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
    <h5 class="fw-bold mb-3">Add New Language Certificate</h5>
    <form method="POST" action="<?= BASE_URL ?>guide/addLanguage" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Language</label>
                <input type="text" name="language" class="form-control" placeholder="e.g., Spanish" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Proficiency</label>
                <select name="proficiency" class="form-select">
                    <option value="fluent">Fluent</option>
                    <option value="native">Native</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="basic">Basic</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Issued Date</label>
                <input type="date" name="issued_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control">
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label fw-semibold">Certificate File</label>
            <input type="file" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
            <small class="text-muted">PDF, JPG, or PNG (max 2 MB)</small>
        </div>
        <button type="submit" class="btn btn-success rounded-pill mt-3">
            <i class="bi bi-upload me-2"></i> Submit Certificate
        </button>
    </form>
</div>

<h5 class="fw-bold mb-3">My Certificates</h5>
<?php if (empty($languages)): ?>
    <div class="alert alert-success bg-opacity-20 border-0 rounded-4">
        You haven’t added any language certificates yet.
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($languages as $lang): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($lang['language']) ?></h5>
                            <p class="mb-0 small text-muted">
                                Proficiency: <?= ucfirst(htmlspecialchars($lang['proficiency_level'])) ?>
                            </p>
                        </div>
                        <?php if ($lang['cert_status'] === 'approved'): ?>
                            <span class="badge bg-success">Verified</span>
                        <?php elseif ($lang['cert_status'] === 'pending'): ?>
                            <span class="badge bg-warning text-dark">Pending Review</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No Certificate</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($lang['cert_path']): ?>
                        <div class="mt-3">
                            <p class="small mb-1">
                                <strong>Current Certificate:</strong>
                                <a href="<?= BASE_URL . ltrim($lang['cert_path'], '/') ?>" target="_blank">View File</a>
                            </p>
                            <?php if ($lang['expiry_date']): ?>
                                <?php $isExpired = strtotime($lang['expiry_date']) < time(); ?>
                                <p class="small mb-1 <?= $isExpired ? 'text-danger' : '' ?>">
                                    Expires: <?= date('M d, Y', strtotime($lang['expiry_date'])) ?>
                                    <?php if ($isExpired): ?> (Expired) <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($lang['all_docs'])): ?>
                        <button class="btn btn-outline-success btn-sm mt-2" type="button" data-bs-toggle="collapse"
                            data-bs-target="#history_<?= $lang['id'] ?>">
                            View History (<?= count($lang['all_docs']) ?>)
                        </button>
                        <div class="collapse mt-2" id="history_<?= $lang['id'] ?>">
                            <ul class="list-group list-group-flush small">
                                <?php foreach ($lang['all_docs'] as $doc): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            Issued: <?= date('M d, Y', strtotime($doc['issued_date'])) ?>
                                            <?= $doc['expiry_date'] ? '· Expires: ' . date('M d, Y', strtotime($doc['expiry_date'])) : '' ?>
                                        </span>
                                        <span
                                            class="badge bg-<?= $doc['status'] == 'approved' ? 'success' : ($doc['status'] == 'pending' ? 'warning text-dark' : 'secondary') ?>">
                                            <?= ucfirst($doc['status']) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($lang['cert_path']) || (isset($isExpired) && $isExpired)): ?>
                        <form method="POST" action="<?= BASE_URL ?>guide/addLanguage" enctype="multipart/form-data" class="mt-2">
                            <input type="hidden" name="language" value="<?= htmlspecialchars($lang['language']) ?>">
                            <input type="hidden" name="proficiency" value="<?= htmlspecialchars($lang['proficiency_level']) ?>">
                            <input type="hidden" name="issued_date" value="<?= date('Y-m-d') ?>">
                            <label class="form-label small fw-semibold">Upload New Certificate</label>
                            <input type="file" name="certificate" class="form-control form-control-sm" required>
                            <button type="submit" class="btn btn-outline-success btn-sm mt-1">Renew</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>