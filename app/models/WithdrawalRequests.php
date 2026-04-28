<?php

class WithdrawalRequests
{
    private $table = "withdrawal_requests";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

