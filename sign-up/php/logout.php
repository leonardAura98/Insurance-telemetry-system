<?php
session_start();
session_destroy();

// Clear all session cookies
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Redirect to management signin page
header('Location: ../html/management_signin.html?message=logout_success');
exit();
?> 