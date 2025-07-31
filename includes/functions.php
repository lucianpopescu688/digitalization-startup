<?php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/views/auth/login.php');
        exit();
    }
}

function checkRole($required_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $required_roles)) {
        header('Location: ' . BASE_URL . '/views/dashboard.php');
        exit();
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateSessionId() {
    return bin2hex(random_bytes(64));
}

function formatFileSize($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    } else {
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
?>