<?php
class Briefing
{
    private $table = "briefings";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByTourType($tourType)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE tour_type = :type");
        $stmt->execute(['type' => $tourType]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}