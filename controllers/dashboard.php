<?php
/**
 * Dashboard Controller
 */

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('danger', 'You must be logged in to access the dashboard.');
    redirect('login');
}

// Get current user
$user = getCurrentUser();
$userId = $user['id'];
$conn = getDbConnection();

// Get user's posts count
$stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$postsCount = $stmt->fetchColumn();

// Get user's published posts count
$stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = :user_id AND status = 'published'");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$publishedPostsCount = $stmt->fetchColumn();

// Get user's pending posts count
$stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = :user_id AND status = 'pending'");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$pendingPostsCount = $stmt->fetchColumn();

// Get user's comments count
$stmt = $conn->prepare("SELECT COUNT(*) FROM comments WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$commentsCount = $stmt->fetchColumn();

// Get user's recent posts
$stmt = $conn->prepare("
    SELECT p.*, t.name as taxonomy_name 
    FROM posts p 
    LEFT JOIN taxonomies t ON p.taxonomy_id = t.id 
    WHERE p.user_id = :user_id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$recentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include dashboard view
require_once 'views/dashboard.php'; 