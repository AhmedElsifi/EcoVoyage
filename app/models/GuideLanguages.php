<?php

class GuideLanguages
{
    private $table = "guide_languages";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

