<?php
// Setup configuration
require_once 'config.php';
require_once 'includes/db.php';

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Check if photos table already exists
    $stmt = $conn->query("SHOW TABLES LIKE 'photos'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "Photos table already exists!\n";
    } else {
        // SQL to create photos table
        $sql = "
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
        ";
        
        // Execute query
        $conn->exec($sql);
        
        echo "Photos table created successfully!\n";
    }
    
    // Show all tables
    echo "\nAll tables in database:\n";
    $stmt = $conn->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 