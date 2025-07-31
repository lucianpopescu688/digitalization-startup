<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../models/Video.php';

// Check authentication
checkAuth();

$error = '';
$success = '';
$video_data = null;

// Get video ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$video_id = intval($_GET['id']);
$video = new Video();
$video_data = $video->findById($video_id);

// Check if video exists and user has access
if (!$video_data || $video_data['company_id'] != $_SESSION['company_id']) {
    header('Location: dashboard.php');
    exit();
}

// Check if user can edit (admin or owner)
if ($_SESSION['role'] !== 'admin' && $video_data['uploaded_by'] != $_SESSION['user_id']) {
    $error = 'You do not have permission to edit this video.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error) {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $tags = sanitizeInput($_POST['tags']);
    
    // Validation
    if (empty($title)) {
        $error = 'Video title is required.';
    } else {
        $video->id = $video_id;
        $video->title = $title;
        $video->description = $description;
        $video->tags = $tags;
        
        if ($video->update()) {
            $success = 'Video updated successfully!';
            // Refresh video data
            $video_data = $video->findById($video_id);
        } else {
            $error = 'Failed to update video.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Video - Digital Archive Management</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">Digital Archive</a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="upload.php">Upload Video</a></li>
                        <li><a href="account.php">My Account</a></li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a href="admin/users.php">Manage Users</a></li>
                            <li><a href="admin/companies.php">Manage Companies</a></li>
                        <?php endif; ?>
                        <li><a href="pages/the-box.php">The Box</a></li>
                        <li><a href="pages/contact.php">Contact</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    <span class="role-badge"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="auth/logout.php" class="btn btn-small">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="flex justify-between align-center mb-2">
                <h1>Edit Video</h1>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Edit Form -->
                <div class="card">
                    <h3>Video Information</h3>
                    
                    <?php if (!$error || $success): ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="title">Video Title:</label>
                                <input type="text" id="title" name="title" class="form-control" 
                                       value="<?php echo htmlspecialchars($video_data['title']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea id="description" name="description" class="form-control" rows="6" 
                                          placeholder="Enter video description..."><?php echo htmlspecialchars($video_data['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="tags">Tags (comma-separated):</label>
                                <input type="text" id="tags" name="tags" class="form-control" 
                                       placeholder="e.g., VHS, family, 1990s, wedding"
                                       value="<?php echo htmlspecialchars($video_data['tags'] ?? ''); ?>">
                            </div>

                            <button type="submit" class="btn" style="width: 100%;">Update Video</button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Video Details -->
                <div class="card">
                    <h3>Video Details</h3>
                    
                    <div class="video-thumbnail" style="height: 200px; margin-bottom: 1rem;">
                        <i>🎬</i>
                    </div>
                    
                    <div class="video-meta">
                        <p><strong>File Name:</strong> <?php echo htmlspecialchars(basename($video_data['file_path'])); ?></p>
                        <p><strong>Format:</strong> <?php echo htmlspecialchars($video_data['format'] ?? 'Unknown'); ?></p>
                        <p><strong>File Size:</strong> <?php echo formatFileSize($video_data['file_size'] ?? 0); ?></p>
                        <p><strong>Duration:</strong> <?php echo formatDuration($video_data['duration'] ?? 0); ?></p>
                        <p><strong>Uploaded by:</strong> <?php echo htmlspecialchars($video_data['uploader_name'] ?? 'Unknown'); ?></p>
                        <p><strong>Upload Date:</strong> <?php echo date('M j, Y g:i A', strtotime($video_data['created_at'])); ?></p>
                        <?php if ($video_data['updated_at'] && $video_data['updated_at'] !== $video_data['created_at']): ?>
                            <p><strong>Last Updated:</strong> <?php echo date('M j, Y g:i A', strtotime($video_data['updated_at'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($video_data['tags'])): ?>
                        <div class="video-tags mt-1">
                            <strong>Current Tags:</strong><br>
                            <?php 
                            $tags = explode(',', $video_data['tags']);
                            foreach ($tags as $tag): 
                                $tag = trim($tag);
                                if (!empty($tag)):
                            ?>
                                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-2">
                        <h4>Actions</h4>
                        <div class="flex gap-1">
                            <button onclick="viewVideo(<?php echo $video_data['id']; ?>)" class="btn btn-small">View Details</button>
                            <?php if ($_SESSION['role'] === 'admin' || $video_data['uploaded_by'] == $_SESSION['user_id']): ?>
                                <button onclick="deleteVideo(<?php echo $video_data['id']; ?>)" class="btn btn-small btn-danger">Delete Video</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Information -->
            <div class="card">
                <h3>File Management</h3>
                <div class="alert alert-info">
                    <strong>Note:</strong> To replace the video file, please delete this video and upload a new one. 
                    File replacement is not currently supported to maintain data integrity.
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="text-center">
                        <h4 style="color: #667eea;">📁 File Path</h4>
                        <p style="word-break: break-all; font-size: 0.9rem;"><?php echo htmlspecialchars($video_data['file_path']); ?></p>
                    </div>
                    <div class="text-center">
                        <h4 style="color: #667eea;">📊 File Info</h4>
                        <p>Size: <?php echo formatFileSize($video_data['file_size'] ?? 0); ?><br>
                        Format: <?php echo strtoupper($video_data['format'] ?? 'Unknown'); ?></p>
                    </div>
                    <div class="text-center">
                        <h4 style="color: #667eea;">⏱️ Duration</h4>
                        <p><?php echo formatDuration($video_data['duration'] ?? 0); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Video View Modal -->
    <div id="videoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Video Details</h2>
            </div>
            <div id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Digital Archive Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../public/js/app.js"></script>
    <script>
        // Delete confirmation
        function deleteVideo(videoId) {
            if (confirm('Are you sure you want to delete this video? This action cannot be undone.')) {
                window.location.href = '../controllers/delete-video.php?id=' + videoId + '&redirect=dashboard';
            }
        }
    </script>
</body>
</html>