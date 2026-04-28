<?php

class Travelers
{
    private $table = "travelers";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

