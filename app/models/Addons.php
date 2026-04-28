<?php

class Addons
{
    private $table = "addons";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByVersionId($versionId)
    {
        $query = $this->db->prepare("SELECT * FROM addons WHERE tour_version_id = :id");
        $query->execute(['id' => $versionId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

