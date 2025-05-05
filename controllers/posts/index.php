<?php
/**
 * Posts Index Controller
 */

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('danger', 'You must be logged in to view your posts.');
    redirect('login');
}

// Get current user ID
$userId = getCurrentUserId();

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$taxonomy = isset($_GET['taxonomy']) ? intval($_GET['taxonomy']) : 0;

// Get database connection
$conn = getDbConnection();

// Build query based on filters
$query = "
    SELECT p.*, t.name as taxonomy_name 
    FROM posts p 
    LEFT JOIN taxonomies t ON p.taxonomy_id = t.id 
    WHERE p.user_id = :user_id
";
$params = [':user_id' => $userId];

if (!empty($status)) {
    $query .= " AND p.status = :status";
    $params[':status'] = $status;
}

if ($taxonomy > 0) {
    $query .= " AND p.taxonomy_id = :taxonomy_id";
    $params[':taxonomy_id'] = $taxonomy;
}

$query .= " ORDER BY p.created_at DESC";

// Prepare and execute query
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

// Include posts index view
require_once 'views/posts/index.php'; 