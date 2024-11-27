<?php
session_start();
require_once 'config.php';
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'signup') {
        // Handle signup
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $id_number = $_POST['id_number'];
        $area_operation = $_POST['area_operation'];
        
        // Handle file upload
        $id_photo_path = '';
        if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === 0) {
            $upload_dir = '../uploads/';
            $file_name = uniqid() . '_' . $_FILES['id_photo']['name'];
            $id_photo_path = $upload_dir . $file_name;
            
            if (!move_uploaded_file($_FILES['id_photo']['tmp_name'], $id_photo_path)) {
                header('Location: ../html/signup.html?error=file_upload_error');
                exit();
            }
        }

        // Insert into database
        $sql = "INSERT INTO users (username, email, password, id_number, area_operation, id_photo_path) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", 
                $username, 
                $email, 
                $password, 
                $id_number, 
                $area_operation, 
                $id_photo_path
            );
            
            if ($stmt->execute()) {
                header('Location: ../html/login.html?message=signup_success');
                exit();
            } else {
                header('Location: ../html/signup.html?error=registration_failed');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ../html/signup.html?error=user_exists');
            exit();
        }
    }
}
?>