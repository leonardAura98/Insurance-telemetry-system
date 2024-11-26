<?php
require 'config.php'; // Include database configuration
session_start(); // Start the session

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized access'])); // Return error if not logged in
}

// Fetch vehicles
if ($_GET['action'] === 'fetch_vehicles') {
    $userId = $_SESSION['user_id'];
    $isAdmin = $_SESSION['role'] === 'admin';

    // Prepare SQL query to fetch vehicles
    $query = "SELECT v.*, u.fullname as owner_name 
              FROM vehicles v 
              JOIN users u ON v.owner_id = u.id";
    
    // If user is not admin, filter by owner
    if (!$isAdmin) {
        $query .= " WHERE v.owner_id = ?";
    }

    $stmt = $conn->prepare($query);
    if (!$isAdmin) {
        $stmt->bind_param("i", $userId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode($result->fetch_all(MYSQLI_ASSOC)); // Return vehicle data as JSON
}

// Additional actions (add, update, delete vehicles) would go here...
?>
