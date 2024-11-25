<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // Login
    if ($action === 'login') {
        $usernameOrEmail = $_POST['usernameOrEmail'];
        $password = $_POST['password'];

        $query = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $query->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../html/home.html");
        } else {
            echo "Invalid login credentials.";
        }
    }

    // Signup
    if ($action === 'signup') {
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $query = $conn->prepare("INSERT INTO users (fullname, email, username, password) VALUES (?, ?, ?, ?)");
        $query->bind_param("ssss", $fullname, $email, $username, $password);

        if ($query->execute()) {
            header("Location: ../html/login.html");
        } else {
            echo "Error: Could not register user.";
        }
    }

    // Reset Password
    if ($action === 'reset_password') {
        $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $confirm_password = password_hash($_POST['confirm_password'], PASSWORD_BCRYPT);

        if ($new_password === $confirm_password) {
            $query = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $query->bind_param("ss", $new_password, $_POST['email']);
            if ($query->execute()) {
                echo "Password updated successfully.";
            }
        } else {
            echo "Passwords do not match.";
        }
    }
}
?>
