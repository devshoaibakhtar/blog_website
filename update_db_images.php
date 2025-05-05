<?php
// Include configuration
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/helpers.php';

// Get database connection
$conn = getDbConnection();

// Update all posts to use the test image
$testImage = 'uploads/images/test-post-image.svg';
$sql = "UPDATE posts SET featured_image = :image_path";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':image_path', $testImage);
$result = $stmt->execute();

if ($result) {
    echo "<h2>Success!</h2>";
    echo "<p>All posts have been updated to use the image: {$testImage}</p>";
} else {
    echo "<h2>Error!</h2>";
    echo "<p>Failed to update post images. Error: " . implode(', ', $stmt->errorInfo()) . "</p>";
}

// Display links
echo "<hr>";
echo "<p>Please check:</p>";
echo "<p><a href='" . SITE_URL . "/?page=home'>Go to home page</a></p>";
echo "<p><a href='" . SITE_URL . "/" . $testImage . "'>View test image directly</a></p>";
?> 