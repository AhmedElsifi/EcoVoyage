<?php

class GuideController
{
    private $toursModel;
    private $usersModel;
    private $guidesModel;
    private $bookingsModel;
    private $locationsModel;
    private $tourVersionsModel;
    private $addonsModel;
    private $travelersModel;
    private $vaultModel;
    private $guideLanguagesModel;
    private $documentsModel;
    private $withdrawalRequestsModel;
    private $fieldReportsModel;   // added

    public function __construct()
    {
        $this->toursModel = new Tours();
        $this->bookingsModel = new Bookings();
        $this->guidesModel = new Guides();
        $this->usersModel = new Users();
        $this->locationsModel = new Locations();
        $this->tourVersionsModel = new TourVersions();
        $this->addonsModel = new Addons();
        $this->travelersModel = new Travelers();
        $this->vaultModel = new Vault();
        $this->guideLanguagesModel = new GuideLanguages();
        $this->documentsModel = new Documents();
        $this->withdrawalRequestsModel = new WithdrawalRequests();
        $this->fieldReportsModel = new FieldReports();

        if (!$this->usersModel->hasRole('guide')) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function dashboard()
    {
        $guideId = $_SESSION['user_id'];

        $approvedTours = $this->toursModel->countByGuideAndStatus($guideId, 'active');
        $nextBooking = $this->bookingsModel->getNextBookingForGuide($guideId);

        $this->guidesModel->recalculateBadges($guideId);
        $guide = $this->guidesModel->getById($guideId);
        $availableBalance = $guide['available_balance'] ?? 0;
        $pendingBalance = $guide['pending_balance'] ?? 0;
        $totalBalance = $availableBalance + $pendingBalance;

        $latestTours = $this->toursModel->getLatestByGuide($guideId, 10);

        $data = [
            'title' => 'Guide Dashboard – EcoVoyage',
            'approvedTours' => $approvedTours,
            'nextBooking' => $nextBooking,
            'availableBalance' => $availableBalance,
            'pendingBalance' => $pendingBalance,
            'totalBalance' => $totalBalance,
            'latestTours' => $latestTours,
            'guide' => $guide,
        ];

        View::load('guide/dashboard', $data);
    }

    public function schedule()
    {
        $guideId = $_SESSION['user_id'];
        $bookings = $this->bookingsModel->getAllByGuide($guideId);

        View::load('guide/schedule', [
            'title' => 'My Schedule – EcoVoyage',
            'bookings' => $bookings
        ]);
    }

    public function booking($bookingId)
    {
        $booking = $this->bookingsModel->getById($bookingId);
        if (!$booking || $booking['guide_id'] != $_SESSION['user_id']) {
            header('Location: ' . BASE_URL . 'guide/schedule');
            exit;
        }

        $version = $this->tourVersionsModel->getTourVersionById($booking['tour_version_id']);
        $tour = $this->toursModel->getById($version['tour_id']);
        $addonIds = json_decode($booking['selected_addons'] ?? '[]', true);
        $addons = !empty($addonIds) ? $this->addonsModel->getByIds($addonIds) : [];
        $traveler = $this->travelersModel->getTravelerById($booking['traveler_id']);
        $user = $this->usersModel->getById($booking['traveler_id']);

        View::load('guide/booking_details', [
            'title' => 'Booking #' . $bookingId . ' – EcoVoyage',
            'booking' => $booking,
            'tour' => $tour,
            'version' => $version,
            'addons' => $addons,
            'traveler' => $traveler,
            'user' => $user
        ]);
    }

    public function updateBookingStatus($bookingId)
    {
        $booking = $this->bookingsModel->getById($bookingId);
        if (!$booking || $booking['guide_id'] != $_SESSION['user_id']) {
            header('Location: ' . BASE_URL . 'guide/schedule');
            exit;
        }

        $action = $_POST['action'] ?? null;
        if (in_array($action, ['accept', 'decline', 'complete'])) {
            $newStatus = match ($action) {
                'accept' => 'confirmed',
                'decline' => 'declined',
                'complete' => 'completed'
            };

            if ($action === 'accept') {
                $version = $this->tourVersionsModel->getTourVersionById($booking['tour_version_id']);
                if ($version && ($version['booking_type'] ?? 'instant') === 'request') {
                    $newStatus = 'payment_pending';
                }
            }

            $this->bookingsModel->updateStatus($bookingId, $newStatus);

            if ($newStatus === 'completed') {
                $this->vaultModel->releaseFunds($bookingId);
                $this->guidesModel->updateCancellationRate($booking['guide_id']);

            }
        }

        header('Location: ' . BASE_URL . 'guide/booking/' . $bookingId);
        exit;
    }

    public function tours()
    {
        $guideId = $_SESSION['user_id'];
        $tours = $this->toursModel->getByGuide($guideId);

        foreach ($tours as &$t) {
            $t['eco_leaves'] = $this->toursModel->getEcoLeafRating($t);
        }
        unset($t);

        View::load('guide/tours', [
            'title' => 'My Tours – EcoVoyage',
            'tours' => $tours
        ]);
    }

    public function createTour()
    {
        $guideId = $_SESSION['user_id'];
        $locations = $this->locationsModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            $tourName = trim($_POST['tour_name'] ?? '');
            if (!Validator::required($tourName)) {
                $errors[] = 'Tour name is required.';
            }

            $locationId = $_POST['location_id'] ?? null;
            if (empty($locationId)) {
                $errors[] = 'Location is required.';
            }

            $price = $_POST['price_per_person'] ?? null;
            if (!Validator::positiveNumber($price)) {
                $errors[] = 'Price per person must be a positive number.';
            }

            $capacity = $_POST['max_capacity'] ?? 10;
            if (!Validator::numeric($capacity) || (int) $capacity < 1) {
                $errors[] = 'Maximum capacity must be at least 1.';
            }

            $versionName = trim($_POST['version_name'] ?? 'Standard');
            if (!Validator::required($versionName)) {
                $errors[] = 'Version name is required.';
            }

            $tour_img_path = null;
            if (!Validator::fileRequired('tour_image')) {
                $errors[] = 'Tour image is required.';
            } else {
                if (!Validator::fileType('tour_image', ['jpg', 'jpeg', 'png'])) {
                    $errors[] = 'Image must be JPG or PNG.';
                } elseif (!Validator::fileMaxSize('tour_image', 2048)) {
                    $errors[] = 'Image must be under 2 MB.';
                } else {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/tour_images/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['tour_image']['name'], PATHINFO_EXTENSION));
                    $filename = 'tour_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    move_uploaded_file($_FILES['tour_image']['tmp_name'], $uploadDir . $filename);
                    $tour_img_path = '/uploads/tour_images/' . $filename;
                }
            }

