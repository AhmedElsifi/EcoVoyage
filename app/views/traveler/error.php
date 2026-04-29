<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="text-center py-5">
    <i class="bi bi-exclamation-circle text-danger display-1 mb-3"></i>
    <h2 class="fw-bold">Oops!</h2>
    <p class="lead">
        <?= htmlspecialchars($message) ?>
    </p>
    <a href="<?= BASE_URL ?>traveler/tours" class="btn btn-success rounded-pill">Back to Tours</a>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>