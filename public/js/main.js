// Main JavaScript functionality for Digital Archive Management System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize flash message auto-close
    initFlashMessages();
    
    // Initialize form enhancements
    initFormEnhancements();
    
    // Initialize video grid interactions
    initVideoGrid();
});

// Flash Messages
function initFlashMessages() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        const closeBtn = alert.querySelector('.alert-close');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                closeAlert(alert);
            });
        }
        
        // Auto-close after 5 seconds
        setTimeout(() => {
            closeAlert(alert);
        }, 5000);
    });
}

function closeAlert(alert) {
    alert.style.animation = 'slideOut 0.3s ease forwards';
    setTimeout(() => {
        alert.remove();
    }, 300);
}

// Form Enhancements
function initFormEnhancements() {
    // Real-time form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearFieldError);
        });
    });
    
    // Password confirmation
    const passwordFields = document.querySelectorAll('input[name="password"]');
    const confirmPasswordFields = document.querySelectorAll('input[name="confirmPassword"]');
    
    if (passwordFields.length && confirmPasswordFields.length) {
        confirmPasswordFields.forEach(confirmField => {
            confirmField.addEventListener('input', validatePasswordMatch);
        });
    }
}

function validateField(event) {
    const field = event.target;
    const value = field.value.trim();
    
    // Remove any existing error styling
    field.classList.remove('error');
    
    // Basic validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Password validation
    if (field.name === 'password' && value) {
        if (value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters long');
            return false;
        }
    }
    
    // Username validation
    if (field.name === 'username' && value) {
        const usernamePattern = /^[a-zA-Z0-9_]+$/;
        if (!usernamePattern.test(value) || value.length < 3) {
            showFieldError(field, 'Username must be at least 3 characters and contain only letters, numbers, and underscores');
            return false;
        }
    }
    
    return true;
}

function validatePasswordMatch() {
    const password = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="confirmPassword"]');
    
    if (password && confirmPassword && confirmPassword.value) {
        if (password.value !== confirmPassword.value) {
            showFieldError(confirmPassword, 'Passwords do not match');
            return false;
        } else {
            clearFieldError({ target: confirmPassword });
            return true;
        }
    }
    
    return true;
}

function showFieldError(field, message) {
    field.classList.add('error');
    
    // Remove any existing error message
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    field.parentNode.appendChild(errorElement);
}

function clearFieldError(event) {
    const field = event.target;
    field.classList.remove('error');
    
    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

// Video Grid Interactions
function initVideoGrid() {
    const videoCards = document.querySelectorAll('.video-card');
    
    videoCards.forEach(card => {
        // Add keyboard navigation
        card.setAttribute('tabindex', '0');
        
        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                card.click();
            }
        });
        
        // Add loading state for video actions
        const actionButtons = card.querySelectorAll('.video-actions button');
        actionButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                if (!button.classList.contains('btn-danger')) {
                    button.disabled = true;
                    button.textContent = 'Loading...';
                }
            });
        });
    });
}

// Utility Functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatDuration(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search functionality
function initSearch() {
    const searchInput = document.querySelector('input[name="q"]');
    if (searchInput) {
        const debouncedSearch = debounce(performSearch, 300);
        searchInput.addEventListener('input', debouncedSearch);
    }
}

function performSearch() {
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.submit();
    }
}

// Video player enhancements
function initVideoPlayer() {
    const videoPlayers = document.querySelectorAll('video');
    
    videoPlayers.forEach(video => {
        // Add custom controls if needed
        video.addEventListener('loadedmetadata', () => {
            updateVideoDuration(video);
        });
        
        video.addEventListener('timeupdate', () => {
            updateVideoProgress(video);
        });
        
        // Add keyboard shortcuts
        video.addEventListener('keydown', (event) => {
            switch(event.key) {
                case ' ':
                    event.preventDefault();
                    video.paused ? video.play() : video.pause();
                    break;
                case 'ArrowLeft':
                    video.currentTime = Math.max(0, video.currentTime - 10);
                    break;
                case 'ArrowRight':
                    video.currentTime = Math.min(video.duration, video.currentTime + 10);
                    break;
                case 'f':
                    if (video.requestFullscreen) {
                        video.requestFullscreen();
                    }
                    break;
            }
        });
    });
}

function updateVideoDuration(video) {
    const durationDisplay = document.querySelector('.video-duration-display');
    if (durationDisplay && video.duration) {
        durationDisplay.textContent = formatDuration(Math.floor(video.duration));
    }
}

function updateVideoProgress(video) {
    const progressBar = document.querySelector('.video-progress-bar');
    if (progressBar && video.duration) {
        const progress = (video.currentTime / video.duration) * 100;
        progressBar.style.width = `${progress}%`;
    }
}

// Admin panel enhancements
function initAdminPanel() {
    // Confirm dangerous actions
    const dangerButtons = document.querySelectorAll('.btn-danger[data-confirm]');
    
    dangerButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const confirmMessage = button.getAttribute('data-confirm');
            if (!confirm(confirmMessage)) {
                event.preventDefault();
            }
        });
    });
    
    // Auto-refresh for system stats
    const statsContainer = document.querySelector('.admin-stats');
    if (statsContainer) {
        setInterval(refreshAdminStats, 30000); // Refresh every 30 seconds
    }
}

function refreshAdminStats() {
    fetch('/admin/api/stats')
        .then(response => response.json())
        .then(data => {
            updateStatsDisplay(data);
        })
        .catch(error => {
            console.error('Failed to refresh admin stats:', error);
        });
}

function updateStatsDisplay(stats) {
    const statElements = document.querySelectorAll('.stat-number');
    statElements.forEach(element => {
        const statType = element.getAttribute('data-stat');
        if (stats[statType] !== undefined) {
            element.textContent = stats[statType];
        }
    });
}

// Theme toggle (for future dark mode support)
function initThemeToggle() {
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
        
        // Apply saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.body.classList.toggle('dark-theme', savedTheme === 'dark');
        }
    }
}

function toggleTheme() {
    document.body.classList.toggle('dark-theme');
    const isDark = document.body.classList.contains('dark-theme');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

// Performance monitoring
function initPerformanceMonitoring() {
    // Track page load time
    window.addEventListener('load', () => {
        const loadTime = performance.now();
        if (loadTime > 3000) {
            console.warn('Page load time is slow:', loadTime + 'ms');
        }
    });
    
    // Track video upload performance
    const uploadForms = document.querySelectorAll('form[enctype="multipart/form-data"]');
    uploadForms.forEach(form => {
        form.addEventListener('submit', () => {
            const startTime = performance.now();
            form.setAttribute('data-upload-start', startTime);
        });
    });
}

// Error handling
window.addEventListener('error', (event) => {
    console.error('JavaScript error:', event.error);
    // In production, you might want to send this to an error tracking service
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason);
    // In production, you might want to send this to an error tracking service
});

// Export functions for use in other scripts
window.DigitalArchive = {
    formatFileSize,
    formatDuration,
    debounce,
    validateField,
    showFieldError,
    clearFieldError
};

// CSS for form validation errors
const style = document.createElement('style');
style.textContent = `
    .error {
        border-color: #e53e3e !important;
        box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1) !important;
    }
    
    .field-error {
        color: #e53e3e;
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .video-card:focus {
        outline: 2px solid #667eea;
        outline-offset: 2px;
    }
    
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
`;
document.head.appendChild(style);