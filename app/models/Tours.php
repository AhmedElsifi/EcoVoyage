<?php

class Tours
{
    private $table = "tours";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getFeatured()
    {
        $query = $this->db->prepare(
            "SELECT t.tour_id,
                t.tour_name AS name,
                t.tour_img_path AS image,
                l.location_name AS location,
                MIN(tv.price_per_person) AS price,
                t.impact_tags
         FROM {$this->table} t
         JOIN locations l ON t.location_id = l.location_id
         LEFT JOIN tour_versions tv ON tv.tour_id = t.tour_id
         WHERE t.status = :status
         GROUP BY t.tour_id
         ORDER BY t.tour_id DESC
         LIMIT 3"
        );
        $query->execute(['status' => 'active']);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countActive()
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'");
        $query->execute();
        return (int) $query->fetchColumn();
    }

    public function getActiveTours()
    {
        $query = $this->db->prepare("SELECT 
        {$this->table}.tour_id,
        tour_name as name,
        tour_img_path as image,
        users.name as guide_name,
        locations.location_name as location,
        locations.country as country,
        impact_tags,
        MIN(tour_versions.price_per_person) AS price
        FROM {$this->table} 
        JOIN users ON {$this->table}.guide_id = users.user_id
        JOIN locations ON {$this->table}.location_id = locations.location_id
        LEFT JOIN tour_versions ON tour_versions.tour_id = {$this->table}.tour_id
        WHERE {$this->table}.status = 'active'
        GROUP BY {$this->table}.tour_id
        ORDER BY {$this->table}.tour_id DESC
        ");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = $this->db->prepare("
        SELECT {$this->table}.*, 
               users.name AS guide_name,
               locations.location_name,
               locations.country,
               MIN(tour_versions.price_per_person) AS min_price
        FROM {$this->table}
        JOIN users ON {$this->table}.guide_id = users.user_id
        JOIN locations ON {$this->table}.location_id = locations.location_id
        LEFT JOIN tour_versions ON tour_versions.tour_id = {$this->table}.tour_id
        WHERE {$this->table}.tour_id = :id
        GROUP BY {$this->table}.tour_id
    ");
        $query->execute(['id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getTourVersions($id)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} JOIN tour_versions ON {$this->table}.tour_id = tour_versions.tour_id WHERE {$this->table}.tour_id = :id");
        $query->execute(["id" => $id]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTourAddons($id)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} JOIN addons ON {$this->table}.tour_id = addons.tour_id WHERE {$this->table}.tour_id = :id");
        $query->execute(["id" => $id]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTourTypes()
    {
        return [
            'eco_adventure' => 'Eco Adventure',
            'wildlife_safari' => 'Wildlife Safari',
            'hiking_trekking' => 'Hiking & Trekking',
            'marine_coastal' => 'Marine & Coastal',
            'cultural_immersion' => 'Cultural Immersion',
            'volunteer_conservation' => 'Volunteer & Conservation',
            'photography_expedition' => 'Photography Expedition',
            'custom' => 'Custom'
        ];
    }


}

