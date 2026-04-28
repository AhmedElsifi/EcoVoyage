<?php

class GuideShadowing
{
    private $table = "guide_shadowing";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

