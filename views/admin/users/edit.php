<?php
/**
 * Edit User Admin Page
 */
$page_title = 'Edit User: ' . htmlspecialchars($user['name']);
require_once 'includes/header.php';
?>

<div class="admin-container">
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="admin-header">
            <h1 class="admin-title">Edit User</h1>
            <div>
                <a href="<?= SITE_URL ?>/?page=admin-users" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-header">
                Edit User
            </div>
            <div class="admin-card-body">
                <form action="<?= SITE_URL ?>/?page=admin-users&action=edit&id=<?= $user['id'] ?>" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                        <small class="form-text text-muted">Username cannot be changed.</small>
                    </div>
                    <div class="form-group">
                        <label for="name">Display Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role" <?= $user['id'] === $_SESSION['user']['id'] ? 'disabled' : '' ?>>
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="author" <?= $user['role'] === 'author' ? 'selected' : '' ?>>Author</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <?php if ($user['id'] === $_SESSION['user']['id']): ?>
                            <small class="form-text text-muted">You cannot change your own role.</small>
                            <input type="hidden" name="role" value="<?= $user['role'] ?>">
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="status" name="status" <?= $user['status'] === 'active' ? 'checked' : '' ?> <?= $user['id'] === $_SESSION['user']['id'] ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="status">
                                Active
                            </label>
                        </div>
                        <?php if ($user['id'] === $_SESSION['user']['id']): ?>
                            <small class="form-text text-muted">You cannot deactivate your own account.</small>
                            <input type="hidden" name="status" value="active">
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="5"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="<?= SITE_URL ?>/?page=admin-users" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 