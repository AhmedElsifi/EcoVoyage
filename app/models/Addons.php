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

    public function getByIds($addonIds)
    {
        if (empty($addonIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($addonIds), '?'));
        $sql = "SELECT * FROM {$this->table} WHERE addon_id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($addonIds);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

