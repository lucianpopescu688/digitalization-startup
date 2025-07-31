<?php
require_once '../../config/config.php';
require_once '../../models/Session.php';

// Destroy session
$session = new Session();
$session->destroy();

// Clear all session data
$_SESSION = array();

// Redirect to login
header('Location: login.php');
exit();
?>