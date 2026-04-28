<?php

class Users
{
    private $table = "users";
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function countByRole($role)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE role = :role");
        $query->execute(['role' => $role]);
        return (int) $query->fetchColumn();
    }

    public function register($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password_hash, role, phone, date_of_birth)
         VALUES (:name, :email, :password, :role, :phone, :dob)"
        );
        $success = $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'dob' => $data['date_of_birth'] ?? null,
        ]);

        if (!$success) {
            return false;
        }

        $userId = $this->db->lastInsertId();

        $role = $data['role'];
        switch ($role) {
            case 'traveler':
                $stmt2 = $this->db->prepare(
                    "INSERT INTO travelers (traveler_id, nationality) VALUES (:uid, :nationality)"
                );
                return $stmt2->execute([
                    'uid' => $userId,
                    'nationality' => $_POST['nationality'] ?? null
                ]);

            case 'guide':
                $residency = json_encode([
                    'city' => $_POST['city'] ?? '',
                    'country' => $_POST['country_of_residence']
                ]);
                $idPath = null;
                if (Validator::fileRequired('identity_doc')) {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/ids/';
                    if (!is_dir($uploadDir))
                        mkdir($uploadDir, 0755, true);
                    $ext = pathinfo($_FILES['identity_doc']['name'], PATHINFO_EXTENSION);
                    $filename = 'guide_' . $userId . '_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['identity_doc']['tmp_name'], $uploadDir . $filename);
                    $idPath = "/uploads/ids/{$filename}";
                }

                $stmt2 = $this->db->prepare(
                    "INSERT INTO guides (guide_id, identity_verification_path, country_of_residence, bio, years_of_experience,residency)
                 VALUES (:uid, :id_path, :country, :bio, :exp,:res)"
                );
                return $stmt2->execute([
                    'uid' => $userId,
                    'id_path' => $idPath,
                    'country' => $_POST['country_of_residence'],
                    'bio' => $_POST['bio'],
                    'exp' => $_POST['years_of_experience'],
                    'res' => $residency,
                ]);

            case 'auditor':
                $cvPath = null;
                if (Validator::fileRequired('cv')) {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/EcoVoyage/public/uploads/cvs/';
                    if (!is_dir($uploadDir))
                        mkdir($uploadDir, 0755, true);
                    $ext = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
                    $filename = 'auditor_' . $userId . '_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['cv']['tmp_name'], $uploadDir . $filename);
                    $cvPath = '/uploads/cvs/' . $filename;
                }

                $stmt2 = $this->db->prepare(
                    "INSERT INTO regional_auditors (auditor_id, assigned_region, cv_path)
                     VALUES (:uid, :region, :cv_path)"
                );
                return $stmt2->execute([
                    'uid' => $userId,
                    'region' => $_POST['assigned_region'],
                    'cv_path' => $cvPath
                ]);
        }

        return false;
    }
}

