<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: views/auth/login.php');
    exit();
} else {
    // Redirect to dashboard if authenticated
    header('Location: views/dashboard.php');
    exit();
}
?>