<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'signup':
            handleSignup($conn);
            break;
        case 'login':
            handleLogin($conn);
            break;
        case 'reset_password':
            handlePasswordReset($conn);
            break;
        default:
            header('Location: ../html/landing.html');
            exit();
    }
}

function handleSignup($conn) {
    // Validate input
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $id_number = filter_input(INPUT_POST, 'id_number', FILTER_SANITIZE_STRING);
    $area_operation = filter_input(INPUT_POST, 'area_operation', FILTER_SANITIZE_STRING);
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_SANITIZE_STRING);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_SANITIZE_STRING);

    // Validate passwords match
    if ($password !== $confirm_password) {
        header('Location: ../html/signup.html?error=passwords_mismatch');
        exit();
    }

    // Handle ID photo upload
    $id_photo_path = handleFileUpload('id_photo');
    if ($id_photo_path === false) {
        header('Location: ../html/signup.html?error=file_upload');
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if user already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            header('Location: ../html/signup.html?error=user_exists');
            exit();
        }

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, id_number, area_operation, id_photo_path, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $username, $email, $hashed_password, $id_number, $area_operation, $id_photo_path, $latitude, $longitude);
        
        if ($stmt->execute()) {
            header('Location: ../html/login.html?signup=success');
        } else {
            header('Location: ../html/signup.html?error=database');
        }
    } catch (Exception $e) {
        header('Location: ../html/signup.html?error=server');
    }
}

function handleLogin($conn) {
    $usernameOrEmail = filter_input(INPUT_POST, 'usernameOrEmail', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                header('Location: ../html/dashboard.html');
            } else {
                header('Location: ../html/login.html?error=true');
            }
        } else {
            header('Location: ../html/login.html?error=true');
        }
    } catch (Exception $e) {
        header('Location: ../html/login.html?error=true');
    }
}

function handlePasswordReset($conn) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    try {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
            $stmt->bind_param("sss", $token, $expiry, $email);
            $stmt->execute();

            // Send reset email (implement your email sending logic here)
            sendResetEmail($email, $token);

            header('Location: ../html/forgotpass.html?status=email_sent');
        } else {
            header('Location: ../html/forgotpass.html?error=email_not_found');
        }
    } catch (Exception $e) {
        header('Location: ../html/forgotpass.html?error=server');
    }
}

function handleFileUpload($file_field) {
    if (!isset($_FILES[$file_field])) {
        return false;
    }

    $file = $_FILES[$file_field];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Validate file
    if (!in_array($file['type'], $allowed_types) || $file['size'] > $max_size) {
        return false;
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $upload_path = '../uploads/id_photos/' . $filename;

    // Create directory if it doesn't exist
    if (!file_exists('../uploads/id_photos/')) {
        mkdir('../uploads/id_photos/', 0777, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $upload_path;
    }

    return false;
}

function sendResetEmail($email, $token) {
    // Implement your email sending logic here
    // You might want to use PHPMailer or similar library
    $reset_link = "http://yourwebsite.com/reset-password.php?token=" . $token;
    
    // For now, we'll just simulate email sending
    error_log("Password reset link for $email: $reset_link");
}
?>