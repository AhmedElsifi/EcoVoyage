<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 class="fw-bold text-success mb-4">
    <?= isset($tour['tour_id']) ? 'Edit Tour' : 'Create New Tour' ?>
</h2>

<form method="POST" action="" enctype="multipart/form-data" id="tourForm">
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-3">Basic Information</h5>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tour Name</label>
                    <input type="text" name="tour_name" class="form-control"
                        value="<?= htmlspecialchars($tour['tour_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control"
                        rows="3"><?= htmlspecialchars($tour['description'] ?? '') ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Location</label>
                        <select name="location_id" class="form-select" required>
                            <option value="">Select location</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= $loc['location_id'] ?>" <?php echo ($tour['location_id'] ?? '') == $loc['location_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loc['location_name']) ?> -
                                    <?= htmlspecialchars($loc['country']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tour Type</label>
                        <select name="tour_type" class="form-select">
                            <?php foreach ((new Tours())->getTourTypes() as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($tour['tour_type'] ?? '') == $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tour Image</label>
                    <?php if (!empty($tour['tour_img_path'])): ?>
                        <div class="mb-2">
                            <img src="<?= BASE_URL . ltrim($tour['tour_img_path'], '/') ?>" alt="Current image"
                                class="img-fluid rounded-3" style="max-height: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="tour_image" class="form-control" accept="image/*">
                    <small class="text-muted">Upload a new image (JPEG/PNG, max 2MB). Leave empty to keep the current
                        one.</small>
                </div>

                <div class="mb-3 border-top pt-3 mt-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="indigenous_consent"
                            id="indigenous_consent" <?= !empty($tour['consent_doc_id']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="indigenous_consent">
                            This tour takes place on protected / indigenous land
                        </label>
                    </div>
                    <div id="consentFileSection" style="<?= !empty($tour['consent_doc_id']) ? '' : 'display:none;' ?>">
                        <label class="form-label fw-semibold">Consent Document</label>
                        <?php if (!empty($tour['consent_doc_path'])): ?>
                            <div class="mb-2">
                                <a href="<?= BASE_URL . ltrim($tour['consent_doc_path'], '/') ?>" target="_blank">
                                    View current consent document
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="consent_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Upload a letter or agreement from the indigenous community / land
                            authority (PDF, JPG, PNG).</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Carbon Footprint (kg CO₂)</label>
                        <input type="number" step="0.01" name="carbon_footprint" class="form-control"
                            value="<?= htmlspecialchars($tour['carbon_footprint'] ?? '0') ?>">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">Impact Tags</label>
                        <div class="row g-2">
                            <?php
                            $allTags = [
                                'carbon_neutral' => 'Carbon Neutral',
                                'plastic_free' => 'Plastic Free',
                                'local_community' => 'Local Community',
                                'wildlife_protection' => 'Wildlife Protection',
                                'renewable_energy' => 'Renewable Energy',
                                'zero_waste' => 'Zero Waste',
                                'sustainable_food' => 'Sustainable Food',
                                'ocean_conservation' => 'Ocean Conservation',
                                'reforestation' => 'Reforestation',
                                'fair_wage' => 'Fair Wage'
                            ];
                            $selectedTags = [];
                            if (isset($tour['impact_tags'])) {
                                $selectedTags = is_array($tour['impact_tags'])
                                    ? $tour['impact_tags']
                                    : explode(',', $tour['impact_tags']);
                            }
                            foreach ($allTags as $val => $label): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="impact_tags[]"
                                            value="<?= $val ?>" id="tag_<?= $val ?>" <?= in_array($val, $selectedTags) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="tag_<?= $val ?>">
                                            🌱 <?= $label ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Route / Stops</label>
                    <textarea name="routes_text" class="form-control" rows="3"
                        placeholder="Start at Manaus&#10;Camp 1 night in the jungle&#10;Kayak back"><?= htmlspecialchars($tour['routes_text'] ?? '') ?></textarea>
                    <small class="text-muted">Enter one stop per line.</small>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="waste_management" id="wm"
                        <?= !empty($tour['waste_management']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="wm">Waste Management</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="local_hiring" id="lh"
                        <?= !empty($tour['local_hiring']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="lh">Local Hiring</label>
                </div>
            </div>
        </div>

        <?php if (!isset($tour['tour_id'])): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3">First Version</h5>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Version Name</label>
                        <input type="text" name="version_name" class="form-control" value="Standard" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Price per Person ($)</label>
                        <input type="number" step="0.01" name="price_per_person" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Max Capacity</label>
                        <input type="number" name="max_capacity" class="form-control" value="10" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Itinerary</label>
                        <textarea name="itinerary_text" class="form-control" rows="4"
                            placeholder="Day 1: Arrival and briefing&#10;Day 2: Jungle trek&#10;Day 3: Return"></textarea>
                        <small class="text-muted">Write one line per day, e.g. "Day 1: Arrival at base camp".</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Booking Type</label>
                        <select name="booking_type" class="form-select">
                            <option value="instant">Instant</option>
                            <option value="request">Request</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Group Discounts</label>
                        <div id="discountTiers"></div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="addDiscountRowNew2()">
                            <i class="bi bi-plus"></i> Add Discount Tier
                        </button>
                        <small class="text-muted d-block mt-1">Example: 4+ persons → 5% discount.</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($tour['tour_id'])): ?>
            <div class="col-12 mt-4">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3">Versions</h5>
                    <?php foreach ($versions as $ver): ?>
                        <div class="card bg-light mb-3 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Version: <?= htmlspecialchars($ver['version_name']) ?></h6>
                                <div class="form-check">
                                    <input class="form-check-input delete-version" type="checkbox" name="delete_versions[]"
                                        value="<?= $ver['tour_version_id'] ?>" id="del_<?= $ver['tour_version_id'] ?>">
                                    <label class="form-check-label text-danger"
                                        for="del_<?= $ver['tour_version_id'] ?>">Delete</label>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small">Name</label>
                                    <input type="text" name="versions[<?= $ver['tour_version_id'] ?>][version_name]"
                                        class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($ver['version_name']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Price ($)</label>
                                    <input type="number" step="0.01"
                                        name="versions[<?= $ver['tour_version_id'] ?>][price_per_person]"
                                        class="form-control form-control-sm" value="<?= $ver['price_per_person'] ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Capacity</label>
                                    <input type="number" name="versions[<?= $ver['tour_version_id'] ?>][max_capacity]"
                                        class="form-control form-control-sm" value="<?= $ver['max_capacity'] ?>" min="1"
                                        required>
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="form-label small">Itinerary</label>
                                <textarea name="versions[<?= $ver['tour_version_id'] ?>][itinerary_text]"
                                    class="form-control form-control-sm"
                                    rows="3"><?= htmlspecialchars($ver['itinerary_text'] ?? '') ?></textarea>
                                <small class="text-muted">One line per day, e.g., "Day 1: Arrival".</small>
                            </div>
                            <div class="mt-2 row">
                                <div class="col-md-6">
                                    <label class="form-label small">Booking Type</label>
                                    <select name="versions[<?= $ver['tour_version_id'] ?>][booking_type]"
                                        class="form-select form-select-sm">
                                        <option value="instant" <?= ($ver['booking_type'] ?? '') == 'instant' ? 'selected' : '' ?>>
                                            Instant</option>
                                        <option value="request" <?= ($ver['booking_type'] ?? '') == 'request' ? 'selected' : '' ?>>
                                            Request</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Group Discounts</label>
                                    <div class="d-flex flex-column existing-discount-container"
                                        data-version-id="<?= $ver['tour_version_id'] ?>">
                                    </div>
                                    <button type="button" class="btn btn-outline-success btn-sm mt-1"
                                        onclick="addDiscountRowExisting(this, '<?= $ver['tour_version_id'] ?>')">+ Add
                                        Tier</button>
                                </div>
                            </div>
                            <input type="hidden" class="existing-discounts-data"
                                value='<?= htmlspecialchars($ver['group_discounts'] ?? '[]') ?>'>
                        </div>
                    <?php endforeach; ?>

                    <hr>
                    <h6 class="fw-bold mb-3 mt-3">Add New Version</h6>
                    <div id="newVersionsContainer"></div>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="addNewVersionRow()">+ Add Another
                        Version</button>
                    <small class="text-muted d-block mt-1">Click the button to add a new version to this tour.</small>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="col-12">
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div><?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-12">
            <button type="submit" class="btn btn-success rounded-pill btn-lg">
                <?= isset($tour['tour_id']) ? 'Update Tour' : 'Create Tour' ?>
            </button>
            <a href="<?= BASE_URL ?>guide/tours" class="btn btn-outline-secondary rounded-pill btn-lg ms-2">Cancel</a>
        </div>
    </div>
</form>

<script>
    function addDiscountRowExisting(btn, versionId) {
        const container = btn.parentElement.querySelector('.existing-discount-container');
        const row = document.createElement('div');
        row.className = 'input-group input-group-sm mt-2';
        row.innerHTML = `
        <span class="input-group-text">Min persons</span>
        <input type="number" name="versions[${versionId}][discount_min][]" class="form-control" min="2" placeholder="4">
        <span class="input-group-text">Discount %</span>
        <input type="number" name="versions[${versionId}][discount_percent][]" class="form-control" min="1" max="100" placeholder="5">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()">×</button>
    `;
        container.appendChild(row);
    }

    document.querySelectorAll('.existing-discounts-data').forEach(function (hidden) {
        const container = hidden.parentElement.querySelector('.existing-discount-container');
        if (!container) return;
        let data = [];
        try { data = JSON.parse(hidden.value); } catch (e) { }
        data.forEach(function (d) {
            const row = document.createElement('div');
            row.className = 'input-group input-group-sm mt-2';
            row.innerHTML = `
            <span class="input-group-text">Min persons</span>
            <input type="number" name="versions[${container.dataset.versionId}][discount_min][]" class="form-control" value="${d.min_persons}" min="2" placeholder="4">
            <span class="input-group-text">Discount %</span>
            <input type="number" name="versions[${container.dataset.versionId}][discount_percent][]" class="form-control" value="${d.discount_percent}" min="1" max="100" placeholder="5">
            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()">×</button>
        `;
            container.appendChild(row);
        });
    });

    function addNewVersionRow() {
        const container = document.getElementById('newVersionsContainer');
        const div = document.createElement('div');
        div.className = 'card bg-light mb-3 p-3';
        div.innerHTML = `
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small">Version Name</label>
                <input type="text" name="new_version_name[]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Price ($)</label>
                <input type="number" step="0.01" name="new_version_price[]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Capacity</label>
                <input type="number" name="new_version_capacity[]" class="form-control form-control-sm" value="10" min="1" required>
            </div>
        </div>
        <div class="mt-2">
            <label class="form-label small">Itinerary</label>
            <textarea name="new_version_itinerary_text[]" class="form-control form-control-sm" rows="3" placeholder="Day 1: ...&#10;Day 2: ..."></textarea>
        </div>
        <div class="mt-2 row">
            <div class="col-md-6">
                <label class="form-label small">Booking Type</label>
                <select name="new_version_booking_type[]" class="form-select form-select-sm">
                    <option value="instant">Instant</option>
                    <option value="request">Request</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small">Group Discounts</label>
                <div class="new-discount-container"></div>
                <button type="button" class="btn btn-outline-success btn-sm mt-1" onclick="addDiscountRowNew(this)">+ Add Tier</button>
            </div>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm mt-2" onclick="this.closest('.card').remove()">Remove this version</button>
    `;
        container.appendChild(div);
    }

    function addDiscountRowNew(btn) {
        const container = btn.previousElementSibling;
        const row = document.createElement('div');
        row.className = 'input-group input-group-sm mt-2';
        row.innerHTML = `
        <span class="input-group-text">Min persons</span>
        <input type="number" name="new_version_discount_min[]" class="form-control" min="2" placeholder="4">
        <span class="input-group-text">Discount %</span>
        <input type="number" name="new_version_discount_percent[]" class="form-control" min="1" max="100" placeholder="5">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()">×</button>
    `;
        container.appendChild(row);
    }

    function addDiscountRowNew2() {
        const container = document.getElementById('discountTiers');
        const row = document.createElement('div');
        row.className = 'input-group input-group-sm mb-2';
        row.innerHTML = `
        <span class="input-group-text">Min persons</span>
        <input type="number" name="discount_min[]" class="form-control" min="2" placeholder="4">
        <span class="input-group-text">Discount %</span>
        <input type="number" name="discount_percent[]" class="form-control" min="1" max="100" placeholder="5">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()">×</button>
    `;
        container.appendChild(row);
    }

    document.getElementById('tourForm').addEventListener('submit', function (e) {
        const deleteCheckboxes = document.querySelectorAll('.delete-version:checked');
        if (deleteCheckboxes.length > 0) {
            const confirmed = confirm(
                '⚠️ You selected ' + deleteCheckboxes.length + ' version(s) for deletion.\n' +
                'All travelers with confirmed bookings on these versions will receive a full refund.\n' +
                'Are you sure you want to continue?'
            );
            if (!confirmed) {
                e.preventDefault();
            }
        }
    });

    document.getElementById('indigenous_consent')?.addEventListener('change', function () {
        const fileSection = document.getElementById('consentFileSection');
        if (this.checked) {
            fileSection.style.display = 'block';
        } else {
            fileSection.style.display = 'none';
        }
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>