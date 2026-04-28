<?php

class Documents
{
    private $table = "documents";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

