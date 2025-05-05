<?php
/**
 * Notifications Fixer Script
 * This script will check and fix issues with the notifications system
 */

// Include configuration
require_once 'config/config.php';

echo "<h1>Notifications System Fix</h1>";

// Get database connection
$conn = getDbConnection();

// Check if user is logged in
if (!isLoggedIn()) {
    echo "<p>Please <a href='?page=login'>login</a> to run this fixer.</p>";
    exit;
}

$userId = getCurrentUserId();
echo "<p>Running fixes for user ID: {$userId}</p>";

// Step 1: Check if notifications table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'notifications'")->rowCount() > 0;
if (!$tableExists) {
    echo "<p>Notifications table does not exist. Creating it now...</p>";
    
    try {
        $sql = "CREATE TABLE notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            reference_id INT,
            content TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $conn->exec($sql);
        echo "<p style='color:green'>✓ Notifications table created successfully!</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Error creating notifications table: " . $e->getMessage() . "</p>";
        exit;
    }
} else {
    echo "<p>✓ Notifications table exists.</p>";
    
    // Check table structure
    $columns = $conn->query("DESCRIBE notifications")->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['id', 'user_id', 'type', 'reference_id', 'content', 'is_read', 'created_at'];
    
    foreach ($requiredColumns as $column) {
        if (!in_array($column, $columns)) {
            echo "<p style='color:red'>✗ Missing column: {$column}</p>";
            // Add code to add missing columns if needed
        }
    }
}

// Step 2: Clear any corrupted notifications for this user
echo "<p>Checking for corrupted notifications...</p>";
try {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = :user_id AND (content IS NULL OR content = '')");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $deleted = $stmt->rowCount();
    if ($deleted > 0) {
        echo "<p>Removed {$deleted} corrupted notifications.</p>";
    } else {
        echo "<p>No corrupted notifications found.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error cleaning notifications: " . $e->getMessage() . "</p>";
}

// Step 3: Create test notifications
echo "<p>Creating test notifications...</p>";

$notificationTypes = [
    'test' => 'This is a test notification',
    'profile-update' => 'You have successfully updated your profile',
    'comment' => 'Someone commented on your post',
    'like' => 'Someone liked your post'
];

foreach ($notificationTypes as $type => $content) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, type, reference_id, content, is_read, created_at) 
            VALUES (:user_id, :type, :reference_id, :content, 0, NOW())
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);
        $refId = 1; // Default reference ID
        $stmt->bindParam(':reference_id', $refId);
        $stmt->bindParam(':content', $content);
        $stmt->execute();
        echo "<p style='color:green'>✓ Created {$type} notification</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error creating {$type} notification: " . $e->getMessage() . "</p>";
    }
}

// Step 4: Verify JavaScript file
$jsFile = 'assets/js/script.js';
if (file_exists($jsFile)) {
    echo "<p>✓ JavaScript file exists.</p>";
    
    // Check for notification handling code
    $jsContent = file_get_contents($jsFile);
    if (strpos($jsContent, 'loadNotifications') !== false) {
        echo "<p>✓ loadNotifications function found in JavaScript.</p>";
    } else {
        echo "<p style='color:red'>✗ loadNotifications function missing from JavaScript!</p>";
    }
    
    // Check for notification type handlers
    $typesToCheck = ['profile-update', 'like', 'comment', 'post_approval', 'post_rejection'];
    foreach ($typesToCheck as $type) {
        if (strpos($jsContent, "notification.type === '{$type}'") !== false) {
            echo "<p>✓ Handler for {$type} notifications found.</p>";
        } else {
            echo "<p style='color:red'>✗ Handler for {$type} notifications missing!</p>";
        }
    }
} else {
    echo "<p style='color:red'>✗ JavaScript file not found!</p>";
}

echo "<hr>";
echo "<h2>Fix Complete!</h2>";
echo "<p>Please <a href='?page=home'>go back to the homepage</a> and test your notifications.</p>";
echo "<p><strong>If notifications still don't work:</strong></p>";
echo "<ol>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Ensure AJAX requests are working by checking the network tab</li>";
echo "<li>Verify that the notifications dropdown is properly wired up with event handlers</li>";
echo "</ol>";
?> 