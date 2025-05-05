<?php
// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    // Set flash message
    setFlashMessage('danger', 'You must be logged in as an admin to access this page.');
    // Redirect to login
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Admin Panel' ?> | <?= SITE_NAME ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<?php
// Display flash messages if any
$flash = getFlashMessage();
if ($flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= $flash['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?> 