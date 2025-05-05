<?php
/**
 * Admin Comments View Detail
 * Shows detailed information about a specific comment
 */

$page_title = "View Comment - Admin";

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="admin-container">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <button id="sidebar-toggle" class="btn btn-primary sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="admin-header">
            <h2><i class="fas fa-comment"></i> View Comment</h2>
            
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash']['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
        </div>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Comment ID:</strong> <?= $comment['id'] ?>
                    <span class="ms-3 badge bg-<?= getStatusBadgeClass($comment['status']) ?>">
                        <?= ucfirst($comment['status']) ?>
                    </span>
                </div>
                <div class="comment-actions">
                    <?php if ($comment['status'] !== 'approved'): ?>
                        <a href="<?= SITE_URL ?>/?page=admin-comments&action=approve&id=<?= $comment['id'] ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-check me-1"></i> Approve
                        </a>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/?page=admin-comments&action=unapprove&id=<?= $comment['id'] ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-times me-1"></i> Unapprove
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($comment['status'] !== 'spam'): ?>
                        <a href="<?= SITE_URL ?>/?page=admin-comments&action=spam&id=<?= $comment['id'] ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-ban me-1"></i> Mark as Spam
                        </a>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/?page=admin-comments&action=notspam&id=<?= $comment['id'] ?>" class="btn btn-sm btn-light">
                            <i class="fas fa-undo me-1"></i> Not Spam
                        </a>
                    <?php endif; ?>
                    
                    <a href="#" class="btn btn-sm btn-danger delete-comment" 
                       data-id="<?= $comment['id'] ?>" 
                       data-bs-toggle="modal" 
                       data-bs-target="#deleteCommentModal">
                        <i class="fas fa-trash me-1"></i> Delete
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Comment Information</h5>
                        <div class="mb-3">
                            <strong>Author:</strong> <?= htmlspecialchars($comment['user_name']) ?>
                        </div>
                        <div class="mb-3">
                            <strong>Posted on:</strong> <?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?>
                        </div>
                        <div class="mb-3">
                            <strong>In response to:</strong> 
                            <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $comment['post_id'] ?>" target="_blank">
                                <?= htmlspecialchars($comment['post_title']) ?>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Content</h5>
                        <div class="border p-3 mb-3 comment-content">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <a href="<?= SITE_URL ?>/?page=admin-comments" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Comments
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Comment Modal -->
<div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-labelledby="deleteCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCommentModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this comment? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?= SITE_URL ?>/?page=admin-comments&action=delete&id=<?= $comment['id'] ?>" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar for mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.querySelector('.admin-sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }
    });
</script>

<?php
/**
 * Helper function to get the appropriate badge class based on status
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'approved':
            return 'success';
        case 'pending':
            return 'warning';
        case 'spam':
            return 'danger';
        default:
            return 'secondary';
    }
}

require_once __DIR__ . '/../../includes/footer.php';
?> 