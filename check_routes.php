<?php
// Load configuration
require_once 'config/config.php';

// Check if upload route exists
echo "Checking routes...<br>";
echo "Upload route exists: " . (isset($routes['upload']) ? 'Yes - ' . $routes['upload'] : 'No') . "<br>";

// Display all routes
echo "<pre>";
print_r($routes);
echo "</pre>";
?> 