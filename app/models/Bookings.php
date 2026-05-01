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
         (traveler_id, guide_id, tour_version_id, carbon_offset, start_time, status, selected_addons, total_price, num_travelers)
         VALUES 
         (:traveler_id, :guide_id, :tour_version_id, :carbon_offset, :start_time, :status, :selected_addons, :total_price, :num_travelers)"
        );
        $success = $stmt->execute([
            'traveler_id' => $data['traveler_id'],
            'guide_id' => $data['guide_id'],
            'tour_version_id' => $data['tour_version_id'],
            'carbon_offset' => $data['carbon_offset'] ?? 0,
            'start_time' => $data['start_time'],
            'status' => $status,
            'selected_addons' => $data['selected_addons'] ?? '[]',
            'total_price' => $data['total_price'] ?? 0,
            'num_travelers' => $data['num_travelers'] ?? 1
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

    public function getNextBookingForGuide($guideId)
    {
        $stmt = $this->db->prepare(
            "SELECT b.booking_id, b.start_time, t.tour_id, t.tour_name
         FROM bookings b
         JOIN tour_versions tv ON b.tour_version_id = tv.tour_version_id
         JOIN tours t ON tv.tour_id = t.tour_id
         WHERE b.guide_id = :gid
           AND b.status = 'confirmed'
           AND b.start_time >= NOW()
         ORDER BY b.start_time ASC
         LIMIT 1"
        );
        $stmt->execute(['gid' => $guideId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllByGuide($guideId)
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, tours.tour_name, tv.version_name, 
                u.name AS traveler_name, u.email AS traveler_email,
                l.location_name, l.country
         FROM {$this->table} b
         JOIN tour_versions tv ON b.tour_version_id = tv.tour_version_id
         JOIN tours ON tv.tour_id = tours.tour_id
         JOIN locations l ON tours.location_id = l.location_id
         JOIN users u ON b.traveler_id = u.user_id
         WHERE b.guide_id = :gid
         ORDER BY b.start_time ASC"
        );
        $stmt->execute(['gid' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($bookingId, $status)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = :status 
         WHERE booking_id = :id"
        );
        return $stmt->execute(['status' => $status, 'id' => $bookingId]);
    }
}

