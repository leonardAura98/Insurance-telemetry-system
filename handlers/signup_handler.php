<?php
require_once '../classes/Database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Get form data
        $fullname = $_POST['fullname'];
        $national_id = $_POST['national-id'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $car_registration = $_POST['car-reg'];
        $car_make_model = $_POST['car-model'];

        // Begin transaction
        $conn->beginTransaction();

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, national_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $fullname, $national_id]);
        
        $user_id = $conn->lastInsertId();

        // Insert vehicle details
        $stmt = $conn->prepare("INSERT INTO driver_details (user_id, car_registration, car_make_model) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $car_registration, $car_make_model]);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Registration successful!'
        ]);

    } catch (Exception $e) {
        if ($conn) {
            $conn->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 