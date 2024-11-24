<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: ../dashboard.php"); // Create a dashboard page
            exit();
        } else {
            header("Location: ../login.html?error=invalid");
            exit();
        }
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?> 