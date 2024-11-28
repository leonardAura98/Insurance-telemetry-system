<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/login.html?error=login_required');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'fetch_premiums':
            fetchPremiums($conn);
            break;
        case 'save_premium':
            savePremium($conn);
            break;
        case 'delete_premium':
            deletePremium($conn);
            break;
        case 'update_premium_status':
            updatePremiumStatus($conn);
            break;
        default:
            header('Location: ../html/dashboard.html?error=invalid_action');
            exit();
    }
}

function fetchPremiums($conn) {
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
        $query .= " AND v.reg_no LIKE ?";
    }
    if ($status) {
        $query .= " AND p.status = ?";
    }

    $stmt = $conn->prepare($query);
    if (!$isAdmin) {
        $stmt->bind_param("i", $userId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function savePremium($conn) {
    $vehicleId = $_POST['vehicle_id'];
    $amount = $_POST['amount'];
    $userId = $_SESSION['user_id'];

    $conn->begin_transaction();

    try {
        if ($_SESSION['role'] !== 'admin') {
            $checkQuery = $conn->prepare("SELECT id FROM vehicles WHERE id = ? AND owner_id = ?");
            $checkQuery->bind_param("ii", $vehicleId, $userId);
            $checkQuery->execute();
            if ($checkQuery->get_result()->num_rows === 0) {
                throw new Exception("Unauthorized to add premium for this vehicle.");
            }
        }

        $query = $conn->prepare("INSERT INTO premiums (vehicle_id, premium_amount, status, start_date, end_date) 
                               VALUES (?, ?, 'pending', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))");
        $query->bind_param("id", $vehicleId, $amount);

        if ($query->execute()) {
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

function deletePremium($conn) {
    if ($_SESSION['role'] !== 'admin') {
        die("Unauthorized action.");
    }

    $premiumId = $_POST['premium_id'];

    $query = $conn->prepare("DELETE FROM premiums WHERE id = ?");
    $query->bind_param("i", $premiumId);

    if ($query->execute()) {
        $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'premium_delete', 'Deleted premium record')");
        $activityQuery->bind_param("i", $_SESSION['user_id']);
        $activityQuery->execute();

        echo "Premium deleted successfully.";
    } else {
        echo "Error deleting premium.";
    }
}

function updatePremiumStatus($conn) {
    if ($_SESSION['role'] !== 'admin') {
        die("Unauthorized action.");
    }

    $premiumId = $_POST['premium_id'];
    $status = $_POST['status'];

    $query = $conn->prepare("UPDATE premiums SET status = ? WHERE id = ?");
    $query->bind_param("si", $status, $premiumId);

    if ($query->execute()) {
        $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'premium_update', 'Updated premium status')");
        $activityQuery->bind_param("i", $_SESSION['user_id']);
        $activityQuery->execute();

        echo "Premium status updated successfully.";
    } else {
        echo "Error updating premium status.";
    }
}
?>
