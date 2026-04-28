<?php

class PlatformSettings
{
    private $table = "platform_settings";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

