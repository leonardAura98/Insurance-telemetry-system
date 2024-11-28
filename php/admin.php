php
<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Fetch dashboard stats
if ($_GET['action'] === 'fetch_dashboard_stats') {
    $query = "SELECT COUNT(id) AS totalUsers FROM users";
    $result = $conn->query($query);
    $totalUsers = $result->fetch_assoc()['totalUsers'];

    $query = "SELECT COUNT(id) AS totalVehicles FROM vehicles";
    $result = $conn->query($query);
    $totalVehicles = $result->fetch_assoc()['totalVehicles'];

    $query = "SELECT COUNT(id) AS pendingClaims FROM claims WHERE status = 'pending'";
    $result = $conn->query($query);
    $pendingClaims = $result->fetch_assoc()['pendingClaims'];

    echo json_encode([
        'totalUsers' => $totalUsers,
        'totalVehicles' => $totalVehicles,
        'pendingClaims' => $pendingClaims
    ]);
    exit();
}
?>