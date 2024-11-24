<?php
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        die("Passwords do not match");
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        
        if ($stmt->rowCount() > 0) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
            $stmt->execute([$hashed_password, $token]);

            header("Location: ../login.html?reset=success");
            exit();
        } else {
            die("Invalid or expired reset token");
        }
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?> 