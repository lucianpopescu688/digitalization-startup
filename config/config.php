<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'digitalization_startup');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('BASE_URL', 'http://localhost');
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 500 * 1024 * 1024); // 500MB
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv']);

// Session configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 hours

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();
?>