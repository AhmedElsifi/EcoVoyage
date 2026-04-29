<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <h2 class="fw-bold text-success mb-4">Account Settings</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4">
            <form method="POST" action="<?= BASE_URL ?>traveler/settings">

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

                <div class="mb-3">
                    <label class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" class="form-control"
                        value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nationality</label>
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($traveler['nationality'] ?? 'Not set') ?>" disabled>
                </div>

                <hr>
                <h5 class="fw-bold mb-3">Change Password</h5>

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