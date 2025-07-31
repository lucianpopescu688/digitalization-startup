<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../models/User.php';
require_once '../../models/Company.php';
require_once '../../models/Session.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $user = new User();
        if ($user->login($username, $password)) {
            // Create session
            $session = new Session();
            if ($session->create($user->id)) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['role'] = $user->role;
                $_SESSION['company_id'] = $user->company_id;
                
                header('Location: ../dashboard.php');
                exit();
            } else {
                $error = 'Failed to create session.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Digital Archive Management</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="card" style="max-width: 400px; margin: 2rem auto;">
                <div class="text-center mb-2">
                    <h1 class="logo">Digital Archive</h1>
                    <p>Digitalization Startup Management System</p>
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
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn" style="width: 100%;">Login</button>
                </form>
                
                <div class="text-center mt-2">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                    <p><strong>Demo Login:</strong> admin / admin123</p>
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