<?php
class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function register($data) {
        try {
            $this->db->beginTransaction();

            // Insert user
            $stmt = $this->db->prepare("INSERT INTO users (fullname, national_id, username, email, password) 
                                      VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['fullname'],
                $data['national_id'],
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ]);
            
            $userId = $this->db->lastInsertId();

            // Insert vehicle
            $stmt = $this->db->prepare("INSERT INTO vehicles (user_id, registration_number, make_model) 
                                      VALUES (?, ?, ?)");
            $stmt->execute([
                $userId,
                $data['car_reg'],
                $data['car_model']
            ]);

            // Insert driver details
            $stmt = $this->db->prepare("INSERT INTO driver_details (user_id, age, experience_years) 
                                      VALUES (?, ?, ?)");
            $stmt->execute([
                $userId,
                $data['age'] ?? 0,
                $data['experience'] ?? 0
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function resetPasswordRequest($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $this->db->prepare("INSERT INTO password_resets (user_id, reset_token, expiry_date) 
                                      VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expiry]);

            return $token;
        }
        return false;
    }
}
?> 