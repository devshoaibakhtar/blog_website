<?php
/**
 * Admin Comments Menu Controller
 * Displays a menu of options for managing comments
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    // Set flash message
    setFlashMessage('danger', 'You must be an admin to access this page.');
    
    // Redirect to login
    echo '<script>window.location.href = "' . SITE_URL . '/?page=login";</script>';
    exit;
}

// Include the comments menu view
require_once 'views/admin-comments-menu.php'; 