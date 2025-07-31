const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { body, validationResult } = require('express-validator');

const { isAuthenticated } = require('../middleware/auth');
const Video = require('../models/Video');

const router = express.Router();

// Apply authentication middleware
router.use(isAuthenticated);

// Configure multer for video uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        const uploadDir = path.join(__dirname, '..', 'uploads');
        if (!fs.existsSync(uploadDir)) {
            fs.mkdirSync(uploadDir, { recursive: true });
        }
        cb(null, uploadDir);
    },
    filename: (req, file, cb) => {
        // Create unique filename
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        const extension = path.extname(file.originalname);
        cb(null, file.fieldname + '-' + uniqueSuffix + extension);
    }
});

const fileFilter = (req, file, cb) => {
    // Check file type
    const allowedTypes = (process.env.ALLOWED_VIDEO_TYPES || 'video/mp4,video/avi,video/mov,video/wmv').split(',');
    
    if (allowedTypes.includes(file.mimetype)) {
        cb(null, true);
    } else {
        cb(new Error(`File type ${file.mimetype} is not allowed. Allowed types: ${allowedTypes.join(', ')}`), false);
    }
};

const upload = multer({
    storage: storage,
    fileFilter: fileFilter,
    limits: {
        fileSize: parseInt(process.env.MAX_FILE_SIZE) || 100 * 1024 * 1024 // 100MB default
    }
});

// Validation rules
const videoUpdateValidation = [
    body('title')
        .optional()
        .isLength({ min: 1, max: 200 })
        .withMessage('Title must be between 1 and 200 characters')
        .trim(),
    body('description')
        .optional()
        .isLength({ max: 1000 })
        .withMessage('Description must be less than 1000 characters')
        .trim(),
    body('tags')
        .optional()
        .isLength({ max: 500 })
        .withMessage('Tags must be less than 500 characters')
        .trim()
];

// GET /videos/upload - Upload page
router.get('/upload', (req, res) => {
    res.render('videos/upload', {
        title: 'Upload Video'
    });
});

// POST /videos/upload - Handle video upload
router.post('/upload', upload.single('video'), async (req, res) => {
    try {
        if (!req.file) {
            req.flash('error', 'Please select a video file to upload');
            return res.redirect('/videos/upload');
        }

        const { title, description, tags } = req.body;
        
        // Create video record
        const videoData = {
            filename: req.file.filename,
            original_name: req.file.originalname,
            title: title || path.parse(req.file.originalname).name,
            description: description || '',
            tags: tags || '',
            file_path: req.file.path,
            file_size: req.file.size,
            mime_type: req.file.mimetype,
            duration: null, // Could be extracted using ffmpeg in the future
            user_id: req.user.id,
            company_id: req.user.company_id
        };

        const video = await Video.create(videoData);
        
        req.flash('success', 'Video uploaded successfully!');
        res.redirect(`/videos/${video.id}`);

    } catch (error) {
        console.error('Upload error:', error);
        
        // Clean up uploaded file if database save failed
        if (req.file && fs.existsSync(req.file.path)) {
            fs.unlinkSync(req.file.path);
        }
        
        if (error.code === 'LIMIT_FILE_SIZE') {
            req.flash('error', 'File size too large. Maximum size allowed is ' + 
                     Math.round((parseInt(process.env.MAX_FILE_SIZE) || 100 * 1024 * 1024) / 1024 / 1024) + 'MB');
        } else {
            req.flash('error', error.message || 'Error uploading video');
        }
        
        res.redirect('/videos/upload');
    }
});

// GET /videos/:id - View single video
router.get('/:id', async (req, res) => {
    try {
        const videoId = req.params.id;
        const video = await Video.findById(videoId);
        
        if (!video) {
            req.flash('error', 'Video not found');
            return res.redirect('/dashboard');
        }

        // Check access permissions
        const canAccess = req.user.role === 'admin' || 
                         video.user_id === req.user.id || 
                         (req.user.company_id && video.company_id === req.user.company_id);
        
        if (!canAccess) {
            req.flash('error', 'You do not have permission to view this video');
            return res.redirect('/dashboard');
        }

        res.render('videos/view', {
            title: video.title || video.original_name,
            video: video
        });

    } catch (error) {
        console.error('Video view error:', error);
        req.flash('error', 'Error loading video');
        res.redirect('/dashboard');
    }
});

