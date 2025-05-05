<?php
/**
 * Like Post Controller
 * Handles liking/unliking posts
 */

// Include required files
require_once __DIR__ . '/../config/config.php';

// Debug log
error_log('Like post controller called. Request method: ' . $_SERVER['REQUEST_METHOD']);
error_log('POST data: ' . print_r($_POST, true));

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    error_log('User not logged in');
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to like posts.'
    ]);
    exit;
}

error_log('User ID: ' . getCurrentUserId());

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
            SELECT user_id, title FROM posts WHERE id = :post_id AND user_id != :current_user_id
        ");
        $stmt->bindParam(':post_id', $postId);
        $stmt->bindParam(':current_user_id', $userId);
        $stmt->execute();
        
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post) {
            // Get user name
            $stmt = $conn->prepare("SELECT name FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $userName = $stmt->fetchColumn();
            
            // Create notification
            $notificationType = 'like';
            $notificationContent = "{$userName} liked your post: {$post['title']}";
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, type, reference_id, content, is_read, created_at) 
                VALUES (:user_id, :type, :reference_id, :content, 0, NOW())
            ");
            $stmt->bindParam(':user_id', $post['user_id']);
            $stmt->bindParam(':type', $notificationType);
            $stmt->bindParam(':reference_id', $postId);
            $stmt->bindParam(':content', $notificationContent);
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