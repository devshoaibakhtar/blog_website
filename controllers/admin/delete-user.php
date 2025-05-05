<?php
/**
 * Admin Delete User Controller
 */

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('danger', 'You must be logged in as an admin to access this page.');
    redirect('login');
    exit;
}

// Get user ID from URL parameter
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$userId) {
    setFlashMessage('danger', 'Invalid user ID');
    redirect('admin');
    exit;
}

// Prevent deleting own account
if ($userId == $_SESSION['user_id']) {
    setFlashMessage('danger', 'You cannot delete your own account');
    redirect('admin');
    exit;
}

// Get database connection
$conn = getDbConnection();

// Get user details to check if they're admin
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    setFlashMessage('danger', 'User not found');
    redirect('admin');
    exit;
}

// Extra protection against deleting admins
if ($user['role'] === 'admin') {
    setFlashMessage('danger', 'You do not have permission to delete admin accounts');
    redirect('admin');
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Delete user's comments
    $stmt = $conn->prepare("DELETE FROM comments WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Delete user's posts
    $stmt = $conn->prepare("DELETE FROM posts WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Delete user's likes (if exists)
    try {
        $stmt = $conn->prepare("DELETE FROM post_likes WHERE user_id = ?");
        $stmt->execute([$userId]);
    } catch (PDOException $e) {
        // If the table doesn't exist, just continue
        error_log('Note: post_likes table might not exist: ' . $e->getMessage());
    }
    
    // Delete user's notifications (if exists)
    try {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->execute([$userId]);
    } catch (PDOException $e) {
        // If the table doesn't exist, just continue
        error_log('Note: notifications table might not exist: ' . $e->getMessage());
    }
    
    // Finally delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    // Commit transaction
    $conn->commit();
    
    setFlashMessage('success', 'User "' . htmlspecialchars($user['name']) . '" deleted successfully along with all their content');
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Error deleting user: ' . $e->getMessage());
    setFlashMessage('danger', 'Failed to delete user: ' . $e->getMessage());
}

// Redirect back to admin dashboard
redirect('admin');
exit; 