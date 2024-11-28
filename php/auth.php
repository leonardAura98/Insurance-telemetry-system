<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Check if all required fields are present
    if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['email'])) {
        throw new Exception("Missing required fields");
    }

    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Database connection
    $conn = new PDO(
        "mysql:host=localhost;dbname=insurance_system",
        "root",
        "",
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    // Check if username or email already exists
    $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $check->execute([$_POST['username'], $_POST['email']]);
    if ($check->fetchColumn() > 0) {
        throw new Exception("Username or email already exists");
    }

    // Handle file upload
    if (!isset($_FILES['id_photo']) || $_FILES['id_photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error: " . $_FILES['id_photo']['error']);
    }

    $upload_dir = "../uploads/";
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($_FILES['id_photo']['type'], $allowed_types)) {
        throw new Exception("Invalid file type. Only JPG, JPEG & PNG files are allowed.");
    }

    $file_extension = pathinfo($_FILES['id_photo']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $file_extension;
    $target_file = $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES['id_photo']['tmp_name'], $target_file)) {
        throw new Exception("Failed to move uploaded file");
    }

    // Insert user data
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, id_photo) VALUES (?, ?, ?, ?)");
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    if (!$stmt->execute([
        $_POST['username'],
        $hashed_password,
        $_POST['email'],
        $file_name
    ])) {
        throw new Exception("Database insertion failed");
    }

    echo json_encode(['status' => 'success', 'message' => 'Registration successful']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
