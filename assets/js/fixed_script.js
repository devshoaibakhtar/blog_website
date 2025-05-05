/**
 * Custom JavaScript for Blog Management System
 */

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Automatically close alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Comment form validation
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
            const contentField = document.getElementById('content');
            if (!contentField.value.trim()) {
                alert('Please enter a comment before submitting.');
                return;
            }
            
            // Submit the main comment form via AJAX
            submitCommentForm(this);
        });
    }

    // Post form validation
    const postForm = document.getElementById('postForm');
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            const title = document.getElementById('title');
            const content = document.getElementById('content');
            const taxonomy = document.getElementById('taxonomy');
            
            if (!title.value.trim() || !content.value.trim() || !taxonomy.value) {
                e.preventDefault();
                alert('Please fill in all required fields before submitting.');
            }
        });
    }

    // Confirmation for delete actions
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Toggle sidebar in admin panel
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.admin-sidebar').classList.toggle('d-none');
        });
    }
    
    // Copy link functionality
    const copyLinkButtons = document.querySelectorAll('.copy-link-btn');
    if (copyLinkButtons.length > 0) {
        copyLinkButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const postUrl = this.getAttribute('data-post-url');
                navigator.clipboard.writeText(postUrl).then(
                    function() {
                        // Create toast notification
                        const toastContainer = document.createElement('div');
                        toastContainer.classList.add('position-fixed', 'bottom-0', 'end-0', 'p-3');
                        toastContainer.style.zIndex = '11';
                        
                        const toastHtml = `
                            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="toast-header">
                                    <i class="fas fa-link me-2"></i>
                                    <strong class="me-auto">Link Copied</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                                <div class="toast-body">
                                    Link copied to clipboard!
                                </div>
                            </div>
                        `;
                        
                        toastContainer.innerHTML = toastHtml;
                        document.body.appendChild(toastContainer);
                        
                        // Auto-remove toast after 3 seconds
                        setTimeout(function() {
                            toastContainer.remove();
                        }, 3000);
                    },
                    function(err) {
                        console.error('Could not copy text: ', err);
                        alert('Failed to copy link to clipboard');
                    }
                );
            });
        });
    }
    
    // Notifications dropdown functionality
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    const notificationsContainer = document.getElementById('notificationsContainer');
    const markAllReadBtn = document.getElementById('markAllNotificationsRead');
    
    if (notificationsDropdown && notificationsContainer) {
        // Load notifications when dropdown is opened
        notificationsDropdown.addEventListener('show.bs.dropdown', function () {
            loadNotifications();
        });
        
        // Handle click events within notification container
        notificationsContainer.addEventListener('click', function(e) {
            // Check if clicked on mark as read button
            const markReadBtn = e.target.closest('.notification-mark-read');
            if (markReadBtn) {
                e.preventDefault();
                e.stopPropagation();
                const notificationId = markReadBtn.getAttribute('data-id');
                markNotificationAsRead(notificationId, markReadBtn.closest('.notification-item'));
                return false;
            }
        });
    }
    
    // Mark all notifications as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllNotificationsAsRead();
        });
    }

    // Post like functionality
    initPostLikes();

    // Initialize comment reply functionality
    initCommentReplies();
});

/**
 * Format date as "time ago"
 */
function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 7) {
        return date.toLocaleDateString();
    } else if (days > 0) {
        return days + (days === 1 ? ' day ago' : ' days ago');
    } else if (hours > 0) {
        return hours + (hours === 1 ? ' hour ago' : ' hours ago');
    } else if (minutes > 0) {
        return minutes + (minutes === 1 ? ' minute ago' : ' minutes ago');
    } else {
        return 'Just now';
    }
}

/**
 * Load notifications via AJAX
 */
