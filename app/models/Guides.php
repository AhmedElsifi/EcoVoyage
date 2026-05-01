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

    public function updateBalances($guideId, $availableDelta, $pendingDelta, $withdrawnDelta)
    {
        $stmt = $this->db->prepare(
            "UPDATE guides 
         SET available_balance = available_balance + :avail,
             pending_balance = pending_balance + :pend,
             withdrawn_balance = withdrawn_balance + :withd
         WHERE guide_id = :id"
        );
        return $stmt->execute([
            'avail' => $availableDelta,
            'pend' => $pendingDelta,
            'withd' => $withdrawnDelta,
            'id' => $guideId
        ]);
    }

    public function updateProfile($guideId, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE guides 
         SET country_of_residence = :country,
             bio = :bio,
             years_of_experience = :experience
         WHERE guide_id = :id"
        );
        return $stmt->execute([
            'country' => $data['country_of_residence'],
            'bio' => $data['bio'],
            'experience' => $data['years_of_experience'],
            'id' => $guideId
        ]);
    }

    public function updateStatus($guideId, $status)
    {
        $stmt = $this->db->prepare("UPDATE guides SET status = :status WHERE guide_id = :id");
        return $stmt->execute(['status' => $status, 'id' => $guideId]);
    }

    public function recalculateBadges($guideId)
    {
        $guide = $this->getById($guideId);
        if (!$guide)
            return;

        $badges = [];

        $langCount = $this->db->prepare(
            "SELECT COUNT(*) FROM guide_languages gl
         JOIN documents d ON d.entity_id = gl.id AND d.entity_type = 'guide_language'
         WHERE gl.guide_id = :gid AND d.status = 'approved'"
        );
        $langCount->execute(['gid' => $guideId]);
        if ($langCount->fetchColumn() > 0) {
            $badges[] = 'Language Pro';
        }

        $certCount = $this->db->prepare(
            "SELECT COUNT(*) FROM documents
         WHERE entity_type = 'guide_cert' AND entity_id = :gid AND status = 'approved'"
        );
        $certCount->execute(['gid' => $guideId]);
        if ($certCount->fetchColumn() > 0) {
            $badges[] = 'Eco Certified';
        }

        $firstAid = $this->db->prepare(
            "SELECT COUNT(*) FROM documents
         WHERE entity_type = 'guide_cert' AND entity_id = :gid 
           AND doc_type = 'first_aid' AND status = 'approved'"
        );
        $firstAid->execute(['gid' => $guideId]);
        if ($firstAid->fetchColumn() > 0) {
            $badges[] = 'First Aid Responder';
        }

        if (($guide['sustainability_score'] ?? 0) >= 90) {
            $badges[] = 'Eco Champion';
        }

        if (($guide['sustainability_score'] ?? 0) >= 80 && ($guide['cancellation_rate'] ?? 100) <= 5) {
            $badges[] = 'Top Rated';
        }

        $badges = array_values(array_unique(array_filter($badges)));

        $stmt = $this->db->prepare("UPDATE guides SET badges = :badges WHERE guide_id = :id");
        $stmt->execute([
            'badges' => json_encode($badges),
            'id' => $guideId
        ]);
    }
}

