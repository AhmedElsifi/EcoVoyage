<?php

class TravelerController
{
    private $toursModel;
    private $tourVersionsModel;
    private $usersModel;
    private $locationsModel;
    private $guidesModel;
    private $bookingsModel;
    private $travelersModel;
    private $offsetProjectsModel;
    private $addonsModel;
    private $platformsettingsModel;

    public function __construct()
    {
        $this->toursModel = new Tours();
        $this->usersModel = new Users();
        $this->bookingsModel = new Bookings();
        $this->locationsModel = new Locations();
        $this->guidesModel = new Guides();
        $this->travelersModel = new Travelers();
        $this->tourVersionsModel = new TourVersions();
        $this->offsetProjectsModel = new OffsetProjects();
        $this->addonsModel = new Addons();
        $this->platformsettingsModel = new PlatformSettings();

        if (!$this->usersModel->hasRole('traveler')) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function dashboard()
    {
        $travelerId = $_SESSION['user_id'];

        $upcoming = $this->bookingsModel->getUpcomingBookingsByTraveler($travelerId, 5);
        $totalBookings = $this->bookingsModel->countByTraveler($travelerId);
        $travelerData = $this->travelersModel->getTravelerById($travelerId);

        $data = [
            'title' => 'My Dashboard – EcoVoyage',
            'upcoming' => $upcoming,
            'totalBookings' => $totalBookings,
            'travelerData' => $travelerData,
        ];

        View::load("traveler/dashboard", $data);
    }

    public function tours()
    {
        $tours = $this->toursModel->getActiveTours();
        $data = [
            'title' => 'Browse Eco-Tours - EcoVoyage',
            'tours' => $tours
        ];
        View::load('traveler/tours', $data);
    }

    public function tour($id)
    {
        $tour = $this->toursModel->getById($id);
        $versions = $this->toursModel->getTourVersions($id);
        $addons = $this->tourVersionsModel->getTourVersionAddons($id);
        $guide = $this->guidesModel->getById($tour['guide_id']);
        $location = $this->locationsModel->getById($tour['location_id']);
        $projects = $this->offsetProjectsModel->getOffsetProjects();
        $ecoLeaves = $this->toursModel->getEcoLeafRating($tour);

        $data = [
            'title' => $tour['tour_name'] . ' - EcoVoyage',
            'tour' => $tour,
            'versions' => $versions,
            'addons' => $addons,
            'guide' => $guide,
            'location' => $location,
            'projects' => $projects,
            'ecoLeaves' => $ecoLeaves
        ];

        View::load('traveler/tour_details', $data);
    }

    public function book()
    {
        $tourId = $_GET['tour_id'] ?? null;
        $versionId = $_GET['version_id'] ?? null;
        $addonIds = $_GET['addons'] ?? [];
        $offsetProjId = $_GET['offset_project'] ?? null;

        if (!$tourId || !$versionId) {
            header('Location: ' . BASE_URL . 'traveler/tours');
            exit;
        }

        $tour = $this->toursModel->getById($tourId);
        $version = $this->tourVersionsModel->getTourVersionById($versionId);
        $addons = !empty($addonIds) ? $this->addonsModel->getByIds($addonIds) : [];
        $offsetProject = $offsetProjId ? $this->offsetProjectsModel->getById($offsetProjId) : null;
        $settings = $this->platformsettingsModel->getSettings();

        $basePricePerPerson = (float) $version['price_per_person'];
        $taxPercent = (float) ($settings['local_tax_percent'] ?? 5);
        $platformFeePct = (float) ($settings['platform_fee_percent'] ?? 10);
        $currency = $settings['currency'] ?? 'USD';

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $numTravelers = max(1, (int) ($_GET['num_travelers'] ?? 1));
            $startDateTime = $_GET['start_time'] ?? null;

            $discountTiers = json_decode($version['group_discounts'] ?? '[]', true);
            $discountPercent = 0;
            foreach ($discountTiers as $tier) {
                if ($numTravelers >= $tier['min_persons']) {
                    $discountPercent = max($discountPercent, $tier['discount_percent']);
                }
            }

            $baseTotal = $basePricePerPerson * $numTravelers;
            if ($discountPercent > 0) {
                $baseTotal *= (1 - $discountPercent / 100);
            }

            $addonTotal = 0;
            foreach ($addons as $addon) {
                $addonTotal += $addon['price'] * $numTravelers;
            }

            $offsetCost = 0;
            if ($offsetProject) {
                $costPerPerson = $tour['carbon_footprint'] * $offsetProject['cost_per_kg'];
                $offsetCost = $costPerPerson * $numTravelers;
            }

            $subtotal = $baseTotal + $addonTotal + $offsetCost;
            $taxAmount = $subtotal * ($taxPercent / 100);
            $totalTravelerPays = $subtotal + $taxAmount;
            $platformFeeAmount = $subtotal * ($platformFeePct / 100);
            $guideEarnings = $subtotal - $platformFeeAmount;

            View::load('traveler/book', [
                'tour' => $tour,
                'version' => $version,
                'addons' => $addons,
                'offsetProject' => $offsetProject,
                'basePricePerPerson' => $basePricePerPerson,
                'numTravelers' => $numTravelers,
                'startDateTime' => $startDateTime,
                'discountPercent' => $discountPercent,
                'discountAmount' => $basePricePerPerson * $numTravelers * ($discountPercent / 100),
                'baseTotal' => $baseTotal,
                'addonTotal' => $addonTotal,
                'offsetCost' => $offsetCost,
                'subtotal' => $subtotal,
                'taxPercent' => $taxPercent,
                'taxAmount' => $taxAmount,
                'platformFeePct' => $platformFeePct,
                'platformFeeAmount' => $platformFeeAmount,
                'totalTravelerPays' => $totalTravelerPays,
                'guideEarnings' => $guideEarnings,
                'currency' => $currency,
                'addonIds' => $addonIds,
                'offsetProjId' => $offsetProjId,
                'tourId' => $tourId,
                'versionId' => $versionId,
                'errors' => [],
            ]);
            return;
        }

        $travelerId = $_SESSION['user_id'];
        $startDateTime = $_POST['start_time'] ?? null;
        $numTravelers = max(1, (int) ($_POST['num_travelers'] ?? 1));

        if ($numTravelers > $version['max_capacity']) {
            $errors = ['The maximum group size for this version is ' . $version['max_capacity'] . ' persons.'];
            View::load('traveler/book', [
                'tour' => $tour,
                'version' => $version,
                'addons' => $addons,
                'offsetProject' => $offsetProject,
                'basePricePerPerson' => $basePricePerPerson,
                'numTravelers' => $numTravelers,
                'startDateTime' => $startDateTime,
                'discountPercent' => $discountPercent ?? 0,
                'discountAmount' => ($basePricePerPerson * $numTravelers * (($discountPercent ?? 0) / 100)),
                'baseTotal' => $basePricePerPerson * $numTravelers * (1 - ($discountPercent ?? 0) / 100),
                'addonTotal' => array_sum(array_column($addons, 'price')) * $numTravelers,
                'offsetCost' => $offsetProject ? ($tour['carbon_footprint'] * $offsetProject['cost_per_kg'] * $numTravelers) : 0,
                'subtotal' => 0,
                'taxPercent' => $taxPercent,
                'taxAmount' => 0,
                'platformFeePct' => $platformFeePct,
                'platformFeeAmount' => 0,
                'totalTravelerPays' => 0,
                'guideEarnings' => 0,
                'currency' => $currency,
                'errors' => $errors,
                'addonIds' => $addonIds,
                'offsetProjId' => $offsetProjId,
                'tourId' => $tourId,
                'versionId' => $versionId,
            ]);
            return;
        }

        if (!$startDateTime) {
            $errors = ['Please select a tour date and time.'];
            View::load('traveler/book', [
                'tour' => $tour,
                'version' => $version,
                'addons' => $addons,
                'offsetProject' => $offsetProject,
                'basePricePerPerson' => $basePricePerPerson,
                'addonTotal' => array_sum(array_column($addons, 'price')),
                'offsetCost' => $offsetProject ? ($tour['carbon_footprint'] * $offsetProject['cost_per_kg']) : 0,
                'subtotal' => 0,
                'taxPercent' => $taxPercent,
                'taxAmount' => 0,
                'platformFeePct' => $platformFeePct,
                'platformFeeAmount' => 0,
                'totalTravelerPays' => 0,
                'guideEarnings' => 0,
                'currency' => $currency,
                'errors' => $errors
            ]);
            return;
        }

        $dateOnly = date('Y-m-d', strtotime($startDateTime));
        $guideId = $tour['guide_id'];
        if (!$this->bookingsModel->isGuideAvailable($guideId, $dateOnly)) {
            header('Location: ' . BASE_URL . 'traveler/error?message=' . urlencode('The guide is not available on this date. Please choose a different date.'));
            exit;
        }

        $discountTiers = json_decode($version['group_discounts'] ?? '[]', true);
        $discountPercent = 0;
        foreach ($discountTiers as $tier) {
            if ($numTravelers >= $tier['min_persons']) {
                $discountPercent = max($discountPercent, $tier['discount_percent']);
            }
        }

        $baseTotal = $basePricePerPerson * $numTravelers;
        if ($discountPercent > 0) {
            $baseTotal *= (1 - $discountPercent / 100);
        }

        $addonTotal = 0;
        foreach ($addons as $addon) {
            $addonTotal += $addon['price'] * $numTravelers;
        }

        $offsetCost = 0;
        if ($offsetProject) {
            $costPerPerson = $tour['carbon_footprint'] * $offsetProject['cost_per_kg'];
            $offsetCost = $costPerPerson * $numTravelers;
        }

        $subtotal = $baseTotal + $addonTotal + $offsetCost;
        $taxAmount = $subtotal * ($taxPercent / 100);
        $totalTravelerPays = $subtotal + $taxAmount;
        $platformFeeAmount = $subtotal * ($platformFeePct / 100);
        $guideEarnings = $subtotal - $platformFeeAmount;

        $bookingType = $version['booking_type'] ?? 'instant';

        if ($bookingType === 'request') {
            $bookingId = (new Bookings())->create([
                'traveler_id' => $travelerId,
                'guide_id' => $guideId,
                'tour_version_id' => $versionId,
                'carbon_offset' => $offsetCost,
                'start_time' => $startDateTime,
                'status' => 'pending',
                'selected_addons' => json_encode(array_column($addons, 'addon_id')),
                'total_price' => $totalTravelerPays,
                'num_travelers' => $numTravelers,
            ]);

            if ($bookingId) {
                header('Location: ' . BASE_URL . 'traveler/requestSubmitted?booking_id=' . $bookingId);
                exit;
            } else {
                $errors = ['Could not create booking request. Please try again.'];
                View::load('traveler/book', [
                    'tour' => $tour,
                    'version' => $version,
                    'addons' => $addons,
                    'offsetProject' => $offsetProject,
                    'basePricePerPerson' => $basePricePerPerson,
                    'addonTotal' => $addonTotal,
                    'offsetCost' => $offsetCost,
                    'subtotal' => $subtotal,
                    'taxPercent' => $taxPercent,
                    'taxAmount' => $taxAmount,
                    'platformFeePct' => $platformFeePct,
                    'platformFeeAmount' => $platformFeeAmount,
                    'totalTravelerPays' => $totalTravelerPays,
                    'guideEarnings' => $guideEarnings,
                    'currency' => $currency,
                    'errors' => $errors
                ]);
                return;
            }
        } else {
            View::load('traveler/payment', [
                'tour' => $tour,
                'version' => $version,
                'addons' => $addons,
                'offsetProject' => $offsetProject,
                'basePricePerPerson' => $basePricePerPerson,
                'addonTotal' => $addonTotal,
                'offsetCost' => $offsetCost,
                'subtotal' => $subtotal,
                'taxPercent' => $taxPercent,
                'taxAmount' => $taxAmount,
                'platformFeePct' => $platformFeePct,
                'platformFeeAmount' => $platformFeeAmount,
                'totalTravelerPays' => $totalTravelerPays,
                'guideEarnings' => $guideEarnings,
                'currency' => $currency,
                'startDate' => $startDateTime,
                'tourId' => $tourId,
                'versionId' => $versionId,
                'addonIds' => $addonIds,
                'offsetProjId' => $offsetProjId,
                'numTravelers' => $numTravelers,
            ]);
            return;
        }
    }

