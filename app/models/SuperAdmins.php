<?php

class SuperAdmins
{
    private $table = "super_admins";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

