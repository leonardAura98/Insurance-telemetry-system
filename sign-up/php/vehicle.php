<?php
require 'config.php';

// Fetch vehicle details by vehicle ID
function getVehicleById($vehicleId) {
    global $conn;
    $query = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
    $query->bind_param("i", $vehicleId);
    $query->execute();
    $result = $query->get_result();
    return $result->fetch_assoc();
}

// Add a new vehicle to the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add_vehicle') {
        $carReg = $_POST['car_reg'];
        $carModel = $_POST['car_model'];
        $ownerId = $_POST['owner_id'];

        // Insert vehicle details into the database
        $query = $conn->prepare("INSERT INTO vehicles (reg_no, make_model, owner_id) VALUES (?, ?, ?)");
        $query->bind_param("ssi", $carReg, $carModel, $ownerId);

        if ($query->execute()) {
            echo "Vehicle added successfully.";
        } else {
            echo "Error adding vehicle.";
        }
    }

    // Update vehicle details
    if ($_POST['action'] === 'update_vehicle') {
        $vehicleId = $_POST['vehicle_id'];
        $carReg = $_POST['car_reg'];
        $carModel = $_POST['car_model'];

        // Update vehicle details
        $query = $conn->prepare("UPDATE vehicles SET reg_no = ?, make_model = ? WHERE id = ?");
        $query->bind_param("ssi", $carReg, $carModel, $vehicleId);

        if ($query->execute()) {
            echo "Vehicle updated successfully.";
        } else {
            echo "Error updating vehicle.";
        }
    }

    // Delete vehicle
    if ($_POST['action'] === 'delete_vehicle') {
        $vehicleId = $_POST['vehicle_id'];

        // Delete the vehicle from the database
        $query = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
        $query->bind_param("i", $vehicleId);

        if ($query->execute()) {
            echo "Vehicle deleted successfully.";
        } else {
            echo "Error deleting vehicle.";
        }
    }
}
?>
