<?php
// Include configuration
require_once 'config/config.php';

// Get database connection
$conn = getDbConnection();

// Set headers for plain text output
header('Content-Type: text/plain');

echo "=== NOTIFICATIONS DEBUGGING ===\n\n";

// Get current user
if (isLoggedIn()) {
    $userId = getCurrentUserId();
    echo "Current User ID: $userId\n";
    
    // Check if notifications table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($stmt->rowCount() > 0) {
        echo "Notifications table exists.\n";
        
        // Get all notifications for the current user
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nUser Notifications: " . count($notifications) . " found\n";
        foreach ($notifications as $i => $n) {
            echo "  [$i] ID: {$n['id']}, Type: {$n['type']}, Content: {$n['content']}, IsRead: {$n['is_read']}, Created: {$n['created_at']}\n";
        }
        
        // Check notifications table structure
        $stmt = $conn->query("DESCRIBE notifications");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nNotifications Table Structure:\n";
        foreach ($columns as $col) {
            echo "  {$col['Field']} - {$col['Type']} - Null: {$col['Null']} - Key: {$col['Key']}\n";
        }
        
        // Check for JavaScript errors
        echo "\nCheck your browser console for JavaScript errors\n";
        echo "Also check these AJAX requests in browser Network tab:\n";
        echo "  - " . SITE_URL . "/?page=notifications\n";
        
        // Add a test notification
        echo "\nAdding a test notification for debugging...\n";
        try {
            $content = "This is a test notification created at " . date('Y-m-d H:i:s');
            $type = 'test';
            $refId = 0;
            
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, reference_id, content, is_read, created_at) 
                VALUES (:user_id, :type, :reference_id, :content, 0, NOW())");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':reference_id', $refId);
            $stmt->bindParam(':content', $content);
            $result = $stmt->execute();
            
            if ($result) {
                echo "Test notification created successfully.\n";
                echo "Please refresh your page and check if the notification appears.\n";
            } else {
                echo "Failed to create test notification.\n";
                echo "Error: " . json_encode($stmt->errorInfo()) . "\n";
            }
        } catch (Exception $e) {
            echo "Error creating test notification: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "Notifications table does not exist. Please check your database setup.\n";
    }
} else {
    echo "User is not logged in. Please log in to debug notifications.\n";
}

echo "\n=== END OF DEBUG INFO ===\n";
?> 