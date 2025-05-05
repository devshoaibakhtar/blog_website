<?php
/**
 * Main entry point for the Blog Management System
 */

// Load configuration
require_once 'config/config.php';

// Include helper functions
require_once 'includes/helpers.php';

// Handle routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Check if the requested page exists in routes
if (isset($routes[$page])) {
    require_once $routes[$page];
} else {
    // If page not found, display 404 error
    header("HTTP/1.0 404 Not Found");
    require_once 'views/404.php';
} 