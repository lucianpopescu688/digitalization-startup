const express = require('express');
const { isAuthenticated } = require('../middleware/auth');
const Video = require('../models/Video');
const User = require('../models/User');
const Company = require('../models/Company');

const router = express.Router();

// Apply authentication middleware to all dashboard routes
router.use(isAuthenticated);

// GET /dashboard - Main dashboard
router.get('/', async (req, res) => {
    try {
        const userId = req.user.id;
        const companyId = req.user.company_id;
        
        // Get videos based on user role
        let videos;
        if (req.user.role === 'admin') {
            videos = await Video.getAll(20); // Get all videos for admin
        } else if (companyId) {
            videos = await Video.findByCompanyId(companyId); // Get company videos
        } else {
            videos = await Video.findByUserId(userId); // Get user's videos only
        }

        // Get statistics
        const stats = {
            totalVideos: videos.length,
            userVideos: 0,
            companyVideos: 0
        };

        if (companyId) {
            stats.companyVideos = await Video.getCount(companyId);
        }
        stats.userVideos = await Video.findByUserId(userId).then(videos => videos.length);

        res.render('dashboard/index', {
            title: 'Dashboard',
            videos: videos,
            stats: stats,
            user: req.user
        });

    } catch (error) {
        console.error('Dashboard error:', error);
        req.flash('error', 'Error loading dashboard');
        res.render('dashboard/index', {
            title: 'Dashboard',
            videos: [],
            stats: { totalVideos: 0, userVideos: 0, companyVideos: 0 },
            user: req.user
        });
    }
});

// GET /dashboard/account - User account management
router.get('/account', async (req, res) => {
    try {
        const user = await User.findById(req.user.id);
        const companies = await Company.getAll();
        
        res.render('dashboard/account', {
            title: 'Account Settings',
            user: user,
            companies: companies
        });

    } catch (error) {
        console.error('Account page error:', error);
        req.flash('error', 'Error loading account page');
        res.redirect('/dashboard');
    }
});

// POST /dashboard/account - Update user account
router.post('/account', async (req, res) => {
    try {
        const { username, email, company_id } = req.body;
        const userId = req.user.id;

        // Validation
        if (!username || !email) {
            req.flash('error', 'Username and email are required');
            return res.redirect('/dashboard/account');
        }

        // Check if username is taken by another user
        const existingUser = await User.findByUsername(username);
        if (existingUser && existingUser.id !== userId) {
            req.flash('error', 'Username is already taken');
            return res.redirect('/dashboard/account');
        }

        // Check if email is taken by another user
        const existingEmail = await User.findByEmail(email);
        if (existingEmail && existingEmail.id !== userId) {
            req.flash('error', 'Email is already taken');
            return res.redirect('/dashboard/account');
        }

        // Update user
        await User.updateProfile(userId, {
            username,
            email,
            company_id: company_id || null,
            role: req.user.role // Keep existing role
        });

        req.flash('success', 'Account updated successfully');
        res.redirect('/dashboard/account');

    } catch (error) {
        console.error('Account update error:', error);
        req.flash('error', 'Error updating account');
        res.redirect('/dashboard/account');
    }
});

// GET /dashboard/search - Search videos
router.get('/search', async (req, res) => {
    try {
        const query = req.query.q || '';
        const companyId = req.user.company_id;
        
        let videos = [];
        if (query.trim()) {
            if (req.user.role === 'admin') {
                videos = await Video.search(query);
            } else {
                videos = await Video.search(query, companyId);
            }
        }

        res.render('dashboard/search', {
            title: 'Search Videos',
            videos: videos,
            query: query
        });

    } catch (error) {
        console.error('Search error:', error);
        req.flash('error', 'Error performing search');
        res.render('dashboard/search', {
            title: 'Search Videos',
            videos: [],
            query: req.query.q || ''
        });
    }
});

// GET /dashboard/contact - Contact page
router.get('/contact', (req, res) => {
    res.render('dashboard/contact', {
        title: 'Contact Us'
    });
});

// POST /dashboard/contact - Handle contact form
router.post('/contact', (req, res) => {
    // In a real application, you would handle the contact form submission
    // For now, just show a success message
    req.flash('success', 'Thank you for your message! We will get back to you soon.');
    res.redirect('/dashboard/contact');
});

// GET /dashboard/the-box - Promotional page for next product
router.get('/the-box', (req, res) => {
    res.render('dashboard/the-box', {
        title: 'The Box - Coming Soon'
    });
});

module.exports = router;