<?php
/**
 * Add Comment Controller
 * Handles adding a new comment to a post
 */

// Include required files
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'You must be logged in to comment.'
        ]);
        exit;
    }
    
    setFlashMessage('danger', 'You must be logged in to comment.');
    redirect('login');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method.'
        ]);
        exit;
    }
    
    setFlashMessage('danger', 'Invalid request.');
    redirect('home');
}

// Check if post_id and content are provided
if (!isset($_POST['post_id']) || empty($_POST['post_id']) || !isset($_POST['content']) || empty($_POST['content'])) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Post ID and content are required.'
        ]);
        exit;
    }
    
    setFlashMessage('danger', 'Post ID and content are required.');
    redirect('home');
}

// Get post ID, content, parent ID, and user ID
$postId = (int) $_POST['post_id'];
$content = trim($_POST['content']);
$parentId = isset($_POST['parent_id']) ? (int) $_POST['parent_id'] : 0;
$userId = getCurrentUserId();

// Validate content
if (strlen($content) < 3) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Comment must be at least 3 characters long.'
        ]);
        exit;
    }
    
    setFlashMessage('danger', 'Comment must be at least 3 characters long.');
    redirect('view-post&id=' . $postId);
}

$conn = getDbConnection();

// Check if post exists
$stmt = $conn->prepare("SELECT id, user_id, title FROM posts WHERE id = :post_id");
$stmt->bindParam(':post_id', $postId);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Post not found.'
        ]);
        exit;
    }
    
    setFlashMessage('danger', 'Post not found.');
    redirect('home');
}

// Begin transaction
$conn->beginTransaction();

try {
    // Add comment
    $stmt = $conn->prepare("
        INSERT INTO comments (post_id, user_id, content, parent_id, created_at) 
        VALUES (:post_id, :user_id, :content, :parent_id, NOW())
    ");
    $stmt->bindParam(':post_id', $postId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':parent_id', $parentId);
    $stmt->execute();
    $commentId = $conn->lastInsertId();
    
    // Notify post author if it's not the current user
    if ($post['user_id'] != $userId) {
        // Get user name
        $stmt = $conn->prepare("SELECT name FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $userName = $stmt->fetchColumn();
        
        // Create notification
        $notificationType = 'comment';
        $notificationContent = "{$userName} commented on your post: {$post['title']}";
        
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, type, reference_id, content, is_read, created_at) 
            VALUES (:user_id, :type, :reference_id, :content, 0, NOW())
        ");
        $stmt->bindParam(':user_id', $post['user_id']);
        $stmt->bindParam(':type', $notificationType);
        $stmt->bindParam(':reference_id', $commentId);
        $stmt->bindParam(':content', $notificationContent);
        $stmt->execute();
    }
    
    // If this is a reply, also notify the parent comment author
    if ($parentId > 0) {
        // Get parent comment author
        $stmt = $conn->prepare("
            SELECT c.user_id, u.name as parent_author 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.id = :parent_id
        ");
        $stmt->bindParam(':parent_id', $parentId);
        $stmt->execute();
        $parentComment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Only notify if the parent comment author is different from current user
        if ($parentComment && $parentComment['user_id'] != $userId) {
            // Get user name
            $stmt = $conn->prepare("SELECT name FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $userName = $stmt->fetchColumn();
            
            // Create notification
            $notificationType = 'reply';
            $notificationContent = "{$userName} replied to your comment on the post: {$post['title']}";
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, type, reference_id, content, is_read, created_at) 
                VALUES (:user_id, :type, :reference_id, :content, 0, NOW())
            ");
            $stmt->bindParam(':user_id', $parentComment['user_id']);
            $stmt->bindParam(':type', $notificationType);
            $stmt->bindParam(':reference_id', $commentId);
            $stmt->bindParam(':content', $notificationContent);
            $stmt->execute();
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Comment added successfully.'
        ]);
        exit;
    }
    
    setFlashMessage('success', 'Comment added successfully.');
    redirect('view-post&id=' . $postId . '#comment-' . $commentId);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add comment: ' . $e->getMessage()
        ]);
        exit;
    }
    
    setFlashMessage('danger', 'Failed to add comment: ' . $e->getMessage());
    redirect('view-post&id=' . $postId);
}

/**
 * Check if the current request is an AJAX request
 * @return boolean
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
} 