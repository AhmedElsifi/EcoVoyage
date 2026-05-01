<?php
class Documents
{
    private $table = "documents";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
         (entity_type, entity_id, doc_type, file_path, status, issued_date, expiry_date)
         VALUES (:etype, :eid, :dtype, :path, 'pending', :issued, :expiry)"
        );
        $stmt->execute([
            'etype' => $data['entity_type'],
            'eid' => $data['entity_id'],
            'dtype' => $data['doc_type'],
            'path' => $data['file_path'],
            'issued' => $data['issued_date'] ?? date('Y-m-d'),
            'expiry' => $data['expiry_date'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getById($docId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE doc_id = :id");
        $stmt->execute(['id' => $docId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByLanguageId($languageId)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE entity_type = 'guide_language' 
               AND entity_id = :id 
             ORDER BY doc_id DESC"
        );
        $stmt->execute(['id' => $languageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($docId, $status)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status WHERE doc_id = :id");
        return $stmt->execute(['status' => $status, 'id' => $docId]);
    }

    public function getCertsByGuide($guideId)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
         WHERE entity_type = 'guide_cert' 
           AND entity_id = :gid 
         ORDER BY doc_id DESC"
        );
        $stmt->execute(['gid' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}