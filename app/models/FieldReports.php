<?php

class FieldReports
{
    private $table = "field_reports";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

