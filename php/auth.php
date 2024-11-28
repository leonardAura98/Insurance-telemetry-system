<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $usernameOrEmail = $_POST['usernameOrEmail'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                if ($user['role'] === 'admin') {
                    header('Location: ../html/admin_dashboard.html');
                } else {
                    header('Location: ../html/home.html');
                }
                exit();
            } else {
                header('Location: ../html/login.html?error=invalid_credentials');
                exit();
            }
        } else {
            header('Location: ../html/login.html?error=invalid_credentials');
            exit();
        }
    }
}
?>
