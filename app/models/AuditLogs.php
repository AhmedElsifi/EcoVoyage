<?php

class AuditLogs
{
    private $table = "audit_logs";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
}

