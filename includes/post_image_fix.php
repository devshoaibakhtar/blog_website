<?php
/**
 * Fix post image paths
 * This function ensures that post images are properly displayed
 */
function fixPostImagePath($imagePath) {
    if (empty($imagePath)) {
        return false;
    }
    
    // Normalize path (replace backslashes with forward slashes)
    $imagePath = str_replace('\\', '/', $imagePath);
    
    // Remove any leading slashes
    $imagePath = ltrim($imagePath, '/');
    
    // If the path already includes uploads or assets, it's a relative path
    if (strpos($imagePath, 'uploads/') === 0 || strpos($imagePath, 'assets/') === 0) {
        // Path is already relative, keep as is
        return $imagePath;
    }
    
    // Check if the file exists
    if (file_exists($imagePath)) {
        return $imagePath;
    }
    
    // If the file doesn't exist, check if it exists with a 'uploads/' prefix
    if (file_exists('uploads/' . $imagePath)) {
        return 'uploads/' . $imagePath;
    }
    
    // If the file still doesn't exist, use a placeholder
    return 'assets/images/placeholder.svg';
}

/**
 * Create a test image and update post image paths in the database
 */
function createTestImagesAndFixDatabase() {
    global $conn;
    
    // Create uploads directory if it doesn't exist
    if (!file_exists('uploads/images')) {
        mkdir('uploads/images', 0777, true);
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
    
    // Update all posts to use the test image
    $sql = "UPDATE posts SET featured_image = :image_path WHERE featured_image IS NULL OR featured_image = ''";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':image_path', $testImage);
    $stmt->execute();
    
    return $testImage;
}
?> 