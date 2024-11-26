<?php
require 'config.php'; // Include database configuration
session_start(); // Start the session

// Function to generate a secure token
function generateToken() {
    return bin2hex(random_bytes(32));
}

// Handle POST requests for login, signup, and password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // Login
    if ($action === 'login') {
        $usernameOrEmail = $_POST['usernameOrEmail'];
        $password = $_POST['password'];

        // Prepare SQL statement to fetch user by email or username
        $query = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $query->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_assoc();

        // Verify password and set session variables
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            // Log activity
            $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'login', 'User logged in')");
            $activityQuery->bind_param("i", $user['id']);
            $activityQuery->execute();

            // Redirect based on user role
            if ($user['role'] === 'admin') {
                header("Location: ../html/admin_dashboard.html");
            } else {
                header("Location: ../html/home.html");
            }
        } else {
            echo "Invalid login credentials.";
        }
    }

    // Additional actions (signup, forgot password) would go here...
}

// Logout
if ($_GET['action'] === 'logout') {
    if (isset($_SESSION['user_id'])) {
        // Log activity before destroying session
        $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'logout', 'User logged out')");
        $activityQuery->bind_param("i", $_SESSION['user_id']);
        $activityQuery->execute();
    }
    
    session_destroy(); // Destroy the session
    header("Location: ../html/login.html"); // Redirect to login page
}
?>
