const express = require('express');
const { isAuthenticated, isAdmin } = require('../middleware/auth');
const User = require('../models/User');
const Company = require('../models/Company');
const Video = require('../models/Video');

const router = express.Router();

// Apply authentication and admin middleware
router.use(isAuthenticated);
router.use(isAdmin);

// GET /admin - Admin dashboard
router.get('/', async (req, res) => {
    try {
        // Get system statistics
        const stats = {
            totalUsers: 0,
            totalCompanies: 0,
            totalVideos: 0,
            recentUsers: [],
            recentVideos: []
        };

        // Get total counts
        const companies = await Company.getAll();
        stats.totalCompanies = companies.length;
        
        stats.totalVideos = await Video.getCount();

        // Get recent videos
        stats.recentVideos = await Video.getAll(5);

        res.render('admin/dashboard', {
            title: 'Admin Dashboard',
            stats: stats
        });

    } catch (error) {
        console.error('Admin dashboard error:', error);
        req.flash('error', 'Error loading admin dashboard');
        res.render('admin/dashboard', {
            title: 'Admin Dashboard',
            stats: {
                totalUsers: 0,
                totalCompanies: 0,
                totalVideos: 0,
                recentUsers: [],
                recentVideos: []
            }
        });
    }
});

// GET /admin/companies - Manage companies
router.get('/companies', async (req, res) => {
    try {
        const companies = await Company.getAll();
        
        // Get user and video counts for each company
        for (let company of companies) {
            company.userCount = await Company.getUserCount(company.id);
            company.videoCount = await Company.getVideoCount(company.id);
        }

        res.render('admin/companies', {
            title: 'Manage Companies',
            companies: companies
        });

    } catch (error) {
        console.error('Admin companies error:', error);
        req.flash('error', 'Error loading companies');
        res.render('admin/companies', {
            title: 'Manage Companies',
            companies: []
        });
    }
});

// GET /admin/companies/:id - View company details
router.get('/companies/:id', async (req, res) => {
    try {
        const companyId = req.params.id;
        const company = await Company.findById(companyId);
        
        if (!company) {
            req.flash('error', 'Company not found');
            return res.redirect('/admin/companies');
        }

        const users = await User.findByCompanyId(companyId);
        const videos = await Video.findByCompanyId(companyId);

        res.render('admin/company-details', {
            title: `Company: ${company.name}`,
            company: company,
            users: users,
            videos: videos
        });

    } catch (error) {
        console.error('Admin company details error:', error);
        req.flash('error', 'Error loading company details');
        res.redirect('/admin/companies');
    }
});

// GET /admin/users - Manage users (accessible via company management)
router.get('/users', async (req, res) => {
    try {
        const companyId = req.query.company_id;
        let users = [];
        let companies = [];

        if (companyId) {
            users = await User.findByCompanyId(companyId);
            const company = await Company.findById(companyId);
            companies = [company];
        } else {
            companies = await Company.getAll();
        }

        res.render('admin/users', {
            title: 'Manage Users',
            users: users,
            companies: companies,
            selectedCompanyId: companyId
        });

    } catch (error) {
        console.error('Admin users error:', error);
        req.flash('error', 'Error loading users');
        res.render('admin/users', {
            title: 'Manage Users',
            users: [],
            companies: [],
            selectedCompanyId: null
        });
    }
});

// GET /admin/videos - Manage all videos
router.get('/videos', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = 20;
        const offset = (page - 1) * limit;

        const videos = await Video.getAll(limit, offset);
        const totalVideos = await Video.getCount();
        const totalPages = Math.ceil(totalVideos / limit);

        res.render('admin/videos', {
            title: 'Manage Videos',
            videos: videos,
            currentPage: page,
            totalPages: totalPages,
            hasNextPage: page < totalPages,
            hasPrevPage: page > 1
        });

    } catch (error) {
        console.error('Admin videos error:', error);
        req.flash('error', 'Error loading videos');
        res.render('admin/videos', {
            title: 'Manage Videos',
            videos: [],
            currentPage: 1,
            totalPages: 1,
            hasNextPage: false,
            hasPrevPage: false
        });
    }
});

// POST /admin/users/:id/role - Update user role
router.post('/users/:id/role', async (req, res) => {
    try {
        const userId = req.params.id;
        const { role } = req.body;

        if (!['user', 'admin'].includes(role)) {
            req.flash('error', 'Invalid role specified');
            return res.redirect('back');
        }

        const user = await User.findById(userId);
        if (!user) {
            req.flash('error', 'User not found');
            return res.redirect('back');
        }

        await User.updateProfile(userId, {
            username: user.username,
            email: user.email,
            company_id: user.company_id,
            role: role
        });

        req.flash('success', `User role updated to ${role}`);
        res.redirect('back');

    } catch (error) {
        console.error('Admin update user role error:', error);
        req.flash('error', 'Error updating user role');
        res.redirect('back');
    }
});

// GET /admin/system - System information
router.get('/system', (req, res) => {
    const systemInfo = {
        nodeVersion: process.version,
        platform: process.platform,
        uptime: process.uptime(),
        memory: process.memoryUsage(),
        environment: process.env.NODE_ENV || 'development'
    };

    res.render('admin/system', {
        title: 'System Information',
        systemInfo: systemInfo
    });
});

module.exports = router;