<?php require_once 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Login</h4>
            </div>
            <div class="card-body">
                <form action="<?= SITE_URL ?>/?page=login" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                        <a href="<?= SITE_URL ?>/?page=google-auth" class="btn btn-danger">
                            <i class="fab fa-google me-2"></i> Login with Google
                        </a>
                    </div>
                </form>
            </div>
            <div class="card-footer text-muted text-center">
                Don't have an account? <a href="<?= SITE_URL ?>/?page=register">Register</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 