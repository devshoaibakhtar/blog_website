<?php
/**
 * Admin Posts Controller
 */

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('danger', 'You do not have permission to access the admin panel.');
    redirect('home');
}

// Get database connection
$conn = getDbConnection();

// Handle approve/reject actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $postId = intval($_GET['id']);
    
    // Get post details
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = :id");
    $stmt->bindParam(':id', $postId);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        setFlashMessage('danger', 'Post not found.');
        redirect('admin-posts');
    }
    
    // Approve post
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE posts SET status = 'published' WHERE id = :id");
        $stmt->bindParam(':id', $postId);
        
        if ($stmt->execute()) {
            // Create notification for the author
            $notification = "Your post '{$post['title']}' has been approved and published.";
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, content, type, reference_id) 
                VALUES (:user_id, :content, 'post_approval', :reference_id)
            ");
            $stmt->bindParam(':user_id', $post['user_id']);
            $stmt->bindParam(':content', $notification);
            $stmt->bindParam(':reference_id', $postId);
            $stmt->execute();
            
            setFlashMessage('success', 'Post approved and published successfully.');
        } else {
            setFlashMessage('danger', 'Failed to approve post. Please try again.');
        }
    } 
    // Reject post
    elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE posts SET status = 'rejected' WHERE id = :id");
        $stmt->bindParam(':id', $postId);
        
        if ($stmt->execute()) {
            // Create notification for the author
            $notification = "Your post '{$post['title']}' has been rejected.";
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, content, type, reference_id) 
                VALUES (:user_id, :content, 'post_rejection', :reference_id)
            ");
            $stmt->bindParam(':user_id', $post['user_id']);
            $stmt->bindParam(':content', $notification);
            $stmt->bindParam(':reference_id', $postId);
            $stmt->execute();
            
            setFlashMessage('success', 'Post rejected successfully.');
        } else {
            setFlashMessage('danger', 'Failed to reject post. Please try again.');
        }
    }
    
    redirect('admin-posts');
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$taxonomy = isset($_GET['taxonomy']) ? intval($_GET['taxonomy']) : 0;

// Build query based on filters
$query = "
    SELECT p.*, t.name as taxonomy_name, u.name as author_name 
    FROM posts p 
    JOIN users u ON p.user_id = u.id
    LEFT JOIN taxonomies t ON p.taxonomy_id = t.id
    WHERE 1=1
";
$params = [];

if (!empty($status)) {
    $query .= " AND p.status = :status";
    $params[':status'] = $status;
}

if ($taxonomy > 0) {
    $query .= " AND p.taxonomy_id = :taxonomy_id";
    $params[':taxonomy_id'] = $taxonomy;
}

$query .= " ORDER BY p.created_at DESC";

// Execute query
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all taxonomies for filter
$stmt = $conn->prepare("SELECT * FROM taxonomies ORDER BY name ASC");
$stmt->execute();
$taxonomies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include admin posts view
require_once 'views/admin/posts.php'; 