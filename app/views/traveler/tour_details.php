<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<a href="<?= BASE_URL ?>traveler/tours" class="btn btn-outline-success rounded-pill mb-4">
    <i class="bi bi-arrow-left me-2"></i>Back to Tours
</a>

<div class="row g-5">
    <div class="col-lg-8">
        <?php
        $imgSrc = $tour['tour_img_path'] ?? null;
        $imgUrl = $imgSrc ? BASE_URL . ltrim($imgSrc, '/') : 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800&h=500&fit=crop';
        ?>
        <img src="<?= $imgUrl ?>" class="img-fluid rounded-4 shadow-sm mb-4 w-100"
            style="max-height: 450px; object-fit: cover;" alt="<?= htmlspecialchars($tour['tour_name']) ?>">

        <h2 class="fw-bold text-success mb-3">
            <?= htmlspecialchars($tour['tour_name']) ?>
        </h2>

        <div class="d-flex flex-wrap align-items-center text-muted mb-3 gap-3">
            <span><i class="bi bi-person-circle me-1"></i>
                <?= htmlspecialchars($tour['guide_name'] ?? 'Eco Guide') ?>
            </span>
            <span><i class="bi bi-geo-alt-fill text-success me-1"></i>
                <?= htmlspecialchars($tour['location_name'] ?? '') ?>,
                <?= htmlspecialchars($tour['country'] ?? '') ?>
            </span>
            <?php if (!empty($tour['tour_type'])): ?>
                <span class="badge bg-success">
                    <?= ucfirst(str_replace('_', ' ', $tour['tour_type'])) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($tour['impact_tags'])): ?>
            <?php $tags = explode(',', $tour['impact_tags']); ?>
            <div class="mb-3">
                <?php foreach ($tags as $tag): ?>
                    <span class="badge bg-success bg-opacity-25 text-success me-2 mb-2">🌱
                        <?= ucfirst(str_replace('_', ' ', $tag)) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <span class="text-muted">From </span>
            <h3 class="fw-bold text-success d-inline">$
                <?= number_format($tour['min_price'] ?? 0) ?>
            </h3>
            <span class="text-muted"> / person</span>
        </div>

        <div class="mb-3">
            <span class="text-muted">Eco‑Leaf Rating</span>
            <div class="mt-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi bi-tree-fill fs-5 <?= $i <= $ecoLeaves ? 'text-success' : 'text-muted' ?>"></i>
                <?php endfor; ?>
                <span class="ms-2 fw-bold text-success">
                    <?= $ecoLeaves ?>/5
                </span>
            </div>
        </div>
        <hr>

        <?php if (!empty($guide['bio'])): ?>
            <h5 class="fw-bold">About the Guide</h5>
            <p>
                <?= nl2br(htmlspecialchars($guide['bio'])) ?>
            </p>
        <?php endif; ?>

        <h5 class="fw-bold mt-4">Sustainability Highlights</h5>
        <ul class="list-unstyled">
            <?php if (!empty($tour['waste_management'])): ?>
                <li><i class="bi bi-recycle text-success me-2"></i>Waste management:
                    <?= htmlspecialchars($tour['waste_management']) ?>
                </li>
            <?php endif; ?>
            <?php if (!empty($tour['local_hiring'])): ?>
                <li><i class="bi bi-people-fill text-success me-2"></i>Local community hiring engaged</li>
            <?php endif; ?>
        </ul>

        <?php if (!empty($tour['routes'])): ?>
            <?php $routeList = json_decode($tour['routes'], true); ?>
            <?php if (is_array($routeList) && count($routeList) > 0): ?>
                <h5 class="fw-bold mt-4">Route / Stops</h5>
                <ol class="list-group list-group-numbered mb-3">
                    <?php foreach ($routeList as $stop): ?>
                        <li class="list-group-item border-0 ps-0">
                            <?= htmlspecialchars($stop) ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <form id="bookingForm" method="GET" action="<?= BASE_URL ?>traveler/book">
            <input type="hidden" name="tour_id" value="<?= $tour['tour_id'] ?>">

            <div class="card border-0 shadow sticky-top rounded-4" style="top: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Book This Tour</h5>
                    <?php if (!empty($versions)): ?>
                        <div class="mb-3">
                            <label for="versionSelect" class="form-label fw-semibold">Choose Version</label>
                            <select id="versionSelect" name="version_id" class="form-select">
                                <?php foreach ($versions as $ver): ?>
                                    <option value="<?= $ver['tour_version_id'] ?>" data-price="<?= $ver['price_per_person'] ?>"
                                        data-capacity="<?= $ver['max_capacity'] ?>"
                                        data-discounts='<?= htmlspecialchars($ver['group_discounts'] ?? '[]') ?>'>
                                        <?= htmlspecialchars($ver['version_name']) ?> –
                                        $<?= number_format($ver['price_per_person']) ?>
                                        <?php if (!empty($ver['booking_type'])): ?>
                                            (<?= $ver['booking_type'] === 'instant' ? 'Instant' : 'Request' ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="numTravelers" class="form-label fw-semibold">Travelers</label>
                        <input type="number" id="numTravelers" name="num_travelers" class="form-control" value="1"
                            min="1" max="<?= $versions[0]['max_capacity'] ?? 20 ?>"
                            data-max-capacity="<?= $versions[0]['max_capacity'] ?? 20 ?>">
                    </div>
                    <div class="mb-3">
                        <label for="startDateTime" class="form-label fw-semibold">Tour Date & Time</label>
                        <input type="datetime-local" id="startDateTime" name="start_time" class="form-control" required
                            min="<?= date('Y-m-d\TH:i', strtotime('+1 day')) ?>">
                    </div>
                    <?php if (!empty($addons)): ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Extra Add‑ons</label>
                            <?php foreach ($addons as $addon): ?>
                                <div class="form-check">
                                    <input class="form-check-input addon-check" type="checkbox" name="addons[]"
                                        value="<?= $addon['addon_id'] ?>" data-price="<?= $addon['price'] ?>"
                                        id="addon_<?= $addon['addon_id'] ?>">
                                    <label class="form-check-label" for="addon_<?= $addon['addon_id'] ?>">
                                        <?= htmlspecialchars($addon['name']) ?>
                                        (+$<?= number_format($addon['price']) ?>)
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($tour['carbon_footprint']) && !empty($projects)): ?>
                        <?php $footprintKg = (float) $tour['carbon_footprint']; ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">🌱 Carbon Offset</label>
                            <div class="card bg-light border-0 p-3">
                                <p class="mb-2 small">This tour emits
                                    <strong><?= number_format($footprintKg, 1) ?> kg CO₂</strong>/person.
                                </p>
                                <label for="offsetProject" class="form-label small fw-semibold">Offset project</label>
                                <select id="offsetProject" name="offset_project" class="form-select">
                                    <option value="">None (skip offset)</option>
                                    <?php foreach ($projects as $proj): ?>
                                        <?php $costPerPerson = $footprintKg * (float) $proj['cost_per_kg']; ?>
                                        <option value="<?= $proj['project_id'] ?>" data-cost="<?= $costPerPerson ?>"
                                            data-description="<?= htmlspecialchars($proj['description']) ?>">
                                            <?= htmlspecialchars($proj['name']) ?>
                                            (+$<?= number_format($costPerPerson, 2) ?>/person)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="small text-muted mt-1" id="projectDesc"></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr>
                    <h5>Total: <span id="totalPrice" class="text-success fw-bold">$0.00</span></h5>
                    <small class="text-muted">Includes group discount & taxes.</small>

                    <button type="submit" class="btn btn-success w-100 rounded-pill btn-lg mt-3 mb-2">
                        <i class="bi bi-calendar-check me-2"></i> Book Now
                    </button>
                    <p class="text-center text-muted small mb-0">
                        <i class="bi bi-shield-check"></i> Secure booking & carbon offset
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($guide)): ?>
    <div class="card border-0 shadow-sm rounded-4 mt-5 p-4">
        <h5 class="fw-bold mb-3">Meet Your Guide</h5>
        <div class="d-flex align-items-start gap-3">
            <img src="<?= BASE_URL ?>assets/images/profile.png" class="rounded-circle" width="80" height="80"
                alt="Guide photo" style="object-fit: cover;">
            <div>
                <h6 class="fw-bold mb-1">
                    <?= htmlspecialchars($tour['guide_name']) ?>
                </h6>
                <?php $guideBadges = json_decode($guide['badges'] ?? '[]', true); ?>
                <?php if (!empty($guideBadges)): ?>
                    <div class="mb-2">
                        <?php foreach ($guideBadges as $badge): ?>
                            <span class="badge bg-success bg-opacity-25 text-success me-1">
                                <?= htmlspecialchars($badge) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <p class="text-muted mb-2">
                    <i class="bi bi-geo-alt-fill text-success me-1"></i>
                    <?= htmlspecialchars($guide['country_of_residence'] ?? '') ?>
                </p>
                <p><?= htmlspecialchars($guide['bio'] ?? '') ?></p>
                <div class="d-flex gap-3 text-muted">
                    <span><i class="bi bi-briefcase me-1"></i>
                        <?= $guide['years_of_experience'] ?? '?' ?> years exp.
                    </span>
                    <span><i class="bi bi-star-fill text-warning me-1"></i>
                        <?= $guide['sustainability_score'] ?? '—' ?>% eco score
                    </span>
                </div>
                <div class="mt-2">
                    <i class="bi bi-pin-map-fill text-success me-1"></i>
                    <strong>Local Cred:</strong>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $localCredScore ?>%;"
                            aria-valuenow="<?= $localCredScore ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">
                        <?= $localCredScore ?>/100
                    </small>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($versions)): ?>
    <?php foreach ($versions as $index => $ver): ?>
        <?php $itinerary = json_decode($ver['itinerary_json'] ?? '{}', true); ?>
        <?php if (!empty($itinerary)): ?>
            <div class="card border-0 shadow-sm rounded-4 mt-4 p-4">
                <h5 class="fw-bold mb-3">
                    <?= htmlspecialchars($ver['version_name']) ?> Itinerary
                    <span class="badge bg-success ms-2">$<?= number_format($ver['price_per_person']) ?></span>
                </h5>
                <div class="row">
                    <?php foreach ($itinerary as $day => $desc): ?>
                        <div class="col-md-6 mb-3">
                            <strong class="text-success"><?= ucfirst($day) ?></strong><br>
                            <span class="text-muted"><?= htmlspecialchars($desc) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($location['seasonal_rules'])): ?>
    <?php $seasons = json_decode($location['seasonal_rules'], true); ?>
    <?php if (!empty($seasons)): ?>
        <div class="card border-0 shadow-sm rounded-4 mt-4 p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-sun text-warning me-2"></i>Best Time to Visit</h5>
            <ul class="list-unstyled mb-0">
                <?php foreach ($seasons as $season => $period): ?>
                    <li><strong><?= ucfirst(str_replace('_', ' ', $season)) ?>:</strong>
                        <?= htmlspecialchars($period) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
    function parseDiscounts(discountsStr) {
        try { return JSON.parse(discountsStr); } catch (e) { return []; }
    }

    function getGroupDiscountPercent(num, tiers) {
        let best = 0;
        tiers.forEach(tier => {
            if (num >= tier.min_persons) {
                best = Math.max(best, tier.discount_percent);
            }
        });
        return best;
    }

    function updatePrice() {
        const versionSelect = document.getElementById('versionSelect');
        const travelersInput = document.getElementById('numTravelers');

        if (!versionSelect || !travelersInput) return;

        const selectedOption = versionSelect.selectedOptions[0];
        if (!selectedOption) return;
        const maxCapacity = parseInt(selectedOption.dataset.capacity) || 10;
        travelersInput.max = maxCapacity;
        let numTravelers = parseInt(travelersInput.value) || 1;
        if (numTravelers > maxCapacity) {
            numTravelers = maxCapacity;
            travelersInput.value = maxCapacity;
        }

        const basePerPerson = parseFloat(selectedOption.dataset.price) || 0;
        const discountStr = selectedOption.dataset.discounts || '[]';
        const discountTiers = parseDiscounts(discountStr);
        const discountPercent = getGroupDiscountPercent(numTravelers, discountTiers);

        let total = basePerPerson * numTravelers;
        if (discountPercent > 0) {
            total *= (1 - discountPercent / 100);
        }

        document.querySelectorAll('.addon-check:checked').forEach(cb => {
            total += (parseFloat(cb.dataset.price) || 0) * numTravelers;
        });

        const offsetSelect = document.getElementById('offsetProject');
        if (offsetSelect && offsetSelect.value) {
            const costPerPerson = parseFloat(offsetSelect.selectedOptions[0]?.dataset.cost) || 0;
            total += costPerPerson * numTravelers;
        }

        const taxPercent = <?= $taxPercent ?? 5 ?>;
        const totalWithTax = total * (1 + taxPercent / 100);

        document.getElementById('totalPrice').textContent =
            '$' + totalWithTax.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    document.addEventListener('DOMContentLoaded', function () {
        const versionSelect = document.getElementById('versionSelect');
        const travelersInput = document.getElementById('numTravelers');
        const offsetSelect = document.getElementById('offsetProject');

        versionSelect?.addEventListener('change', updatePrice);
        travelersInput?.addEventListener('input', updatePrice);
        document.querySelectorAll('.addon-check').forEach(cb => cb.addEventListener('change', updatePrice));

        offsetSelect?.addEventListener('change', function () {
            updatePrice();
            const desc = this.options[this.selectedIndex]?.dataset.description || '';
            document.getElementById('projectDesc').textContent = desc;
        });

        updatePrice();
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>