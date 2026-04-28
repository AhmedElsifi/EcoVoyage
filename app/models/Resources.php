<?php

class Resources
{
    private $table = "resources";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

