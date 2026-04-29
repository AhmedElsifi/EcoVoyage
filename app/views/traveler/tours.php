<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="card shadow-sm border-0 rounded-4 mb-5">
    <div class="card-body p-4">
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-success"></i>
            </span>
            <input type="text" id="tourSearch" class="form-control border-start-0"
                placeholder="Search by name, location, or guide...">
        </div>
    </div>
</div>

<div class="row g-4" id="toursContainer">
    <?php if (empty($tours)): ?>
        <div class="col-12 text-center py-5" id="noToursMsg">
            <h4 class="text-muted">No tours available right now.</h4>
        </div>
    <?php else: ?>
        <?php foreach ($tours as $tour): ?>
            <?php
            $imgSrc = $tour['image'] ?? null;
            $imgUrl = $imgSrc ? BASE_URL . ltrim($imgSrc, '/') : 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=600&h=400&fit=crop';
            $impactTags = !empty($tour['impact_tags']) ? explode(',', $tour['impact_tags']) : [];

            $searchText = strtolower(
                ($tour['name'] ?? '') . ' ' .
                ($tour['location'] ?? '') . ' ' .
                ($tour['country'] ?? '') . ' ' .
                ($tour['guide_name'] ?? '')
            );
            ?>
            <div class="col-md-6 col-lg-4 tour-card" data-search="<?= htmlspecialchars($searchText) ?>">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <img src="<?= $imgUrl ?>" class="card-img-top" alt="<?= htmlspecialchars($tour['name']) ?>"
                        style="height: 220px; object-fit: cover;">
                    <div class="card-body p-4">
                        <p class="small text-muted mb-1">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($tour['guide_name'] ?? 'Eco Guide') ?>
                        </p>
                        <h5 class="card-title fw-bold">
                            <?= htmlspecialchars($tour['name']) ?>
                        </h5>
                        <p class="text-muted mb-2">
                            <i class="bi bi-geo-alt-fill text-success me-1"></i>
                            <?= htmlspecialchars($tour['location'] ?? '') ?>
                            <?= isset($tour['country']) ? ', ' . htmlspecialchars($tour['country']) : '' ?>
                        </p>

                        <?php if (!empty($impactTags)): ?>
                            <div class="mb-2">
                                <?php foreach ($impactTags as $tag): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success me-1 mb-1">🌱
                                        <?= ucfirst(str_replace('_', ' ', $tag)) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <h5 class="fw-bold text-success mb-0">$
                                <?= number_format($tour['price'] ?? 0) ?>
                            </h5>
                            <a href="<?= BASE_URL ?>traveler/tour/<?= $tour['tour_id'] ?>"
                                class="btn btn-outline-success rounded-pill me-1">
                                <i class="bi bi-info-circle me-1"></i>Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="noResultsMsg" class="text-center py-5 d-none">
    <h4 class="text-muted">No tours match your search.</h4>
</div>

<script>
    document.getElementById('tourSearch').addEventListener('input', function (e) {
        const query = e.target.value.trim().toLowerCase();
        const cards = document.querySelectorAll('.tour-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const searchData = card.getAttribute('data-search') || '';
            if (query === '' || searchData.includes(query)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noResults = document.getElementById('noResultsMsg');
        if (visibleCount === 0 && cards.length > 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>