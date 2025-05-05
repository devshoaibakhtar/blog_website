<?php
/**
 * Database Connection Test Script
 * 
 * This script tests the database connection using the configured credentials.
 * Important: Delete this file after successful installation!
 */

// Include database configuration
require_once 'config/database.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test database connection
    $conn = getDbConnection();
    echo "<p style='color:green'>✅ Connection successful!</p>";
    
    // Test query execution
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "<p>Found {$userCount} users in the database.</p>";
    
    // Test database tables
    $tables = [
        'users',
        'posts',
        'taxonomies',
        'tags',
        'post_tags',
        'comments',
        'notifications'
    ];
    
    echo "<h2>Database Tables Check:</h2>";
    echo "<ul>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT 1 FROM {$table} LIMIT 1");
            echo "<li style='color:green'>✅ Table '{$table}' exists</li>";
        } catch (PDOException $e) {
            echo "<li style='color:red'>❌ Table '{$table}' is missing or has errors</li>";
        }
    }
    
    echo "</ul>";
    
    echo "<p><strong>Important:</strong> Delete this file after confirming your database connection works!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials in config/database.php.</p>";
} 