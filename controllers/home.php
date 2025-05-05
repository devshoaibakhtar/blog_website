<?php
/**
 * Home Controller
 */

// Include the post image fix functions
require_once 'includes/post_image_fix.php';

// Get all published posts with their taxonomies and authors
$conn = getDbConnection();

// Create post_likes table if it doesn't exist
try {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS post_likes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (post_id, user_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
} catch (PDOException $e) {
    // Log error and continue
    error_log("Error creating post_likes table: " . $e->getMessage());
}

// Get pagination parameters
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : null;
$tagSearch = isset($_GET['tag_search']) ? $_GET['tag_search'] : null;
$taxonomySlug = isset($_GET['taxonomy']) ? $_GET['taxonomy'] : null;
$tagSlug = isset($_GET['tag']) ? $_GET['tag'] : null;

// Build base query
$sql = "SELECT p.*, t.name as taxonomy_name, t.slug as taxonomy_slug, u.name as author_name,
       (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count
       FROM posts p
       LEFT JOIN taxonomies t ON p.taxonomy_id = t.id
       LEFT JOIN users u ON p.user_id = u.id";

// Add joins for filters
if ($tagSlug || $tagSearch) {
    $sql .= " LEFT JOIN post_tags pt ON p.id = pt.post_id
              LEFT JOIN tags t2 ON pt.tag_id = t2.id";
}

if ($taxonomySlug) {
    $sql .= " LEFT JOIN taxonomies tax ON p.taxonomy_id = tax.id";
}

// Build WHERE conditions
$whereConditions = ["p.status = 'published'"];
$params = [];

if ($search) {
    $whereConditions[] = "p.title LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

if ($tagSearch) {
    $whereConditions[] = "t2.name LIKE :tag_search";
    $params[':tag_search'] = '%' . $tagSearch . '%';
}

if ($taxonomySlug) {
    $whereConditions[] = "tax.slug = :taxonomy_slug";
    $params[':taxonomy_slug'] = $taxonomySlug;
}

if ($tagSlug) {
    $whereConditions[] = "t2.slug = :tag_slug";
    $params[':tag_slug'] = $tagSlug;
}

// Combine conditions
if (count($whereConditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

// Add grouping and ordering
$sql .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT :offset, :limit";
$params[':offset'] = $offset;
$params[':limit'] = $itemsPerPage;

// Execute query
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    if ($key === ':offset' || $key === ':limit') {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value);
    }
}
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total posts for pagination
$countSql = "SELECT COUNT(DISTINCT p.id) FROM posts p";
if ($tagSlug || $tagSearch) {
    $countSql .= " LEFT JOIN post_tags pt ON p.id = pt.post_id
                   LEFT JOIN tags t2 ON pt.tag_id = t2.id";
}
if ($taxonomySlug) {
    $countSql .= " LEFT JOIN taxonomies tax ON p.taxonomy_id = tax.id";
}
$countWhere = " WHERE p.status = 'published'";
if ($search) {
    $countWhere .= " AND p.title LIKE :search";
}
if ($tagSearch) {
    $countWhere .= " AND t2.name LIKE :tag_search";
}
if ($taxonomySlug) {
    $countWhere .= " AND tax.slug = :taxonomy_slug";
}
if ($tagSlug) {
    $countWhere .= " AND t2.slug = :tag_slug";
}
$countSql .= $countWhere;

$countStmt = $conn->prepare($countSql);
if ($search) {
    $countStmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
if ($tagSearch) {
    $countStmt->bindValue(':tag_search', '%' . $tagSearch . '%', PDO::PARAM_STR);
}
if ($taxonomySlug) {
    $countStmt->bindValue(':taxonomy_slug', $taxonomySlug, PDO::PARAM_STR);
}
if ($tagSlug) {
    $countStmt->bindValue(':tag_slug', $tagSlug, PDO::PARAM_STR);
}
$countStmt->execute();
$totalPosts = $countStmt->fetchColumn();
$totalPages = ceil($totalPosts / $itemsPerPage);

// Process featured image path for each post
foreach ($posts as &$post) {
    if (!empty($post['featured_image'])) {
        // Normalize path
        $post['featured_image'] = str_replace('\\', '/', $post['featured_image']);
        
        // If path starts with uploads/ or assets/, make sure it's formatted correctly
        if (strpos($post['featured_image'], 'uploads/') === 0 || 
            strpos($post['featured_image'], 'assets/') === 0) {
            // Path is already relative, keep as is
            $post['featured_image'] = ltrim($post['featured_image'], '/');
        }
        
        // Log the final image path
        error_log('Featured image path for post ' . $post['id'] . ': ' . $post['featured_image']);
    }
}
unset($post); // Break reference

// Get all taxonomies
$query = "SELECT * FROM taxonomies ORDER BY name ASC";
$stmt = $conn->prepare($query);
$stmt->execute();
$taxonomies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load the view
require 'views/home.php';