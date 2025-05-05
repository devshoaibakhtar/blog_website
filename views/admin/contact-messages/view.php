<?php $page_title = "View Message"; ?>
<?php require_once __DIR__ . '/../header.php'; ?>

<div class="admin-header d-flex justify-content-between mb-4">
    <h1 class="admin-title">View Message</h1>
    <div>
        <a href="<?= SITE_URL ?>/?page=admin-contact-messages" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Messages
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= htmlspecialchars($message['subject']) ?></h5>
            <span class="badge <?= $message['read'] ? 'bg-secondary' : 'bg-primary' ?>">
                <?= $message['read'] ? 'Read' : 'New' ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>From:</strong> <?= htmlspecialchars($message['name']) ?> &lt;<?= htmlspecialchars($message['email']) ?>&gt;
            </div>
            <div class="col-md-6 text-md-end">
                <strong>Date:</strong> <?= formatDate($message['created_at']) ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body bg-light">
                <p class="card-text"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between">
            <div>
                <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= urlencode($message['subject']) ?>" class="btn btn-primary">
                    <i class="fas fa-reply me-1"></i> Reply via Email
                </a>
                <?php if (!$message['read']): ?>
                    <a href="<?= SITE_URL ?>/?page=admin-contact-messages&action=mark-read&id=<?= $message['id'] ?>" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Mark as Read
                    </a>
                <?php endif; ?>
            </div>
            <a href="<?= SITE_URL ?>/?page=admin-contact-messages&action=delete&id=<?= $message['id'] ?>" class="btn btn-danger delete-btn">
                <i class="fas fa-trash me-1"></i> Delete
            </a>
        </div>
    </div>
</div>

<script>
// Add confirmation for delete button
document.addEventListener('DOMContentLoaded', function() {
    const deleteButton = document.querySelector('.delete-btn');
    
    if (deleteButton) {
        deleteButton.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this message?')) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?> 