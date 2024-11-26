<?php
require 'config.php'; // Include database configuration
session_start();

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die(json_encode(['error' => 'Unauthorized access']));
}

// Get dashboard statistics
if (isset($_GET['action']) && $_GET['action'] === 'get_stats') {
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

    // Get recent activity (optional)
    $result = $conn->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 5");
    $stats['recent_activity'] = $result->fetch_all(MYSQLI_ASSOC);

    // Return JSON response
    echo json_encode($stats);
    exit; // Exit after sending the response
}

// Other actions (like fetching users, etc.) can go here...

?>