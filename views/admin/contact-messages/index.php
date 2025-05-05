<?php $page_title = "Contact Messages"; ?>
<?php require_once __DIR__ . '/../header.php'; ?>

<div class="admin-header d-flex justify-content-between mb-4">
    <h1 class="admin-title">Contact Messages</h1>
    <?php if ($unreadCount > 0): ?>
        <span class="badge bg-danger fs-6 align-self-center"><?= $unreadCount ?> unread</span>
    <?php endif; ?>
</div>

<?php if (empty($messages)): ?>
    <div class="alert alert-info">No contact messages found.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr class="<?= $message['read'] ? '' : 'table-primary' ?>">
                        <td><?= $message['id'] ?></td>
                        <td><?= htmlspecialchars($message['name']) ?></td>
                        <td><a href="mailto:<?= htmlspecialchars($message['email']) ?>"><?= htmlspecialchars($message['email']) ?></a></td>
                        <td><?= htmlspecialchars($message['subject']) ?></td>
                        <td><?= formatDate($message['created_at']) ?></td>
                        <td>
                            <?php if ($message['read']): ?>
                                <span class="badge bg-secondary">Read</span>
                            <?php else: ?>
                                <span class="badge bg-primary">New</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= SITE_URL ?>/?page=admin-contact-messages&action=view&id=<?= $message['id'] ?>" class="btn btn-sm btn-info me-1">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <?php if (!$message['read']): ?>
                                <a href="<?= SITE_URL ?>/?page=admin-contact-messages&action=mark-read&id=<?= $message['id'] ?>" class="btn btn-sm btn-success me-1">
                                    <i class="fas fa-check"></i> Mark Read
                                </a>
                            <?php endif; ?>
                            <a href="<?= SITE_URL ?>/?page=admin-contact-messages&action=delete&id=<?= $message['id'] ?>" class="btn btn-sm btn-danger delete-btn">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
// Add confirmation for delete buttons
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this message?')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?> 
