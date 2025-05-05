<?php 
$page_title = "Admin Dashboard";
require_once 'header.php'; 
?>

<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">

<div class="admin-header">
    <h1 class="admin-title">Admin Dashboard</h1>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-body">
            <div class="stat-card-content">
                <div class="stat-card-title">Total Users</div>
                <h3><?= $usersCount ?></h3>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-card-footer">
            <a href="#user-list" class="scroll-to-users">Manage Users</a>
        </div>
    </div>
    
    <div class="stat-card stat-card-success">
        <div class="stat-card-body">
            <div class="stat-card-content">
                <div class="stat-card-title">Total Posts</div>
                <h3><?= $postsCount ?></h3>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
        <div class="stat-card-footer">
            <a href="<?= SITE_URL ?>/?page=admin-posts">Manage Posts</a>
        </div>
    </div>
    
    <div class="stat-card stat-card-warning">
        <div class="stat-card-body">
            <div class="stat-card-content">
                <div class="stat-card-title">Pending Posts</div>
                <h3><?= $pendingPostsCount ?></h3>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-card-footer">
            <a href="<?= SITE_URL ?>/?page=admin-posts&status=pending">View Pending</a>
        </div>
    </div>
    
    <div class="stat-card stat-card-info">
        <div class="stat-card-body">
            <div class="stat-card-content">
                <div class="stat-card-title">Comments</div>
                <?php 
                // Get comments count
                $commentsStmt = $conn->prepare("SELECT COUNT(*) FROM comments");
                $commentsStmt->execute();
                $commentsCount = $commentsStmt->fetchColumn();
                ?>
                <h3><?= $commentsCount ?></h3>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-comments"></i>
            </div>
        </div>
        <div class="stat-card-footer">
            <a href="<?= SITE_URL ?>/?page=admin-comments">Manage Comments</a>
        </div>
    </div>
    
    <div class="stat-card stat-card-danger">
        <div class="stat-card-body">
            <div class="stat-card-content">
                <div class="stat-card-title">Contact Messages</div>
                <?php 
                // Get contact message count
                $contactMessagesStmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages");
                $contactMessagesStmt->execute();
                $contactMessagesCount = $contactMessagesStmt->fetchColumn();
                
                // Get unread contact message count
                $contactUnreadStmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE `read` = 0");
                $contactUnreadStmt->execute();
                $contactUnreadCount = $contactUnreadStmt->fetchColumn();
                ?>
                <h3><?= $contactMessagesCount ?></h3>
                <?php if ($contactUnreadCount > 0): ?>
                    <span class="badge bg-warning"><?= $contactUnreadCount ?> unread</span>
                <?php endif; ?>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-envelope"></i>
            </div>
        </div>
        <div class="stat-card-footer">
            <a href="<?= SITE_URL ?>/?page=admin-contact-messages">View Messages</a>
        </div>
    </div>
</div>

<!-- Pending Posts -->
<div class="admin-card">
    <div class="admin-card-header">
        Pending Posts
    </div>
    <div class="admin-card-body">
        <?php if (empty($pendingPosts)): ?>
            <p class="text-muted">No pending posts at the moment.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingPosts as $post): ?>
                            <tr>
                                <td><?= htmlspecialchars($post['title']) ?></td>
                                <td><?= htmlspecialchars($post['author_name']) ?></td>
                                <td><?= htmlspecialchars($post['taxonomy_name']) ?></td>
                                <td><?= formatDate($post['created_at']) ?></td>
                                <td class="d-flex gap-10">
                                    <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $post['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/?page=admin-posts&action=approve&id=<?= $post['id'] ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/?page=admin-posts&action=reject&id=<?= $post['id'] ?>" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="admin-card-footer">
        <a href="<?= SITE_URL ?>/?page=admin-posts" class="btn btn-primary">View All Posts</a>
    </div>
</div>

<!-- Recent Users -->
<div class="admin-card" id="user-list">
    <div class="admin-card-header">
        Recent Users
    </div>
    <div class="admin-card-body">
        <?php if (empty($recentUsers)): ?>
            <p class="text-muted">No users found.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge badge-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatDate($user['created_at']) ?></td>
                                <td class="d-flex gap-10">
                                    <?php if ($user['role'] !== 'admin'): ?>
                                    <a href="<?= SITE_URL ?>/?page=admin-delete-user&id=<?= $user['id'] ?>" class="btn btn-danger btn-sm delete-user" title="Delete User" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="admin-card-footer">
        <a href="<?= SITE_URL ?>/?page=admin-users#user-list" class="btn btn-primary">View All Users</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add delete confirmation for users
    const deleteUserButtons = document.querySelectorAll('.delete-user');
    deleteUserButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userName = this.closest('tr').querySelector('td:first-child').textContent.trim();
            if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone and will delete all their content.`)) {
                window.location.href = this.getAttribute('href');
            }
        });
    });
    
    // Add smooth scrolling for the Manage Users link
    const scrollToUsersLink = document.querySelector('.scroll-to-users');
    if (scrollToUsersLink) {
        scrollToUsersLink.addEventListener('click', function(e) {
            e.preventDefault();
            const userListSection = document.getElementById('user-list');
            if (userListSection) {
                userListSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
});
</script>

<?php require_once 'footer.php'; ?> 