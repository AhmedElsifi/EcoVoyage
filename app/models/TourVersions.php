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

    public function createVersion($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
         (tour_id, version_name, price_per_person, max_capacity, itinerary_json, booking_type, group_discounts)
         VALUES 
         (:tour_id, :name, :price, :capacity, :itinerary, :booking_type, :discounts)"
        );
        return $stmt->execute([
            'tour_id' => $data['tour_id'],
            'name' => $data['version_name'],
            'price' => $data['price_per_person'],
            'capacity' => $data['max_capacity'],
            'itinerary' => $data['itinerary_json'] ?? null,
            'booking_type' => $data['booking_type'] ?? 'instant',
            'discounts' => $data['group_discounts'] ?? null
        ]) ? $this->db->lastInsertId() : false;
    }

    public function update($versionId, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
         SET version_name = :name,
             price_per_person = :price,
             max_capacity = :capacity,
             itinerary_json = :itinerary,
             booking_type = :booking_type,
             group_discounts = :discounts
         WHERE tour_version_id = :id"
        );
        return $stmt->execute([
            'name' => $data['version_name'],
            'price' => $data['price_per_person'],
            'capacity' => $data['max_capacity'],
            'itinerary' => $data['itinerary_json'] ?? null,
            'booking_type' => $data['booking_type'] ?? 'instant',
            'discounts' => $data['group_discounts'] ?? null,
            'id' => $versionId
        ]);
    }

    public function delete($versionId)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE tour_version_id = :id");
        return $stmt->execute(['id' => $versionId]);
    }
}

