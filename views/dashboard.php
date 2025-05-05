<?php require_once 'includes/header.php'; ?>

<div class="custom-dashboard">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <p class="welcome-message">Welcome back, <?= $user['name'] ?>! Here's an overview of your activity.</p>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-content stat-content1">
                    <div class="stat-info">
                        <h3>Total Posts</h3>
                        <div class="stat-number"><?= $postsCount ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-content stat-content2">
                    <div class="stat-info">
                        <h3>Published</h3>
                        <div class="stat-number"><?= $publishedPostsCount ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-content stat-content3">
                    <div class="stat-info">
                        <h3>Pending</h3>
                        <div class="stat-number"><?= $pendingPostsCount ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-content stat-content4">
                    <div class="stat-info">
                        <h3>Comments</h3>
                        <div class="stat-number"><?= $commentsCount ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="dashboard-main">
            <div class="posts-section">
                <div class="section-header">
                    <h2>Recent Posts</h2>
                    <div class="section-divider"></div>
                </div>
                
                <div class="section-content">
                    <?php if (empty($recentPosts)): ?>
                        <p class="empty-message">You haven't created any posts yet.</p>
                    <?php else: ?>
                        <div class="custom-table">
                            <div class="table-header">
                                <div class="th">Title</div>
                                <div class="th">Category</div>
                                <div class="th">Status</div>
                                <div class="th">Date</div>
                                <div class="th">Actions</div>
                            </div>
                            
                            <?php foreach ($recentPosts as $post): ?>
                                <div class="table-row">
                                    <div class="td title"><?= $post['title'] ?></div>
                                    <div class="td category"><?= $post['taxonomy_name'] ?></div>
                                    <div class="td status">
                                        <?php if ($post['status'] === 'published'): ?>
                                            <span class="status-badge published">Published</span>
                                        <?php elseif ($post['status'] === 'pending'): ?>
                                            <span class="status-badge pending">Pending</span>
                                        <?php elseif ($post['status'] === 'draft'): ?>
                                            <span class="status-badge draft">Draft</span>
                                        <?php else: ?>
                                            <span class="status-badge rejected">Rejected</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="td date"><?= formatDate($post['created_at']) ?></div>
                                    <div class="td actions">
                                        <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $post['id'] ?>" class="action-btn view">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= SITE_URL ?>/?page=edit-post&id=<?= $post['id'] ?>" class="action-btn edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="section-footer">
                    <a href="<?= SITE_URL ?>/?page=posts" class="dashboard-btn outline">View All Posts</a>
                    <a href="<?= SITE_URL ?>/?page=create-post" class="dashboard-btn primary">Create New Post</a>
                </div>
            </div>
            
            <div class="quick-actions">
                <div class="section-header">
                    <h2>Quick Actions</h2>
                    <div class="section-divider"></div>
                </div>
                
                <div class="actions-container">
                    <a href="<?= SITE_URL ?>/?page=create-post" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h3>Create New Post</h3>
                        <p>Start writing a new blog post</p>
                    </a>
                    
                    <a href="<?= SITE_URL ?>/?page=profile" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>Edit Profile</h3>
                        <p>Update your personal information</p>
                    </a>
                    
                    <a href="<?= SITE_URL ?>/?page=notifications" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h3>View Notifications</h3>
                        <p>Check all your recent notifications</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 