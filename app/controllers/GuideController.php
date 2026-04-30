<?php

class GuideController
{
    private $toursModel;
    private $usersModel;
    private $guidesModel;
    private $bookingsModel;

    public function __construct()
    {
        $this->toursModel = new Tours();
        $this->bookingsModel = new Bookings();
        $this->guidesModel = new Guides();
        $this->usersModel = new Users();

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

}