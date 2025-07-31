const User = require('../models/User');

// Middleware to check if user is authenticated
function isAuthenticated(req, res, next) {
    if (req.isAuthenticated()) {
        return next();
    }
    
    // Store the original URL for redirect after login
    req.session.returnTo = req.originalUrl;
    res.redirect('/auth/login');
}

// Middleware to check if user is admin
function isAdmin(req, res, next) {
    if (req.isAuthenticated() && req.user.role === 'admin') {
        return next();
    }
    
    res.status(403).render('error', { 
        message: 'Access denied. Admin privileges required.',
        error: { status: 403 }
    });
}

// Middleware to check if user can access company resources
function canAccessCompany(req, res, next) {
    if (!req.isAuthenticated()) {
        return res.redirect('/auth/login');
    }
    
    const userCompanyId = req.user.company_id;
    const requestedCompanyId = req.params.companyId || req.body.company_id;
    
    // Admin can access all companies
    if (req.user.role === 'admin') {
        return next();
    }
    
    // User can only access their own company
    if (userCompanyId && userCompanyId.toString() === requestedCompanyId?.toString()) {
        return next();
    }
    
    res.status(403).render('error', { 
        message: 'Access denied. You can only access your company\'s resources.',
        error: { status: 403 }
    });
}

// Middleware to add user info to all views
function addUserToViews(req, res, next) {
    res.locals.user = req.user || null;
    res.locals.isAuthenticated = req.isAuthenticated();
    next();
}

// Middleware to add flash messages to views
function addFlashMessages(req, res, next) {
    res.locals.messages = {
        success: req.flash('success'),
        error: req.flash('error'),
        info: req.flash('info'),
        warning: req.flash('warning')
    };
    next();
}

module.exports = {
    isAuthenticated,
    isAdmin,
    canAccessCompany,
    addUserToViews,
    addFlashMessages
};