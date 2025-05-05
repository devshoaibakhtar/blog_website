<?php
// Load configuration
require_once 'config/config.php';
require_once 'includes/helpers.php';

// Connect to database
try {
    $conn = getDbConnection();
    
    // Check if photos table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'photos'");
    if ($stmt->rowCount() == 0) {
        echo "The photos table doesn't exist in the database.";
        exit;
    }
    
    // Count total photos
    $stmt = $conn->query("SELECT COUNT(*) FROM photos");
    $totalPhotos = $stmt->fetchColumn();
    
    echo "<h2>Photos in Database</h2>";
    echo "Total photos: " . $totalPhotos . "<br><br>";
    
    if ($totalPhotos > 0) {
        // Get most recent photos
        $stmt = $conn->query("SELECT * FROM photos ORDER BY created_at DESC LIMIT 10");
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Most Recent Photos:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Title</th><th>Path</th><th>Created At</th></tr>";
        
        foreach ($photos as $photo) {
            echo "<tr>";
            echo "<td>" . $photo['id'] . "</td>";
            echo "<td>" . $photo['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($photo['title']) . "</td>";
            echo "<td>" . $photo['photo_path'] . "</td>";
            echo "<td>" . $photo['created_at'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Check if files exist
        echo "<h3>File Existence Check:</h3>";
        foreach ($photos as $photo) {
            $filepath = $photo['photo_path'];
            echo "File: " . $filepath . " - ";
            echo file_exists($filepath) ? "Exists" : "Missing";
            echo "<br>";
        }
    } else {
        echo "No photos found in the database.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?> 