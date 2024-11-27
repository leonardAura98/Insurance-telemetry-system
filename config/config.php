<?php
// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'vehicle_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// Auto-load classes
spl_autoload_register(function($className) {
    $classFile = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
}); 