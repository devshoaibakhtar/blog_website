<?php
// Check for active user session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    redirect('login');
}
$_SESSION['last_activity'] = time();

// Get the current user if logged in
$currentUser = getCurrentUser();

// Get unread notification count if user is logged in
$unreadNotificationCount = 0;
if (isLoggedIn()) {
    $conn = getDbConnection();
    $userId = $currentUser['id'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $unreadNotificationCount = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <link rel="icon" href="<?= SITE_URL ?>/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/user/main.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= SITE_URL ?>/?page=home"><?= SITE_NAME ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/?page=home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/?page=about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/?page=contact">Contact</a>
                    </li>
               
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/?page=dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/?page=posts">My Posts</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/?page=admin">Admin Panel</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <!-- Notifications Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell notification-bell-icon"></i>
                                <?php if ($unreadNotificationCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount ?>
                                        <span class="visually-hidden">unread notifications</span>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end notifications-dropdown" aria-labelledby="notificationsDropdown">
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <?php if ($unreadNotificationCount > 0): ?>
                                        <a href="#" id="markAllNotificationsRead" class="text-primary text-decoration-none fs-sm">
                                            <small>Mark all as read</small>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="dropdown-divider"></div>
                                <div class="notifications-container" id="notificationsContainer">
                                    <div class="text-center p-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php
                                // Get profile image from user data or session
                                $profileImage = !empty($currentUser['profile_image']) ? $currentUser['profile_image'] : ($_SESSION['user_profile_image'] ?? null);
                                
                                if (!empty($profileImage)) {
                                    $imageUrl = SITE_URL . '/' . $profileImage;
                                    $timestamp = time(); // Add timestamp to prevent caching
                                    $imageUrlWithCache = $imageUrl . '?t=' . $timestamp;
                                ?>
                                <img src="<?= $imageUrlWithCache ?>" class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;" 
                                     onerror="this.onerror=null; this.src='<?= SITE_URL ?>/assets/images/placeholder.svg';">
                                <?php } else { ?>
                                <i class="fas fa-user-circle me-2"></i>
                                <?php } ?>
                                <?= $currentUser['name'] ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/?page=profile">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/?page=logout">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/?page=login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/?page=register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php
        // Display flash messages if any
        $flash = getFlashMessage();
        if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?> 