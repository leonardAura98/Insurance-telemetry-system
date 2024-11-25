<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized access']));
}

// Fetch vehicles
if ($_GET['action'] === 'fetch_vehicles') {
    $userId = $_SESSION['user_id'];
    $isAdmin = $_SESSION['role'] === 'admin';

    $query = "SELECT v.*, u.fullname as owner_name 
              FROM vehicles v 
              JOIN users u ON v.owner_id = u.id";
    
    if (!$isAdmin) {
        $query .= " WHERE v.owner_id = ?";
    }

    $stmt = $conn->prepare($query);
    if (!$isAdmin) {
        $stmt->bind_param("i", $userId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// Add vehicle
if ($_POST['action'] === 'add_vehicle') {
    $carReg = $_POST['car_reg'];
    $carModel = $_POST['car_model'];
    $carYear = $_POST['car_year'];
    $carType = $_POST['car_type'];
    $ownerId = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if registration number already exists
        $checkQuery = $conn->prepare("SELECT id FROM vehicles WHERE reg_no = ?");
        $checkQuery->bind_param("s", $carReg);
        $checkQuery->execute();
        if ($checkQuery->get_result()->num_rows > 0) {
            throw new Exception("Vehicle with this registration number already exists.");
        }

        // Insert vehicle
        $query = $conn->prepare("INSERT INTO vehicles (reg_no, make_model, year, type, owner_id) VALUES (?, ?, ?, ?, ?)");
        $query->bind_param("ssisi", $carReg, $carModel, $carYear, $carType, $ownerId);
        
        if ($query->execute()) {
            // Log activity
            $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'vehicle_add', 'Added new vehicle')");
            $activityQuery->bind_param("i", $ownerId);
            $activityQuery->execute();

            $conn->commit();
            echo "Vehicle added successfully.";
        } else {
            throw new Exception("Error adding vehicle.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Delete vehicle
if ($_POST['action'] === 'delete_vehicle') {
    $vehicleId = $_POST['vehicle_id'];
    $userId = $_SESSION['user_id'];
    $isAdmin = $_SESSION['role'] === 'admin';

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if user owns the vehicle or is admin
        if (!$isAdmin) {
            $checkQuery = $conn->prepare("SELECT id FROM vehicles WHERE id = ? AND owner_id = ?");
            $checkQuery->bind_param("ii", $vehicleId, $userId);
            $checkQuery->execute();
            if ($checkQuery->get_result()->num_rows === 0) {
                throw new Exception("Unauthorized to delete this vehicle.");
            }
        }

        // Delete vehicle
        $query = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
        $query->bind_param("i", $vehicleId);
        
        if ($query->execute()) {
            // Log activity
            $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'vehicle_delete', 'Deleted vehicle')");
            $activityQuery->bind_param("i", $userId);
            $activityQuery->execute();

            $conn->commit();
            echo "Vehicle deleted successfully.";
        } else {
            throw new Exception("Error deleting vehicle.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Update vehicle
if ($_POST['action'] === 'update_vehicle') {
    $vehicleId = $_POST['vehicle_id'];
    $carReg = $_POST['car_reg'];
    $carModel = $_POST['car_model'];
    $carYear = $_POST['car_year'];
    $carType = $_POST['car_type'];
    $userId = $_SESSION['user_id'];
    $isAdmin = $_SESSION['role'] === 'admin';

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if user owns the vehicle or is admin
        if (!$isAdmin) {
            $checkQuery = $conn->prepare("SELECT id FROM vehicles WHERE id = ? AND owner_id = ?");
            $checkQuery->bind_param("ii", $vehicleId, $userId);
            $checkQuery->execute();
            if ($checkQuery->get_result()->num_rows === 0) {
                throw new Exception("Unauthorized to update this vehicle.");
            }
        }

        // Update vehicle
        $query = $conn->prepare("UPDATE vehicles SET reg_no = ?, make_model = ?, year = ?, type = ? WHERE id = ?");
        $query->bind_param("ssisi", $carReg, $carModel, $carYear, $carType, $vehicleId);
        
        if ($query->execute()) {
            // Log activity
            $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'vehicle_update', 'Updated vehicle details')");
            $activityQuery->bind_param("i", $userId);
            $activityQuery->execute();

            $conn->commit();
            echo "Vehicle updated successfully.";
        } else {
            throw new Exception("Error updating vehicle.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>
