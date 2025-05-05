<?php
// Include required files
require_once 'config/config.php';
require_once 'config/database.php';

// Get database connection
$conn = getDbConnection();

// Update multiple posts with sample images
$sql = "UPDATE posts SET featured_image = :image WHERE id = :id";
$stmt = $conn->prepare($sql);

// Update posts
$updates = [
    ['id' => 1, 'image' => 'uploads/images/sample-post.svg'],
    ['id' => 2, 'image' => 'uploads/images/sample-post.svg'],
    ['id' => 3, 'image' => 'uploads/images/sample-post.svg']
];

foreach ($updates as $update) {
    $stmt->bindParam(':id', $update['id']);
    $stmt->bindParam(':image', $update['image']);
    $stmt->execute();
    echo "Updated post {$update['id']} with image {$update['image']}<br>";
}

echo "<p>Posts updated successfully!</p>";
echo "<p><a href='" . SITE_URL . "/?page=home'>Go to home page</a></p>";
?> 