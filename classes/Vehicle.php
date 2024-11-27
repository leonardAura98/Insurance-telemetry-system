<?php
class Vehicle {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getVehicleDetails($userId) {
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMaintenanceRecord($data) {
        $stmt = $this->db->prepare("INSERT INTO maintenance_records 
                                  (vehicle_id, service_date, service_type, description, cost, next_service_date) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['vehicle_id'],
            $data['service_date'],
            $data['service_type'],
            $data['description'],
            $data['cost'],
            $data['next_service_date']
        ]);
    }

    public function updateInsurance($data) {
        $stmt = $this->db->prepare("INSERT INTO insurance_records 
                                  (vehicle_id, policy_number, provider, coverage_type, premium_amount, 
                                   start_date, end_date) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['vehicle_id'],
            $data['policy_number'],
            $data['provider'],
            $data['coverage_type'],
            $data['premium_amount'],
            $data['start_date'],
            $data['end_date']
        ]);
    }
}
?> 