<?php
// Simple script to fix image paths and create test images

// Include configuration
require_once 'config/config.php';
require_once 'config/database.php';

// Create uploads directory if it doesn't exist
if (!file_exists('uploads/images')) {
    mkdir('uploads/images', 0777, true);
    echo "Created uploads/images directory<br>";
}

// Create a simple test image
$testImage = 'uploads/images/test-image.svg';
$imageContent = <<<SVG
<svg width="800" height="400" xmlns="http://www.w3.org/2000/svg">
  <rect width="800" height="400" fill="#0d6efd"/>
  <text x="400" y="200" font-family="Arial" font-size="30" text-anchor="middle" fill="white">Test Post Image</text>
</svg>
SVG;

file_put_contents($testImage, $imageContent);
echo "Created test image at $testImage<br>";

// Get database connection
$conn = getDbConnection();

// Update all posts to use the test image
$sql = "UPDATE posts SET featured_image = 'uploads/images/test-image.svg'";
$conn->exec($sql);
echo "Updated all posts to use the test image<br>";

// Display links
echo "<p>Images fixed! Please try:</p>";
echo "<p><a href='" . SITE_URL . "/?page=home'>Go to home page</a></p>";
echo "<p><a href='" . SITE_URL . "/uploads/images/test-image.svg'>View test image directly</a></p>";
?> 