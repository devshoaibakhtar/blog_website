<?php
/**
 * Admin Dashboard Controller
 */

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('danger', 'You do not have permission to access the admin panel.');
    redirect('home');
}

// Get database connection
$conn = getDbConnection();

// Get total users count
$stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$stmt->execute();
$usersCount = $stmt->fetchColumn();

// Get total posts count
$stmt = $conn->prepare("SELECT COUNT(*) FROM posts");
$stmt->execute();
$postsCount = $stmt->fetchColumn();

// Get pending posts count
$stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE status = 'pending'");
$stmt->execute();
$pendingPostsCount = $stmt->fetchColumn();

// Get total comments count
$stmt = $conn->prepare("SELECT COUNT(*) FROM comments");
$stmt->execute();
$commentsCount = $stmt->fetchColumn();

// Get recent pending posts
$stmt = $conn->prepare("
    SELECT p.*, u.name as author_name, t.name as taxonomy_name 
    FROM posts p 
    JOIN users u ON p.user_id = u.id
    LEFT JOIN taxonomies t ON p.taxonomy_id = t.id
    WHERE p.status = 'pending' 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$pendingPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent users
$stmt = $conn->prepare("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include admin dashboard view
require_once 'views/admin/index.php'; 