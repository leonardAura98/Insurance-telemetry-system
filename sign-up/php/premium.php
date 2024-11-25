<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized access']));
}

// Fetch premiums
if ($_GET['action'] === 'fetch_premiums') {
    $userId = $_SESSION['user_id'];
    $isAdmin = $_SESSION['role'] === 'admin';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    $query = "SELECT p.*, v.reg_no, u.fullname as owner_name 
              FROM premiums p 
              JOIN vehicles v ON p.vehicle_id = v.id 
              JOIN users u ON v.owner_id = u.id 
              WHERE 1=1";

    if (!$isAdmin) {
        $query .= " AND v.owner_id = ?";
    }
    if ($search) {
        $query .= " AND v.reg_no LIKE '%$search%'";
    }
    if ($status) {
        $query .= " AND p.status = '$status'";
    }

    $stmt = $conn->prepare($query);
    if (!$isAdmin) {
        $stmt->bind_param("i", $userId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// Save premium
if ($_POST['action'] === 'save_premium') {
    $vehicleId = $_POST['vehicle_id'];
    $amount = $_POST['amount'];
    $userId = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if user owns the vehicle or is admin
        if ($_SESSION['role'] !== 'admin') {
            $checkQuery = $conn->prepare("SELECT id FROM vehicles WHERE id = ? AND owner_id = ?");
            $checkQuery->bind_param("ii", $vehicleId, $userId);
            $checkQuery->execute();
            if ($checkQuery->get_result()->num_rows === 0) {
                throw new Exception("Unauthorized to add premium for this vehicle.");
            }
        }

        // Insert premium
        $query = $conn->prepare("INSERT INTO premiums (vehicle_id, premium_amount, status, start_date, end_date) 
                               VALUES (?, ?, 'pending', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))");
        $query->bind_param("id", $vehicleId, $amount);
        
        if ($query->execute()) {
            // Log activity
            $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'premium_add', 'Added new premium')");
            $activityQuery->bind_param("i", $userId);
            $activityQuery->execute();

            $conn->commit();
            echo "Premium saved successfully.";
        } else {
            throw new Exception("Error saving premium.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Delete premium
if ($_POST['action'] === 'delete_premium') {
    if ($_SESSION['role'] !== 'admin') {
        die("Unauthorized action.");
    }

    $premiumId = $_POST['premium_id'];

    $query = $conn->prepare("DELETE FROM premiums WHERE id = ?");
    $query->bind_param("i", $premiumId);
    
    if ($query->execute()) {
        // Log activity
        $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'premium_delete', 'Deleted premium record')");
        $activityQuery->bind_param("i", $_SESSION['user_id']);
        $activityQuery->execute();

        echo "Premium deleted successfully.";
    } else {
        echo "Error deleting premium.";
    }
}

// Update premium status
if ($_POST['action'] === 'update_premium_status') {
    if ($_SESSION['role'] !== 'admin') {
        die("Unauthorized action.");
    }

    $premiumId = $_POST['premium_id'];
    $status = $_POST['status'];

    $query = $conn->prepare("UPDATE premiums SET status = ? WHERE id = ?");
    $query->bind_param("si", $status, $premiumId);
    
    if ($query->execute()) {
        // Log activity
        $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'premium_update', 'Updated premium status')");
        $activityQuery->bind_param("i", $_SESSION['user_id']);
        $activityQuery->execute();

        echo "Premium status updated successfully.";
    } else {
        echo "Error updating premium status.";
    }
}
?>
