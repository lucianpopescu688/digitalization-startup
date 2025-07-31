<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../models/User.php';
require_once '../../models/Company.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = sanitizeInput($_POST['email']);
    $company_name = sanitizeInput($_POST['company_name']);
    $role = sanitizeInput($_POST['role']);
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($company_name)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        $user = new User();
        
        // Check if username already exists
        if ($user->usernameExists($username)) {
            $error = 'Username already exists.';
        } else {
            // Create or find company
            $company = new Company();
            $company->name = $company_name;
            
            // Check if company exists
            $existing_companies = $company->getAll();
            $company_id = null;
            
            foreach ($existing_companies as $existing_company) {
                if (strtolower($existing_company['name']) === strtolower($company_name)) {
                    $company_id = $existing_company['id'];
                    break;
                }
            }
            
            if (!$company_id) {
                if ($company->create()) {
                    $company_id = $company->id;
                } else {
                    $error = 'Failed to create company.';
                }
            }
            
            if ($company_id && !$error) {
                // Create user
                $user->username = $username;
                $user->password = $password;
                $user->email = $email;
                $user->company_id = $company_id;
                $user->role = $role;
                
                if ($user->create()) {
                    $success = 'Account created successfully! You can now log in.';
                } else {
                    $error = 'Failed to create account.';
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
    <title>Register - Digital Archive Management</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="card" style="max-width: 500px; margin: 2rem auto;">
                <div class="text-center mb-2">
                    <h1 class="logo">Digital Archive</h1>
                    <p>Create Your Account</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="company_name">Company Name:</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" 
                               value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                            <option value="manager" <?php echo (isset($_POST['role']) && $_POST['role'] === 'manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn" style="width: 100%;">Create Account</button>
                </form>
                
                <div class="text-center mt-2">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="container">
            <p>&copy; 2024 Digital Archive Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>