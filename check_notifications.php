<?php
// Include configuration file to get database connection
require_once 'config/config.php';

// Get database connection
$conn = getDbConnection();

// Query notifications
$stmt = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display notifications
echo "<h1>All Notifications</h1>";

if (count($notifications) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Content</th><th>Is Read</th><th>Reference ID</th><th>Created At</th></tr>";
    
    foreach ($notifications as $notification) {
        echo "<tr>";
        echo "<td>" . $notification['id'] . "</td>";
        echo "<td>" . $notification['user_id'] . "</td>";
        echo "<td>" . $notification['type'] . "</td>";
        echo "<td>" . $notification['content'] . "</td>";
        echo "<td>" . ($notification['is_read'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . ($notification['reference_id'] ?? 'NULL') . "</td>";
        echo "<td>" . $notification['created_at'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No notifications found in the database.</p>";
}

// Also display the structure of the notifications table
echo "<h2>Notifications Table Structure</h2>";
$stmt = $conn->query("DESCRIBE notifications");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

foreach ($columns as $column) {
    echo "<tr>";
    echo "<td>" . $column['Field'] . "</td>";
    echo "<td>" . $column['Type'] . "</td>";
    echo "<td>" . $column['Null'] . "</td>";
    echo "<td>" . $column['Key'] . "</td>";
    echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . $column['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>"; 