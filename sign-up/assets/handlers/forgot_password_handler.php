<?php
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    try {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->execute([$token, $expiry, $email]);

        if ($stmt->rowCount() > 0) {
            // In a real application, you would send an email here
            // For testing, we'll redirect to the reset page directly
            header("Location: ../resetpassword.html?token=" . $token);
            exit();
        } else {
            header("Location: ../forgotpass.html?error=email_not_found");
            exit();
        }
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?> 