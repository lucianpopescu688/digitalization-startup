<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../models/User.php';

// Check authentication
checkAuth();

$error = '';
$success = '';

// Get current user data
$user = new User();
$user->findById($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($email)) {
        $error = 'Username and email are required.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if username is taken by another user
        $temp_user = new User();
        if ($temp_user->usernameExists($username) && $temp_user->username !== $user->username) {
            $error = 'Username is already taken by another user.';
        } else {
            // Update basic info
            $user->username = $username;
            $user->email = $email;
            
            // Handle password change
            $password_updated = false;
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $error = 'All password fields are required to change password.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters long.';
                } else {
                    // Verify current password
                    $temp_user = new User();
                    if ($temp_user->login($_SESSION['username'], $current_password)) {
                        $user->password = password_hash($new_password, PASSWORD_DEFAULT);
                        $password_updated = true;
                    } else {
                        $error = 'Current password is incorrect.';
                    }
                }
            }
            
            if (!$error) {
                if ($user->update()) {
                    $_SESSION['username'] = $user->username;
                    $success = 'Account updated successfully!' . ($password_updated ? ' Password changed.' : '');
                } else {
                    $error = 'Failed to update account.';
                }
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
    <title>My Account - Digital Archive Management</title>
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
                <h1>My Account</h1>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <h3>Account Information</h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($user->username); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user->email ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Role:</label>
                        <input type="text" class="form-control" 
                               value="<?php echo ucfirst($user->role); ?>" disabled>
                        <small class="text-muted">Contact an administrator to change your role.</small>
                    </div>

                    <hr style="margin: 2rem 0;">
                    
                    <h4>Change Password (Optional)</h4>
                    <small class="text-muted">Leave blank to keep current password</small>

                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                    </div>

                    <button type="submit" class="btn" style="width: 100%;">Update Account</button>
                </form>
            </div>

            <div class="card" style="max-width: 600px; margin: 2rem auto 0;">
                <h3>Account Statistics</h3>
                <?php
                // Get user statistics
                require_once '../models/Video.php';
                $video = new Video();
                $user_videos = $video->getAllByCompany($_SESSION['company_id']);
                $user_video_count = 0;
                $total_size = 0;
                
                foreach ($user_videos as $vid) {
                    if ($vid['uploaded_by'] == $_SESSION['user_id']) {
                        $user_video_count++;
                        $total_size += $vid['file_size'] ?? 0;
                    }
                }
                ?>
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="text-center">
                        <h2 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo $user_video_count; ?></h2>
                        <p>Videos Uploaded</p>
                    </div>
                    <div class="text-center">
                        <h2 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo formatFileSize($total_size); ?></h2>
                        <p>Total Storage Used</p>
                    </div>
                    <div class="text-center">
                        <h2 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo date('M j, Y', strtotime($user->created_at ?? '')); ?></h2>
                        <p>Member Since</p>
                    </div>
                </div>
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