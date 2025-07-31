<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../models/Video.php';

// Check authentication
checkAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $tags = sanitizeInput($_POST['tags']);
    
    // Validation
    if (empty($title)) {
        $error = 'Please enter a video title.';
    } elseif (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a video file to upload.';
    } else {
        $file = $_FILES['video_file'];
        
        // Validate file
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, ALLOWED_VIDEO_TYPES)) {
            $error = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_VIDEO_TYPES);
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $error = 'File size exceeds maximum limit of ' . formatFileSize(MAX_FILE_SIZE);
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = UPLOAD_PATH . 'videos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Save to database
                $video = new Video();
                $video->title = $title;
                $video->description = $description;
                $video->file_path = $file_path;
                $video->tags = $tags;
                $video->uploaded_by = $_SESSION['user_id'];
                $video->company_id = $_SESSION['company_id'];
                $video->file_size = $file['size'];
                $video->format = $file_extension;
                $video->duration = 0; // Could be extracted using FFmpeg
                
                if ($video->create()) {
                    $success = 'Video uploaded successfully!';
                    // Clear form
                    $_POST = array();
                } else {
                    $error = 'Failed to save video information to database.';
                    // Delete uploaded file
                    unlink($file_path);
                }
            } else {
                $error = 'Failed to upload file.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video - Digital Archive Management</title>
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
                <h1>Upload New Video</h1>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Video Title:</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" class="form-control" rows="4" 
                                  placeholder="Enter video description..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags (comma-separated):</label>
                        <input type="text" id="tags" name="tags" class="form-control" 
                               placeholder="e.g., VHS, family, 1990s, wedding"
                               value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="video_file">Video File:</label>
                        <input type="file" id="video_file" name="video_file" class="form-control" 
                               accept="video/*" required onchange="validateVideoUpload(this)">
                        <small class="text-muted">
                            Supported formats: MP4, AVI, MOV, WMV, FLV, MKV. Maximum size: 500MB.
                        </small>
                        <div id="fileInfo" class="mt-1"></div>
                    </div>

                    <div class="form-group">
                        <div id="uploadProgress" style="display: none;">
                            <div class="progress-bar" style="width: 0%; background: #667eea; height: 20px; border-radius: 10px; text-align: center; color: white; line-height: 20px;">0%</div>
                        </div>
                    </div>

                    <button type="submit" class="btn" style="width: 100%;">Upload Video</button>
                </form>
            </div>

            <div class="card mt-2">
                <h3>Upload Guidelines</h3>
                <ul>
                    <li>Ensure your video files are in good quality</li>
                    <li>Use descriptive titles and tags for better organization</li>
                    <li>Large files may take longer to upload - please be patient</li>
                    <li>Supported formats: MP4, AVI, MOV, WMV, FLV, MKV</li>
                    <li>Maximum file size: 500MB per video</li>
                </ul>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Digital Archive Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../public/js/app.js"></script>
</body>
</html>