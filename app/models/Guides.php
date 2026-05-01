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

    public function getById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT g.*, u.name 
         FROM guides g 
         JOIN users u ON g.guide_id = u.user_id 
         WHERE g.guide_id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getLocalCredScore($guideId, $tourCountry)
    {
        $guide = $this->getById($guideId);
        if (!$guide)
            return 0;

        $score = 0;

        $years = min((int) ($guide['years_of_experience'] ?? 0), 10);
        $score += $years * 5;

        if (strcasecmp(trim($guide['country_of_residence'] ?? ''), trim($tourCountry)) === 0) {
            $score += 50;
        }

        return $score;
    }

    public function addToBalance($guideId, $amount)
    {
        $stmt = $this->db->prepare(
            "UPDATE guides SET available_balance = available_balance + :amount WHERE guide_id = :gid"
        );
        return $stmt->execute(['amount' => $amount, 'gid' => $guideId]);
    }

}

