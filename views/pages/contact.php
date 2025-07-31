<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check authentication
checkAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    $service = sanitizeInput($_POST['service']);
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($message) < 10) {
        $error = 'Message must be at least 10 characters long.';
    } else {
        // In a real application, you would send an email or save to database
        // For this demo, we'll just show a success message
        
        // Here you could implement email sending with PHPMailer or similar
        // mail($to, $subject, $message, $headers);
        
        $success = 'Thank you for your message! We will get back to you within 24 hours.';
        
        // Clear form data
        $_POST = array();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Digital Archive Management</title>
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
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a href="../admin/users.php">Manage Users</a></li>
                            <li><a href="../admin/companies.php">Manage Companies</a></li>
                        <?php endif; ?>
                        <li><a href="the-box.php">The Box</a></li>
                        <li><a href="contact.php">Contact</a></li>
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
                <h1>Contact Us</h1>
                <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Contact Form -->
                <div class="card">
                    <h3>Send us a Message</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name:</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="service">Service Interest:</label>
                            <select id="service" name="service" class="form-control">
                                <option value="general" <?php echo (isset($_POST['service']) && $_POST['service'] === 'general') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="digitization" <?php echo (isset($_POST['service']) && $_POST['service'] === 'digitization') ? 'selected' : ''; ?>>Digitization Services</option>
                                <option value="software" <?php echo (isset($_POST['service']) && $_POST['service'] === 'software') ? 'selected' : ''; ?>>Software Support</option>
                                <option value="enterprise" <?php echo (isset($_POST['service']) && $_POST['service'] === 'enterprise') ? 'selected' : ''; ?>>Enterprise Solutions</option>
                                <option value="partnership" <?php echo (isset($_POST['service']) && $_POST['service'] === 'partnership') ? 'selected' : ''; ?>>Partnership</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject:</label>
                            <input type="text" id="subject" name="subject" class="form-control" 
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Message:</label>
                            <textarea id="message" name="message" class="form-control" rows="6" 
                                      placeholder="Please describe your inquiry in detail..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>

                        <button type="submit" class="btn" style="width: 100%;">Send Message</button>
                    </form>
                </div>

                <!-- Contact Information -->
                <div>
                    <div class="card">
                        <h3>Get in Touch</h3>
                        <p>We're here to help with all your digitalization needs. Reach out to us through any of the following channels:</p>
                        
                        <div style="margin: 2rem 0;">
                            <h4>📞 Phone Support</h4>
                            <p><strong>Main Line:</strong> +1 (555) 123-4567</p>
                            <p><strong>Support:</strong> +1 (555) 123-4568</p>
                            <p><em>Monday - Friday: 9 AM - 6 PM EST</em></p>
                        </div>

                        <div style="margin: 2rem 0;">
                            <h4>📧 Email</h4>
                            <p><strong>General:</strong> info@digitalarchive.com</p>
                            <p><strong>Support:</strong> support@digitalarchive.com</p>
                            <p><strong>Sales:</strong> sales@digitalarchive.com</p>
                        </div>

                        <div style="margin: 2rem 0;">
                            <h4>📍 Office Address</h4>
                            <p>Digital Archive Management<br>
                            1234 Technology Blvd<br>
                            Suite 567<br>
                            San Francisco, CA 94107</p>
                        </div>
                    </div>

                    <div class="card">
                        <h3>Frequently Asked Questions</h3>
                        
                        <div style="margin: 1rem 0;">
                            <h4>How long does digitization take?</h4>
                            <p>Standard orders: 5-7 business days<br>
                            Premium orders: 3-5 business days<br>
                            Enterprise orders: Custom timeline</p>
                        </div>

                        <div style="margin: 1rem 0;">
                            <h4>What formats do you support?</h4>
                            <p>VHS, Betamax, DV Cassettes, Hi8, MiniDV, Digital8, and many other legacy formats.</p>
                        </div>

                        <div style="margin: 1rem 0;">
                            <h4>Is my data secure?</h4>
                            <p>Yes! We use enterprise-grade security measures and your data is encrypted both in transit and at rest.</p>
                        </div>

                        <div style="margin: 1rem 0;">
                            <h4>Do I get my original tapes back?</h4>
                            <p>Absolutely! We return all original media along with your digital files.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Response Times -->
            <div class="card">
                <h3>Response Times</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; text-align: center;">
                    <div>
                        <h4 style="color: #667eea;">📧 Email</h4>
                        <p>Within 4 hours during business hours</p>
                    </div>
                    <div>
                        <h4 style="color: #667eea;">📞 Phone</h4>
                        <p>Immediate during business hours</p>
                    </div>
                    <div>
                        <h4 style="color: #667eea;">💬 Live Chat</h4>
                        <p>Available Monday-Friday 9 AM - 6 PM EST</p>
                    </div>
                    <div>
                        <h4 style="color: #667eea;">🎫 Support Tickets</h4>
                        <p>Within 24 hours guaranteed</p>
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

    <script src="../../public/js/app.js"></script>
    <script>
        // Form validation enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const messageField = document.getElementById('message');
            
            if (messageField) {
                messageField.addEventListener('input', function() {
                    const length = this.value.length;
                    const minLength = 10;
                    
                    if (length < minLength) {
                        this.style.borderColor = '#e74c3c';
                    } else {
                        this.style.borderColor = '#27ae60';
                    }
                });
            }
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value;
                    const message = document.getElementById('message').value;
                    
                    if (!validateEmail(email)) {
                        e.preventDefault();
                        alert('Please enter a valid email address.');
                        return;
                    }
                    
                    if (message.length < 10) {
                        e.preventDefault();
                        alert('Message must be at least 10 characters long.');
                        return;
                    }
                });
            }
        });
    </script>
</body>
</html>