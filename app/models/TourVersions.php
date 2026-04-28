<?php

class TourVersions
{
    private $table = "tour_versions";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByTourId($tourId)
    {
        $query = $this->db->prepare("SELECT * FROM tour_versions WHERE tour_id = :id");
        $query->execute(['id' => $tourId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

