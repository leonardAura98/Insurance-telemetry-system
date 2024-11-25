<?php
require 'config.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if ($_GET['action'] === 'fetch_users') {
    $result = $conn->query("SELECT id, fullname, email, username FROM users");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

if ($_POST['action'] === 'delete_user') {
    $userId = $_POST['id'];
    $query = $conn->prepare("DELETE FROM users WHERE id = ?");
    $query->bind_param("i", $userId);
    if ($query->execute()) {
        echo "User deleted.";
    } else {
        echo "Error deleting user.";
    }
}
?>
