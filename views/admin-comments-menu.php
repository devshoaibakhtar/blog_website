<?php require_once dirname(__DIR__) . '/includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-comments me-2"></i> Comments Management</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p>Welcome to the comments management system. From here, you can moderate comments, remove abusive content, and manage user interactions.</p>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <a href="<?= SITE_URL ?>/?page=admin-comments" class="btn btn-lg btn-primary">
                            <i class="fas fa-comments me-2"></i> Access Comments Dashboard
                        </a>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <a href="<?= SITE_URL ?>/?page=admin-comments&status=pending" class="btn btn-warning w-100">
                                    <i class="fas fa-clock me-2"></i> Pending Comments
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?= SITE_URL ?>/?page=admin-comments&status=spam" class="btn btn-danger w-100">
                                    <i class="fas fa-ban me-2"></i> Spam Comments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= SITE_URL ?>/?page=admin" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Admin Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?> 