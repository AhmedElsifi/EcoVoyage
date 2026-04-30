<?php

class GuestController
{
    private $toursModel;
    private $usersModel;
    private $locationsModel;
    private $guidesModel;

    public function __construct()
    {
        $this->toursModel = new Tours();
        $this->usersModel = new Users();
        $this->locationsModel = new Locations();
        $this->guidesModel = new Guides();
    }
    public function index()
    {

        $featuredTours = $this->toursModel->getFeatured();
        $toursCount = $this->toursModel->countActive();
        $travelersCount = $this->usersModel->countByRole('traveler');
        $guidesCount = $this->usersModel->countByRole('guide');
        $countriesCount = $this->locationsModel->countDistinctCountries();
        $featuredGuides = $this->guidesModel->getFeatured(2);

        $data = [
            'title' => 'EcoVoyage - Sustainable Travel',
            'featuredTours' => $featuredTours,
            'tours' => $toursCount,
            'travelers' => $travelersCount,
            'guides' => $guidesCount,
            'countries' => $countriesCount,
            'featuredGuides' => $featuredGuides
        ];
        View::load("guest/home", $data);
    }

    public function tours()
    {
        $tours = $this->toursModel->getActiveTours();

        $data = [
            'title' => 'Browse Eco-Tours - EcoVoyage',
            'tours' => $tours
        ];
        View::load("guest/tours", $data);
    }

    public function tour($id)
    {
        $toursModel = new Tours();
        $versionsModel = new TourVersions();
        $addonsModel = new Addons();
        $guidesModel = new Guides();
        $locationsModel = new Locations();

        $tour = $toursModel->getById($id);

        if (!$tour) {
            header('Location: ' . BASE_URL . 'guest/tours');
            exit;
        }

        $versions = $versionsModel->getByTourId($id);

        $addons = !empty($versions) ? $addonsModel->getByVersionId($versions[0]['tour_version_id']) : [];

        $guide = $guidesModel->getById($tour['guide_id']);

        $location = $locationsModel->getById($tour['location_id']);

        $ecoLeaves = $this->toursModel->getEcoLeafRating($tour);

        $data = [
            'title' => $tour['tour_name'] . ' - EcoVoyage',
            'tour' => $tour,
            'versions' => $versions,
            'addons' => $addons,
            'guide' => $guide,
            'location' => $location,
            'ecoLeaves' => $ecoLeaves
        ];

        View::load("guest/tour_details", $data);
    }
}