<?php
$pageTitle = 'Notifications';
require_once 'includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Your Notifications</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= SITE_URL ?>/?page=notifications&action=mark-all-read" class="btn btn-outline-primary">
                <i class="fas fa-check-double me-2"></i>Mark All as Read
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x mb-3 text-muted"></i>
                    <p class="lead text-muted">You don't have any notifications yet.</p>
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item list-group-item-action <?= $notification['is_read'] ? '' : 'unread' ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-1">
                                    <?php 
                                    switch ($notification['type']) {
                                        case 'post_approval':
                                            echo '<i class="fas fa-check-circle text-success me-2"></i>';
                                            break;
                                        case 'post_rejection':
                                            echo '<i class="fas fa-times-circle text-danger me-2"></i>';
                                            break;
                                        case 'comment':
                                            echo '<i class="fas fa-comment text-primary me-2"></i>';
                                            break;
                                        case 'reply':
                                            echo '<i class="fas fa-reply text-primary me-2"></i>';
                                            break;
                                        case 'profile_update':
                                            echo '<i class="fas fa-user-edit text-info me-2"></i>';
                                            break;
                                        case 'like':
                                            echo '<i class="fas fa-heart text-danger me-2"></i>';
                                            break;
                                        case 'follow':
                                            echo '<i class="fas fa-user-plus text-success me-2"></i>';
                                            break;
                                        case 'mention':
                                            echo '<i class="fas fa-at text-warning me-2"></i>';
                                            break;
                                        case 'tag':
                                            echo '<i class="fas fa-tag text-secondary me-2"></i>';
                                            break;
                                        case 'admin':
                                            echo '<i class="fas fa-shield-alt text-danger me-2"></i>';
                                            break;
                                        default:
                                            echo '<i class="fas fa-bell text-secondary me-2"></i>';
                                    }
                                    ?>
                                    <?= htmlspecialchars($notification['content']) ?>
                                </h6>
                                <div>
                                    <small class="text-muted me-3"><?= formatDate($notification['created_at']) ?></small>
                                    <?php if (!$notification['is_read']): ?>
                                        <a href="<?= SITE_URL ?>/?page=notifications&action=mark-read&id=<?= $notification['id'] ?>" class="btn btn-sm btn-light">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                            // For post-related notifications, add a link to the post
                            if (in_array($notification['type'], ['post_approval', 'post_rejection', 'like'])): 
                                // Get the post info
                                $stmt = $conn->prepare("SELECT id, title FROM posts WHERE id = :id");
                                $stmt->bindParam(':id', $notification['reference_id']);
                                $stmt->execute();
                                $post = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($post): ?>
                                    <div class="mt-2">
                                        <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $post['id'] ?>" class="text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            View "<?= htmlspecialchars($post['title']) ?>"
                                        </a>
                                    </div>
                                <?php endif;
                            elseif (in_array($notification['type'], ['comment', 'reply'])):
                                // Get the post info for comment notification
                                $stmt = $conn->prepare("
                                    SELECT p.id, p.title FROM posts p
                                    JOIN comments c ON p.id = c.post_id
                                    WHERE c.id = :id
                                ");
                                $stmt->bindParam(':id', $notification['reference_id']);
                                $stmt->execute();
                                $post = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($post): ?>
                                    <div class="mt-2">
                                        <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $post['id'] ?>#comment-<?= $notification['reference_id'] ?>" class="text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            View comment on "<?= htmlspecialchars($post['title']) ?>"
                                        </a>
                                    </div>
                                <?php endif;
                            elseif ($notification['type'] === 'profile_update'): ?>
                                <div class="mt-2">
                                    <a href="<?= SITE_URL ?>/?page=profile" class="text-decoration-none">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        View your profile
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.list-group-item.unread {
    background-color: rgba(13, 110, 253, 0.05);
    font-weight: 500;
    border-left: 3px solid #0d6efd;
}
</style>

<?php require_once 'includes/footer.php'; ?> 