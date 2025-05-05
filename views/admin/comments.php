<?php
/**
 * Admin Comments View
 * Displays a list of comments and provides actions for managing them
 */

$page_title = "Admin - Comments";

require_once 'header.php';
?>

<div class="admin-header">
    <h2><i class="fas fa-comments"></i> Manage Comments</h2>
</div>

<div class="admin-actions">
    <form class="search-form" action="<?= SITE_URL ?>/?page=admin-comments" method="GET">
        <input type="hidden" name="page" value="admin-comments">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search comments, posts, or users..." value="<?= htmlspecialchars($search ?? '') ?>">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </form>
</div>

<div class="admin-table-container">
    <?php if (empty($comments)): ?>
        <div class="alert alert-info">
            No comments found. <?= !empty($search) ? 'Try a different search term.' : '' ?>
        </div>
    <?php else: ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Comment</th>
                    <th>Post</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($comment['user_name']) ?></strong></td>
                        <td>
                            <div class="comment-content">
                                <?php
                                // Truncate content for display
                                $content = htmlspecialchars($comment['content']);
                                echo strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content;
                                ?>
                            </div>
                        </td>
                        <td>
                            <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $comment['post_id'] ?>" target="_blank">
                                <?= htmlspecialchars($comment['post_title']) ?>
                            </a>
                        </td>
                        <td>
                            <?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $comment['post_id'] ?>#comment-<?= $comment['id'] ?>" class="btn btn-sm btn-info" title="View Comment">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <a href="<?= SITE_URL ?>/?page=admin-comments&action=delete&id=<?= $comment['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete Comment"
                                   onclick="return confirm('Are you sure you want to delete this comment? This cannot be undone.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Comments pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= SITE_URL ?>/?page=admin-comments&p=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= SITE_URL ?>/?page=admin-comments&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= SITE_URL ?>/?page=admin-comments&p=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?> 