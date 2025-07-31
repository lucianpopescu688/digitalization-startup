<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../models/User.php';

// Check authentication and role
checkAuth();
checkRole(['admin']);

$user = new User();
$users = $user->getAllByCompany($_SESSION['company_id']);

$error = '';
$success = '';

// Handle user role updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_role') {
        $user_id = intval($_POST['user_id']);
        $new_role = sanitizeInput($_POST['role']);
        
        if (in_array($new_role, ['admin', 'manager', 'user'])) {
            $user->findById($user_id);
            $user->role = $new_role;
            
            if ($user->update()) {
                $success = 'User role updated successfully.';
                // Refresh users list
                $users = $user->getAllByCompany($_SESSION['company_id']);
            } else {
                $error = 'Failed to update user role.';
            }
        } else {
            $error = 'Invalid role selected.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Digital Archive Management</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="../dashboard.php" class="logo">Digital Archive</a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="../dashboard.php">Dashboard</a></li>
                        <li><a href="../upload.php">Upload Video</a></li>
                        <li><a href="../account.php">My Account</a></li>
                        <li><a href="users.php">Manage Users</a></li>
                        <li><a href="companies.php">Manage Companies</a></li>
                        <li><a href="../pages/the-box.php">The Box</a></li>
                        <li><a href="../pages/contact.php">Contact</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    <span class="role-badge"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../auth/logout.php" class="btn btn-small">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="flex justify-between align-center mb-2">
                <h1>Manage Users</h1>
                <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Company Users</h3>
                
                <?php if (empty($users)): ?>
                    <p>No users found in your company.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user_item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user_item['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user_item['email'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="role-badge" style="background-color: 
                                            <?php 
                                            echo $user_item['role'] === 'admin' ? '#e74c3c' : 
                                                 ($user_item['role'] === 'manager' ? '#f39c12' : '#3498db'); 
                                            ?>">
                                            <?php echo ucfirst($user_item['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user_item['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user_item['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline-block;">
                                                <input type="hidden" name="action" value="update_role">
                                                <input type="hidden" name="user_id" value="<?php echo $user_item['id']; ?>">
                                                <select name="role" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                                                    <option value="user" <?php echo $user_item['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                    <option value="manager" <?php echo $user_item['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                                    <option value="admin" <?php echo $user_item['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>User Management Guidelines</h3>
                <ul>
                    <li><strong>Admin:</strong> Can manage users, companies, and all videos</li>
                    <li><strong>Manager:</strong> Can manage videos and view reports</li>
                    <li><strong>User:</strong> Can upload and manage their own videos</li>
                    <li>Users can only access videos from their own company</li>
                    <li>Role changes take effect immediately</li>
                </ul>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Digital Archive Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="../../public/js/app.js"></script>
</body>
</html>