// GET /videos/:id/edit - Edit video details
router.get('/:id/edit', async (req, res) => {
    try {
        const videoId = req.params.id;
        const video = await Video.findById(videoId);
        
        if (!video) {
            req.flash('error', 'Video not found');
            return res.redirect('/dashboard');
        }

        // Check edit permissions
        const canEdit = req.user.role === 'admin' || 
                       video.user_id === req.user.id;
        
        if (!canEdit) {
            req.flash('error', 'You do not have permission to edit this video');
            return res.redirect(`/videos/${videoId}`);
        }

        res.render('videos/edit', {
            title: 'Edit Video',
            video: video
        });

    } catch (error) {
        console.error('Video edit page error:', error);
        req.flash('error', 'Error loading video edit page');
        res.redirect('/dashboard');
    }
});

// POST /videos/:id/edit - Update video details
router.post('/:id/edit', videoUpdateValidation, async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        try {
            const video = await Video.findById(req.params.id);
            return res.render('videos/edit', {
                title: 'Edit Video',
                video: video,
                errors: errors.array(),
                formData: req.body
            });
        } catch (error) {
            req.flash('error', 'Error loading video');
            return res.redirect('/dashboard');
        }
    }

    try {
        const videoId = req.params.id;
        const video = await Video.findById(videoId);
        
        if (!video) {
            req.flash('error', 'Video not found');
            return res.redirect('/dashboard');
        }

        // Check edit permissions
        const canEdit = req.user.role === 'admin' || 
                       video.user_id === req.user.id;
        
        if (!canEdit) {
            req.flash('error', 'You do not have permission to edit this video');
            return res.redirect(`/videos/${videoId}`);
        }

        const { title, description, tags } = req.body;
        
        await Video.update(videoId, {
            title: title || video.title,
            description: description || video.description,
            tags: tags || video.tags
        });

        req.flash('success', 'Video updated successfully!');
        res.redirect(`/videos/${videoId}`);

    } catch (error) {
        console.error('Video update error:', error);
        req.flash('error', 'Error updating video');
        res.redirect(`/videos/${req.params.id}/edit`);
    }
});

// POST /videos/:id/delete - Delete video
router.post('/:id/delete', async (req, res) => {
    try {
        const videoId = req.params.id;
        const video = await Video.findById(videoId);
        
        if (!video) {
            req.flash('error', 'Video not found');
            return res.redirect('/dashboard');
        }

        // Check delete permissions
        const canDelete = req.user.role === 'admin' || 
                         video.user_id === req.user.id;
        
        if (!canDelete) {
            req.flash('error', 'You do not have permission to delete this video');
            return res.redirect(`/videos/${videoId}`);
        }

        // Delete file from filesystem
        if (fs.existsSync(video.file_path)) {
            fs.unlinkSync(video.file_path);
        }

        // Delete from database
        await Video.delete(videoId);

        req.flash('success', 'Video deleted successfully!');
        res.redirect('/dashboard');

    } catch (error) {
        console.error('Video delete error:', error);
        req.flash('error', 'Error deleting video');
        res.redirect(`/videos/${req.params.id}`);
    }
});

// GET /videos/:id/download - Download video file
router.get('/:id/download', async (req, res) => {
    try {
        const videoId = req.params.id;
        const video = await Video.findById(videoId);
        
        if (!video) {
            req.flash('error', 'Video not found');
            return res.redirect('/dashboard');
        }

        // Check access permissions
        const canAccess = req.user.role === 'admin' || 
                         video.user_id === req.user.id || 
                         (req.user.company_id && video.company_id === req.user.company_id);
        
        if (!canAccess) {
            req.flash('error', 'You do not have permission to download this video');
            return res.redirect('/dashboard');
        }

        if (!fs.existsSync(video.file_path)) {
            req.flash('error', 'Video file not found on server');
            return res.redirect(`/videos/${videoId}`);
        }

        res.download(video.file_path, video.original_name);

    } catch (error) {
        console.error('Video download error:', error);
        req.flash('error', 'Error downloading video');
        res.redirect('/dashboard');
    }
});

module.exports = router;