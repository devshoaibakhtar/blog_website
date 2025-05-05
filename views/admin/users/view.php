<?php
/**
 * View User Details Admin Page
 */
$page_title = 'View User: ' . htmlspecialchars($user['name']);
require_once 'includes/header.php';
?>

<div class="admin-container">
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="admin-header">
            <h1 class="admin-title">User Details</h1>
            <div>
                <a href="<?= SITE_URL ?>/?page=admin-users" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <a href="<?= SITE_URL ?>/?page=admin-users&action=edit&id=<?= $user['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit User
                </a>
                <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                    <a href="<?= SITE_URL ?>/?page=admin-users&action=delete&id=<?= $user['id'] ?>" class="btn btn-danger delete-user">
                        <i class="fas fa-trash"></i> Delete User
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-header">
                User Information
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Username:</strong> <?= htmlspecialchars($user['username']) ?>
                        </div>
                        <div class="mb-3">
                            <strong>Display Name:</strong> <?= htmlspecialchars($user['display_name']) ?>
                        </div>
                        <div class="mb-3">
                            <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Role:</strong> <span class="badge badge-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'author' ? 'success' : 'secondary') ?>"><?= ucfirst($user['role']) ?></span>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong> <span class="badge badge-<?= $user['status'] === 'active' ? 'success' : 'warning' ?>"><?= ucfirst($user['status']) ?></span>
                        </div>
                        <div class="mb-3">
                            <strong>Joined:</strong> <?= formatDate($user['created_at']) ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($user['bio'])): ?>
                    <div class="mb-3">
                        <strong>Bio:</strong>
                        <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <strong>Posts:</strong> <?= $user['post_count'] ?>
                </div>
                <div class="mb-3">
                    <strong>Comments:</strong> <?= $user['comment_count'] ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Posts -->
        <?php if (!empty($recentPosts)): ?>
            <div class="admin-card mt-4">
                <div class="admin-card-header">
                    Recent Posts
                </div>
                <div class="admin-card-body">
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPosts as $post): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($post['title']) ?></td>
                                        <td><?= htmlspecialchars($post['taxonomy_name'] ?? 'Uncategorized') ?></td>
                                        <td><span class="badge badge-<?= getStatusBadgeClass($post['status']) ?>"><?= ucfirst($post['status']) ?></span></td>
                                        <td><?= formatDate($post['created_at']) ?></td>
                                        <td>
                                            <a href="<?= SITE_URL ?>/?page=post&slug=<?= urlencode($post['slug']) ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= SITE_URL ?>/?page=admin-post-edit&id=<?= $post['id'] ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Recent Comments -->
        <?php if (!empty($recentComments)): ?>
            <div class="admin-card mt-4">
                <div class="admin-card-header">
                    Recent Comments
                </div>
                <div class="admin-card-body">
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Post</th>
                                    <th>Comment</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentComments as $comment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($comment['post_title']) ?></td>
                                        <td><?= htmlspecialchars(substr($comment['content'], 0, 100)) . (strlen($comment['content']) > 100 ? '...' : '') ?></td>
                                        <td><?= formatDate($comment['created_at']) ?></td>
                                        <td>
                                            <a href="<?= SITE_URL ?>/?page=post&slug=<?= urlencode($comment['post_slug']) ?>#comment-<?= $comment['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteButton = document.querySelector('.delete-user');
    if (deleteButton) {
        deleteButton.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 