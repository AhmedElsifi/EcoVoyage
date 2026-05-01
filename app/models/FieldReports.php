<?php
class FieldReports
{
    private $table = "field_reports";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByGuide($guideId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE guide_id = :gid ORDER BY created_at DESC");
        $stmt->execute(['gid' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (guide_id, tour_id, content_text, photo_path, created_at)
             VALUES (:gid, :tour_id, :text, :photo, NOW())"
        );
        return $stmt->execute([
            'gid' => $data['guide_id'],
            'tour_id' => $data['tour_id'] ?? null,
            'text' => $data['content_text'],
            'photo' => $data['photo_path'] ?? null
        ]);
    }

    public function delete($reportId)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE report_id = :id");
        return $stmt->execute(['id' => $reportId]);
    }

    public function getById($reportId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE report_id = :id");
        $stmt->execute(['id' => $reportId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}