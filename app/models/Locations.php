<?php

class Locations
{
    private $table = "locations";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function countDistinctCountries()
    {
        $query = $this->db->prepare("SELECT COUNT(DISTINCT country) FROM {$this->table}");
        $query->execute();
        return (int) $query->fetchColumn();
    }

    public function getById($locationId)
    {
        $query = $this->db->prepare("SELECT * FROM locations WHERE location_id = :id");
        $query->execute(['id' => $locationId]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY location_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

