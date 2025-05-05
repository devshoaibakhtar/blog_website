<?php
/**
 * Database Configuration
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'blog_management');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password

// Database connection
function getDbConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create or update comments table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS comments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                post_id INT NOT NULL,
                user_id INT NOT NULL,
                parent_id INT DEFAULT 0,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        // Check if parent_id column exists, if not add it
        $result = $conn->query("SHOW COLUMNS FROM comments LIKE 'parent_id'");
        if ($result->rowCount() == 0) {
            $conn->exec("ALTER TABLE comments ADD COLUMN parent_id INT DEFAULT 0 AFTER user_id");
        }
        
        // Create photos table if it doesn't exist
        $conn->exec("
            CREATE TABLE IF NOT EXISTS photos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                photo_path VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        return $conn;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        exit;
    }
} 