            // Indigenous consent validation using Validator
            $consentChecked = isset($_POST['indigenous_consent']);
            $consentExt = null;
            if ($consentChecked) {
                if (!Validator::fileRequired('consent_document')) {
                    $errors[] = 'Consent document is required when the tour is on protected/indigenous land.';
                } else {
                    if (!Validator::fileType('consent_document', ['pdf', 'jpg', 'jpeg', 'png'])) {
                        $errors[] = 'Consent document must be PDF, JPG, or PNG.';
                    } elseif (!Validator::fileMaxSize('consent_document', 2048)) {
                        $errors[] = 'Consent document must be under 2 MB.';
                    } else {
                        $consentExt = strtolower(pathinfo($_FILES['consent_document']['name'], PATHINFO_EXTENSION));
                    }
                }
            }

            if (!empty($errors)) {
                View::load('guide/tour_form', [
                    'title' => 'Create Tour – EcoVoyage',
                    'locations' => $locations,
                    'errors' => $errors,
                    'tour' => $_POST,
                    'versions' => []
                ]);
                return;
            }

            // ... routes, itinerary, discounts, tags (unchanged) ...
            $routesText = trim($_POST['routes_text'] ?? '');
            $routesArray = $routesText !== '' ? array_filter(array_map('trim', explode("\n", $routesText)), fn($s) => $s !== '') : [];
            $routesJson = !empty($routesArray) ? json_encode(array_values($routesArray)) : null;

            $itineraryText = trim($_POST['itinerary_text'] ?? '');
            $itineraryArray = [];
            if ($itineraryText !== '') {
                $lines = array_filter(array_map('trim', explode("\n", $itineraryText)), fn($s) => $s !== '');
                foreach ($lines as $line) {
                    if (preg_match('/^Day\s*(\d+)\s*:\s*(.+)/i', $line, $m)) {
                        $itineraryArray['day' . $m[1]] = $m[2];
                    } else {
                        $itineraryArray[] = $line;
                    }
                }
            }
            $itineraryJson = !empty($itineraryArray) ? json_encode($itineraryArray) : null;

            $minArr = $_POST['discount_min'] ?? [];
            $percentArr = $_POST['discount_percent'] ?? [];
            $discountsArray = [];
            foreach ($minArr as $i => $min) {
                $min = (int) $min;
                $percent = (int) ($percentArr[$i] ?? 0);
                if ($min >= 2 && $percent > 0) {
                    $discountsArray[] = ['min_persons' => $min, 'discount_percent' => $percent];
                }
            }
            $discountsJson = !empty($discountsArray) ? json_encode($discountsArray) : null;

