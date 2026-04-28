<?php

class RegionalAuditors
{
    private $table = "regional_auditors";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