function loadNotifications() {
    const container = document.getElementById('notificationsContainer');
    if (!container) return;
    
    // Show loading spinner
    container.innerHTML = `
        <div class="text-center p-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Fetch notifications
    fetch(siteUrl + '/?page=notifications', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.notifications && data.notifications.length > 0) {
            let html = '';
            
            data.notifications.forEach(notification => {
                let icon = '';
                let linkUrl = '#';
                
                if (notification.type === 'post_approval') {
                    icon = '<i class="fas fa-check-circle text-success me-2"></i>';
                    linkUrl = siteUrl + '/?page=view-post&id=' + notification.reference_id;
                } else if (notification.type === 'post_rejection') {
                    icon = '<i class="fas fa-times-circle text-danger me-2"></i>';
                    linkUrl = siteUrl + '/?page=view-post&id=' + notification.reference_id;
                } else if (notification.type === 'comment') {
                    icon = '<i class="fas fa-comment text-primary me-2"></i>';
                    linkUrl = siteUrl + '/?page=view-post&id=' + notification.reference_id + '#comment-' + notification.id;
                } else if (notification.type === 'profile_update') {
                    icon = '<i class="fas fa-user-edit text-info me-2"></i>';
                    linkUrl = siteUrl + '/?page=profile';
                } else if (notification.type === 'like') {
                    icon = '<i class="fas fa-heart text-danger me-2"></i>';
                    linkUrl = siteUrl + '/?page=view-post&id=' + notification.reference_id;
                } else if (notification.type === 'follow') {
                    icon = '<i class="fas fa-user-plus text-success me-2"></i>';
                    linkUrl = siteUrl + '/?page=profile&id=' + notification.reference_id;
                } else if (notification.type === 'mention') {
                    icon = '<i class="fas fa-at text-warning me-2"></i>';
                    linkUrl = siteUrl + '/?page=view-post&id=' + notification.reference_id;
                } else if (notification.type === 'tag') {
                    icon = '<i class="fas fa-tag text-secondary me-2"></i>';
                    linkUrl = siteUrl + '/?page=view-post&id=' + notification.reference_id;
                } else if (notification.type === 'admin') {
                    icon = '<i class="fas fa-shield-alt text-danger me-2"></i>';
                    linkUrl = siteUrl + '/?page=notifications';
                } else if (notification.type === 'test') {
                    icon = '<i class="fas fa-bell text-warning me-2"></i>';
                    linkUrl = '#';
                } else {
                    // Default icon for any other types
                    icon = '<i class="fas fa-bell text-secondary me-2"></i>';
                }
                
                html += `
                    <a href="${linkUrl}" class="dropdown-item notification-item ${notification.is_read == 0 ? 'unread' : ''}">
                        <div class="d-flex align-items-center">
                            ${icon}
                            <div class="flex-grow-1">
                                <div class="small">${notification.content}</div>
                                <div class="small text-muted">${formatTimeAgo(notification.created_at)}</div>
                            </div>
                            ${notification.is_read == 0 ? `
                                <button class="btn btn-sm text-primary notification-mark-read" data-id="${notification.id}">
                                    <i class="fas fa-check"></i>
                                </button>
                            ` : ''}
                        </div>
                    </a>
                `;
            });
            
            container.innerHTML = html;
            
            // Add event listeners to mark-read buttons
            const markReadButtons = container.querySelectorAll('.notification-mark-read');
            markReadButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const notificationId = this.getAttribute('data-id');
                    markNotificationAsRead(notificationId, this.closest('.notification-item'));
                });
            });
        } else {
            container.innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-bell-slash fa-2x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No notifications yet</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error fetching notifications:', error);
        container.innerHTML = `
            <div class="text-center p-3">
                <p class="text-danger mb-0">Failed to load notifications</p>
            </div>
        `;
    });
}

/**
 * Mark a notification as read
 */
function markNotificationAsRead(notificationId, element) {
    if (!notificationId || !element) return;
    
    fetch(siteUrl + '/?page=notifications&action=mark-read&id=' + notificationId, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            element.classList.remove('unread');
            const markButton = element.querySelector('.notification-mark-read');
            if (markButton) {
                markButton.remove();
            }
            
            // Update notification count
            updateNotificationCount();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

/**
 * Update notification count in badge
 */
function updateNotificationCount() {
    fetch(siteUrl + '/?page=notifications', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const badge = document.querySelector('#notificationsDropdown .badge');
        if (data.unreadCount > 0) {
            if (badge) {
                badge.textContent = data.unreadCount > 9 ? '9+' : data.unreadCount;
            } else {
                const newBadge = document.createElement('span');
                newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                newBadge.textContent = data.unreadCount > 9 ? '9+' : data.unreadCount;
                
                const hiddenSpan = document.createElement('span');
                hiddenSpan.className = 'visually-hidden';
                hiddenSpan.textContent = 'unread notifications';
                
                newBadge.appendChild(hiddenSpan);
                document.querySelector('#notificationsDropdown').appendChild(newBadge);
            }
        } else if (badge) {
            badge.remove();
        }
    })
    .catch(error => {
        console.error('Error updating notification count:', error);
    });
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsAsRead() {
    fetch(siteUrl + '/?page=notifications&action=mark-all-read', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const unreadNotifications = document.querySelectorAll('.notification-item.unread');
            unreadNotifications.forEach(item => {
                item.classList.remove('unread');
                const markButton = item.querySelector('.notification-mark-read');
                if (markButton) {
                    markButton.remove();
                }
            });
            
            // Update badge
            const badge = document.querySelector('#notificationsDropdown .badge');
            if (badge) {
                badge.remove();
            }
            
            // Hide mark all as read button
            const markAllBtn = document.getElementById('markAllNotificationsRead');
            if (markAllBtn) {
                markAllBtn.style.display = 'none';
            }
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

/**
 * Handle post likes
 */
function initPostLikes() {
    const likeButtons = document.querySelectorAll('.post-like-btn');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            likePost(postId, this);
        });
    });
}

/**
 * Like a post
 */
function likePost(postId, buttonElement) {
    // Check if user is logged in
    if (!isUserLoggedIn()) {
        // Redirect to login page with return URL
        window.location.href = `${siteUrl}/?page=login&redirect=${encodeURIComponent(window.location.href)}`;
        return;
    }
    
    // Visual feedback while waiting for response
    buttonElement.disabled = true;
    const originalContent = buttonElement.innerHTML;
    buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    
    // Send like request to server
    fetch(`${siteUrl}/?page=like-post`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `post_id=${postId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update like count
            const likeCount = buttonElement.querySelector('.like-count');
            if (likeCount) {
                likeCount.textContent = `(${data.likeCount})`;
            }
            
            // Change button appearance based on liked status with a smooth transition
            if (data.liked) {
                buttonElement.classList.remove('btn-outline-secondary');
                buttonElement.classList.add('btn-secondary', 'liked');
                buttonElement.innerHTML = '<i class="fas fa-thumbs-up me-1"></i> Liked <span class="like-count">(' + data.likeCount + ')</span>';
            } else {
                buttonElement.classList.remove('btn-secondary', 'liked');
                buttonElement.classList.add('btn-outline-secondary', 'unliked');
                buttonElement.innerHTML = '<i class="far fa-thumbs-up me-1"></i> Like <span class="like-count">(' + data.likeCount + ')</span>';
            }
        } else {
            // Restore original button content
            buttonElement.innerHTML = originalContent;
            
            // Show error message
            alert(data.message || 'Failed to process like. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error liking post:', error);
        buttonElement.innerHTML = originalContent;
        
        // Create a more detailed error message
        let errorMsg = 'Failed to process like. ';
        if (error.message) {
            errorMsg += error.message;
        } else {
            errorMsg += 'Please try again.';
        }
        
        alert(errorMsg);
    })
    .finally(() => {
        buttonElement.disabled = false;
    });
}

