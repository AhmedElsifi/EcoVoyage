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

    public function getTourVersionAddons($tourId)
    {
        $stmt = $this->db->prepare(
            "SELECT addons.* FROM addons
         JOIN tour_versions ON addons.tour_version_id = tour_versions.tour_version_id
         WHERE tour_versions.tour_id = :tid"
        );
        $stmt->execute(['tid' => $tourId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTourVersionById($tourVersionId)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE tour_version_id = :tourVersionId");
        $query->execute(["tourVersionId" => $tourVersionId]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}

