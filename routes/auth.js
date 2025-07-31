const express = require('express');
const passport = require('passport');
const LocalStrategy = require('passport-local').Strategy;
const GoogleStrategy = require('passport-google-oauth20').Strategy;
const { body, validationResult } = require('express-validator');

const User = require('../models/User');
const Company = require('../models/Company');

const router = express.Router();

// Passport Local Strategy
passport.use(new LocalStrategy(
    { usernameField: 'username' },
    async (username, password, done) => {
        try {
            const user = await User.findByUsername(username);
            if (!user) {
                return done(null, false, { message: 'Invalid username or password.' });
            }

            const isValidPassword = await User.validatePassword(user, password);
            if (!isValidPassword) {
                return done(null, false, { message: 'Invalid username or password.' });
            }

            return done(null, user);
        } catch (error) {
            return done(error);
        }
    }
));

// Passport Google OAuth Strategy
if (process.env.GOOGLE_CLIENT_ID && process.env.GOOGLE_CLIENT_SECRET) {
    passport.use(new GoogleStrategy({
        clientID: process.env.GOOGLE_CLIENT_ID,
        clientSecret: process.env.GOOGLE_CLIENT_SECRET,
        callbackURL: "/auth/google/callback"
    },
    async (accessToken, refreshToken, profile, done) => {
        try {
            // Check if user exists with this Google ID
            let user = await User.findByGoogleId(profile.id);
            
            if (user) {
                return done(null, user);
            }

            // Check if user exists with this email
            user = await User.findByEmail(profile.emails[0].value);
            
            if (user) {
                // Link Google account to existing user
                // Note: This is a simplified approach. In production, you might want to ask for confirmation
                return done(null, user);
            }

            // Create new user
            const userData = {
                username: profile.displayName || profile.emails[0].value,
                email: profile.emails[0].value,
                password: 'google-oauth', // Placeholder password for Google users
                google_id: profile.id,
                role: 'user'
            };

            user = await User.create(userData);
            return done(null, user);
        } catch (error) {
            return done(error);
        }
    }));
}

// Passport serialization
passport.serializeUser((user, done) => {
    done(null, user.id);
});

passport.deserializeUser(async (id, done) => {
    try {
        const user = await User.findById(id);
        done(null, user);
    } catch (error) {
        done(error);
    }
});

// Validation rules
const registerValidation = [
    body('username')
        .isLength({ min: 3, max: 30 })
        .withMessage('Username must be between 3 and 30 characters')
        .matches(/^[a-zA-Z0-9_]+$/)
        .withMessage('Username can only contain letters, numbers, and underscores'),
    body('email')
        .isEmail()
        .withMessage('Please enter a valid email address')
        .normalizeEmail(),
    body('password')
        .isLength({ min: 6 })
        .withMessage('Password must be at least 6 characters long'),
    body('confirmPassword')
        .custom((value, { req }) => {
            if (value !== req.body.password) {
                throw new Error('Passwords do not match');
            }
            return true;
        }),
    body('company')
        .isLength({ min: 2, max: 100 })
        .withMessage('Company name must be between 2 and 100 characters')
        .trim()
];

const loginValidation = [
    body('username')
        .notEmpty()
        .withMessage('Username is required'),
    body('password')
        .notEmpty()
        .withMessage('Password is required')
];

// Routes

// GET /auth/login
router.get('/login', (req, res) => {
    if (req.isAuthenticated()) {
        return res.redirect('/dashboard');
    }
    res.render('auth/login', { 
        title: 'Login',
        googleEnabled: !!(process.env.GOOGLE_CLIENT_ID && process.env.GOOGLE_CLIENT_SECRET)
    });
});

// POST /auth/login
router.post('/login', loginValidation, (req, res, next) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.render('auth/login', {
            title: 'Login',
            errors: errors.array(),
            googleEnabled: !!(process.env.GOOGLE_CLIENT_ID && process.env.GOOGLE_CLIENT_SECRET),
            username: req.body.username
        });
    }

    passport.authenticate('local', (err, user, info) => {
        if (err) {
            console.error('Login error:', err);
            req.flash('error', 'An error occurred during login');
            return res.redirect('/auth/login');
        }
        
        if (!user) {
            return res.render('auth/login', {
                title: 'Login',
                errors: [{ msg: info.message }],
                googleEnabled: !!(process.env.GOOGLE_CLIENT_ID && process.env.GOOGLE_CLIENT_SECRET),
                username: req.body.username
            });
        }

        req.logIn(user, (err) => {
            if (err) {
                console.error('Login session error:', err);
                req.flash('error', 'An error occurred during login');
                return res.redirect('/auth/login');
            }

            req.flash('success', 'Welcome back!');
            const redirectTo = req.session.returnTo || '/dashboard';
            delete req.session.returnTo;
            return res.redirect(redirectTo);
        });
    })(req, res, next);
});

// GET /auth/register
router.get('/register', (req, res) => {
    if (req.isAuthenticated()) {
        return res.redirect('/dashboard');
    }
    res.render('auth/register', { title: 'Register' });
});

// POST /auth/register
router.post('/register', registerValidation, async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.render('auth/register', {
            title: 'Register',
            errors: errors.array(),
            formData: req.body
        });
    }

    try {
        const { username, email, password, company, role } = req.body;

        // Check if username already exists
        const existingUser = await User.findByUsername(username);
        if (existingUser) {
            return res.render('auth/register', {
                title: 'Register',
                errors: [{ msg: 'Username already exists' }],
                formData: req.body
            });
        }

        // Check if email already exists
        const existingEmail = await User.findByEmail(email);
        if (existingEmail) {
            return res.render('auth/register', {
                title: 'Register',
                errors: [{ msg: 'Email already exists' }],
                formData: req.body
            });
        }

        // Find or create company
        const companyRecord = await Company.findOrCreate(company);

        // Create user
        const userData = {
            username,
            email,
            password,
            company_id: companyRecord.id,
            role: role || 'user'
        };

        const user = await User.create(userData);

        // Log in the user
        req.logIn(user, (err) => {
            if (err) {
                console.error('Registration login error:', err);
                req.flash('error', 'Account created but login failed. Please try logging in.');
                return res.redirect('/auth/login');
            }

            req.flash('success', 'Account created successfully! Welcome to the platform.');
            res.redirect('/dashboard');
        });

    } catch (error) {
        console.error('Registration error:', error);
        res.render('auth/register', {
            title: 'Register',
            errors: [{ msg: 'An error occurred during registration. Please try again.' }],
            formData: req.body
        });
    }
});

// Google OAuth routes
if (process.env.GOOGLE_CLIENT_ID && process.env.GOOGLE_CLIENT_SECRET) {
    router.get('/google',
        passport.authenticate('google', { scope: ['profile', 'email'] })
    );

    router.get('/google/callback',
        passport.authenticate('google', { failureRedirect: '/auth/login' }),
        (req, res) => {
            req.flash('success', 'Successfully signed in with Google!');
            res.redirect('/dashboard');
        }
    );
}

// GET /auth/logout
router.get('/logout', (req, res) => {
    req.logout((err) => {
        if (err) {
            console.error('Logout error:', err);
        }
        req.flash('info', 'You have been logged out successfully.');
        res.redirect('/auth/login');
    });
});

module.exports = router;