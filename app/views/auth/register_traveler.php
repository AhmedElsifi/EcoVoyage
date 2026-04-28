<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-success">Traveler Registration</h2>
                <p class="text-muted">Fill in your details to start exploring</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e)
                        echo htmlspecialchars($e) . '<br>'; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>auth/register/traveler">
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
                    <label class="form-label fw-semibold">Nationality</label>
                    <input type="text" name="nationality" class="form-control" placeholder="e.g., USA">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+123456789">
                </div>
                <input type="hidden" name="role" value="traveler">
                <button type="submit" class="btn btn-success w-100 rounded-pill btn-lg mb-3">Create Account</button>
                <p class="text-center text-muted">Already registered? <a href="<?= BASE_URL ?>auth/login">Login</a></p>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>