<?php
require 'config.php';
session_start();

function generateToken() {
    return bin2hex(random_bytes(32));
}

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
            
            // Log activity
            $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'login', 'User logged in')");
            $activityQuery->bind_param("i", $user['id']);
            $activityQuery->execute();

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../html/admin_dashboard.html");
            } else {
                header("Location: ../html/home.html");
            }
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
        $car_reg = $_POST['car_reg'];
        $car_model = $_POST['car_model'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert user
            $userQuery = $conn->prepare("INSERT INTO users (fullname, email, username, password) VALUES (?, ?, ?, ?)");
            $userQuery->bind_param("ssss", $fullname, $email, $username, $password);
            $userQuery->execute();
            $userId = $conn->insert_id;

            // Insert vehicle
            $vehicleQuery = $conn->prepare("INSERT INTO vehicles (reg_no, make_model, owner_id) VALUES (?, ?, ?)");
            $vehicleQuery->bind_param("ssi", $car_reg, $car_model, $userId);
            $vehicleQuery->execute();

            // Log activity
            $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'signup', 'New user registration')");
            $activityQuery->bind_param("i", $userId);
            $activityQuery->execute();

            $conn->commit();
            header("Location: ../html/login.html");
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }

    // Reset Password
    if ($action === 'reset_password') {
        $token = $_POST['token'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

        // Verify token and update password
        $query = $conn->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
        $query->bind_param("s", $token);
        $query->execute();
        $result = $query->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $updateQuery = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateQuery->bind_param("si", $new_password, $user['user_id']);
            
            if ($updateQuery->execute()) {
                // Delete used token
                $deleteQuery = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
                $deleteQuery->bind_param("s", $token);
                $deleteQuery->execute();
                
                echo "Password updated successfully.";
                header("Location: ../html/login.html");
            }
        } else {
            echo "Invalid or expired token.";
        }
    }

    // Forgot Password
    if ($action === 'forgot_password') {
        $email = $_POST['email'];
        $token = generateToken();
        
        $query = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Store token
            $tokenQuery = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
            $tokenQuery->bind_param("is", $user['id'], $token);
            
            if ($tokenQuery->execute()) {
                // In a real application, send email with reset link
                $resetLink = "http://yourdomain.com/html/resetpassword.html?token=" . $token;
                echo "Reset link has been sent to your email.";
            }
        } else {
            echo "Email not found.";
        }
    }
}

// Logout
if ($_GET['action'] === 'logout') {
    if (isset($_SESSION['user_id'])) {
        // Log activity before destroying session
        $activityQuery = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'logout', 'User logged out')");
        $activityQuery->bind_param("i", $_SESSION['user_id']);
        $activityQuery->execute();
    }
    
    session_destroy();
    header("Location: ../html/login.html");
}
?>
