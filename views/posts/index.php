<?php require_once 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Posts</h1>
            <a href="<?= SITE_URL ?>/?page=create-post" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create New Post
            </a>
        </div>

        <!-- Filter Options -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Filter Posts</h5>
            </div>
            <div class="card-body">
                <form action="<?= SITE_URL ?>/?page=posts" method="get" class="row g-3">
                    <input type="hidden" name="page" value="posts">
                    
                    <div class="col-md-5">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="col-md-5">
                        <label for="taxonomy" class="form-label">Category</label>
                        <select class="form-select" id="taxonomy" name="taxonomy">
                            <option value="0">All Categories</option>
                            <?php foreach ($taxonomies as $tax): ?>
                                <option value="<?= $tax['id'] ?>" <?= $taxonomy === $tax['id'] ? 'selected' : '' ?>>
                                    <?= $tax['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Posts List -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Your Posts</h5>
            </div>
            <div class="card-body">
                <?php if (empty($posts)): ?>
                    <p class="text-muted mb-0">You haven't created any posts yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td><?= $post['title'] ?></td>
                                        <td><?= $post['taxonomy_name'] ?></td>
                                        <td>
                                            <?php if ($post['status'] === 'published'): ?>
                                                <span class="badge bg-success">Published</span>
                                            <?php elseif ($post['status'] === 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif ($post['status'] === 'draft'): ?>
                                                <span class="badge bg-secondary">Draft</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($post['created_at']) ?></td>
                                        <td>
                                            <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= SITE_URL ?>/?page=edit-post&id=<?= $post['id'] ?>" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= SITE_URL ?>/?page=delete-post&id=<?= $post['id'] ?>" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Sidebar -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="quick-actions-container">
                    <a href="<?= SITE_URL ?>/?page=create-post" class="quick-action-link">
                        <div class="quick-action-card">
                            <div class="action-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="action-text">
                                <h5>Create New Post</h5>
                                <p>Start writing a new blog post</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="<?= SITE_URL ?>/?page=notifications" class="quick-action-link">
                        <div class="quick-action-card">
                            <div class="action-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="action-text">
                                <h5>View Notifications</h5>
                                <p>Check all your recent notifications</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="<?= SITE_URL ?>/?page=profile" class="quick-action-link">
                        <div class="quick-action-card">
                            <div class="action-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="action-text">
                                <h5>Edit Profile</h5>
                                <p>Update your personal information</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 