    public function requestSubmitted()
    {
        $bookingId = $_GET['booking_id'] ?? null;
        if (!$bookingId) {
            header('Location: ' . BASE_URL . 'traveler/dashboard');
            exit;
        }
        $booking = $this->bookingsModel->getById($bookingId);
        if (!$booking || $booking['traveler_id'] != $_SESSION['user_id']) {
            header('Location: ' . BASE_URL . 'traveler/dashboard');
            exit;
        }

        $version = $this->tourVersionsModel->getTourVersionById($booking['tour_version_id']);
        $tour = $this->toursModel->getById($version['tour_id']);

        View::load('traveler/request_submitted', [
            'title' => 'Request Submitted - EcoVoyage',
            'booking' => $booking,
            'tour' => $tour
        ]);
    }

    public function processPayment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'traveler/tours');
            exit;
        }

        $tourId = $_POST['tour_id'] ?? null;
        $versionId = $_POST['version_id'] ?? null;
        $addonIds = $_POST['addons'] ?? [];
        $offsetProjId = $_POST['offset_project'] ?? null;
        $startDate = $_POST['start_date'] ?? null;
        $totalPrice = $_POST['total_price'] ?? 0;

        $cardNumber = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
        $expiry = $_POST['expiry'] ?? '';
        $cvv = $_POST['cvv'] ?? '';

        $errors = [];

        if (!preg_match('/^\d{16}$/', $cardNumber)) {
            $errors[] = 'Card number must be exactly 16 digits.';
        }

        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry, $matches)) {
            $errors[] = 'Expiry must be in MM/YY format.';
        } else {
            $month = $matches[1];
            $year = '20' . $matches[2];
            $expiryDate = \DateTime::createFromFormat('Y-m-d', "$year-$month-01");
            $expiryDate->modify('last day of this month');
            $now = new \DateTime();
            if ($expiryDate < $now) {
                $errors[] = 'Card has expired.';
            }
        }

        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            $errors[] = 'CVV must be 3 or 4 digits.';
        }

        if (!empty($errors)) {
            $tour = $this->toursModel->getById($tourId);
            $version = $this->tourVersionsModel->getTourVersionById($versionId);
            $addons = !empty($addonIds) ? $this->addonsModel->getByIds($addonIds) : [];
            $offsetProject = $offsetProjId ? $this->offsetProjectsModel->getById($offsetProjId) : null;
            $settings = $this->platformsettingsModel->getSettings();

            $basePrice = (float) $version['price_per_person'];
            $addonTotal = array_sum(array_column($addons, 'price'));
            $offsetCost = $offsetProject ? ($tour['carbon_footprint'] * $offsetProject['cost_per_kg']) : 0;
            $subtotal = $basePrice + $addonTotal + $offsetCost;
            $taxPercent = (float) ($settings['local_tax_percent'] ?? 5);
            $taxAmount = $subtotal * ($taxPercent / 100);
            $totalTravelerPays = $subtotal + $taxAmount;

            View::load('traveler/payment', [
                'tour' => $tour,
                'version' => $version,
                'addons' => $addons,
                'offsetProject' => $offsetProject,
                'basePrice' => $basePrice,
                'addonTotal' => $addonTotal,
                'offsetCost' => $offsetCost,
                'subtotal' => $subtotal,
                'taxPercent' => $taxPercent,
                'taxAmount' => $taxAmount,
                'platformFeePct' => $settings['platform_fee_percent'] ?? 10,
                'platformFeeAmount' => $subtotal * (($settings['platform_fee_percent'] ?? 10) / 100),
                'totalTravelerPays' => $totalTravelerPays,
                'guideEarnings' => $subtotal - ($subtotal * (($settings['platform_fee_percent'] ?? 10) / 100)),
                'currency' => $settings['currency'] ?? 'USD',
                'startDate' => $startDate,
                'tourId' => $tourId,
                'versionId' => $versionId,
                'addonIds' => $addonIds,
                'offsetProjId' => $offsetProjId,
                'errors' => $errors
            ]);
            return;
        }

        $travelerId = $_SESSION['user_id'];
        $guideId = $tour['guide_id'] ?? $this->toursModel->getById($tourId)['guide_id'];

        $tour = $this->toursModel->getById($tourId);
        $version = $this->tourVersionsModel->getTourVersionById($versionId);
        $addons = !empty($addonIds) ? $this->addonsModel->getByIds($addonIds) : [];
        $offsetProject = $offsetProjId ? $this->offsetProjectsModel->getById($offsetProjId) : null;
        $offsetCost = $offsetProject ? ($tour['carbon_footprint'] * $offsetProject['cost_per_kg']) : 0;

        $bookingId = $this->bookingsModel->create([
            'traveler_id' => $travelerId,
            'guide_id' => $tour['guide_id'],
            'tour_version_id' => $versionId,
            'carbon_offset' => $offsetCost,
            'start_time' => $startDate,
            'status' => 'confirmed',
            'selected_addons' => json_encode(array_column($addons, 'addon_id')),
            'total_price' => $totalPrice,
        ]);

        if ($bookingId) {
            header('Location: ' . BASE_URL . 'traveler/bookingConfirmation?booking_id=' . $bookingId);
            exit;
        } else {
            $errors = ['Payment processing failed. Please try again.'];
        }
    }

    public function bookingConfirmation()
    {
        $bookingId = $_GET['booking_id'] ?? null;
        if (!$bookingId) {
            header('Location: ' . BASE_URL . 'traveler/dashboard');
            exit;
        }
        $booking = $this->bookingsModel->getById($bookingId);
        if (!$booking || $booking['traveler_id'] != $_SESSION['user_id']) {
            header('Location: ' . BASE_URL . 'traveler/dashboard');
            exit;
        }

        $version = $this->tourVersionsModel->getTourVersionById($booking['tour_version_id']);
        $tour = $this->toursModel->getById($version['tour_id']);

        View::load('traveler/booking_confirmation', [
            'title' => 'Booking Confirmed – EcoVoyage',
            'booking' => $booking,
            'tour' => $tour
        ]);
    }

    public function error()
    {
        $message = $_GET['message'] ?? 'An error occurred.';
        View::load('traveler/error', ['message' => $message]);
    }

    public function booking($bookingId)
    {
        if (!$bookingId) {
            header('Location: ' . BASE_URL . 'traveler/dashboard');
            exit;
        }

        $booking = $this->bookingsModel->getById($bookingId);

        if (!$booking || $booking['traveler_id'] != $_SESSION['user_id']) {
            header('Location: ' . BASE_URL . 'traveler/dashboard');
            exit;
        }

        $version = $this->tourVersionsModel->getTourVersionById($booking['tour_version_id']);
        $tour = $this->toursModel->getById($version['tour_id']);
        $guide = $this->guidesModel->getById($tour['guide_id']);
        $location = $this->locationsModel->getById($tour['location_id']);
        $addonIds = json_decode($booking['selected_addons'] ?? '[]', true);
        $addons = !empty($addonIds) ? $this->addonsModel->getByIds($addonIds) : [];

        View::load('traveler/booking_details', [
            'title' => 'Booking #' . $bookingId . ' – EcoVoyage',
            'booking' => $booking,
            'tour' => $tour,
            'version' => $version,
            'guide' => $guide,
            'location' => $location,
            'addons' => $addons,
        ]);
    }

    public function cancelBooking($bookingId)
    {
        if (!$bookingId) {
            header('Location: ' . BASE_URL . 'traveler/dashboard');
            exit;
        }

        $booking = $this->bookingsModel->getById($bookingId);

        if (!$booking || $booking['traveler_id'] != $_SESSION['user_id']) {
            header('Location: ' . BASE_URL . 'traveler/dashboard');
            exit;
        }

        if (!in_array($booking['status'], ['pending', 'confirmed'])) {
            header('Location: ' . BASE_URL . 'traveler/booking/' . $bookingId);
            exit;
        }

        $startDate = new DateTime($booking['start_time']);
        $today = new DateTime('today');
        $daysLeft = (int) $today->diff($startDate)->format('%r%a');

        $isPaid = ($booking['status'] === 'confirmed');
        $refundPercent = 0;
        $refundAmount = 0;

        if ($isPaid) {
            $fullRefundDays = 30;
            $halfRefundDays = 7;

            if ($daysLeft > $fullRefundDays) {
                $refundPercent = 100;
            } elseif ($daysLeft > $halfRefundDays) {
                $refundPercent = 50;
            } else {
                $refundPercent = 0;
            }
            $refundAmount = $booking['total_price'] * ($refundPercent / 100);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->bookingsModel->cancel($bookingId);
            header('Location: ' . BASE_URL . 'traveler/booking/' . $bookingId);
            exit;
        }

        View::load('traveler/cancel_booking', [
            'title' => 'Cancel Booking #' . $bookingId . ' – EcoVoyage',
            'booking' => $booking,
            'daysLeft' => $daysLeft,
            'isPaid' => $isPaid,
            'refundPercent' => $refundPercent,
            'refundAmount' => $refundAmount,
        ]);
    }

    public function bookings()
    {
        $travelerId = $_SESSION['user_id'];
        $allBookings = $this->bookingsModel->getAllByTraveler($travelerId);

        View::load('traveler/bookings', [
            'title' => 'My Bookings - EcoVoyage',
            'bookings' => $allBookings
        ]);
    }

    public function settings()
    {
        $travelerId = $_SESSION['user_id'];
        $user = $this->usersModel->getById($travelerId);
        $traveler = $this->travelersModel->getTravelerById($travelerId);

        $data = [
            'title' => 'Account Settings – EcoVoyage',
            'user' => $user,
            'traveler' => $traveler,
            'errors' => [],
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
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
                $errors[] = 'Phone number is not valid.';
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
                View::load('traveler/settings', $data);
                return;
            }

            $updateData = [
                'name' => $name,
                'phone' => $phone
            ];
            if (!empty($password)) {
                $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->usersModel->update($travelerId, $updateData);
            $user = $this->usersModel->getById($travelerId);
            $data['user'] = $user;
            $data['success'] = 'Profile updated successfully.';
        }

        View::load('traveler/settings', $data);
    }

    public function briefing($tourId)
    {
        $tour = $this->toursModel->getById($tourId);
        if (!$tour) {
            header('Location: ' . BASE_URL . 'traveler/dashboard');
            exit;
        }

        $tourType = $tour['tour_type'];
        $briefingModel = new Briefing();
        $briefing = $briefingModel->getByTourType($tourType);

        if (!$briefing) {
            $briefing = [
                'safety_text' => 'Follow general safety guidelines.',
                'environmental_text' => 'Respect local ecosystems.',
                'equipment_text' => 'Carry essential gear.',
                'emergency_contact' => 'Call 911 in case of emergency.'
            ];
        }

        View::load('traveler/briefing', [
            'title' => 'Trip Briefing – ' . $tour['tour_name'],
            'tour' => $tour,
            'briefing' => $briefing
        ]);
    }
}