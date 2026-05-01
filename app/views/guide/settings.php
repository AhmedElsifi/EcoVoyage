<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <h2 class="fw-bold text-success mb-4">Guide Settings</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="alert alert-warning border-0 rounded-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Any changes to your profile will require re‑approval by a regional auditor.
            </div>
            <form method="POST" action="<?= BASE_URL ?>guide/settings">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    <small class="text-muted">Email cannot be changed.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="tel" name="phone" class="form-control"
                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>

                <hr>
                <h5 class="fw-bold mb-3">Guide Profile</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Country of Residence</label>
                    <input type="text" name="country_of_residence" class="form-control"
                        value="<?= htmlspecialchars($guide['country_of_residence'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Bio</label>
                    <textarea name="bio" class="form-control"
                        rows="4"><?= htmlspecialchars($guide['bio'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Years of Experience</label>
                    <input type="number" name="years_of_experience" class="form-control" min="0"
                        value="<?= htmlspecialchars($guide['years_of_experience'] ?? 0) ?>">
                </div>

                <hr>
                <h5 class="fw-bold mb-3">Change Password <small class="text-muted">(optional)</small></h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password</label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Leave blank to keep current">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control"
                        placeholder="Re‑type new password">
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

                <button type="submit" class="btn btn-success w-100 rounded-pill btn-lg mt-3">
                    <i class="bi bi-check-circle me-2"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>