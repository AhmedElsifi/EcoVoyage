<?php

class GuideController
{
    private $toursModel;
    private $usersModel;
    private $guidesModel;
    private $bookingsModel;
    private $locationsModel;

    public function __construct()
    {
        $this->toursModel = new Tours();
        $this->bookingsModel = new Bookings();
        $this->guidesModel = new Guides();
        $this->usersModel = new Users();
        $this->locationsModel = new Locations();

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

        $version = (new TourVersions())->getTourVersionById($booking['tour_version_id']);
        $tour = $this->toursModel->getById($version['tour_id']);
        $addonIds = json_decode($booking['selected_addons'] ?? '[]', true);
        $addons = !empty($addonIds) ? (new Addons())->getByIds($addonIds) : [];
        $traveler = (new Travelers())->getTravelerById($booking['traveler_id']);
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
            $this->bookingsModel->updateStatus($bookingId, $newStatus);

            if ($newStatus === 'completed') {
                (new Vault())->releaseFunds($bookingId);
            }
        }

        header('Location: ' . BASE_URL . 'guide/booking/' . $bookingId);
        exit;
    }

    public function tours()
    {
        $guideId = $_SESSION['user_id'];
        $tours = $this->toursModel->getByGuide($guideId);
        View::load('guide/tours', [
            'title' => 'My Tours – EcoVoyage',
            'tours' => $tours
        ]);
    }

    public function createTour()
    {
        $guideId = $_SESSION['user_id'];
        $locations = (new Locations())->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            $tourName = trim($_POST['tour_name'] ?? '');
            if ($tourName === '') {
                $errors[] = 'Tour name is required.';
            }

            $locationId = $_POST['location_id'] ?? null;
            if (empty($locationId)) {
                $errors[] = 'Location is required.';
            }

            $price = $_POST['price_per_person'] ?? null;
            if ($price === '' || !is_numeric($price) || (float) $price <= 0) {
                $errors[] = 'Price per person must be a positive number.';
            }

            $capacity = $_POST['max_capacity'] ?? 10;
            if (!is_numeric($capacity) || (int) $capacity < 1) {
                $errors[] = 'Maximum capacity must be at least 1.';
            }

            $versionName = trim($_POST['version_name'] ?? 'Standard');
            if ($versionName === '') {
                $errors[] = 'Version name is required.';
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

            $routesText = trim($_POST['routes_text'] ?? '');
            $routesArray = $routesText !== '' ? array_filter(array_map('trim', explode("\n", $routesText)), fn($s) => $s !== '') : [];
            $routesJson = !empty($routesArray) ? json_encode(array_values($routesArray)) : null;

            $itineraryText = trim($_POST['itinerary_text'] ?? '');
            $itineraryArray = [];
            if ($itineraryText !== '') {
                $lines = array_filter(array_map('trim', explode("\n", $itineraryText)), fn($s) => $s !== '');
                foreach ($lines as $line) {
                    if (preg_match('/^Day\s*(\d+)\s*:\s*(.+)/i', $line, $m)) {
                        $dayKey = 'day' . $m[1];
                        $itineraryArray[$dayKey] = $m[2];
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

            $impactTags = $_POST['impact_tags'] ?? [];   // will be an array
            $impactTagsStr = is_array($impactTags) ? implode(',', $impactTags) : '';

            $tourData = [
                'tour_name' => $tourName,
                'description' => $_POST['description'] ?? '',
                'guide_id' => $guideId,
                'location_id' => $locationId,
                'tour_type' => $_POST['tour_type'] ?? 'eco_adventure',
                'status' => 'pending',
                'tour_img_path' => $_POST['tour_img_path'] ?? null,
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

            $versionData = [
                'tour_id' => $tourId,
                'version_name' => $versionName,
                'price_per_person' => (float) $price,
                'max_capacity' => (int) $capacity,
                'itinerary_json' => $itineraryJson,
                'booking_type' => $_POST['booking_type'] ?? 'instant',
                'group_discounts' => $discountsJson,
            ];
            (new TourVersions())->createVersion($versionData);

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
        $versions = (new TourVersions())->getByTourId($tourId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            $tourName = trim($_POST['tour_name'] ?? '');
            if ($tourName === '') {
                $errors[] = 'Tour name is required.';
            }

            $tour_img_path = $tour['tour_img_path'];
            if (isset($_FILES['tour_image']) && $_FILES['tour_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png'];
                $ext = strtolower(pathinfo($_FILES['tour_image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $errors[] = 'Image must be JPG or PNG.';
                } elseif ($_FILES['tour_image']['size'] > 2 * 1024 * 1024) {
                    $errors[] = 'Image must be under 2 MB.';
                } else {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/tour_images/';
                    if (!is_dir($uploadDir))
                        mkdir($uploadDir, 0755, true);
                    $filename = 'tour_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    move_uploaded_file($_FILES['tour_image']['tmp_name'], $uploadDir . $filename);
                    $tour_img_path = '/uploads/tour_images/' . $filename;
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

            $routesText = trim($_POST['routes_text'] ?? '');
            $routesArray = $routesText !== '' ? array_filter(array_map('trim', explode("\n", $routesText)), fn($s) => $s !== '') : [];
            $routesJson = !empty($routesArray) ? json_encode(array_values($routesArray)) : null;

            $impactTags = $_POST['impact_tags'] ?? [];
            $impactTagsStr = is_array($impactTags) ? implode(',', $impactTags) : '';

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

            $existingVersions = $_POST['versions'] ?? [];
            $deleteVersionIds = $_POST['delete_versions'] ?? [];

            foreach ($existingVersions as $versionId => $versionFields) {
                if (in_array($versionId, $deleteVersionIds)) {
                    continue;
                }
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

                (new TourVersions())->update($versionId, [
                    'version_name' => $versionFields['version_name'],
                    'price_per_person' => $versionFields['price_per_person'],
                    'max_capacity' => $versionFields['max_capacity'],
                    'itinerary_json' => $versionItineraryJson,
                    'booking_type' => $versionFields['booking_type'] ?? 'instant',
                    'group_discounts' => $discountsJson,
                ]);
            }

            foreach ($deleteVersionIds as $delId) {
                (new TourVersions())->delete($delId);
            }

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
                if (!is_numeric($price) || (float) $price <= 0 || !is_numeric($capacity) || (int) $capacity < 1) {
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

                (new TourVersions())->createVersion([
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
}