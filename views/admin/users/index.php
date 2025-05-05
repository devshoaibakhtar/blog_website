<?php
/**
 * Admin Users List View
 */
$pageTitle = 'Manage Users';
require_once 'includes/admin-header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Manage Users</h1>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-md-8">
                    <form action="" method="GET" class="d-flex">
                        <input type="hidden" name="page" value="admin-users">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search ?? '') ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <select name="status" class="form-select ms-2" onchange="this.form.submit()">
                            <option value="all" <?= ($statusFilter ?? '') === 'all' ? 'selected' : '' ?>>All Status</option>
                            <option value="active" <?= ($statusFilter ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($statusFilter ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <select name="role" class="form-select ms-2" onchange="this.form.submit()">
                            <option value="all" <?= ($roleFilter ?? '') === 'all' ? 'selected' : '' ?>>All Roles</option>
                            <option value="admin" <?= ($roleFilter ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="user" <?= ($roleFilter ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No users found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($user['profile_image']): ?>
                                                <img src="<?= SITE_URL ?>/<?= $user['profile_image'] ?>" class="rounded-circle me-2" width="40" height="40" alt="<?= htmlspecialchars($user['name']) ?>">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($user['name']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="badge bg-primary">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= SITE_URL ?>/?page=admin-users&action=view&id=<?= $user['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= SITE_URL ?>/?page=admin-users&action=edit&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="<?= SITE_URL ?>/?page=admin-users&action=delete&id=<?= $user['id'] ?>" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/admin-footer.php';
?> 