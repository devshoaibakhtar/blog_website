<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/post_image_fix.php'; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="mb-4 bg-light p-4 rounded shadow-sm">
            <h1 class="display-5 fw-bold">Welcome to <?= SITE_NAME ?></h1>
            <?php if (isset($_GET['tag']) && !empty($_GET['tag'])): 
                // Get tag name for display
                $stmt = $conn->prepare("SELECT name FROM tags WHERE slug = :slug");
                $stmt->bindParam(':slug', $_GET['tag']);
                $stmt->execute();
                $tagName = $stmt->fetchColumn();
            ?>
                <p class="lead">Browsing posts tagged with <span class="badge bg-primary"><?= htmlspecialchars($tagName) ?></span></p>
                <div class="mt-3">
                    <a href="<?= SITE_URL ?>/?page=home" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear tag filter
                    </a>
                </div>
            <?php elseif (isset($_GET['tag_search']) && !empty($_GET['tag_search'])): ?>
                <p class="lead">Tag search results for <span class="badge bg-primary"><?= htmlspecialchars($_GET['tag_search']) ?></span></p>
                <div class="mt-3">
                    <a href="<?= SITE_URL ?>/?page=home" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear tag search
                    </a>
                </div>
            <?php elseif (isset($_GET['taxonomy']) && !empty($_GET['taxonomy'])): ?>
                <p class="lead">Browsing posts in category <span class="badge bg-primary"><?= htmlspecialchars($taxonomySlug) ?></span></p>
                <div class="mt-3">
                    <a href="<?= SITE_URL ?>/?page=home" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear category filter
                    </a>
                </div>
            <?php elseif (isset($_GET['search']) && !empty($_GET['search'])): ?>
                <p class="lead">Search results for <span class="badge bg-primary"><?= htmlspecialchars($_GET['search']) ?></span></p>
                <div class="mt-3">
                    <a href="<?= SITE_URL ?>/?page=home" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear search
                    </a>
                </div>
            <?php else: ?>
                <p class="lead">Discover the latest blog posts across various topics.</p>
                <?php if (isLoggedIn()): ?>
                <div class="mt-3">
                    <a href="<?= SITE_URL ?>/?page=create-post" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Create New Post
                    </a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <p class="mb-0">No posts found. Check back later for new content!</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="card mb-4">
                    <?php 
                    // Handle featured image with our helper function
                    $imagePath = $post['featured_image'] ? fixPostImagePath($post['featured_image']) : false;
                    ?>
                      <div class="title-overlay1"
                       style="margin: 20px; text-align: center;"
                      >
                            <h2 class="post-title"><?= $post['title'] ?></h2>
                          
                        </div>
                    <!-- Featured Image with Title Overlay -->
                    <div class="featured-image-container position-relative">
                        <?php if ($imagePath): ?>
                            <img src="<?= SITE_URL . '/' . $imagePath ?>" 
                                class="img-fluid featured-image w-100" 
                                alt="<?= $post['title'] ?>" 
                                style="height: 250px; object-fit: cover;"
                                onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.svg';">
                        <?php else: ?>
                            <img src="<?= SITE_URL ?>/assets/images/placeholder.svg" 
                                class="img-fluid featured-image w-100" 
                                alt="No image available"
                                style="height: 250px; object-fit: cover;">
                        <?php endif; ?>
                        
                      
                    </div>
                    
                    <div class="card-body">
                        <!-- Tags display -->
                        <?php if (!empty($post['tags'])): ?>
                            <div class="mb-3">
                                <?php foreach ($post['tags'] as $tag): ?>
                                    <a href="<?= SITE_URL ?>/?page=home&tag=<?= $tag['slug'] ?>" class="badge bg-secondary tag-badge"><?= $tag['name'] ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="post-meta">
                                <span><i class="fas fa-user me-1"></i> <?= htmlspecialchars($post['author_name']) ?></span>
                                <span class="mx-2">|</span>
                                <span><i class="fas fa-calendar-alt me-1"></i> <?= formatDate($post['created_at']) ?></span>
                                <?php if (!empty($post['taxonomy_name'])): ?>
                                    <span class="mx-2">|</span>
                                    <span>
                                        <i class="fas fa-folder me-1"></i> 
                                        <a href="<?= SITE_URL ?>/?page=home&taxonomy=<?= $post['taxonomy_slug'] ?>" class="taxonomy-link"><?= $post['taxonomy_name'] ?></a>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <p class="card-text"><?= truncateText(strip_tags($post['content']), 200) ?></p>
                        <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $post['id'] ?>" class="btn btn-primary">Read More</a>
                        
                        <!-- Social Interaction Area -->
                        <div class="post-interactions mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <button class="btn btn-sm btn-outline-secondary post-like-btn" data-post-id="<?= $post['id'] ?>">
                                            <i class="far fa-thumbs-up me-1"></i> 
                                            Like
                                            <span class="like-count">(<?= $post['like_count'] ?? 0 ?>)</span>
                                        </button>
                                    </div>
                                    <div>
                                        <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $post['id'] ?>#comments" class="btn btn-sm btn-outline-secondary">
                                            <i class="far fa-comment me-1"></i> Comment
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <div class="dropup">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-share-alt me-1"></i> Share
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;">
                                            <div class="d-flex flex-wrap">
                                                <a class="btn btn-sm btn-outline-primary me-2 mb-2" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/?page=view-post&id=' . $post['id']) ?>" target="_blank">
                                                    <i class="fab fa-facebook me-1"></i> Facebook
                                                </a>
                                                <a class="btn btn-sm btn-outline-info me-2 mb-2" href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL . '/?page=view-post&id=' . $post['id']) ?>&text=<?= urlencode($post['title']) ?>" target="_blank">
                                                    <i class="fab fa-twitter me-1"></i> Twitter
                                                </a>
                                                <a class="btn btn-sm btn-outline-primary me-2 mb-2" href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(SITE_URL . '/?page=view-post&id=' . $post['id']) ?>&title=<?= urlencode($post['title']) ?>" target="_blank">
                                                    <i class="fab fa-linkedin me-1"></i> LinkedIn
                                                </a>
                                                <a class="btn btn-sm btn-outline-success me-2 mb-2" href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ': ' . SITE_URL . '/?page=view-post&id=' . $post['id']) ?>" target="_blank">
                                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                                </a>
                                                <button class="btn btn-sm btn-outline-secondary me-2 mb-2 copy-link-btn" data-post-url="<?= SITE_URL ?>/?page=view-post&id=<?= $post['id'] ?>">
                                                    <i class="fas fa-link me-1"></i> Copy Link
                                                </button>
                                                <a class="btn btn-sm btn-outline-secondary mb-2" href="mailto:?subject=<?= urlencode($post['title']) ?>&body=<?= urlencode('Check out this post: ' . SITE_URL . '/?page=view-post&id=' . $post['id']) ?>">
                                                    <i class="fas fa-envelope me-1"></i> Email
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search Posts</h5>
            </div>
            <div class="card-body">
                <form action="<?= SITE_URL ?>/" method="GET">
                    <input type="hidden" name="page" value="home">
                    <div class="form-group mb-3">
                        <label for="title-search" class="form-label">By Title</label>
                        <div class="input-group">
                            <input type="search" id="title-search" class="form-control" name="search" placeholder="Search by title..." aria-label="Search by title" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tag-search" class="form-label">By Tag</label>
                        <div class="input-group">
                            <input type="search" id="tag-search" class="form-control" name="tag_search" placeholder="Search by tag..." aria-label="Search by tag" value="<?= isset($_GET['tag_search']) ? htmlspecialchars($_GET['tag_search']) : '' ?>">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-tags"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-folder me-2"></i>Categories</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($taxonomies as $taxonomy): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?= SITE_URL ?>/?page=home&taxonomy=<?= $taxonomy['slug'] ?>" class="text-decoration-none">
                                <?= $taxonomy['name'] ?>
                            </a>
                            <span class="badge bg-primary rounded-pill">
                                <?php 
                                // Count posts in this taxonomy
                                $query = "SELECT COUNT(*) FROM posts WHERE taxonomy_id = :taxonomy_id AND status = 'published'";
                                $stmt = $conn->prepare($query);
                                $stmt->bindParam(':taxonomy_id', $taxonomy['id'], PDO::PARAM_INT);
                                $stmt->execute();
                                echo $stmt->fetchColumn();
                                ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- About section with custom CSS styling (no Bootstrap) -->
        <div class="custom-about-section">
            <div class="about-content">
                <h2>About WriteWise</h2>
                <p>Welcome to WriteWise where you can share your thoughts, learn from others, and engage with a community of like-minded individuals. Our platform is designed to help writers of all skill levels connect and grow together.</p>
              
            </div>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav aria-label="Page navigation" class="my-5">
  <ul class="pagination justify-content-center">
    <?php 
    // Build query string for pagination links
    $queryParams = [];
    if (isset($_GET['search'])) $queryParams['search'] = $_GET['search'];
    if (isset($_GET['tag_search'])) $queryParams['tag_search'] = $_GET['tag_search'];
    if (isset($_GET['tag'])) $queryParams['tag'] = $_GET['tag'];
    if (isset($_GET['taxonomy'])) $queryParams['taxonomy'] = $_GET['taxonomy'];
    
    // Previous page link
    if ($page > 1): 
        $prevParams = array_merge($queryParams, ['p' => $page - 1]);
        $prevUrl = '?' . http_build_query($prevParams);
    ?>
        <li class="page-item">
            <a class="page-link" href="<?= $prevUrl ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
    <?php else: ?>
        <li class="page-item disabled">
            <a class="page-link" href="#" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
    <?php endif; ?>
    
    <?php 
    // Page number links
    $startPage = max(1, min($page - 2, $totalPages - 4));
    $endPage = min($totalPages, max(5, $page + 2));
    
    for ($i = $startPage; $i <= $endPage; $i++): 
        $pageParams = array_merge($queryParams, ['p' => $i]);
        $pageUrl = '?' . http_build_query($pageParams);
    ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="<?= $pageUrl ?>"><?= $i ?></a>
        </li>
    <?php endfor; ?>
    
    <?php 
    // Next page link
    if ($page < $totalPages): 
        $nextParams = array_merge($queryParams, ['p' => $page + 1]);
        $nextUrl = '?' . http_build_query($nextParams);
    ?>
        <li class="page-item">
            <a class="page-link" href="<?= $nextUrl ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    <?php else: ?>
        <li class="page-item disabled">
            <a class="page-link" href="#" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    <?php endif; ?>
  </ul>
</nav>
<?php endif; ?>

<?php 
// Initialize the database with test images if needed
if (empty($posts) || !$posts[0]['featured_image']) {
    createTestImagesAndFixDatabase();
}
?>

<?php require_once 'includes/footer.php'; ?> 