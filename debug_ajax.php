<?php
/**
 * AJAX Debug Tool
 * This script captures and logs AJAX requests to help debug issues
 */

// Set headers to prevent caching
header('Content-Type: text/plain');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Include configuration
require_once 'config/config.php';

echo "=== AJAX DEBUG TOOL ===\n\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Log request information
echo "REQUEST METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "REQUEST URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "REMOTE ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n";
echo "USER AGENT: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

// Log headers
echo "\nREQUEST HEADERS:\n";
$headers = getallheaders();
foreach ($headers as $name => $value) {
    echo "$name: $value\n";
}

// Check if we're mimicking the notifications endpoint
if (isset($_GET['simulate']) && $_GET['simulate'] === 'notifications') {
    echo "\nSIMULATING NOTIFICATIONS ENDPOINT\n";
    
    // Get connection
    $conn = getDbConnection();
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo "\nERROR: User not logged in\n";
        exit;
    }
    
    $userId = getCurrentUserId();
    echo "\nUser ID: $userId\n";
    
    try {
        // Get notifications
        $stmt = $conn->prepare("
            SELECT * FROM notifications 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count unread
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE user_id = :user_id AND is_read = 0
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $unreadCount = $stmt->fetchColumn();
        
        echo "\nFound " . count($notifications) . " notifications, $unreadCount unread\n";
        
        // Generate response
        $response = [
            'success' => true,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ];
        
        // JSON encode with error checking
        $json = json_encode($response);
        if ($json === false) {
            echo "\nERROR: JSON encoding failed: " . json_last_error_msg() . "\n";
            
            // Check each notification for encoding issues
            foreach ($notifications as $i => $notification) {
                $singleJson = json_encode($notification);
                if ($singleJson === false) {
                    echo "Notification #$i has encoding issues: " . json_last_error_msg() . "\n";
                    
                    // Check each field in the notification
                    foreach ($notification as $field => $value) {
                        $fieldJson = json_encode($value);
                        if ($fieldJson === false) {
                            echo "  - Field '$field' has encoding issues\n";
                            
                            // If it's a string, try to fix encoding
                            if (is_string($value)) {
                                echo "    Original value: " . bin2hex(substr($value, 0, 20)) . "...\n";
                                $fixedValue = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                                $fixedJson = json_encode($fixedValue);
                                if ($fixedJson !== false) {
                                    echo "    Fixed with mb_convert_encoding!\n";
                                    // Update the value in the notifications array
                                    $notifications[$i][$field] = $fixedValue;
                                }
                            }
                        }
                    }
                }
            }
            
            // Try encoding again with fixed data
            $response['notifications'] = $notifications;
            $json = json_encode($response);
            if ($json === false) {
                echo "\nERROR: Still failed after trying to fix encoding issues: " . json_last_error_msg() . "\n";
                exit;
            } else {
                echo "\nFixed JSON encoding issues!\n";
            }
        }
        
        // Output the JSON response that would be sent
        echo "\nJSON RESPONSE:\n" . $json . "\n";
        
    } catch (Exception $e) {
        echo "\nERROR: " . $e->getMessage() . "\n";
    }
}

// Create test notification button
echo "\n\n<form method='post'>";
echo "<input type='submit' name='create_test' value='Create Test Notification'>";
echo "</form>";

// Handle create test notification
if (isset($_POST['create_test'])) {
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $conn = getDbConnection();
        
        try {
            $type = 'test';
            $refId = 1;
            $content = "Test notification created at " . date('Y-m-d H:i:s');
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, type, reference_id, content, is_read, created_at) 
                VALUES (:user_id, :type, :reference_id, :content, 0, NOW())
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':reference_id', $refId);
            $stmt->bindParam(':content', $content);
            $result = $stmt->execute();
            
            if ($result) {
                echo "\nTest notification created successfully!\n";
            } else {
                echo "\nFailed to create test notification.\n";
            }
        } catch (Exception $e) {
            echo "\nError: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\nYou must be logged in to create test notifications.\n";
    }
}

// JavaScript to help debug client-side issues
echo "\n\n<script>
// Test the notifications AJAX request
function testNotificationsRequest() {
    console.log('Testing notifications request...');
    fetch('?page=notifications', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        response.text().then(text => {
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON response:', data);
                document.getElementById('response-display').textContent = JSON.stringify(data, null, 2);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                console.log('Raw response text:', text);
                document.getElementById('response-display').textContent = text;
            }
        });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('response-display').textContent = 'Error: ' + error.message;
    });
    return false;
}
</script>

<div style='margin-top: 20px;'>
    <button onclick='return testNotificationsRequest();'>Test Notifications AJAX</button>
    <pre id='response-display' style='margin-top: 10px; padding: 10px; background: #f5f5f5; border: 1px solid #ddd;'></pre>
</div>

<div style='margin-top: 20px;'>
    <h3>Debugging Steps:</h3>
    <ol>
        <li>Click the 'Test Notifications AJAX' button above</li>
        <li>Check the response in the box that appears</li>
        <li>Look at your browser console (F12) for any errors</li>
        <li>If you see a JSON parsing error, go to <a href='?simulate=notifications'>Simulate Notifications Endpoint</a> to diagnose</li>
    </ol>
    
    <h3>Fix Instructions:</h3>
    <ol>
        <li>Open browser console with F12</li>
        <li>Go to the Network tab</li>
        <li>Click the notifications bell icon</li>
        <li>Look for the request to '?page=notifications'</li>
        <li>Check the response - if it's not valid JSON, that's the issue</li>
    </ol>
</div>
"; 