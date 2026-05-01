<?php
class WithdrawalRequests
{
    private $table = "withdrawal_requests";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByGuide($guideId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE guide_id = :gid ORDER BY requested_at DESC");
        $stmt->execute(['gid' => $guideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
         (guide_id, amount, card_number, card_expiry, card_cvv, cardholder_name, status, requested_at)
         VALUES 
         (:gid, :amount, :cardnumber, :expiry, :cvv, :cardholder, 'pending', NOW())"
        );
        return $stmt->execute([
            'gid' => $data['guide_id'],
            'amount' => $data['amount'],
            'cardnumber' => $data['card_number'],
            'expiry' => $data['card_expiry'],
            'cvv' => $data['card_cvv'],
            'cardholder' => $data['cardholder_name']
        ]);
    }
}