<?php
class Driver {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getDriverDetails($userId) {
        $stmt = $this->db->prepare("SELECT * FROM driver_details WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addViolation($data) {
        $stmt = $this->db->prepare("INSERT INTO traffic_violations 
                                  (driver_id, violation_date, violation_type, penalty_amount, 
                                   points_deducted, location) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['driver_id'],
            $data['violation_date'],
            $data['violation_type'],
            $data['penalty_amount'],
            $data['points_deducted'],
            $data['location']
        ]);
    }

    public function updateRating($driverId) {
        // Calculate new rating based on violations and experience
        $stmt = $this->db->prepare("
            SELECT 
                (5 - (COUNT(*) * 0.5)) as new_rating 
            FROM traffic_violations 
            WHERE driver_id = ? 
            AND violation_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
        $stmt->execute([$driverId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $newRating = max(1, min(5, $result['new_rating']));
        
        $stmt = $this->db->prepare("UPDATE driver_details SET rating = ? WHERE id = ?");
        return $stmt->execute([$newRating, $driverId]);
    }
}
?> 