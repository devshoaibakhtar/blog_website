<?php
/**
 * Delete Comment Controller
 * Allows admin users to delete comments directly from posts
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    // Set flash message
    setFlashMessage('danger', 'You must be an admin to delete comments.');
    
    // Redirect back to the post
    if (isset($_GET['post_id'])) {
        header('Location: ' . SITE_URL . '/?page=view-post&id=' . $_GET['post_id']);
    } else {
        header('Location: ' . SITE_URL . '/?page=home');
    }
    exit;
}

// Check if the comment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('danger', 'Comment ID is required.');
    
    // Redirect back to the post
    if (isset($_GET['post_id'])) {
        header('Location: ' . SITE_URL . '/?page=view-post&id=' . $_GET['post_id']);
    } else {
        header('Location: ' . SITE_URL . '/?page=home');
    }
    exit;
}

$commentId = (int) $_GET['id'];
$postId = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;

// Get database connection
$conn = getDbConnection();

// Start a transaction
$conn->beginTransaction();

try {
    // Delete the comment
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = :comment_id");
    $stmt->bindParam(':comment_id', $commentId);
    $stmt->execute();
    
    // Delete any replies to this comment
    $stmt = $conn->prepare("DELETE FROM comments WHERE parent_id = :comment_id");
    $stmt->bindParam(':comment_id', $commentId);
    $stmt->execute();
    
    // Commit the transaction
    $conn->commit();
    
    setFlashMessage('success', 'Comment deleted successfully.');
} catch (Exception $e) {
    // Rollback on error
    $conn->rollBack();
    
    setFlashMessage('danger', 'Failed to delete comment: ' . $e->getMessage());
}

// Redirect back to the post
if ($postId > 0) {
    header('Location: ' . SITE_URL . '/?page=view-post&id=' . $postId);
} else {
    // If post ID is not provided, try to get it from the deleted comment
    $stmt = $conn->prepare("SELECT post_id FROM comments WHERE id = :comment_id");
    $stmt->bindParam(':comment_id', $commentId);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($post) {
        header('Location: ' . SITE_URL . '/?page=view-post&id=' . $post['post_id']);
    } else {
        header('Location: ' . SITE_URL . '/?page=home');
    }
}
exit; 