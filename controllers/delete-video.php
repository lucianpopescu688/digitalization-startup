<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../models/Video.php';

// Check authentication
checkAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$video_id = intval($_POST['id']);
$video = new Video();
$video_data = $video->findById($video_id);

if (!$video_data) {
    echo json_encode(['success' => false, 'message' => 'Video not found']);
    exit();
}

// Check if user has permission to delete (admin or owner)
if ($_SESSION['role'] !== 'admin' && $video_data['uploaded_by'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

// Check if video belongs to same company
if ($video_data['company_id'] != $_SESSION['company_id']) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Delete the video file if it exists
if (!empty($video_data['file_path']) && file_exists($video_data['file_path'])) {
    unlink($video_data['file_path']);
}

// Delete the thumbnail if it exists
if (!empty($video_data['thumbnail_path']) && file_exists($video_data['thumbnail_path'])) {
    unlink($video_data['thumbnail_path']);
}

// Delete from database
$video->id = $video_id;
if ($video->delete()) {
    echo json_encode(['success' => true, 'message' => 'Video deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete video from database']);
}
?>