/**
 * Show an error toast message
 */
function showErrorToast(message) {
    const toastContainer = document.createElement('div');
    toastContainer.classList.add('position-fixed', 'bottom-0', 'end-0', 'p-3');
    toastContainer.style.zIndex = '11';
    
    const toastHtml = `
        <div class="toast show error-toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    // Auto-remove toast after 4 seconds
    setTimeout(function() {
        toastContainer.querySelector('.toast').classList.add('fade-out');
        setTimeout(function() {
            toastContainer.remove();
        }, 500);
    }, 3500);
}

/**
 * Helper function to check if user is logged in
 */
function isUserLoggedIn() {
    // This is a simple client-side check based on presence of user info in the page
    // The server will do a proper authentication check
    return document.querySelector('#navbarDropdown') !== null;
}

/**
 * Initialize comment reply functionality
 */
function initCommentReplies() {
    // Get all reply buttons
    const replyButtons = document.querySelectorAll('.reply-button');
    
    if (replyButtons.length === 0) return;
    
    // Add click event listener to each reply button
    replyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const authorName = this.getAttribute('data-author');
            toggleReplyForm(commentId, authorName);
        });
    });
    
    // Handle reply form submission via event delegation
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('reply-form')) {
            e.preventDefault();
            submitReply(e.target);
        }
    });
}

/**
 * Toggle the reply form for a comment
 */
function toggleReplyForm(commentId, authorName) {
    const container = document.querySelector(`.reply-form-container-${commentId}`);
    
    if (!container) return;
    
    // If the form already exists, just toggle visibility
    if (container.querySelector('form')) {
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
        return;
    }
    
    // Create the reply form
    const formHtml = `
        <form class="reply-form mt-2 mb-3" action="${siteUrl}/?page=add-comment" method="post">
            <input type="hidden" name="post_id" value="${getPostIdFromUrl()}">
            <input type="hidden" name="parent_id" value="${commentId}">
            <div class="form-group">
                <textarea name="content" class="form-control form-control-sm" rows="2" placeholder="Reply to ${authorName}..." required></textarea>
            </div>
            <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary me-2 cancel-reply-btn">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary">Submit Reply</button>
            </div>
        </form>
    `;
    
    // Add the form to the container
    container.innerHTML = formHtml;
    container.style.display = 'block';
    
    // Focus on the textarea
    const textarea = container.querySelector('textarea');
    if (textarea) {
        textarea.focus();
    }
    
    // Add cancel button event listener
    const cancelButton = container.querySelector('.cancel-reply-btn');
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            container.style.display = 'none';
        });
    }
}

/**
 * Get post ID from the current URL
 */
function getPostIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || 0;
}

/**
 * Submit a reply to a comment
 */
function submitReply(form) {
    // Get necessary data
    const postId = form.querySelector('input[name="post_id"]').value;
    const parentId = form.querySelector('input[name="parent_id"]').value;
    const content = form.querySelector('textarea[name="content"]').value;
    
    if (!content.trim()) {
        alert('Please enter a reply before submitting.');
        return;
    }
    
    // Disable the submit button
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    // Send the request
    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `post_id=${postId}&parent_id=${parentId}&content=${encodeURIComponent(content)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.message || 'Reply submitted successfully!');
            
            // Hide the form
            form.parentElement.style.display = 'none';
            
            // Reload the page to show the new reply
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert(data.message || 'Failed to submit reply. Please try again.');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    })
    .catch(error => {
        console.error('Error submitting reply:', error);
        alert('An error occurred while submitting your reply. Please try again.');
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    });
}

/**
 * Submit the main comment form via AJAX
 */
function submitCommentForm(form) {
    // Disable the submit button
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    // Get form data
    const formData = new FormData(form);
    
    // Convert FormData to URL-encoded string
    const urlEncodedData = new URLSearchParams();
    for (const [name, value] of formData) {
        urlEncodedData.append(name, value);
    }
    
    // Send AJAX request
    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: urlEncodedData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const toastContainer = document.createElement('div');
            toastContainer.classList.add('position-fixed', 'bottom-0', 'end-0', 'p-3');
            toastContainer.style.zIndex = '11';
            
            const toastHtml = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <i class="fas fa-comment me-2 text-primary"></i>
                        <strong class="me-auto">Success</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${data.message}
                    </div>
                </div>
            `;
            
            toastContainer.innerHTML = toastHtml;
            document.body.appendChild(toastContainer);
            
            // Clear the form
            form.reset();
            
            // Reload the page after a short delay to show the new comment
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            // Show error message
            alert(data.message || 'Failed to submit comment. Please try again.');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
        alert('An error occurred while submitting your comment. Please try again.');
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    });
} 