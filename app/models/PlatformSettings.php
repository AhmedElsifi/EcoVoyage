<?php

class PlatformSettings
{
    private $table = "platform_settings";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getSettings()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

