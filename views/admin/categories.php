<?php
$page_title = "Admin - Categories";

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

// Get filter values from query string
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build the query based on filters
$query = "SELECT t.*, 
          (SELECT COUNT(*) FROM post_taxonomy WHERE taxonomy_id = t.id) as post_count 
          FROM taxonomies t 
          WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM taxonomies t WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (t.name LIKE ? OR t.description LIKE ?)";
    $count_query .= " AND (t.name LIKE ? OR t.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($type)) {
    $query .= " AND t.type = ?";
    $count_query .= " AND t.type = ?";
    $params[] = $type;
}

// Add sorting
$query .= " ORDER BY t.$sort $order";

// Add pagination
$query .= " LIMIT $limit OFFSET $offset";

// Execute the query
try {
    // Get total count for pagination
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_results = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_results / $limit);
    
    // Get categories
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $taxonomies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    setFlashMessage('error', 'Database error: ' . $e->getMessage());
    $taxonomies = [];
    $total_pages = 1;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add' && isset($_POST['name'], $_POST['type'])) {
        $name = trim($_POST['name']);
        $taxonomy_type = $_POST['type'];
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $slug = createSlug($name);
        
        if (empty($name)) {
            setFlashMessage('error', 'Name is required');
        } else {
            try {
                // Check if taxonomy with this name already exists
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM taxonomies WHERE name = ? AND type = ?");
                $check_stmt->execute([$name, $taxonomy_type]);
                if ($check_stmt->fetchColumn() > 0) {
                    setFlashMessage('error', 'A ' . $taxonomy_type . ' with this name already exists');
                } else {
                    // Insert new taxonomy
                    $insert_stmt = $pdo->prepare("INSERT INTO taxonomies (name, slug, description, type) VALUES (?, ?, ?, ?)");
                    $insert_stmt->execute([$name, $slug, $description, $taxonomy_type]);
                    
                    setFlashMessage('success', ucfirst($taxonomy_type) . ' created successfully');
                    header('Location: ' . SITE_URL . '/admin-categories');
                    exit;
                }
            } catch (PDOException $e) {
                setFlashMessage('error', 'Database error: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'edit' && isset($_POST['id'], $_POST['name'], $_POST['type'])) {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $taxonomy_type = $_POST['type'];
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $slug = createSlug($name);
        
        if (empty($name)) {
            setFlashMessage('error', 'Name is required');
        } else {
            try {
                // Check if taxonomy with this name already exists (excluding current one)
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM taxonomies WHERE name = ? AND type = ? AND id != ?");
                $check_stmt->execute([$name, $taxonomy_type, $id]);
                if ($check_stmt->fetchColumn() > 0) {
                    setFlashMessage('error', 'A ' . $taxonomy_type . ' with this name already exists');
                } else {
                    // Update taxonomy
                    $update_stmt = $pdo->prepare("UPDATE taxonomies SET name = ?, slug = ?, description = ?, type = ? WHERE id = ?");
                    $update_stmt->execute([$name, $slug, $description, $taxonomy_type, $id]);
                    
                    setFlashMessage('success', ucfirst($taxonomy_type) . ' updated successfully');
                    header('Location: ' . SITE_URL . '/admin-categories');
                    exit;
                }
            } catch (PDOException $e) {
                setFlashMessage('error', 'Database error: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'delete' && isset($_POST['taxonomy_id'])) {
        $taxonomy_id = $_POST['taxonomy_id'];
        
        try {
            // Check if taxonomy is used in posts
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM post_taxonomy WHERE taxonomy_id = ?");
            $check_stmt->execute([$taxonomy_id]);
            if ($check_stmt->fetchColumn() > 0) {
                setFlashMessage('error', 'This taxonomy is used in posts. Remove it from posts before deleting.');
            } else {
                // Delete taxonomy
                $delete_stmt = $pdo->prepare("DELETE FROM taxonomies WHERE id = ?");
                $delete_stmt->execute([$taxonomy_id]);
                
                setFlashMessage('success', 'Taxonomy deleted successfully');
                header('Location: ' . SITE_URL . '/admin-categories');
                exit;
            }
        } catch (PDOException $e) {
            setFlashMessage('error', 'Database error: ' . $e->getMessage());
        }
    }
}

// Helper function to create a slug
function createSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}
?>

<div class="admin-container">
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="admin-header">
            <h1 class="admin-title">Manage Categories</h1>
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
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">
                <i class="fas fa-plus"></i> New Category
            </button>
            
            <form action="" method="GET" class="admin-search-form">
                <input type="hidden" name="page" value="admin-categories">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search categories..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
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
                <?php if (empty($taxonomies)): ?>
                    <p class="text-muted">No categories found.</p>
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
                                <?php foreach ($taxonomies as $taxonomy): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($taxonomy['name']) ?></td>
                                        <td><?= htmlspecialchars($taxonomy['slug']) ?></td>
                                        <td><?= !empty($taxonomy['description']) ? htmlspecialchars(substr($taxonomy['description'], 0, 100)) . (strlen($taxonomy['description']) > 100 ? '...' : '') : '—' ?></td>
                                        <td><?= $taxonomy['post_count'] ?></td>
                                        <td><?= formatDate($taxonomy['created_at']) ?></td>
                                        <td class="d-flex gap-10">
                                            <a href="<?= SITE_URL ?>/?page=category&slug=<?= urlencode($taxonomy['slug']) ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-secondary btn-sm edit-category" 
                                                    data-id="<?= $taxonomy['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($taxonomy['name']) ?>" 
                                                    data-slug="<?= htmlspecialchars($taxonomy['slug']) ?>" 
                                                    data-description="<?= htmlspecialchars($taxonomy['description']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($taxonomy['post_count'] == 0): ?>
                                                <a href="<?= SITE_URL ?>/?page=admin-categories&action=delete&id=<?= $taxonomy['id'] ?>" class="btn btn-danger btn-sm delete-category">
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
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-categories&p=<?= $page-1 ?><?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-categories&p=<?= $i ?><?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-categories&p=<?= $page+1 ?><?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= SITE_URL ?>/?page=admin-categories&action=add" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Category Name *</label>
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
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= SITE_URL ?>/?page=admin-categories&action=edit" method="POST">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Category Name *</label>
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
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-category');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Populate edit modal
    const editButtons = document.querySelectorAll('.edit-category');
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
            
            $('#editCategoryModal').modal('show');
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