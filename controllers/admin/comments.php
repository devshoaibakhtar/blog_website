<?php
/**
 * Admin Comments Controller
 * Handles admin actions for comments: view and delete
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    // Set flash message
    setFlashMessage('danger', 'You must be an admin to access this page.');
    
    // Redirect to login
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

// Get database connection
$conn = getDbConnection();

// Handle delete action if requested
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $commentId = (int) $_GET['id'];
    
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
    
    // Redirect back to comments page
    header('Location: ' . SITE_URL . '/?page=admin-comments');
    exit;
}

// Pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchCondition = '';
$searchParams = [];

if (!empty($search)) {
    $searchCondition = " AND (c.content LIKE :search OR u.name LIKE :search OR p.title LIKE :search)";
    $searchParams[':search'] = "%$search%";
}

// Count total comments
$query = "SELECT COUNT(*) FROM comments c 
          JOIN users u ON c.user_id = u.id 
          JOIN posts p ON c.post_id = p.id 
          WHERE 1=1" . $searchCondition;

$stmt = $conn->prepare($query);

foreach ($searchParams as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$totalComments = $stmt->fetchColumn();
$totalPages = ceil($totalComments / $limit);

// Get comments for current page
$query = "SELECT c.*, u.name as user_name, p.title as post_title, p.id as post_id  
          FROM comments c
          JOIN users u ON c.user_id = u.id
          JOIN posts p ON c.post_id = p.id
          WHERE 1=1" . $searchCondition . "
          ORDER BY c.created_at DESC
          LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($query);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

foreach ($searchParams as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load view file
include __DIR__ . '/../../views/admin/comments-standalone.php'; 