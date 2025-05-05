<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/post_image_fix.php'; ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Actions (edit/delete) for author or admin -->
        <?php if (isLoggedIn() && ($post['user_id'] === getCurrentUserId() || isAdmin())): ?>
            <div class="mb-3 d-flex justify-content-end">
                <a href="<?= SITE_URL ?>/?page=edit-post&id=<?= $post['id'] ?>" class="btn btn-sm btn-secondary me-2">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="<?= SITE_URL ?>/?page=delete-post&id=<?= $post['id'] ?>" class="btn btn-sm btn-danger delete-btn">
                    <i class="fas fa-trash me-1"></i> Delete
                </a>
                
                <!-- Share button -->
                <div class="ms-auto">
                    <div class="dropup">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-share-alt me-1"></i> Share
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
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
        <?php else: ?>
            <!-- Only Share button for non-authors -->
            <div class="mb-3 d-flex justify-content-end">
                <div class="dropup">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-share-alt me-1"></i> Share
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
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
        <?php endif; ?>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
            <div class="mb-4">
                <?php foreach ($tags as $tag): ?>
                    <a href="<?= SITE_URL ?>/?page=home&tag=<?= $tag['slug'] ?>" class="badge bg-secondary tag-badge"><?= $tag['name'] ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Featured Image with Title Overlay -->
        <?php 
        // Handle featured image with our helper function
        $imagePath = $post['featured_image'] ? fixPostImagePath($post['featured_image']) : false;
        ?>
        
        <div class="featured-image-container position-relative mb-4">
            <?php if ($imagePath): ?>
                <img src="<?= SITE_URL . '/' . $imagePath ?>" 
                     class="img-fluid rounded featured-image w-100" 
                     alt="<?= $post['title'] ?>" 
                     onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.svg';">
            <?php else: ?>
                <img src="<?= SITE_URL ?>/assets/images/placeholder.svg" 
                     class="img-fluid rounded featured-image w-100" 
                     alt="No image available">
            <?php endif; ?>
            
            <div class="title-overlay">
                <h1 class="post-title"><?= $post['title'] ?></h1>
                <div class="post-meta">
                <span><i class="fas fa-user me-1"></i> <?= $post['author_name'] ?></span>
                <span class="mx-2">|</span>
                <span><i class="fas fa-calendar-alt me-1"></i> <?= formatDate($post['created_at']) ?></span>
                <?php if ($post['taxonomy_name']): ?>
                    <span class="mx-2">|</span>
                    <span>
                        <i class="fas fa-folder me-1"></i> 
                            <a href="<?= SITE_URL ?>/?page=home&taxonomy=<?= $post['taxonomy_slug'] ?>" class="taxonomy-link"><?= $post['taxonomy_name'] ?></a>
                    </span>
                <?php endif; ?>
                <?php if ($post['status'] !== 'published'): ?>
                    <span class="mx-2">|</span>
                    <span>
                        <?php if ($post['status'] === 'pending'): ?>
                                <span class="status-badge pending">Pending</span>
                        <?php elseif ($post['status'] === 'draft'): ?>
                                <span class="status-badge draft">Draft</span>
                        <?php else: ?>
                                <span class="status-badge rejected">Rejected</span>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
                </div>
                </div>
        </div>
        
        <!-- Post Content -->
        <div class="blog-content mb-5">
            <?= $post['content'] ?>
        </div>
        
        <!-- Comments Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Comments (<?= count($comments) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (isLoggedIn()): ?>
                    <!-- Comment Form -->
                    <form action="<?= SITE_URL ?>/?page=add-comment" method="post" id="commentForm" class="mb-4">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="parent_id" value="0">
                        <div class="mb-3">
                            <label for="content" class="form-label">Leave a Comment</label>
                            <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">Submit Comment</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info mb-4">
                        Please <a href="<?= SITE_URL ?>/?page=login">login</a> to leave a comment.
                    </div>
                <?php endif; ?>
                
                <!-- Comments List -->
                <?php if (empty($comments)): ?>
                    <p class="text-muted">No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                            <div class="comment mb-3" id="comment-<?= $comment['id'] ?>">
                                <div class="d-flex">
                                <div class="flex-shrink-0">
                                        <?php if (isset($comment['profile_image']) && $comment['profile_image']): ?>
                                        <img src="<?= SITE_URL ?>/<?= $comment['profile_image'] ?>" class="rounded-circle" width="50" height="50" alt="<?= $comment['author_name'] ?>">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="comment-author mb-0"><?= htmlspecialchars($comment['author_name'] ?? $comment['name']) ?></h6>
                                        <small class="comment-date text-muted"><?= formatDate($comment['created_at']) ?></small>
                                        </div>
                                        <p class="mt-2 mb-2"><?= htmlspecialchars($comment['content']) ?></p>
                                        
                                        <?php if (isLoggedIn()): ?>
                                            <div class="comment-actions mb-2">
                                                <button class="btn btn-sm btn-link text-primary reply-button p-0" 
                                                        data-comment-id="<?= $comment['id'] ?>" 
                                                        data-author="<?= htmlspecialchars($comment['author_name'] ?? $comment['name']) ?>">
                                                    <i class="fas fa-reply"></i> Reply
                                                </button>
                                                
                                                <?php if (isAdmin()): ?>
                                                    <a href="<?= SITE_URL ?>/?page=delete-comment&id=<?= $comment['id'] ?>&post_id=<?= $post['id'] ?>" 
                                                       class="btn btn-sm btn-link text-danger delete-comment-button p-0 ms-3"
                                                       onclick="return confirm('Are you sure you want to delete this comment?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="reply-form-container-<?= $comment['id'] ?>" style="display: none;">
                                                <!-- Reply form will be inserted here by JavaScript -->
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Check if this comment has replies -->
                                        <?php if (isset($comment['replies']) && !empty($comment['replies'])): ?>
                                            <div class="replies-container ms-4 mt-3 border-start border-light ps-3">
                                                <?php foreach ($comment['replies'] as $reply): ?>
                                                    <div class="reply mb-3" id="comment-<?= $reply['id'] ?>">
                                                        <div class="d-flex">
                                                            <div class="flex-shrink-0">
                                                                <?php if (isset($reply['profile_image']) && $reply['profile_image']): ?>
                                                                    <img src="<?= SITE_URL ?>/<?= $reply['profile_image'] ?>" class="rounded-circle" width="40" height="40" alt="<?= $reply['author_name'] ?>">
                                                                <?php else: ?>
                                                                    <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                                                                        <i class="fas fa-user"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <h6 class="reply-author mb-0"><?= htmlspecialchars($reply['author_name'] ?? $reply['name']) ?></h6>
                                                                    <small class="reply-date text-muted"><?= formatDate($reply['created_at']) ?></small>
                                                                </div>
                                                                <p class="mt-2 mb-2"><?= htmlspecialchars($reply['content']) ?></p>
                                                                
                                                                <?php if (isLoggedIn()): ?>
                                                                    <div class="reply-actions mb-2">
                                                                        <button class="btn btn-sm btn-link text-primary reply-button p-0" 
                                                                                data-comment-id="<?= $reply['id'] ?>" 
                                                                                data-author="<?= htmlspecialchars($reply['author_name'] ?? $reply['name']) ?>">
                                                                            <i class="fas fa-reply"></i> Reply
                                                                        </button>
                                                                        
                                                                        <?php if (isAdmin()): ?>
                                                                            <a href="<?= SITE_URL ?>/?page=delete-comment&id=<?= $reply['id'] ?>&post_id=<?= $post['id'] ?>" 
                                                                               class="btn btn-sm btn-link text-danger delete-comment-button p-0 ms-3"
                                                                               onclick="return confirm('Are you sure you want to delete this reply?')">
                                                                                <i class="fas fa-trash"></i> Delete
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="reply-form-container-<?= $reply['id'] ?>" style="display: none;">
                                                                        <!-- Reply form will be inserted here by JavaScript -->
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Related Posts or Sidebar Content -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Related Posts</h5>
            </div>
            <div class="card-body">
                <?php
                // Get related posts (same taxonomy)
                $stmt = $conn->prepare("
                    SELECT p.id, p.title, p.slug, p.created_at 
                    FROM posts p 
                    WHERE p.taxonomy_id = :taxonomy_id 
                    AND p.id != :post_id 
                    AND p.status = 'published'
                    ORDER BY p.created_at DESC
                    LIMIT 5
                ");
                $stmt->bindParam(':taxonomy_id', $post['taxonomy_id']);
                $stmt->bindParam(':post_id', $post['id']);
                $stmt->execute();
                $relatedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <?php if (empty($relatedPosts)): ?>
                    <p class="text-muted mb-0">No related posts found.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($relatedPosts as $relatedPost): ?>
                            <li class="list-group-item">
                                <a href="<?= SITE_URL ?>/?page=view-post&id=<?= $relatedPost['id'] ?>" class="text-decoration-none">
                                    <?= $relatedPost['title'] ?>
                                </a>
                                <div><small class="text-muted"><?= formatDate($relatedPost['created_at']) ?></small></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tags Cloud -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Popular Tags</h5>
            </div>
            <div class="card-body">
                <?php
                // Get popular tags
                $stmt = $conn->prepare("
                    SELECT t.id, t.name, t.slug, COUNT(pt.post_id) as post_count
                    FROM tags t
                    JOIN post_tags pt ON t.id = pt.tag_id
                    JOIN posts p ON pt.post_id = p.id
                    WHERE p.status = 'published'
                    GROUP BY t.id
                    ORDER BY post_count DESC
                    LIMIT 15
                ");
                $stmt->execute();
                $popularTags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <?php if (empty($popularTags)): ?>
                    <p class="text-muted mb-0">No tags found.</p>
                <?php else: ?>
                    <div>
                        <?php foreach ($popularTags as $tag): ?>
                            <a href="<?= SITE_URL ?>/?page=home&tag=<?= $tag['slug'] ?>" class="badge bg-secondary tag-badge mb-1"><?= $tag['name'] ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 