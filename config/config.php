<?php
/**
 * Main Configuration File
 */

// Site settings
define('SITE_NAME', 'WriteWise');
define('SITE_URL', 'http://localhost/blog');
define('ADMIN_EMAIL', 'admin@writewise.com');

// Session settings
session_start();
define('SESSION_TIMEOUT', 1800); // 30 minutes

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Fix upload directory path for cross-platform compatibility
$upload_path = $_SERVER['DOCUMENT_ROOT'] . '/blog/uploads/';
// On Windows, convert backslashes to forward slashes for consistency
$upload_path = str_replace('\\', '/', $upload_path);
define('UPLOAD_DIR', $upload_path);

// Enable error logging for file upload issues
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// Google OAuth settings
define('GOOGLE_CLIENT_ID', '544444460999-1lugsj91t4dq5b55q69v96pu2g29ivq3.apps.googleusercontent.com'); // Replace with actual client ID
define('GOOGLE_CLIENT_SECRET', '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'); // Replace with actual client secret
define('GOOGLE_REDIRECT_URI', SITE_URL . '/?page=google-callback');


// Email settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Replace with your email
define('SMTP_PASSWORD', 'your-app-password'); // Replace with your app password
define('SMTP_FROM_EMAIL', 'noreply@writewise.com');
define('SMTP_FROM_NAME', SITE_NAME);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'database.php';

// Include helper functions
require_once __DIR__ . '/../includes/helpers.php';

// Define routes
$routes = [
    'home' => 'controllers/home.php',
    'login' => 'controllers/auth/login.php',
    'register' => 'controllers/auth/register.php',
    'logout' => 'controllers/auth/logout.php',
    'google-auth' => 'controllers/auth/google-auth.php',
    'google-callback' => 'controllers/auth/google-callback.php',
    'dashboard' => 'controllers/dashboard.php',
    'profile' => 'controllers/profile.php',
    'notifications' => 'controllers/notifications.php',
    'upload' => 'controllers/upload.php',
    'posts' => 'controllers/posts/index.php',
    'create-post' => 'controllers/posts/create.php',
    'edit-post' => 'controllers/posts/edit.php',
    'view-post' => 'controllers/posts/view-post.php',
    'delete-post' => 'controllers/posts/delete-post.php',
    'admin' => 'controllers/admin/index.php',
    'admin-posts' => 'controllers/admin/posts.php',
    'admin-users' => 'controllers/admin/users.php',
    'admin-delete-user' => 'controllers/admin/delete-user.php',
    'admin-comments' => 'controllers/admin/comments.php',
    'admin-contact-messages' => 'controllers/admin/contact-messages.php',
    'about' => 'controllers/about.php',
    'contact' => 'controllers/contact.php',
    'terms' => 'controllers/terms.php',
    'privacy' => 'controllers/privacy.php',
    'like-post' => 'controllers/like-post.php',
    'comments' => 'controllers/comments.php',
    'add-comment' => 'controllers/add-comment.php',
    'delete-comment' => 'controllers/delete-comment.php',
]; 