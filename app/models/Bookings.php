<?php

class Bookings
{
    private $table = "bookings";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getUpcomingBookingsByTraveler($travelerId, $limit = 5)
    {
        $stmt = $this->db->prepare(
            "SELECT {$this->table}.*, tours.tour_name, tour_versions.version_name, 
                DATE({$this->table}.start_time) AS trip_date,
                locations.location_name, locations.country
         FROM {$this->table}
         JOIN tour_versions ON {$this->table}.tour_version_id = tour_versions.tour_version_id
         JOIN tours ON tour_versions.tour_id = tours.tour_id
         JOIN locations ON tours.location_id = locations.location_id
         WHERE {$this->table}.traveler_id = :tid 
           AND {$this->table}.status = 'confirmed' 
           AND {$this->table}.start_time >= NOW()
         ORDER BY {$this->table}.start_time ASC
         LIMIT :limit"
        );
        $stmt->bindValue(':tid', $travelerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByTraveler($travelerId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE traveler_id = :tid");
        $stmt->execute(['tid' => $travelerId]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data)
    {
        $status = $data['status'] ?? 'pending';

        $stmt = $this->db->prepare(
            "INSERT INTO bookings 
         (traveler_id, guide_id, tour_version_id, carbon_offset, start_time, status, selected_addons, total_price)
         VALUES 
         (:traveler_id, :guide_id, :tour_version_id, :carbon_offset, :start_time, :status, :selected_addons, :total_price)"
        );
        $success = $stmt->execute([
            'traveler_id' => $data['traveler_id'],
            'guide_id' => $data['guide_id'],
            'tour_version_id' => $data['tour_version_id'],
            'carbon_offset' => $data['carbon_offset'] ?? 0,
            'start_time' => $data['start_time'],
            'status' => $status,                     // dynamic
            'selected_addons' => $data['selected_addons'] ?? '[]',
            'total_price' => $data['total_price'] ?? 0
        ]);
        return $success ? $this->db->lastInsertId() : false;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE booking_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isGuideAvailable($guideId, $date)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table}
         WHERE guide_id = :guide_id
           AND status = 'confirmed'
           AND DATE(start_time) = :date"
        );
        $stmt->execute(['guide_id' => $guideId, 'date' => $date]);
        return $stmt->fetchColumn() == 0;
    }

    public function getPendingByTraveler($travelerId)
    {
        $stmt = $this->db->prepare(
            "SELECT {$this->table}.*, tours.tour_name, tour_versions.version_name, DATE({$this->table}.start_time) AS trip_date,
                locations.location_name, locations.country
         FROM {$this->table}
         JOIN tour_versions ON {$this->table}.tour_version_id = tour_versions.tour_version_id
         JOIN tours ON tour_versions.tour_id = tours.tour_id
         JOIN locations ON tours.location_id = locations.location_id
         WHERE {$this->table}.traveler_id = :tid AND {$this->table}.status = 'pending'
         ORDER BY {$this->table}.start_time ASC"
        );
        $stmt->execute(['tid' => $travelerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancel($bookingId)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
         SET status = 'cancelled', 
             cancelled_at = NOW()
         WHERE booking_id = :id"
        );
        return $stmt->execute(['id' => $bookingId]);
    }

    public function getAllByTraveler($travelerId)
    {
        $stmt = $this->db->prepare(
            "SELECT {$this->table}.*, tours.tour_name, tour_versions.version_name, 
                DATE({$this->table}.start_time) AS trip_date,
                locations.location_name, locations.country
         FROM {$this->table}
         JOIN tour_versions ON {$this->table}.tour_version_id = tour_versions.tour_version_id
         JOIN tours ON tour_versions.tour_id = tours.tour_id
         JOIN locations ON tours.location_id = locations.location_id
         WHERE {$this->table}.traveler_id = :tid
         ORDER BY {$this->table}.booking_id DESC"
        );
        $stmt->execute(['tid' => $travelerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

