<?php
/**
 * Admin Contact Messages Controller
 */

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('danger', 'You do not have permission to access this page.');
    redirect('home');
}

// Get database connection
$conn = getDbConnection();

// First check if the contact_messages table exists and create it if not
try {
    $checkTableStmt = $conn->prepare("
        SELECT 1 FROM contact_messages LIMIT 1
    ");
    $checkTableStmt->execute();
} catch (PDOException $e) {
    // Table doesn't exist, create it
    $sql = "
        CREATE TABLE IF NOT EXISTS `contact_messages` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `subject` varchar(255) NOT NULL,
          `message` text NOT NULL,
          `read` tinyint(1) NOT NULL DEFAULT 0,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    try {
        $conn->exec($sql);
        error_log('Contact messages table created successfully in admin controller.');
    } catch (PDOException $createErr) {
        error_log('Error creating contact_messages table in admin controller: ' . $createErr->getMessage());
        setFlashMessage('danger', 'There was an error with the contact messages feature. Please try again later.');
        redirect('admin');
    }
}

// Handle marking message as read
if (isset($_GET['action']) && $_GET['action'] === 'mark-read' && isset($_GET['id'])) {
    $messageId = intval($_GET['id']);
    
    $stmt = $conn->prepare("UPDATE contact_messages SET `read` = 1 WHERE id = :id");
    $stmt->bindParam(':id', $messageId);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Message marked as read.');
    } else {
        setFlashMessage('danger', 'Failed to update message status.');
    }
    
    redirect('admin-contact-messages');
}

// Handle deleting message
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $messageId = intval($_GET['id']);
    
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = :id");
    $stmt->bindParam(':id', $messageId);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Message deleted successfully.');
    } else {
        setFlashMessage('danger', 'Failed to delete message.');
    }
    
    redirect('admin-contact-messages');
}

// View single message
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $messageId = intval($_GET['id']);
    
    // Get message details
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = :id");
    $stmt->bindParam(':id', $messageId);
    $stmt->execute();
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        setFlashMessage('danger', 'Message not found.');
        redirect('admin-contact-messages');
    }
    
    // Mark as read if not already
    if (!$message['read']) {
        $updateStmt = $conn->prepare("UPDATE contact_messages SET `read` = 1 WHERE id = :id");
        $updateStmt->bindParam(':id', $messageId);
        $updateStmt->execute();
    }
    
    // Include the view message template
    require_once 'views/admin/contact-messages/view.php';
} else {
    try {
        // Get all messages
        $stmt = $conn->prepare("
            SELECT * FROM contact_messages 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get unread count
        $stmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE `read` = 0");
        $stmt->execute();
        $unreadCount = $stmt->fetchColumn();
        
        // Include the list messages template
        require_once 'views/admin/contact-messages/index.php';
    } catch (PDOException $e) {
        error_log('Error in admin contact messages: ' . $e->getMessage());
        $messages = [];
        $unreadCount = 0;
        
        setFlashMessage('danger', 'There was an error loading contact messages.');
        require_once 'views/admin/contact-messages/index.php';
    }
} 