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

    public function getEcoLeafRating($tour)
    {
        $leaves = 0;
        $footprint = (float) ($tour['carbon_footprint'] ?? 0);
        if ($footprint <= 50) {
            $leaves += 3;
        } elseif ($footprint <= 150) {
            $leaves += 2;
        } elseif ($footprint <= 300) {
            $leaves += 1;
        }
        if (!empty($tour['waste_management'])) {
            $leaves += 1;
        }
        if (!empty($tour['local_hiring'])) {
            $leaves += 1;
        }
        $leaves = max(2, $leaves);
        return min(5, $leaves);
    }

    public function countByGuideAndStatus($guideId, $status)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE guide_id = :gid AND status = :status"
        );
        $stmt->execute(['gid' => $guideId, 'status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function getLatestByGuide($guideId, $limit = 10)
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, l.location_name, l.country
         FROM {$this->table} t
         JOIN locations l ON t.location_id = l.location_id
         WHERE t.guide_id = :gid
         ORDER BY t.tour_id DESC
         LIMIT :limit"
        );
        $stmt->bindValue(':gid', $guideId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByGuide($guideId)
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, l.location_name, l.country
         FROM {$this->table} t
         JOIN locations l ON t.location_id = l.location_id
         WHERE t.guide_id = :gid
         ORDER BY t.tour_id DESC"
        );
        $stmt->execute(['gid' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
         (tour_name, description, guide_id, location_id, tour_type, status, tour_img_path,
          carbon_footprint, waste_management, local_hiring, impact_tags, routes)
         VALUES 
         (:name, :desc, :gid, :loc, :type, :status, :img, :cf, :wm, :lh, :tags, :routes)"
        );
        $stmt->execute([
            'name' => $data['tour_name'],
            'desc' => $data['description'] ?? null,
            'gid' => $data['guide_id'],
            'loc' => $data['location_id'],
            'type' => $data['tour_type'],
            'status' => $data['status'] ?? 'pending',
            'img' => $data['tour_img_path'] ?? null,
            'cf' => $data['carbon_footprint'] ?? 0,
            'wm' => $data['waste_management'] ? 1 : 0,
            'lh' => $data['local_hiring'] ? 1 : 0,
            'tags' => $data['impact_tags'] ?? '',
            'routes' => $data['routes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($tourId, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
         SET tour_name = :name,
             description = :desc,
             location_id = :loc,
             tour_type = :type,
             status = 'pending',
             tour_img_path = :img,
             carbon_footprint = :cf,
             waste_management = :wm,
             local_hiring = :lh,
             impact_tags = :tags,
             routes = :routes
         WHERE tour_id = :id"
        );
        return $stmt->execute([
            'name' => $data['tour_name'],
            'desc' => $data['description'] ?? null,
            'loc' => $data['location_id'],
            'type' => $data['tour_type'],
            'img' => $data['tour_img_path'] ?? null,
            'cf' => $data['carbon_footprint'] ?? 0,
            'wm' => $data['waste_management'] ? 1 : 0,
            'lh' => $data['local_hiring'] ? 1 : 0,
            'tags' => $data['impact_tags'] ?? '',
            'routes' => $data['routes'] ?? null,
            'id' => $tourId
        ]);
    }

    public function delete($tourId)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE tour_id = :id");
        return $stmt->execute(['id' => $tourId]);
    }
}