            $impactTags = $_POST['impact_tags'] ?? [];
            $impactTags = is_array($impactTags) ? $impactTags : [];
            $autoTags = [];
            if (!empty($_POST['waste_management']))
                $autoTags[] = 'zero_waste';
            if (!empty($_POST['local_hiring']))
                $autoTags[] = 'local_community';
            if (isset($_POST['carbon_footprint']) && (float) $_POST['carbon_footprint'] <= 0)
                $autoTags[] = 'carbon_neutral';
            if (($_POST['tour_type'] ?? '') === 'wildlife_safari')
                $autoTags[] = 'wildlife_protection';

            $allTags = array_unique(array_merge($impactTags, $autoTags));
            $allowedTags = ['carbon_neutral', 'plastic_free', 'local_community', 'wildlife_protection', 'renewable_energy', 'zero_waste', 'sustainable_food', 'ocean_conservation', 'reforestation', 'fair_wage'];
            $allTags = array_intersect($allTags, $allowedTags);
            $impactTagsStr = implode(',', $allTags);

            $tourData = [
                'tour_name' => $tourName,
                'description' => $_POST['description'] ?? '',
                'guide_id' => $guideId,
                'location_id' => $locationId,
                'tour_type' => $_POST['tour_type'] ?? 'eco_adventure',
                'status' => 'pending',
                'tour_img_path' => $tour_img_path,
                'carbon_footprint' => $_POST['carbon_footprint'] ?? 0,
                'waste_management' => isset($_POST['waste_management']),
                'local_hiring' => isset($_POST['local_hiring']),
                'impact_tags' => $impactTagsStr,
                'routes' => $routesJson,
            ];
            $tourId = $this->toursModel->create($tourData);

            if (!$tourId) {
                $errors[] = 'Failed to create tour. Please try again.';
                View::load('guide/tour_form', [
                    'title' => 'Create Tour – EcoVoyage',
                    'locations' => $locations,
                    'errors' => $errors,
                    'tour' => $_POST,
                    'versions' => []
                ]);
                return;
            }

            // Use the injected Documents model
            if ($consentChecked) {
                $consentDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/consent/';
                if (!is_dir($consentDir))
                    mkdir($consentDir, 0755, true);
                $consentFilename = 'consent_' . $tourId . '_' . time() . '.' . $consentExt;
                move_uploaded_file($_FILES['consent_document']['tmp_name'], $consentDir . $consentFilename);
                $consentPath = '/uploads/consent/' . $consentFilename;

                $consentDocId = $this->documentsModel->create([
                    'entity_type' => 'tour',
                    'entity_id' => $tourId,
                    'doc_type' => 'indigenous_consent',
                    'file_path' => $consentPath,
                    'issued_date' => date('Y-m-d')
                ]);

                $this->toursModel->updateConsent($tourId, $consentDocId, 0);
            }

            $versionData = [
                'tour_id' => $tourId,
                'version_name' => $versionName,
                'price_per_person' => (float) $price,
                'max_capacity' => (int) $capacity,
                'itinerary_json' => $itineraryJson,
                'booking_type' => $_POST['booking_type'] ?? 'instant',
                'group_discounts' => $discountsJson,
            ];
            $this->tourVersionsModel->createVersion($versionData);

