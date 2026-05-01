<?php
class Vault
{
    private $table = "vault";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
            (booking_id, total_amount, guide_earnings, platform_fee, tax_amount, status) 
            VALUES (:booking_id, :total, :guide, :fee, :tax, :status)"
        );
        return $stmt->execute([
            'booking_id' => $data['booking_id'],
            'total' => $data['total_amount'],
            'guide' => $data['guide_earnings'],
            'fee' => $data['platform_fee'],
            'tax' => $data['tax_amount'],
            'status' => $data['status']
        ]);
    }

    public function releaseFunds($bookingId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE booking_id = :id AND status = 'held'");
        $stmt->execute(['id' => $bookingId]);
        $vaultEntry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vaultEntry) {
            return false;
        }

        $updateStmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = 'released' WHERE booking_id = :id AND status = 'held'"
        );
        $updateStmt->execute(['id' => $bookingId]);

        $bookingStmt = $this->db->prepare("SELECT guide_id FROM bookings WHERE booking_id = :bid");
        $bookingStmt->execute(['bid' => $bookingId]);
        $guideId = $bookingStmt->fetchColumn();

        if ($guideId) {
            $guidesModel = new Guides();
            $guidesModel->addToBalance($guideId, $vaultEntry['guide_earnings']);
        }

        return true;
    }

    public function refund($bookingId, $refundAmount)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
             SET status = 'refunded', refund_amount = :amount 
             WHERE booking_id = :id AND status = 'held'"
        );
        $stmt->execute(['amount' => $refundAmount, 'id' => $bookingId]);
    }

    public function getByBookingId($bookingId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE booking_id = :id");
        $stmt->execute(['id' => $bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cancelVault($bookingId)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = 'cancelled' WHERE booking_id = :id AND status = 'held'"
        );
        $stmt->execute(['id' => $bookingId]);
    }
}