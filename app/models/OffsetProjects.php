<?php

class offsetProjects
{
    private $table = "offset_projects";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getOffsetProjects()
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table}");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE project_id = :id");
        $query->execute(["id" => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}

