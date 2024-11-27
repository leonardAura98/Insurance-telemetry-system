<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'fetch_users') {
    $sql = "SELECT id, username, email, id_number, role, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($users);
    exit();
}
?>