<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-brand">
            <h1>Telemetry Insurance</h1>
        </div>
        <ul class="nav-links">
            <li><a href="landing.html" <?php echo $current_page == 'landing.html' ? 'class="active"' : ''; ?>>Home</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.html">Dashboard</a></li>
                <li><a href="file-claim.html">File Claim</a></li>
                <li><a href="../php/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="signup.html" <?php echo $current_page == 'signup.html' ? 'class="active"' : ''; ?>>Sign Up</a></li>
                <li><a href="login.html" <?php echo $current_page == 'login.html' ? 'class="active"' : ''; ?>>Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</body>
</html> 