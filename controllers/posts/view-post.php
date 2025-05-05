<?php
/**
 * View Post Controller
 */

// Include the post image fix functions
require_once 'includes/post_image_fix.php';

// Check if post ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('danger', 'Invalid post ID.');
    redirect('home');
}

$postId = intval($_GET['id']);

// Get database connection
$conn = getDbConnection();

// Get post details with author and taxonomy info
$query = "
    SELECT p.*, t.name as taxonomy_name, t.slug as taxonomy_slug, u.name as author_name 
    FROM posts p 
    LEFT JOIN taxonomies t ON p.taxonomy_id = t.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE p.id = :id
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $postId);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// Process featured image path if available
if (!empty($post['featured_image'])) {
    // Normalize path
    $post['featured_image'] = str_replace('\\', '/', $post['featured_image']);
    
    // If path starts with uploads/ or assets/, make sure it's formatted correctly
    if (strpos($post['featured_image'], 'uploads/') === 0 || 
        strpos($post['featured_image'], 'assets/') === 0) {
        // Path is already relative, keep as is
        $post['featured_image'] = ltrim($post['featured_image'], '/');
    }
    
    // Log the final image path
    error_log('Featured image path for post ' . $postId . ': ' . $post['featured_image']);
}

// If post doesn't exist or is not published (and user is not the author or admin)
if (!$post || ($post['status'] !== 'published' && !isLoggedIn() && $post['user_id'] !== getCurrentUserId() && !isAdmin())) {
    setFlashMessage('danger', 'The post you are looking for does not exist or you do not have permission to view it.');
    redirect('home');
}

// Get post tags
$query = "
    SELECT t.id, t.name, t.slug 
    FROM tags t 
    JOIN post_tags pt ON t.id = pt.tag_id 
    WHERE pt.post_id = :post_id
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':post_id', $postId);
$stmt->execute();
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get comments for the post
// First, get all top-level comments (parent_id = 0)
$query = "
    SELECT c.*, u.name as author_name, u.profile_image 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = :post_id AND c.parent_id = 0
    ORDER BY c.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':post_id', $postId);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Then, get all replies
$query = "
    SELECT c.*, u.name as author_name, u.profile_image 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = :post_id AND c.parent_id > 0
    ORDER BY c.created_at ASC
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':post_id', $postId);
$stmt->execute();
$replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group replies by parent comment ID
$repliesByParent = [];
foreach ($replies as $reply) {
    $parentId = $reply['parent_id'];
    if (!isset($repliesByParent[$parentId])) {
        $repliesByParent[$parentId] = [];
    }
    $repliesByParent[$parentId][] = $reply;
}

// Add replies to their parent comments
foreach ($comments as &$comment) {
    if (isset($repliesByParent[$comment['id']])) {
        $comment['replies'] = $repliesByParent[$comment['id']];
    } else {
        $comment['replies'] = [];
    }
}
unset($comment); // Break the reference

// Handle comment submission
if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $commentContent = sanitize($_POST['comment']);
    
    if (!empty($commentContent)) {
        $userId = getCurrentUserId();
        
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
        $stmt->bindParam(':post_id', $postId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':content', $commentContent);
        
        if ($stmt->execute()) {
            // Create notification for post author (if the commenter is not the author)
            if ($userId !== $post['user_id']) {
                $notification = "New comment on your post '{$post['title']}'.";
                $stmt = $conn->prepare("
                    INSERT INTO notifications (user_id, content, type, reference_id) 
                    VALUES (:user_id, :content, 'comment', :reference_id)
                ");
                $stmt->bindParam(':user_id', $post['user_id']);
                $stmt->bindParam(':content', $notification);
                $stmt->bindParam(':reference_id', $postId);
                $stmt->execute();
            }
            
            setFlashMessage('success', 'Comment added successfully.');
            redirect('view-post&id=' . $postId);
        } else {
            setFlashMessage('danger', 'Failed to add comment. Please try again.');
        }
    } else {
        setFlashMessage('danger', 'Comment cannot be empty.');
    }
}

// Include view post view
require_once 'views/posts/view.php'; 