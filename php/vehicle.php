php
<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_vehicle':
            handleAddVehicle($conn);
            break;
        case 'delete_vehicle':
            handleDeleteVehicle($conn);
            break;
        default:
            header('Location: ../html/vehicledetails.html?error=invalid_action');
            exit();
    }
}

function handleAddVehicle($conn) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../html/login.html?error=login_required');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $make = filter_input(INPUT_POST, 'make', FILTER_SANITIZE_STRING);
    $model = filter_input(INPUT_POST, 'model', FILTER_SANITIZE_STRING);
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
    $registration = filter_input(INPUT_POST, 'registration', FILTER_SANITIZE_STRING);

    try {
        $stmt = $conn->prepare("INSERT INTO vehicles (user_id, make, model, year, registration) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $user_id, $make, $model, $year, $registration);

        if ($stmt->execute()) {
            header('Location: ../html/dashboard.html?status=vehicle_added');
        } else {
            header('Location: ../html/vehicledetails.html?error=database_error');
        }
    } catch (Exception $e) {
        error_log("Error adding vehicle: " . $e->getMessage());
        header('Location: ../html/vehicledetails.html?error=server_error');
    }
}

function handleDeleteVehicle($conn) {
    if ($_SESSION['role'] !== 'admin') {
        die("Unauthorized action.");
    }

    $vehicleId = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);

    $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $vehicleId);

    if ($stmt->execute()) {
        echo "Vehicle deleted successfully.";
    } else {
        echo "Error deleting vehicle.";
    }
}
?>