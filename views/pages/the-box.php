<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check authentication
checkAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Box - Digital Archive Management</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 4rem 0;
        }
        
        .hero-title {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .testimonial {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 2rem;
            margin: 2rem 0;
            border-radius: 0 10px 10px 0;
        }
        
        .price-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            border: 3px solid #667eea;
            position: relative;
            overflow: hidden;
        }
        
        .price-badge {
            background: #667eea;
            color: white;
            padding: 0.5rem 2rem;
            border-radius: 25px;
            position: absolute;
            top: -10px;
            right: 20px;
            font-weight: bold;
        }
        
        .price {
            font-size: 2.5rem;
            color: #667eea;
            font-weight: bold;
            margin: 1rem 0;
        }
    </style>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">📦 The Box</h1>
            <p class="hero-subtitle">Revolutionary digitalization service for your precious memories</p>
            <p>Transform your old VHS, Betamax, and DV cassettes into digital treasures</p>
            <a href="contact.php" class="btn" style="font-size: 1.2rem; padding: 1rem 2rem; margin-top: 1rem;">Get Started Today</a>
        </div>
    </section>

    <main class="main-content">
        <div class="container">
            <!-- Features Section -->
            <section>
                <h2 class="text-center mb-2">Why Choose The Box?</h2>
                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🎬</div>
                        <h3>Professional Quality</h3>
                        <p>State-of-the-art equipment ensures the highest quality digital conversion of your memories.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Secure & Safe</h3>
                        <p>Your precious memories are handled with care and stored securely throughout the process.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Fast Turnaround</h3>
                        <p>Most projects completed within 5-7 business days without compromising quality.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">💾</div>
                        <h3>Multiple Formats</h3>
                        <p>Support for VHS, Betamax, DV Cassettes, Hi8, MiniDV, and many other legacy formats.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">☁️</div>
                        <h3>Cloud Storage</h3>
                        <p>Access your digitized videos from anywhere with our secure cloud storage platform.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">🎯</div>
                        <h3>Easy Management</h3>
                        <p>Organize, tag, and share your videos with our intuitive management system.</p>
                    </div>
                </div>
            </section>

            <!-- Process Section -->
            <section class="card">
                <h2 class="text-center mb-2">How It Works</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; text-align: center;">
                    <div>
                        <h3 style="color: #667eea;">📦 Step 1: Ship</h3>
                        <p>Send us your tapes using our secure shipping kit. We provide tracking and insurance.</p>
                    </div>
                    <div>
                        <h3 style="color: #667eea;">⚙️ Step 2: Digitize</h3>
                        <p>Our experts carefully convert your tapes using professional-grade equipment.</p>
                    </div>
                    <div>
                        <h3 style="color: #667eea;">📤 Step 3: Deliver</h3>
                        <p>Access your digital files online and receive your original tapes back safely.</p>
                    </div>
                </div>
            </section>

            <!-- Pricing Section -->
            <section>
                <h2 class="text-center mb-2">Pricing Plans</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div class="price-card">
                        <div class="price-badge">Basic</div>
                        <h3 style="margin-top: 2rem;">Standard Conversion</h3>
                        <div class="price">$15<span style="font-size: 1rem;">/tape</span></div>
                        <ul style="text-align: left; margin: 1rem 0;">
                            <li>✓ Digital MP4 conversion</li>
                            <li>✓ Cloud storage included</li>
                            <li>✓ 7-day turnaround</li>
                            <li>✓ Original tapes returned</li>
                        </ul>
                        <a href="contact.php" class="btn">Choose Basic</a>
                    </div>
                    
                    <div class="price-card">
                        <div class="price-badge">Premium</div>
                        <h3 style="margin-top: 2rem;">Enhanced Quality</h3>
                        <div class="price">$25<span style="font-size: 1rem;">/tape</span></div>
                        <ul style="text-align: left; margin: 1rem 0;">
                            <li>✓ High-definition conversion</li>
                            <li>✓ Color correction & enhancement</li>
                            <li>✓ Multiple format options</li>
                            <li>✓ 5-day turnaround</li>
                            <li>✓ Priority support</li>
                        </ul>
                        <a href="contact.php" class="btn">Choose Premium</a>
                    </div>
                    
                    <div class="price-card">
                        <div class="price-badge">Enterprise</div>
                        <h3 style="margin-top: 2rem;">Bulk Processing</h3>
                        <div class="price">$10<span style="font-size: 1rem;">/tape</span></div>
                        <ul style="text-align: left; margin: 1rem 0;">
                            <li>✓ 50+ tapes minimum</li>
                            <li>✓ Custom workflow options</li>
                            <li>✓ Dedicated project manager</li>
                            <li>✓ Volume discounts available</li>
                            <li>✓ White-label solutions</li>
                        </ul>
                        <a href="contact.php" class="btn">Contact Sales</a>
                    </div>
                </div>
            </section>

            <!-- Testimonials -->
            <section>
                <h2 class="text-center mb-2">What Our Customers Say</h2>
                
                <div class="testimonial">
                    <p><em>"The Box saved our family memories! They converted 30 years of VHS tapes with incredible quality. The online platform makes it so easy to organize and share with relatives."</em></p>
                    <strong>- Sarah Johnson, Family Archivist</strong>
                </div>
                
                <div class="testimonial">
                    <p><em>"As a wedding videographer, I needed to digitize hundreds of old client tapes. The Box handled the entire project professionally and the turnaround was amazing."</em></p>
                    <strong>- Mike Rodriguez, Rodriguez Wedding Films</strong>
                </div>
                
                <div class="testimonial">
                    <p><em>"Our company had decades of training videos on Betamax. The Box not only digitized them but helped us create a searchable archive system."</em></p>
                    <strong>- Jennifer Chen, Training Director</strong>
                </div>
            </section>

            <!-- Call to Action -->
            <section class="card text-center">
                <h2>Ready to Preserve Your Memories?</h2>
                <p>Don't let your precious memories fade away. Start your digitalization journey today!</p>
                <div style="margin-top: 2rem;">
                    <a href="contact.php" class="btn" style="margin-right: 1rem;">Get Quote</a>
                    <a href="../upload.php" class="btn btn-secondary">Upload Existing Videos</a>
                </div>
            </section>
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