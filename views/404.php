<?php require_once 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="error-template text-center">
            <h1>Oops!</h1>
            <h2>404 Not Found</h2>
            <div class="error-details mb-4">
                Sorry, the page you requested was not found.
            </div>
            <div class="error-actions">
                <a href="<?= SITE_URL ?>/?page=home" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Go Home
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>