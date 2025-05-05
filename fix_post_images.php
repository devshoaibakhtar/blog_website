<?php
// Simple script to fix image paths and create test images

// Include configuration
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/functions.php';

// Get database connection
$conn = getDbConnection();

// Create uploads directory if it doesn't exist
if (!file_exists('uploads/images')) {
    mkdir('uploads/images', 0777, true);
    echo "Created uploads/images directory<br>";
}

// Create a simple test image
$testImage = 'uploads/images/test-post-image.svg';
$imageContent = <<<SVG
<svg width="800" height="400" xmlns="http://www.w3.org/2000/svg">
  <rect width="800" height="400" fill="#0d6efd"/>
  <text x="400" y="200" font-family="Arial" font-size="30" text-anchor="middle" fill="white">Blog Post Image</text>
</svg>
SVG;

file_put_contents($testImage, $imageContent);
echo "Created test image at $testImage<br>";

// Check if the image was created successfully
if (file_exists($testImage)) {
    echo "Image created successfully!<br>";
} else {
    echo "Failed to create image!<br>";
}

// Update all posts to use the test image
$sql = "UPDATE posts SET featured_image = :image_path";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':image_path', $testImage);
$stmt->execute();
echo "Updated all posts to use the test image<br>";

// Display paths for debugging
echo "<h2>Debug Information</h2>";
echo "<p>SITE_URL: " . SITE_URL . "</p>";
echo "<p>UPLOAD_DIR: " . UPLOAD_DIR . "</p>";
echo "<p>Image absolute path: " . $_SERVER['DOCUMENT_ROOT'] . "/" . $testImage . "</p>";
echo "<p>Image URL: " . SITE_URL . "/" . $testImage . "</p>";

// Display links
echo "<hr>";
echo "<p>Images fixed! Please try:</p>";
echo "<p><a href='" . SITE_URL . "/?page=home'>Go to home page</a></p>";
echo "<p><a href='" . SITE_URL . "/" . $testImage . "'>View test image directly</a></p>";
?> 