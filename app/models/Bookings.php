<?php

class Bookings
{
    private $table = "bookings";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

