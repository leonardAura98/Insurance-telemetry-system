<?php
require 'db_connection.php'; // Include your database connection file

// Sample user data
$username = "admin";
$email = "admin@example.com";
$password = password_hash("adminpassword", PASSWORD_DEFAULT); // Hash the password
$role = "admin"; // Set role to admin

// Prepare SQL statement to insert user
$query = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$query->bind_param("ssss", $username, $email, $password, $role);

if ($query->execute()) {
    echo "Test user created successfully.";
} else {
    echo "Error: " . $query->error;
}

$query->close();
$conn->close();
?>