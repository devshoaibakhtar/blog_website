<?php
/**
 * Delete User Confirmation Page
 */
$page_title = 'Delete User: ' . htmlspecialchars($user['name']);
require_once 'includes/header.php';
?>

<div class="admin-container">
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="admin-header">
            <h1 class="admin-title">Delete User</h1>
            <div>
                <a href="<?= SITE_URL ?>/?page=admin-users" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-header">
                Confirm Deletion
            </div>
            <div class="admin-card-body">
                <div class="alert alert-danger">
                    <h4><i class="fas fa-exclamation-triangle"></i> Warning!</h4>
                    <p>You are about to delete the user <strong><?= htmlspecialchars($user['name']) ?></strong>. This action cannot be undone.</p>
                    <p>All content created by this user will be deleted, including:</p>
                    <ul>
                        <li>Posts (<?= $user['post_count'] ?? 0 ?>)</li>
                        <li>Comments (<?= $user['comment_count'] ?? 0 ?>)</li>
                    </ul>
                </div>
                
                <form action="<?= SITE_URL ?>/?page=admin-users&action=delete&id=<?= $user['id'] ?>" method="POST">
                    <input type="hidden" name="confirm_delete" value="yes">
                    <button type="submit" class="btn btn-danger">Yes, Delete This User</button>
                    <a href="<?= SITE_URL ?>/?page=admin-users" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 