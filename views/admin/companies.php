<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../models/Company.php';

// Check authentication and role
checkAuth();
checkRole(['admin']);

$company = new Company();
$companies = $company->getAll();

$error = '';
$success = '';

// Handle company management
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $name = sanitizeInput($_POST['name']);
        
        if (empty($name)) {
            $error = 'Company name is required.';
        } else {
            $company->name = $name;
            if ($company->create()) {
                $success = 'Company created successfully.';
                // Refresh companies list
                $companies = $company->getAll();
            } else {
                $error = 'Failed to create company.';
            }
        }
    } elseif ($_POST['action'] === 'update') {
        $company_id = intval($_POST['company_id']);
        $name = sanitizeInput($_POST['name']);
        
        if (empty($name)) {
            $error = 'Company name is required.';
        } else {
            $company->id = $company_id;
            $company->name = $name;
            if ($company->update()) {
                $success = 'Company updated successfully.';
                // Refresh companies list
                $companies = $company->getAll();
            } else {
                $error = 'Failed to update company.';
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $company_id = intval($_POST['company_id']);
        
        // Don't allow deleting the current user's company
        if ($company_id == $_SESSION['company_id']) {
            $error = 'Cannot delete your own company.';
        } else {
            $company->id = $company_id;
            if ($company->delete()) {
                $success = 'Company deleted successfully.';
                // Refresh companies list
                $companies = $company->getAll();
            } else {
                $error = 'Failed to delete company. Make sure no users are assigned to it.';
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
    <title>Manage Companies - Digital Archive Management</title>
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
                <h1>Manage Companies</h1>
                <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Create New Company -->
            <div class="card">
                <h3>Create New Company</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create">
                    <div class="flex gap-1 align-center">
                        <div class="form-group" style="flex: 1; margin: 0;">
                            <input type="text" name="name" class="form-control" 
                                   placeholder="Enter company name..." required>
                        </div>
                        <button type="submit" class="btn">Create Company</button>
                    </div>
                </form>
            </div>

            <!-- Companies List -->
            <div class="card">
                <h3>All Companies</h3>
                
                <?php if (empty($companies)): ?>
                    <p>No companies found.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Created</th>
                                <th>Users</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $comp): ?>
                                <tr>
                                    <td><?php echo $comp['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($comp['name']); ?>
                                        <?php if ($comp['id'] == $_SESSION['company_id']): ?>
                                            <span class="tag" style="background-color: #27ae60; color: white;">Your Company</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($comp['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        // Count users in this company
                                        require_once '../../models/User.php';
                                        $user = new User();
                                        $company_users = $user->getAllByCompany($comp['id']);
                                        echo count($company_users);
                                        ?>
                                    </td>
                                    <td>
                                        <!-- Edit Form -->
                                        <form method="POST" style="display: inline-block; margin-right: 0.5rem;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="company_id" value="<?php echo $comp['id']; ?>">
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($comp['name']); ?>" 
                                                   style="width: 150px; display: inline-block;" class="form-control">
                                            <button type="submit" class="btn btn-small btn-secondary">Update</button>
                                        </form>
                                        
                                        <!-- Delete Button -->
                                        <?php if ($comp['id'] != $_SESSION['company_id'] && count($company_users) == 0): ?>
                                            <form method="POST" style="display: inline-block;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this company?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="company_id" value="<?php echo $comp['id']; ?>">
                                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                                            </form>
                                        <?php elseif ($comp['id'] == $_SESSION['company_id']): ?>
                                            <span class="text-muted">Current Company</span>
                                        <?php else: ?>
                                            <span class="text-muted">Has Users</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Company Management Guidelines</h3>
                <ul>
                    <li>Companies organize users and their video archives</li>
                    <li>Users can only access videos from their own company</li>
                    <li>You cannot delete a company that has users assigned to it</li>
                    <li>You cannot delete your own company</li>
                    <li>When users register, they can create new companies or join existing ones</li>
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