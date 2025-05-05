<?php
/**
 * Test script to generate various notification types
 */

// Include configuration
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/helpers.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('danger', 'You must be logged in to create test notifications.');
    redirect('login');
}

// Get database connection
$conn = getDbConnection();

// Get current user ID
$userId = getCurrentUserId();

// Array of notification types to create
$notificationTypes = [
    'post_approval' => 'Your post has been approved and published',
    'post_rejection' => 'Your post has been rejected due to content guidelines',
    'comment' => 'Someone commented on your post',
    'reply' => 'Someone replied to your comment',
    'profile_update' => 'Your profile has been updated successfully',
    'like' => 'Someone liked your post',
    'follow' => 'A new user is following you',
    'mention' => 'You were mentioned in a post',
    'tag' => 'You were tagged in a post',
    'admin' => 'System notification: Site maintenance scheduled'
];

// Create notifications
$created = 0;
$conn->beginTransaction();

try {
    foreach ($notificationTypes as $type => $content) {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, type, reference_id, content, is_read, created_at) 
            VALUES (:user_id, :type, :reference_id, :content, 0, NOW())
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);
        
        // Use an appropriate reference ID for each type
        $referenceId = 1; // Default reference ID
        
        // For posts or comments, try to get a real ID from database
        if (in_array($type, ['post_approval', 'post_rejection', 'comment', 'like', 'mention', 'tag'])) {
            $postStmt = $conn->prepare("SELECT id FROM posts LIMIT 1");
            $postStmt->execute();
            $post = $postStmt->fetch(PDO::FETCH_ASSOC);
            if ($post) {
                $referenceId = $post['id'];
            }
        } elseif ($type === 'reply') {
            // Try to get a real comment ID
            $commentStmt = $conn->prepare("SELECT id FROM comments LIMIT 1");
            $commentStmt->execute();
            $comment = $commentStmt->fetch(PDO::FETCH_ASSOC);
            if ($comment) {
                $referenceId = $comment['id'];
            }
        } elseif ($type === 'follow' || $type === 'profile_update') {
            // Use user's own ID as reference
            $referenceId = $userId;
        }
        
        $stmt->bindParam(':reference_id', $referenceId);
        $stmt->bindParam(':content', $content);
        
        if ($stmt->execute()) {
            $created++;
        }
    }
    
    $conn->commit();
    setFlashMessage('success', "Created $created test notifications successfully.");
} catch (Exception $e) {
    $conn->rollBack();
    setFlashMessage('danger', 'Error creating notifications: ' . $e->getMessage());
}

// Redirect to notifications page
redirect('notifications');
?> 