<?php
$page_title = "Admin - Posts";

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
$status = isset($_GET['status']) ? $_GET['status'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$author = isset($_GET['author']) ? $_GET['author'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build the query based on filters
$query = "SELECT p.*, u.username, u.name as author_name, 
          (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count 
          FROM posts p
          JOIN users u ON p.user_id = u.id
          WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM posts p 
               JOIN users u ON p.user_id = u.id 
               WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $count_query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($status)) {
    $query .= " AND p.status = ?";
    $count_query .= " AND p.status = ?";
    $params[] = $status;
}

if (!empty($category)) {
    $query .= " AND p.id IN (SELECT post_id FROM post_taxonomy WHERE taxonomy_id = ?)";
    $count_query .= " AND p.id IN (SELECT post_id FROM post_taxonomy WHERE taxonomy_id = ?)";
    $params[] = $category;
}

if (!empty($author)) {
    $query .= " AND p.user_id = ?";
    $count_query .= " AND p.user_id = ?";
    $params[] = $author;
}

// Add sorting
$query .= " ORDER BY p.$sort $order";

// Add pagination
$query .= " LIMIT $limit OFFSET $offset";

// Execute the query
try {
    // Get total count for pagination
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_results = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_results / $limit);
    
    // Get posts
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for filter
    $categories_stmt = $pdo->query("SELECT * FROM taxonomies WHERE type = 'category' ORDER BY name ASC");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get authors for filter
    $authors_stmt = $pdo->query("SELECT id, username, name FROM users ORDER BY name ASC");
    $authors = $authors_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    setFlashMessage('error', 'Database error: ' . $e->getMessage());
    $posts = [];
    $categories = [];
    $authors = [];
    $total_pages = 1;
}
?>

<!-- Include admin CSS -->
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin-no-sidebar.css">

<div class="admin-container">
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="admin-header">
            <h1 class="admin-title">Manage Posts</h1>
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
            <a href="<?= SITE_URL ?>/?page=admin-post-create" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Post
            </a>
            
            <form action="" method="GET" class="admin-search-form">
                <input type="hidden" name="page" value="admin-posts">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="admin-filter">
                <label for="status-filter">Filter:</label>
                <select id="status-filter" class="form-control" onchange="window.location.href=this.value">
                    <option value="<?= SITE_URL ?>/?page=admin-posts" <?= !isset($_GET['status']) ? 'selected' : '' ?>>All Status</option>
                    <option value="<?= SITE_URL ?>/?page=admin-posts&status=published" <?= isset($_GET['status']) && $_GET['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="<?= SITE_URL ?>/?page=admin-posts&status=draft" <?= isset($_GET['status']) && $_GET['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="<?= SITE_URL ?>/?page=admin-posts&status=pending" <?= isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-body">
                <?php if (empty($posts)): ?>
                    <p class="text-muted">No posts found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Comments</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td class="post-title-cell">
                                            <?php if (!empty($post['featured_image'])): ?>
                                                <div class="post-thumbnail">
                                                    <img src="<?= SITE_URL ?>/uploads/<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                                                </div>
                                            <?php endif; ?>
                                            <div class="post-title-content">
                                                <span class="post-title"><?= htmlspecialchars($post['title']) ?></span>
                                                <span class="post-slug"><?= htmlspecialchars($post['slug']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($post['author_name']) ?></td>
                                        <td><?= htmlspecialchars($post['category_name']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= getStatusBadgeClass($post['status']) ?>">
                                                <?= ucfirst($post['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $post['comment_count'] ?></td>
                                        <td><?= formatDate($post['created_at']) ?></td>
                                        <td class="d-flex gap-10">
                                            <a href="<?= SITE_URL ?>/?page=post&slug=<?= urlencode($post['slug']) ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (isAdmin() || $_SESSION['user_id'] === $post['author_id']): ?>
                                                <a href="<?= SITE_URL ?>/?page=admin-post-edit&id=<?= $post['id'] ?>" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= SITE_URL ?>/?page=admin-posts&action=delete&id=<?= $post['id'] ?>" class="btn btn-danger btn-sm delete-post">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (isAdmin() && $post['status'] === 'pending'): ?>
                                                <a href="<?= SITE_URL ?>/?page=admin-posts&action=approve&id=<?= $post['id'] ?>" class="btn btn-success btn-sm" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="<?= SITE_URL ?>/?page=admin-posts&action=reject&id=<?= $post['id'] ?>" class="btn btn-danger btn-sm" title="Reject">
                                                    <i class="fas fa-times"></i>
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
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-posts<?= isset($_GET['status']) ? '&status='.$_GET['status'] : '' ?><?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>&p=<?= $currentPage - 1 ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-posts<?= isset($_GET['status']) ? '&status='.$_GET['status'] : '' ?><?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>&p=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= SITE_URL ?>/?page=admin-posts<?= isset($_GET['status']) ? '&status='.$_GET['status'] : '' ?><?= isset($_GET['search']) ? '&search='.$_GET['search'] : '' ?>&p=<?= $currentPage + 1 ?>">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-post');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<style>
    .post-title-admin {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<?php require_once 'includes/footer.php'; ?> 