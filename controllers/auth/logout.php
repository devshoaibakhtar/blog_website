<?php
/**
 * Logout Controller
 */

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to login page
setFlashMessage('success', 'You have been successfully logged out.');
redirect('login'); 