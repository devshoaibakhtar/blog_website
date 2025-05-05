<?php
$page_title = "Admin - Tags";

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
            <h1 class="admin-title">Manage Tags</h1>
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
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTagModal">
                <i class="fas fa-plus"></i> New Tag
            </button>
            
            <form action="" method="GET" class="admin-search-form">
                <input type="hidden" name="page" value="admin-tags">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search tags..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
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
                <?php if (empty($tags)): ?>
                    <p class="text-muted">No tags found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th>Posts</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tags as $tag): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tag['name']) ?></td>
                                        <td><?= htmlspecialchars($tag['slug']) ?></td>
                                        <td><?= !empty($tag['description']) ? htmlspecialchars(substr($tag['description'], 0, 100)) . (strlen($tag['description']) > 100 ? '...' : '') : '—' ?></td>
                                        <td><?= $tag['post_count'] ?></td>
                                        <td><?= formatDate($tag['created_at']) ?></td>
                                        <td class="d-flex gap-10">
                                            <a href="<?= SITE_URL ?>/?page=tag&slug=<?= urlencode($tag['slug']) ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-secondary btn-sm edit-tag" 
                                                    data-id="<?= $tag['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($tag['name']) ?>" 
                                                    data-slug="<?= htmlspecialchars($tag['slug']) ?>" 
                                                    data-description="<?= htmlspecialchars($tag['description']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($tag['post_count'] == 0): ?>
                                                <a href="<?= SITE_URL ?>/?page=admin-tags&action=delete&id=<?= $tag['id'] ?>" class="btn btn-danger btn-sm delete-tag">
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
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-tags<?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>&p=<?= $currentPage - 1 ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-tags<?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>&p=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-tags<?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>&p=<?= $currentPage + 1 ?>">
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

<!-- Add Tag Modal -->
<div class="modal fade" id="addTagModal" tabindex="-1" role="dialog" aria-labelledby="addTagModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTagModalLabel">Add New Tag</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= SITE_URL ?>/?page=admin-tags&action=add" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Tag Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" placeholder="Leave empty to generate automatically">
                        <small class="form-text text-muted">The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</small>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tag Modal -->
<div class="modal fade" id="editTagModal" tabindex="-1" role="dialog" aria-labelledby="editTagModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTagModalLabel">Edit Tag</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= SITE_URL ?>/?page=admin-tags&action=edit" method="POST">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Tag Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_slug">Slug</label>
                        <input type="text" class="form-control" id="edit_slug" name="slug">
                        <small class="form-text text-muted">The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-tag');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this tag? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Populate edit modal
    const editButtons = document.querySelectorAll('.edit-tag');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const slug = this.getAttribute('data-slug');
            const description = this.getAttribute('data-description');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_slug').value = slug;
            document.getElementById('edit_description').value = description;
            
            $('#editTagModal').modal('show');
        });
    });
    
    // Generate slug from name
    document.getElementById('name').addEventListener('blur', function() {
        const nameField = this;
        const slugField = document.getElementById('slug');
        
        if (slugField.value === '') {
            slugField.value = nameField.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
    
    // Generate slug from name in edit form
    document.getElementById('edit_name').addEventListener('blur', function() {
        const nameField = this;
        const slugField = document.getElementById('edit_slug');
        
        if (slugField.value === '') {
            slugField.value = nameField.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 