            header('Location: ' . BASE_URL . 'guide/tours');
            exit;
        }

        View::load('guide/tour_form', [
            'title' => 'Create Tour – EcoVoyage',
            'locations' => $locations,
            'errors' => [],
            'tour' => [],
            'versions' => []
        ]);
    }

    public function editTour($tourId)
    {
        $guideId = $_SESSION['user_id'];
        $tour = $this->toursModel->getById($tourId);
        if (!$tour || $tour['guide_id'] != $guideId) {
            header('Location: ' . BASE_URL . 'guide/tours');
            exit;
        }

        $locations = $this->locationsModel->getAll();
        $versions = $this->tourVersionsModel->getByTourId($tourId);

        // Consent document information
        $consentDoc = null;
        if (!empty($tour['consent_doc_id'])) {
            $consentDoc = $this->documentsModel->getById($tour['consent_doc_id']);
            $tour['consent_doc_path'] = $consentDoc['file_path'] ?? null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            $tourName = trim($_POST['tour_name'] ?? '');
            if (!Validator::required($tourName)) {
                $errors[] = 'Tour name is required.';
            }

            $tour_img_path = $tour['tour_img_path'];
            if (isset($_FILES['tour_image']) && $_FILES['tour_image']['error'] === UPLOAD_ERR_OK) {
                if (!Validator::fileType('tour_image', ['jpg', 'jpeg', 'png'])) {
                    $errors[] = 'Image must be JPG or PNG.';
                } elseif (!Validator::fileMaxSize('tour_image', 2048)) {
                    $errors[] = 'Image must be under 2 MB.';
                } else {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/tour_images/';
                    if (!is_dir($uploadDir))
                        mkdir($uploadDir, 0755, true);
                    $ext = strtolower(pathinfo($_FILES['tour_image']['name'], PATHINFO_EXTENSION));
                    $filename = 'tour_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    move_uploaded_file($_FILES['tour_image']['tmp_name'], $uploadDir . $filename);
                    $tour_img_path = '/uploads/tour_images/' . $filename;
                }
            }

            // Consent document validation using Validator
            $consentChecked = isset($_POST['indigenous_consent']);
            $newConsentUploaded = isset($_FILES['consent_document']) && $_FILES['consent_document']['error'] === UPLOAD_ERR_OK;

            if ($consentChecked) {
                if ($newConsentUploaded) {
                    if (!Validator::fileType('consent_document', ['pdf', 'jpg', 'jpeg', 'png'])) {
                        $errors[] = 'Consent document must be PDF, JPG, or PNG.';
                    } elseif (!Validator::fileMaxSize('consent_document', 2048)) {
                        $errors[] = 'Consent document must be under 2 MB.';
                    }
                } elseif (empty($tour['consent_doc_id'])) {
                    $errors[] = 'Consent document is required.';
                }
            }

            if (!empty($errors)) {
                View::load('guide/tour_form', [
                    'title' => 'Edit Tour – EcoVoyage',
                    'locations' => $locations,
                    'errors' => $errors,
                    'tour' => $tour,
                    'versions' => $versions,
                ]);
                return;
            }

            // ... tags, routes, impact (unchanged) ...
            $routesText = trim($_POST['routes_text'] ?? '');
            $routesArray = $routesText !== '' ? array_filter(array_map('trim', explode("\n", $routesText)), fn($s) => $s !== '') : [];
            $routesJson = !empty($routesArray) ? json_encode(array_values($routesArray)) : null;

            $impactTags = $_POST['impact_tags'] ?? [];
            $impactTags = is_array($impactTags) ? $impactTags : [];
            $autoTags = [];
            if (!empty($_POST['waste_management']))
                $autoTags[] = 'zero_waste';
            if (!empty($_POST['local_hiring']))
                $autoTags[] = 'local_community';
            if (isset($_POST['carbon_footprint']) && (float) $_POST['carbon_footprint'] <= 0)
                $autoTags[] = 'carbon_neutral';
            if (($_POST['tour_type'] ?? '') === 'wildlife_safari')
                $autoTags[] = 'wildlife_protection';

            $allTags = array_unique(array_merge($impactTags, $autoTags));
            $allowedTags = ['carbon_neutral', 'plastic_free', 'local_community', 'wildlife_protection', 'renewable_energy', 'zero_waste', 'sustainable_food', 'ocean_conservation', 'reforestation', 'fair_wage'];
            $allTags = array_intersect($allTags, $allowedTags);
            $impactTagsStr = implode(',', $allTags);

            $updateData = [
                'tour_name' => $tourName,
                'description' => $_POST['description'] ?? '',
                'location_id' => $_POST['location_id'],
                'tour_type' => $_POST['tour_type'] ?? 'eco_adventure',
                'tour_img_path' => $tour_img_path,
                'carbon_footprint' => $_POST['carbon_footprint'] ?? 0,
                'waste_management' => isset($_POST['waste_management']),
                'local_hiring' => isset($_POST['local_hiring']),
                'impact_tags' => $impactTagsStr,
                'routes' => $routesJson,
            ];
            $this->toursModel->update($tourId, $updateData);

            // Handle consent document update/removal using injected models
            if ($consentChecked) {
                if ($newConsentUploaded) {
                    $consentDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/consent/';
                    if (!is_dir($consentDir))
                        mkdir($consentDir, 0755, true);
                    $consentExt = strtolower(pathinfo($_FILES['consent_document']['name'], PATHINFO_EXTENSION));
                    $consentFilename = 'consent_' . $tourId . '_' . time() . '.' . $consentExt;
                    move_uploaded_file($_FILES['consent_document']['tmp_name'], $consentDir . $consentFilename);
                    $consentPath = '/uploads/consent/' . $consentFilename;

                    $consentDocId = $this->documentsModel->create([
                        'entity_type' => 'tour',
                        'entity_id' => $tourId,
                        'doc_type' => 'indigenous_consent',
                        'file_path' => $consentPath,
                        'issued_date' => date('Y-m-d')
                    ]);

                    $this->toursModel->updateConsent($tourId, $consentDocId, 0);
                }
            } else {
                if (!empty($tour['consent_doc_id'])) {
                    $oldDoc = $this->documentsModel->getById($tour['consent_doc_id']);
                    if ($oldDoc && !empty($oldDoc['file_path'])) {
                        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public' . $oldDoc['file_path'];
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                    $this->toursModel->updateConsent($tourId, null, 0);
                }
            }

            // ... versions handling (unchanged except using $this->tourVersionsModel) ...
            $existingVersions = $_POST['versions'] ?? [];
            $deleteVersionIds = $_POST['delete_versions'] ?? [];

            foreach ($existingVersions as $versionId => $versionFields) {
                if (in_array($versionId, $deleteVersionIds))
                    continue;
                if (empty($versionFields['version_name']) || empty($versionFields['price_per_person']) || empty($versionFields['max_capacity'])) {
                    $errors[] = "Version #$versionId: Name, price, and capacity are required.";
                    continue;
                }

                $versionItineraryText = trim($versionFields['itinerary_text'] ?? '');
                $versionItineraryArray = [];
                if ($versionItineraryText !== '') {
                    $lines = array_filter(array_map('trim', explode("\n", $versionItineraryText)), fn($s) => $s !== '');
                    foreach ($lines as $line) {
                        if (preg_match('/^Day\s*(\d+)\s*:\s*(.+)/i', $line, $m)) {
                            $versionItineraryArray['day' . $m[1]] = $m[2];
                        } else {
                            $versionItineraryArray[] = $line;
                        }
                    }
                }
                $versionItineraryJson = !empty($versionItineraryArray) ? json_encode($versionItineraryArray) : null;

                $minArr = $versionFields['discount_min'] ?? [];
                $percentArr = $versionFields['discount_percent'] ?? [];
                $discountsArray = [];
                foreach ($minArr as $i => $min) {
                    $min = (int) $min;
                    $percent = (int) ($percentArr[$i] ?? 0);
                    if ($min >= 2 && $percent > 0) {
                        $discountsArray[] = ['min_persons' => $min, 'discount_percent' => $percent];
                    }
                }
                $discountsJson = !empty($discountsArray) ? json_encode($discountsArray) : null;

                $this->tourVersionsModel->update($versionId, [
                    'version_name' => $versionFields['version_name'],
                    'price_per_person' => $versionFields['price_per_person'],
                    'max_capacity' => $versionFields['max_capacity'],
                    'itinerary_json' => $versionItineraryJson,
                    'booking_type' => $versionFields['booking_type'] ?? 'instant',
                    'group_discounts' => $discountsJson,
                ]);
            }

            foreach ($deleteVersionIds as $delId) {
                $this->tourVersionsModel->delete($delId);
            }

            // ... new versions (same logic, using Validator) ...
            $newVersionNames = $_POST['new_version_name'] ?? [];
            $newPrices = $_POST['new_version_price'] ?? [];
            $newCapacities = $_POST['new_version_capacity'] ?? [];
            $newItineraries = $_POST['new_version_itinerary_text'] ?? [];
            $newBookingTypes = $_POST['new_version_booking_type'] ?? [];
            $newDiscountMins = $_POST['new_version_discount_min'] ?? [];
            $newDiscountPcts = $_POST['new_version_discount_percent'] ?? [];

            foreach ($newVersionNames as $index => $name) {
                $name = trim($name);
                if ($name === '')
                    continue;

                $price = $newPrices[$index] ?? 0;
                $capacity = $newCapacities[$index] ?? 10;
                if (!Validator::positiveNumber($price) || !Validator::numeric($capacity) || (int) $capacity < 1) {
                    $errors[] = "New version '{$name}': Price and capacity must be valid.";
                    continue;
                }

                $itText = trim($newItineraries[$index] ?? '');
                $itArray = [];
                if ($itText !== '') {
                    $lines = array_filter(array_map('trim', explode("\n", $itText)), fn($s) => $s !== '');
                    foreach ($lines as $line) {
                        if (preg_match('/^Day\s*(\d+)\s*:\s*(.+)/i', $line, $m)) {
                            $itArray['day' . $m[1]] = $m[2];
                        } else {
                            $itArray[] = $line;
                        }
                    }
                }
                $itJson = !empty($itArray) ? json_encode($itArray) : null;

                $minArr = $newDiscountMins[$index] ?? [];
                $percentArr = $newDiscountPcts[$index] ?? [];
                $discountsArray = [];
                foreach ($minArr as $i => $min) {
                    $min = (int) $min;
                    $percent = (int) ($percentArr[$i] ?? 0);
                    if ($min >= 2 && $percent > 0) {
                        $discountsArray[] = ['min_persons' => $min, 'discount_percent' => $percent];
                    }
                }
                $discountsJson = !empty($discountsArray) ? json_encode($discountsArray) : null;

                $this->tourVersionsModel->createVersion([
                    'tour_id' => $tourId,
                    'version_name' => $name,
                    'price_per_person' => (float) $price,
                    'max_capacity' => (int) $capacity,
                    'itinerary_json' => $itJson,
                    'booking_type' => $newBookingTypes[$index] ?? 'instant',
                    'group_discounts' => $discountsJson,
                ]);
            }

            if (!empty($errors)) {
                View::load('guide/tour_form', [
                    'title' => 'Edit Tour – EcoVoyage',
                    'locations' => $locations,
                    'errors' => $errors,
                    'tour' => $tour,
                    'versions' => $versions,
                ]);
                return;
            }

            header('Location: ' . BASE_URL . 'guide/tours');
            exit;
        }

        // Prepare data for the form
        $routeList = json_decode($tour['routes'] ?? '[]', true);
        $tour['routes_text'] = is_array($routeList) ? implode("\n", $routeList) : '';

        foreach ($versions as &$ver) {
            $it = json_decode($ver['itinerary_json'] ?? '{}', true);
            $lines = [];
            if (is_array($it)) {
                foreach ($it as $key => $val) {
                    if (is_int($key)) {
                        $lines[] = $val;
                    } else {
                        $dayNum = str_replace('day', '', $key);
                        $lines[] = "Day $dayNum: $val";
                    }
                }
            }
            $ver['itinerary_text'] = implode("\n", $lines);
        }
        unset($ver);

        View::load('guide/tour_form', [
            'title' => 'Edit Tour – EcoVoyage',
            'locations' => $locations,
            'tour' => $tour,
            'versions' => $versions,
            'errors' => [],
        ]);
    }

    public function deleteTour($tourId)
    {
        $guideId = $_SESSION['user_id'];
        $tour = $this->toursModel->getById($tourId);
        if ($tour && $tour['guide_id'] == $guideId) {
            $this->toursModel->delete($tourId);
        }
        header('Location: ' . BASE_URL . 'guide/tours');
        exit;
    }

    public function languages()
    {
        $guideId = $_SESSION['user_id'];
        $languages = $this->guideLanguagesModel->getByGuide($guideId);

        foreach ($languages as $lang) {
            if ($lang['cert_status'] === 'approved' && $lang['expiry_date'] && strtotime($lang['expiry_date']) < time()) {
                $this->documentsModel->updateStatus($lang['doc_id'], 'expired');
                $this->guideLanguagesModel->updateStatus($lang['id'], 'expired');
            }
        }

        foreach ($languages as &$lang) {
            $lang['all_docs'] = $this->documentsModel->getByLanguageId($lang['id']);
        }

        View::load('guide/languages', [
            'title' => 'Language Certificates – EcoVoyage',
            'languages' => $languages
        ]);
    }

    public function addLanguage()
    {
        $guideId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'guide/languages');
            exit;
        }

        $errors = [];
        $language = trim($_POST['language'] ?? '');
        $proficiency = $_POST['proficiency'] ?? 'fluent';
        $issued = $_POST['issued_date'] ?? date('Y-m-d');
        $expiry = $_POST['expiry_date'] ?? null;

        if (!Validator::required($language)) {
            $errors[] = 'Language is required.';
        }
        if (!Validator::inArray($proficiency, ['basic', 'intermediate', 'fluent', 'native'])) {
            $errors[] = 'Invalid proficiency level.';
        }
        if (!Validator::fileRequired('certificate')) {
            $errors[] = 'Certificate file is required.';
        } else {
            if (!Validator::fileType('certificate', ['pdf', 'jpg', 'jpeg', 'png'])) {
                $errors[] = 'File must be PDF, JPG, or PNG.';
            } elseif (!Validator::fileMaxSize('certificate', 2048)) {
                $errors[] = 'File must be under 2 MB.';
            }
        }

        if (!empty($errors)) {
            $languages = $this->guideLanguagesModel->getByGuide($guideId);
            View::load('guide/languages', [
                'title' => 'Language Certificates – EcoVoyage',
                'languages' => $languages,
                'errors' => $errors
            ]);
            return;
        }

        $languageId = $this->guideLanguagesModel->exists($guideId, $language);

        if (!$languageId) {
            $languageId = $this->guideLanguagesModel->create([
                'guide_id' => $guideId,
                'language' => $language,
                'proficiency_level' => $proficiency
            ]);
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/certificates/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['certificate']['name'], PATHINFO_EXTENSION));
        $filename = 'cert_' . $guideId . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['certificate']['tmp_name'], $uploadDir . $filename);
        $filePath = '/uploads/certificates/' . $filename;

        $this->documentsModel->create([
            'entity_type' => 'guide_language',
            'entity_id' => $languageId,
            'doc_type' => 'language_cert',
            'file_path' => $filePath,
            'issued_date' => $issued,
            'expiry_date' => $expiry
        ]);

        header('Location: ' . BASE_URL . 'guide/languages');
        exit;
    }

    public function wallet()
    {
        $guideId = $_SESSION['user_id'];
        $guide = $this->guidesModel->getById($guideId);

        $available = $guide['available_balance'] ?? 0;
        $pending = $guide['pending_balance'] ?? 0;
        $withdrawn = $guide['withdrawn_balance'] ?? 0;

        $requests = $this->withdrawalRequestsModel->getByGuide($guideId);

        View::load('guide/wallet', [
            'title' => 'My Wallet – EcoVoyage',
            'available' => $available,
            'pending' => $pending,
            'withdrawn' => $withdrawn,
            'requests' => $requests,
            'errors' => []
        ]);
    }

    public function processWithdrawal()
    {
        $guideId = $_SESSION['user_id'];
        $guide = $this->guidesModel->getById($guideId);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'guide/wallet');
            exit;
        }

        $amount = (float) ($_POST['amount'] ?? 0);
        $cardNumber = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
        $expiry = $_POST['expiry'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        $cardholderName = trim($_POST['cardholder_name'] ?? '');

        $errors = [];

        if (!Validator::positiveNumber($amount)) {
            $errors[] = 'Please enter a valid positive amount.';
        }
        $available = $guide['available_balance'] ?? 0;
        if ($amount > $available) {
            $errors[] = 'Insufficient available balance.';
        }

        if (!Validator::regex($cardNumber, '/^\d{16}$/')) {
            $errors[] = 'Card number must be exactly 16 digits.';
        }

        if (!Validator::regex($expiry, '/^(0[1-9]|1[0-2])\/(\d{2})$/')) {
            $errors[] = 'Expiry must be in MM/YY format.';
        } else {
            $matches = [];
            preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry, $matches);
            $month = $matches[1];
            $year = '20' . $matches[2];
            $expiryDate = \DateTime::createFromFormat('Y-m-d', "$year-$month-01");
            $expiryDate->modify('last day of this month');
            $now = new \DateTime();
            if ($expiryDate < $now) {
                $errors[] = 'Card has expired.';
            }
        }

        if (!Validator::regex($cvv, '/^\d{3,4}$/')) {
            $errors[] = 'CVV must be 3 or 4 digits.';
        }

        if (!Validator::required($cardholderName)) {
            $errors[] = 'Cardholder name is required.';
        }

        $requests = $this->withdrawalRequestsModel->getByGuide($guideId);
        if (!empty($errors)) {
            View::load('guide/wallet', [
                'title' => 'My Wallet – EcoVoyage',
                'available' => $available,
                'pending' => $guide['pending_balance'] ?? 0,
                'withdrawn' => $guide['withdrawn_balance'] ?? 0,
                'requests' => $requests,
                'errors' => $errors
            ]);
            return;
        }

        $this->guidesModel->updateBalances($guideId, -$amount, 0, $amount);

        $this->withdrawalRequestsModel->create([
            'guide_id' => $guideId,
            'amount' => $amount,
            'card_number' => $cardNumber,
            'card_expiry' => $expiry,
            'card_cvv' => $cvv,
            'cardholder_name' => $cardholderName,
        ]);

        $guide = $this->guidesModel->getById($guideId);
        View::load('guide/wallet', [
            'title' => 'My Wallet – EcoVoyage',
            'available' => $guide['available_balance'] ?? 0,
            'pending' => $guide['pending_balance'] ?? 0,
            'withdrawn' => $guide['withdrawn_balance'] ?? 0,
            'requests' => $requests,
            'errors' => [],
            'success' => 'Withdrawal request submitted successfully.'
        ]);
    }

    public function settings()
    {
        $guideId = $_SESSION['user_id'];
        $user = $this->usersModel->getById($guideId);
        $guide = $this->guidesModel->getById($guideId);

        $data = [
            'title' => 'Guide Settings – EcoVoyage',
            'user' => $user,
            'guide' => $guide,
            'errors' => [],
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $country = trim($_POST['country_of_residence'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $experience = $_POST['years_of_experience'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $errors = [];

            if (!Validator::required($name)) {
                $errors[] = 'Full name is required.';
            } elseif (!Validator::alphaSpaces($name)) {
                $errors[] = 'Name may only contain letters and spaces.';
            }

            if (!Validator::required($phone)) {
                $errors[] = 'Phone number is required.';
            } elseif (!Validator::phone($phone)) {
                $errors[] = 'Invalid phone format.';
            }

            if (!Validator::required($country)) {
                $errors[] = 'Country of residence is required.';
            }
            if (!Validator::required($bio) || !Validator::minLength($bio, 20)) {
                $errors[] = 'Bio must be at least 20 characters.';
            }
            if (!Validator::required($experience) || !Validator::numeric($experience) || (int) $experience < 0) {
                $errors[] = 'Years of experience must be a positive number or zero.';
            }

            if (!empty($password) || !empty($confirm)) {
                if (!Validator::minLength($password, 6)) {
                    $errors[] = 'Password must be at least 6 characters.';
                }
                if (!Validator::match($password, $confirm)) {
                    $errors[] = 'Passwords do not match.';
                }
            }

            if (!empty($errors)) {
                $data['errors'] = $errors;
                View::load('guide/settings', $data);
                return;
            }

            $userUpdate = ['name' => $name, 'phone' => $phone];
            if (!empty($password)) {
                $userUpdate['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->usersModel->update($guideId, $userUpdate);

            $this->guidesModel->updateProfile($guideId, [
                'country_of_residence' => $country,
                'bio' => $bio,
                'years_of_experience' => (int) $experience
            ]);

            $this->guidesModel->updateStatus($guideId, 'pending');

            $data['user'] = $this->usersModel->getById($guideId);
            $data['guide'] = $this->guidesModel->getById($guideId);
            $data['success'] = 'Profile updated successfully.';
        }

        View::load('guide/settings', $data);
    }

    public function certifications()
    {
        $guideId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            $certType = trim($_POST['cert_type'] ?? '');
            $issuedDate = $_POST['issued_date'] ?? date('Y-m-d');
            $expiryDate = $_POST['expiry_date'] ?? null;
            $file = $_FILES['cert_file'] ?? null;

            if (!Validator::required($certType)) {
                $errors[] = 'Certification type is required.';
            }
            if (!Validator::fileRequired('cert_file')) {
                $errors[] = 'Certificate file is required.';
            } else {
                if (!Validator::fileType('cert_file', ['pdf', 'jpg', 'jpeg', 'png'])) {
                    $errors[] = 'File must be PDF, JPG, or PNG.';
                } elseif (!Validator::fileMaxSize('cert_file', 2048)) {
                    $errors[] = 'File must be under 2 MB.';
                }
            }

            if (!empty($errors)) {
                $certs = $this->documentsModel->getCertsByGuide($guideId);
                View::load('guide/certifications', [
                    'title' => 'Eco‑Certifications – EcoVoyage',
                    'certs' => $certs,
                    'errors' => $errors
                ]);
                return;
            }

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/certifications/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['cert_file']['name'], PATHINFO_EXTENSION));
            $filename = 'cert_' . $guideId . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['cert_file']['tmp_name'], $uploadDir . $filename);
            $filePath = '/uploads/certifications/' . $filename;

            $this->documentsModel->create([
                'entity_type' => 'guide_cert',
                'entity_id' => $guideId,
                'doc_type' => $certType,
                'file_path' => $filePath,
                'issued_date' => $issuedDate,
                'expiry_date' => $expiryDate
            ]);

            header('Location: ' . BASE_URL . 'guide/certifications');
            exit;
        }

        $certs = $this->documentsModel->getCertsByGuide($guideId);
        foreach ($certs as &$cert) {
            if ($cert['status'] === 'approved' && $cert['expiry_date'] && strtotime($cert['expiry_date']) < time()) {
                $this->documentsModel->updateStatus($cert['doc_id'], 'expired');
                $cert['status'] = 'expired';
            }
        }
        unset($cert);

        View::load('guide/certifications', [
            'title' => 'Eco‑Certifications – EcoVoyage',
            'certs' => $certs,
            'errors' => []
        ]);
    }

    public function fieldReports()
    {
        $guideId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            $contentText = trim($_POST['content_text'] ?? '');
            $tourId = $_POST['tour_id'] ?? null;

            if (!Validator::required($contentText)) {
                $errors[] = 'Report content is required.';
            }

            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                if (!Validator::fileType('photo', ['jpg', 'jpeg', 'png'])) {
                    $errors[] = 'Photo must be JPG or PNG.';
                } elseif (!Validator::fileMaxSize('photo', 2048)) {
                    $errors[] = 'Photo must be under 2 MB.';
                } else {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/field_reports/';
                    if (!is_dir($uploadDir))
                        mkdir($uploadDir, 0755, true);
                    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $filename = 'report_' . $guideId . '_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename);
                    $photoPath = '/uploads/field_reports/' . $filename;
                }
            }

            if (!empty($errors)) {
                $reports = $this->fieldReportsModel->getByGuide($guideId);
                View::load('guide/field_reports', [
                    'title' => 'Field Reports – EcoVoyage',
                    'reports' => $reports,
                    'errors' => $errors
                ]);
                return;
            }

            $this->fieldReportsModel->create([
                'guide_id' => $guideId,
                'tour_id' => $tourId,
                'content_text' => $contentText,
                'photo_path' => $photoPath
            ]);

            header('Location: ' . BASE_URL . 'guide/fieldReports');
            exit;
        }

        $reports = $this->fieldReportsModel->getByGuide($guideId);
        View::load('guide/field_reports', [
            'title' => 'Field Reports – EcoVoyage',
            'reports' => $reports,
            'errors' => []
        ]);
    }

    public function deleteFieldReport($reportId)
    {
        $guideId = $_SESSION['user_id'];
        $report = $this->fieldReportsModel->getById($reportId);
        if ($report && $report['guide_id'] == $guideId) {
            $this->fieldReportsModel->delete($reportId);
        }
        header('Location: ' . BASE_URL . 'guide/fieldReports');
        exit;
    }
}