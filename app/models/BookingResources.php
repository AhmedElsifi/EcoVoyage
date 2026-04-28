<?php

class BookingResources
{
    private $table = "booking_resources";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

