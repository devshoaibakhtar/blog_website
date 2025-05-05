<?php
/**
 * Delete Post Controller
 */

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('danger', 'You must be logged in to delete a post.');
    redirect('login');
}

// Check if post ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('danger', 'Invalid post ID.');
    redirect('posts');
}

$postId = intval($_GET['id']);
$userId = getCurrentUserId();
$isAdmin = isAdmin();

// Get database connection
$conn = getDbConnection();

// Check if post exists and belongs to the current user (or user is admin)
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->bindParam(':id', $postId);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    setFlashMessage('danger', 'Post not found.');
    redirect('posts');
}

// Check if user is authorized to delete the post
if ($post['user_id'] !== $userId && !$isAdmin) {
    setFlashMessage('danger', 'You do not have permission to delete this post.');
    redirect('posts');
}

// Delete post
try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Delete post tags
    $stmt = $conn->prepare("DELETE FROM post_tags WHERE post_id = :post_id");
    $stmt->bindParam(':post_id', $postId);
    $stmt->execute();
    
    // Delete post comments
    $stmt = $conn->prepare("DELETE FROM comments WHERE post_id = :post_id");
    $stmt->bindParam(':post_id', $postId);
    $stmt->execute();
    
    // Delete post notifications
    $stmt = $conn->prepare("DELETE FROM notifications WHERE reference_id = :post_id AND type IN ('post_approval', 'post_rejection', 'comment')");
    $stmt->bindParam(':post_id', $postId);
    $stmt->execute();
    
    // Delete post
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = :id");
    $stmt->bindParam(':id', $postId);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Delete featured image file if exists
    if ($post['featured_image'] && file_exists(UPLOAD_DIR . $post['featured_image'])) {
        unlink(UPLOAD_DIR . $post['featured_image']);
    }
    
    setFlashMessage('success', 'Post deleted successfully.');
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    setFlashMessage('danger', 'Failed to delete post. Please try again later.');
}

// Redirect to posts page
redirect('posts'); 