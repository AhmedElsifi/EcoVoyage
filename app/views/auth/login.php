<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-success">Welcome Back</h2>
                <p class="text-muted">Log in to your EcoVoyage account</p>
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

            <form method="POST" action="<?= BASE_URL ?>auth/login">
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="you@example.com">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required
                        placeholder="Your password">
                </div>
                <button type="submit" class="btn btn-success w-100 rounded-pill btn-lg mb-3">
                    Log In
                </button>
                <p class="text-center text-muted mb-0">
                    Don't have an account? <a href="<?= BASE_URL ?>auth/register"
                        class="text-success fw-semibold">Register here</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>