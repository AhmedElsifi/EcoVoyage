<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-success">Regional Auditor Application</h2>
                <p class="text-muted">Help us maintain eco‑standards in your region</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><?php foreach ($errors as $e)
                    echo htmlspecialchars($e) . '<br>'; ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>auth/register/auditor" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="name" class="form-control" required
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Region You Want to Audit</label>
                    <input type="text" name="assigned_region" class="form-control" placeholder="e.g., Amazon Basin"
                        required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+...">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Upload Your CV (PDF only)</label>
                    <input type="file" name="cv" class="form-control" accept="application/pdf" required>
                    <small class="text-muted">This will be reviewed by our admin team.</small>
                </div>

                <input type="hidden" name="role" value="regional_auditor">
                <button type="submit" class="btn btn-success w-100 rounded-pill btn-lg">Submit Application</button>
                <p class="text-center text-muted mt-2">Already have an account? <a
                        href="<?= BASE_URL ?>auth/login">Login</a></p>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>