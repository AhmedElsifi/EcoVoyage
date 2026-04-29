<?php

class Travelers
{
    private $table = "travelers";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTravelerById($travelerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM travelers WHERE traveler_id = :tid");
        $stmt->execute(['tid' => $travelerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

