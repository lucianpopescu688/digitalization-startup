// Digital Archive Management System - JavaScript

// Modal functionality
function viewVideo(videoId) {
    const modal = document.getElementById('videoModal');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    
    // Show loading
    modalContent.innerHTML = '<div class="loading"></div> Loading video details...';
    modal.style.display = 'block';
    
    // Fetch video details
    fetch(`../controllers/get-video.php?id=${videoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const video = data.video;
                modalTitle.textContent = video.title;
                modalContent.innerHTML = `
                    <div class="video-details">
                        <h3>${video.title}</h3>
                        <p><strong>Description:</strong> ${video.description || 'No description'}</p>
                        <p><strong>Format:</strong> ${video.format || 'Unknown'}</p>
                        <p><strong>File Size:</strong> ${formatFileSize(video.file_size)}</p>
                        <p><strong>Duration:</strong> ${formatDuration(video.duration)}</p>
                        <p><strong>Uploaded by:</strong> ${video.uploader_name || 'Unknown'}</p>
                        <p><strong>Upload Date:</strong> ${new Date(video.created_at).toLocaleDateString()}</p>
                        ${video.tags ? `<p><strong>Tags:</strong> ${video.tags}</p>` : ''}
                        <div class="mt-2">
                            <a href="edit-video.php?id=${video.id}" class="btn btn-secondary">Edit Video</a>
                            <button onclick="closeModal()" class="btn">Close</button>
                        </div>
                    </div>
                `;
            } else {
                modalContent.innerHTML = '<div class="alert alert-error">Failed to load video details.</div>';
            }
        })
        .catch(error => {
            modalContent.innerHTML = '<div class="alert alert-error">Error loading video details.</div>';
        });
}

function closeModal() {
    document.getElementById('videoModal').style.display = 'none';
}

// Delete video confirmation
function deleteVideo(videoId) {
    if (confirm('Are you sure you want to delete this video? This action cannot be undone.')) {
        // Show loading
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Deleting...';
        button.disabled = true;
        
        fetch('../controllers/delete-video.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${videoId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the video card from the grid
                button.closest('.video-card').remove();
                alert('Video deleted successfully.');
            } else {
                alert('Failed to delete video: ' + (data.message || 'Unknown error'));
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            alert('Error deleting video.');
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}

// File upload validation
function validateVideoUpload(input) {
    const file = input.files[0];
    const maxSize = 500 * 1024 * 1024; // 500MB
    const allowedTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/x-flv', 'video/x-matroska'];
    
    if (file) {
        if (file.size > maxSize) {
            alert('File size must be less than 500MB.');
            input.value = '';
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid video file (MP4, AVI, MOV, WMV, FLV, MKV).');
            input.value = '';
            return false;
        }
        
        // Update file info display
        const fileInfo = document.getElementById('fileInfo');
        if (fileInfo) {
            fileInfo.innerHTML = `
                <p><strong>File:</strong> ${file.name}</p>
                <p><strong>Size:</strong> ${formatFileSize(file.size)}</p>
                <p><strong>Type:</strong> ${file.type}</p>
            `;
        }
    }
    
    return true;
}

// Form validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Utility functions
function formatFileSize(bytes) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let size = bytes;
    let unitIndex = 0;
    
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return Math.round(size * 100) / 100 + ' ' + units[unitIndex];
}

function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    if (hours > 0) {
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    } else {
        return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('videoModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Form submit handlers
document.addEventListener('DOMContentLoaded', function() {
    // Handle file upload forms
    const uploadForms = document.querySelectorAll('form[enctype="multipart/form-data"]');
    uploadForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Handle video file inputs
    const videoInputs = document.querySelectorAll('input[type="file"][accept*="video"]');
    videoInputs.forEach(input => {
        input.addEventListener('change', function() {
            validateVideoUpload(this);
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Search functionality with debounce
let searchTimeout;
function debounceSearch(func, delay) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(func, delay);
}

// Live search (if implemented)
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        debounceSearch(() => {
            // Could implement live search here
        }, 500);
    });
}

// Progress bar for file uploads
function updateUploadProgress(percent) {
    const progressBar = document.getElementById('uploadProgress');
    if (progressBar) {
        progressBar.style.width = percent + '%';
        progressBar.textContent = Math.round(percent) + '%';
    }
}

// AJAX file upload with progress
function uploadFileWithProgress(formData, onSuccess, onError) {
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            updateUploadProgress(percentComplete);
        }
    });
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    onSuccess(response);
                } else {
                    onError(response.message || 'Upload failed');
                }
            } catch (e) {
                onError('Invalid server response');
            }
        } else {
            onError('Upload failed');
        }
    };
    
    xhr.onerror = function() {
        onError('Network error');
    };
    
    xhr.open('POST', '../controllers/upload-video.php');
    xhr.send(formData);
}