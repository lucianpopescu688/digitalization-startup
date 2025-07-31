<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../models/Video.php';

// Check authentication
checkAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$video_id = intval($_GET['id']);
$video = new Video();
$video_data = $video->findById($video_id);

if (!$video_data) {
    echo json_encode(['success' => false, 'message' => 'Video not found']);
    exit();
}

// Check if user has access to this video (same company)
if ($video_data['company_id'] != $_SESSION['company_id']) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

echo json_encode(['success' => true, 'video' => $video_data]);
?>