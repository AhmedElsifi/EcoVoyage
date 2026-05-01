<?php
class GuideLanguages
{
    private $table = "guide_languages";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByGuide($guideId)
    {
        $stmt = $this->db->prepare(
            "SELECT gl.*, d.file_path AS cert_path, d.status AS cert_status, 
                    d.expiry_date, d.doc_id, d.issued_date
             FROM {$this->table} gl
             LEFT JOIN (
                 SELECT d1.*
                 FROM documents d1
                 WHERE d1.entity_type = 'guide_language'
                 AND d1.doc_type = 'language_cert'
                 AND d1.doc_id IN (
                     SELECT MAX(d2.doc_id)
                     FROM documents d2
                     WHERE d2.entity_id = d1.entity_id
                     AND d2.entity_type = 'guide_language'
                 )
             ) d ON d.entity_id = gl.id
             WHERE gl.guide_id = :gid
             ORDER BY gl.language"
        );
        $stmt->execute(['gid' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exists($guideId, $language)
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM {$this->table} WHERE guide_id = :gid AND LOWER(language) = LOWER(:lang)"
        );
        $stmt->execute(['gid' => $guideId, 'lang' => $language]);
        return $stmt->fetchColumn();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (guide_id, language, proficiency_level, status)
             VALUES (:gid, :lang, :level, 'pending')"
        );
        $stmt->execute([
            'gid' => $data['guide_id'],
            'lang' => $data['language'],
            'level' => $data['proficiency_level'] ?? 'fluent'
        ]);
        return $this->db->lastInsertId();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }
}