<?php
// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    // Set flash message
    setFlashMessage('danger', 'You must be logged in as an admin to access this page.');
    // Redirect to login
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

// Get current page from URL
$currentPage = $_GET['page'] ?? 'admin';

// Include main site header to show the navbar
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <!-- Admin Navigation -->

    <!-- Main Content -->
    <div class="admin-content">
        <?php
        // Display flash messages if any
        $flash = getFlashMessage();
        if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?> 