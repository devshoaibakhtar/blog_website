<?php
/**
 * AJAX endpoint for liking/unliking posts
 */

// Include configuration files
require_once '../config/config.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to like posts.'
    ]);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Check if post_id is provided
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID is required.'
    ]);
    exit;
}

// Get post ID and user ID
$postId = (int) $_POST['post_id'];
$userId = getCurrentUserId();

// Connect to database
$conn = getDbConnection();

// Check if post exists
$stmt = $conn->prepare("SELECT id FROM posts WHERE id = :post_id");
$stmt->bindParam(':post_id', $postId);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Post not found.'
    ]);
    exit;
}

// Check if user has already liked the post
$stmt = $conn->prepare("SELECT id FROM post_likes WHERE post_id = :post_id AND user_id = :user_id");
$stmt->bindParam(':post_id', $postId);
$stmt->bindParam(':user_id', $userId);
$stmt->execute();

$alreadyLiked = $stmt->rowCount() > 0;
$liked = false;

// Create post_likes table if it doesn't exist
try {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS post_likes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (post_id, user_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

// Begin transaction
$conn->beginTransaction();

try {
    if ($alreadyLiked) {
        // Unlike post
        $stmt = $conn->prepare("DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id");
        $stmt->bindParam(':post_id', $postId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $liked = false;
    } else {
        // Like post
        $stmt = $conn->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (:post_id, :user_id)");
        $stmt->bindParam(':post_id', $postId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $liked = true;
        
        // Notify post author if the post wasn't created by the current user
        $stmt = $conn->prepare("
            SELECT user_id FROM posts WHERE id = :post_id AND user_id != :current_user_id
        ");
        $stmt->bindParam(':post_id', $postId);
        $stmt->bindParam(':current_user_id', $userId);
        $stmt->execute();
        
        $authorId = $stmt->fetchColumn();
        
        if ($authorId) {
            // Get user name
            $stmt = $conn->prepare("SELECT name FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $userName = $stmt->fetchColumn();
            
            // Get post title
            $stmt = $conn->prepare("SELECT title FROM posts WHERE id = :post_id");
            $stmt->bindParam(':post_id', $postId);
            $stmt->execute();
            $postTitle = $stmt->fetchColumn();
            
            // Create notification
            $message = "{$userName} liked your post: {$postTitle}";
            $link = "?page=view-post&id={$postId}";
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, link) 
                VALUES (:user_id, :message, :link)
            ");
            $stmt->bindParam(':user_id', $authorId);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':link', $link);
            $stmt->execute();
        }
    }
    
    // Get updated like count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = :post_id");
    $stmt->bindParam(':post_id', $postId);
    $stmt->execute();
    $likeCount = $stmt->fetchColumn();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likeCount' => $likeCount
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process like: ' . $e->getMessage()
    ]);
} 