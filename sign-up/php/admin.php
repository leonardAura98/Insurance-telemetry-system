<?php
require 'config.php';
session_start();

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die(json_encode(['error' => 'Unauthorized access']));
}

// Get dashboard statistics
if ($_GET['action'] === 'get_stats') {
    $stats = [
        'users' => 0,
        'vehicles' => 0,
        'premiums' => 0,
        'recent_activity' => []
    ];

    // Get user count
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $stats['users'] = $result->fetch_assoc()['count'];

    // Get vehicle count
    $result = $conn->query("SELECT COUNT(*) as count FROM vehicles");
    $stats['vehicles'] = $result->fetch_assoc()['count'];

    // Get active premiums count
    $result = $conn->query("SELECT COUNT(*) as count FROM premiums WHERE status = 'active'");
    $stats['premiums'] = $result->fetch_assoc()['count'];

    // Get recent activity
    $result = $conn->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 5");
    $stats['recent_activity'] = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($stats);
}

// Fetch users
if ($_GET['action'] === 'fetch_users') {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $role = isset($_GET['role']) ? $_GET['role'] : '';

    $query = "SELECT id, fullname, email, username, role FROM users WHERE 1=1";
    if ($search) {
        $query .= " AND (fullname LIKE '%$search%' OR email LIKE '%$search%' OR username LIKE '%$search%')";
    }
    if ($role) {
        $query .= " AND role = '$role'";
    }

    $result = $conn->query($query);
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// Delete user
if ($_POST['action'] === 'delete_user') {
    $userId = $_POST['id'];
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete user's vehicles and related premiums (cascade)
        $query = $conn->prepare("DELETE FROM users WHERE id = ?");
        $query->bind_param("i", $userId);
        
        if ($query->execute()) {
            // Log activity
            $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'user_deletion', 'Admin deleted user')");
            $activityQuery->bind_param("i", $_SESSION['user_id']);
            $activityQuery->execute();

            $conn->commit();
            echo "User deleted successfully.";
        } else {
            throw new Exception("Error deleting user");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Edit user
if ($_POST['action'] === 'edit_user') {
    $userId = $_POST['id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $query = $conn->prepare("UPDATE users SET fullname = ?, email = ?, role = ? WHERE id = ?");
    $query->bind_param("sssi", $fullname, $email, $role, $userId);
    
    if ($query->execute()) {
        echo "User updated successfully.";
    } else {
        echo "Error updating user.";
    }
}
?>
