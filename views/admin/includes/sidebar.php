<?php
/**
 * Admin Sidebar Navigation
 * Provides navigation links for the admin panel
 */

// Get current page from URL
$currentPage = $_GET['page'] ?? 'admin';
?>

<div class="admin-sidebar">
    <div class="sidebar-header">
        <a href="<?= SITE_URL ?>/?page=admin" class="sidebar-brand">
            <i class="fas fa-tachometer-alt"></i> Admin Panel
        </a>
    </div>
    
    <ul class="sidebar-menu">
        <li class="sidebar-item">
            <a href="<?= SITE_URL ?>/?page=admin" class="sidebar-link <?= $currentPage === 'admin' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= SITE_URL ?>/?page=admin-posts" class="sidebar-link <?= $currentPage === 'admin-posts' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i> Posts
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= SITE_URL ?>/?page=admin-comments" class="sidebar-link <?= $currentPage === 'admin-comments' ? 'active' : '' ?>">
                <i class="fas fa-comments"></i> Comments
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= SITE_URL ?>/?page=admin-users" class="sidebar-link <?= $currentPage === 'admin-users' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= SITE_URL ?>/?page=admin-categories" class="sidebar-link <?= $currentPage === 'admin-categories' ? 'active' : '' ?>">
                <i class="fas fa-folder"></i> Categories
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= SITE_URL ?>/?page=admin-tags" class="sidebar-link <?= $currentPage === 'admin-tags' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Tags
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= SITE_URL ?>/?page=admin-contact-messages" class="sidebar-link <?= $currentPage === 'admin-contact-messages' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Contact Messages
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= SITE_URL ?>/?page=admin-settings" class="sidebar-link <?= $currentPage === 'admin-settings' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="<?= SITE_URL ?>/?page=home" class="sidebar-link">
            <i class="fas fa-arrow-left"></i> Back to Site
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
});
</script> 