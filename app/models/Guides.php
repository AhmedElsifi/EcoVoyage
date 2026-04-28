<?php

class Guides
{
    private $table = "guides";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getFeatured($limit = 2)
    {
        $query = $this->db->prepare(
            "SELECT g.*, u.name AS guide_name
             FROM {$this->table} g
             JOIN users u ON g.guide_id = u.user_id
             WHERE g.status = 'active'
             ORDER BY g.sustainability_score DESC
             LIMIT :limit"
        );
        $query->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($guideId)
    {
        $query = $this->db->prepare("SELECT * FROM guides WHERE guide_id = :id");
        $query->execute(['id' => $guideId]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}

