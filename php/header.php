<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/login.html?error=login_required');
    exit();
}
?>
