<?php
/**
 * Notifications Controller
 * Handles fetching, updating, and managing user notifications
 */

// Check if user is logged in
if (!isLoggedIn()) {
    // Return JSON response for AJAX requests
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    setFlashMessage('danger', 'You must be logged in to access notifications.');
    redirect('login');
}

// Get current user
$userId = getCurrentUserId();
$conn = getDbConnection();

// Get action parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle different actions
switch ($action) {
    case 'list':
        // Get notifications - most recent first
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        
        $stmt = $conn->prepare("
            SELECT * FROM notifications 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count unread notifications
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE user_id = :user_id AND is_read = 0
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $unreadCount = $stmt->fetchColumn();
        
        // Return JSON for AJAX requests
        if (isAjaxRequest()) {
            // Add debug logging
            error_log("Notifications AJAX response - Count: " . count($notifications) . " Unread: " . $unreadCount);
            
            // Set proper headers to prevent caching issues
            header('Content-Type: application/json');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Ensure proper JSON encoding with error handling
            $response = [
                'notifications' => $notifications,
                'unreadCount' => $unreadCount,
                'success' => true,
                'timestamp' => time()
            ];
            
            // Ensure notifications array has proper encoding
            foreach ($response['notifications'] as &$notification) {
                // Fix any encoding issues for string fields
                foreach ($notification as $key => $value) {
                    if (is_string($value)) {
                        $notification[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }
                
                // Ensure is_read is properly typed as an integer
                if (isset($notification['is_read'])) {
                    $notification['is_read'] = (int)$notification['is_read'];
                }
                
                // Ensure reference_id is properly typed as an integer
                if (isset($notification['reference_id'])) {
                    $notification['reference_id'] = (int)$notification['reference_id'];
                }
            }
            
            // Encode as JSON with error handling
            $json = json_encode($response);
            if ($json === false) {
                // Log the error
                error_log("JSON encoding error in notifications: " . json_last_error_msg());
                
                // Fallback to a simpler response
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to encode notifications data: ' . json_last_error_msg(),
                    'unreadCount' => $unreadCount
                ]);
            } else {
                echo $json;
            }
            exit;
        }
        
        // Include notifications view for non-AJAX requests
        require_once 'views/notifications.php';
        break;
        
    case 'mark-read':
        // Mark notification as read
        $notificationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($notificationId > 0) {
            // Verify notification belongs to user
            $stmt = $conn->prepare("
                SELECT id FROM notifications 
                WHERE id = :id AND user_id = :user_id
            ");
            $stmt->bindParam(':id', $notificationId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $stmt = $conn->prepare("
                    UPDATE notifications 
                    SET is_read = 1 
                    WHERE id = :id
                ");
                $stmt->bindParam(':id', $notificationId);
                $stmt->execute();
                
                if (isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
                
                setFlashMessage('success', 'Notification marked as read.');
            } else {
                if (isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Notification not found']);
                    exit;
                }
                
                setFlashMessage('danger', 'Notification not found.');
            }
        } else {
            if (isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Invalid notification ID']);
                exit;
            }
            
            setFlashMessage('danger', 'Invalid notification ID.');
        }
        
        // Redirect to notifications page for non-AJAX requests
        redirect('notifications');
        break;
        
    case 'mark-all-read':
        // Mark all notifications as read
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        
        setFlashMessage('success', 'All notifications marked as read.');
        redirect('notifications');
        break;
        
    default:
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid action']);
            exit;
        }
        
        setFlashMessage('danger', 'Invalid action.');
        redirect('notifications');
}

/**
 * Check if the current request is an AJAX request
 * @return boolean
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
} 