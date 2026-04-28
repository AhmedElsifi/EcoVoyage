<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-success">Guide Registration</h2>
                <p class="text-muted">Set up your guide profile and start offering eco‑tours</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e)
                        echo htmlspecialchars($e) . '<br>'; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>auth/register/guide" enctype="multipart/form-data">
                <h5 class="fw-bold mb-3">Account Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="name" class="form-control" required
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <h5 class="fw-bold mb-3 mt-4">Guide Profile</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Country of Residence</label>
                        <input type="text" name="country_of_residence" class="form-control" placeholder="e.g., Brazil">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">City / Region</label>
                        <input type="text" name="city" class="form-control" placeholder="e.g., Manaus">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Bio</label>
                    <textarea name="bio" class="form-control" rows="4"
                        placeholder="Tell us about your background, passion for eco‑travel..."><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Years of Experience</label>
                        <input type="number" name="years_of_experience" class="form-control" min="0"
                            value="<?= htmlspecialchars($_POST['years_of_experience'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+...">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Identity Verification Document</label>
                    <input type="file" name="identity_doc" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Upload ID/passport for verification</small>
                </div>

                <input type="hidden" name="role" value="guide">
                <button type="submit" class="btn btn-success w-100 rounded-pill btn-lg mt-3">Apply as Guide</button>
                <p class="text-center text-muted mt-2">Already have an account? <a
                        href="<?= BASE_URL ?>auth/login">Login</a></p>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>