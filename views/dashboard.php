<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../models/User.php';
require_once '../models/Video.php';

// Check authentication
checkAuth();

$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'created_at';
$order = isset($_GET['order']) ? sanitizeInput($_GET['order']) : 'DESC';

// Get videos for user's company
$video = new Video();
$videos = $video->getAllByCompany($_SESSION['company_id'], $search, $sort, $order);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Digital Archive Management</title>
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
                <h1>Video Archive Dashboard</h1>
                <a href="upload.php" class="btn">Upload New Video</a>
            </div>

            <!-- Search and Filter -->
            <div class="card">
                <form method="GET" action="">
                    <div class="search-filter">
                        <div class="form-group search-input">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search videos by title, description, or tags..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group filter-select">
                            <select name="sort" class="form-control">
                                <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Date Created</option>
                                <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title</option>
                                <option value="file_size" <?php echo $sort === 'file_size' ? 'selected' : ''; ?>>File Size</option>
                                <option value="duration" <?php echo $sort === 'duration' ? 'selected' : ''; ?>>Duration</option>
                            </select>
                        </div>
                        <div class="form-group filter-select">
                            <select name="order" class="form-control">
                                <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                                <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                            </select>
                        </div>
                        <button type="submit" class="btn">Search</button>
                    </div>
                </form>
            </div>

            <!-- Video Grid -->
            <?php if (empty($videos)): ?>
                <div class="card text-center">
                    <h3>No videos found</h3>
                    <p>Upload your first video to get started!</p>
                    <a href="upload.php" class="btn">Upload Video</a>
                </div>
            <?php else: ?>
                <div class="video-grid">
                    <?php foreach ($videos as $vid): ?>
                        <div class="video-card">
                            <div class="video-thumbnail">
                                <i>🎬</i>
                            </div>
                            <div class="video-info">
                                <h3 class="video-title"><?php echo htmlspecialchars($vid['title']); ?></h3>
                                <div class="video-meta">
                                    <p><strong>Format:</strong> <?php echo htmlspecialchars($vid['format'] ?? 'Unknown'); ?></p>
                                    <p><strong>Size:</strong> <?php echo formatFileSize($vid['file_size'] ?? 0); ?></p>
                                    <p><strong>Duration:</strong> <?php echo formatDuration($vid['duration'] ?? 0); ?></p>
                                    <p><strong>Uploaded by:</strong> <?php echo htmlspecialchars($vid['uploader_name'] ?? 'Unknown'); ?></p>
                                    <p><strong>Date:</strong> <?php echo date('M j, Y', strtotime($vid['created_at'])); ?></p>
                                </div>
                                
                                <?php if (!empty($vid['tags'])): ?>
                                    <div class="video-tags">
                                        <?php 
                                        $tags = explode(',', $vid['tags']);
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
                                
                                <div class="video-actions">
                                    <button onclick="viewVideo(<?php echo $vid['id']; ?>)" class="btn btn-small">View</button>
                                    <a href="edit-video.php?id=<?php echo $vid['id']; ?>" class="btn btn-small btn-secondary">Edit</a>
                                    <?php if ($_SESSION['role'] === 'admin' || $vid['uploaded_by'] == $_SESSION['user_id']): ?>
                                        <button onclick="deleteVideo(<?php echo $vid['id']; ?>)" class="btn btn-small btn-danger">Delete</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
</body>
</html>