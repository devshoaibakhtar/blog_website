<?php
$page_title = "Admin - Users";

// Check if user is admin before including header
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You do not have permission to access this page'
    ];
    // Use JavaScript redirect instead of header()
    echo '<script>window.location.href = "' . SITE_URL . '/?page=login";</script>';
    exit;
}

require_once 'includes/header.php';
?>

<div class="admin-container">
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="admin-header">
            <h1 class="admin-title">Manage Users</h1>
            <?php if (hasFlash('message')): ?>
                <div class="alert alert-<?= getFlash('type') ?> alert-dismissible fade show" role="alert">
                    <?= getFlash('message') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="admin-actions">
            <?php if (isAdmin()): ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                    <i class="fas fa-user-plus"></i> New User
                </button>
            <?php endif; ?>
            
            <form action="" method="GET" class="admin-search-form">
                <input type="hidden" name="page" value="admin-users">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-body">
                <?php if (empty($users)): ?>
                    <p class="text-muted">No users found.</p>
                <?php else: ?>
                    <div class="table-container" id="user-list">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Display Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Posts</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['display_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><span class="badge badge-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'author' ? 'success' : 'secondary') ?>"><?= ucfirst($user['role']) ?></span></td>
                                        <td><?= $user['post_count'] ?></td>
                                        <td><span class="badge badge-<?= $user['status'] === 'active' ? 'success' : 'warning' ?>"><?= ucfirst($user['status']) ?></span></td>
                                        <td><?= formatDate($user['created_at']) ?></td>
                                        <td class="d-flex gap-10">
                                            <a href="<?= SITE_URL ?>/?page=author&username=<?= urlencode($user['username']) ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (isAdmin() || $_SESSION['user']['id'] === $user['id']): ?>
                                                <a href="<?= SITE_URL ?>/?page=admin-users&action=edit&id=<?= $user['id'] ?>" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (isAdmin() && $_SESSION['user']['id'] !== $user['id'] && $user['role'] !== 'admin'): ?>
                                                <a href="<?= SITE_URL ?>/?page=admin-delete-user&id=<?= $user['id'] ?>" class="btn btn-danger btn-sm delete-user">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-users<?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>&p=<?= $currentPage - 1 ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-users<?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>&p=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-users<?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>&p=<?= $currentPage + 1 ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<?php if (isAdmin()): ?>
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?= SITE_URL ?>/?page=admin-users&action=add" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <small class="form-text text-muted">Username can only contain letters, numbers, and underscores.</small>
                        </div>
                        <div class="form-group">
                            <label for="display_name">Display Name *</label>
                            <input type="text" class="form-control" id="display_name" name="display_name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                        </div>
                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="author">Author</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-user');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userName = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
            if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone and will delete all content created by this user, including posts and comments.`)) {
                window.location.href = this.getAttribute('href');
            }
        });
    });
    
    // Scroll to user list if hash is present
    if (window.location.hash === '#user-list') {
        const userList = document.getElementById('user-list');
        if (userList) {
            // Add a small delay to ensure the page is fully loaded
            setTimeout(() => {
                userList.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 