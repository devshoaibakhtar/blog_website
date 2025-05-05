<?php
/**
 * Profile Controller
 * Handles user profile viewing and editing
 */

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('danger', 'You must be logged in to access your profile.');
    redirect('login');
}

// Get database connection
$conn = getDbConnection();

// Get user data
$userId = getCurrentUserId();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user activity stats
$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM posts WHERE user_id = :user_id) as post_count,
        (SELECT COUNT(*) FROM comments WHERE user_id = :user_id) as comment_count,
        (SELECT COUNT(*) FROM post_likes WHERE user_id = :user_id) as like_count,
        (SELECT COUNT(*) FROM photos WHERE user_id = :user_id) as photo_count
";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bindParam(':user_id', $userId);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get user's photos
try {
    $photosStmt = $conn->prepare("
        SELECT * FROM photos 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC 
        LIMIT 6
    ");
    $photosStmt->bindParam(':user_id', $userId);
    $photosStmt->execute();
    $userPhotos = $photosStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched " . count($userPhotos) . " photos for user ID: " . $userId);
} catch (PDOException $e) {
    error_log("Error fetching user photos: " . $e->getMessage());
    $userPhotos = []; // Initialize to empty array if there's an error
}

// Create uploads directory if it doesn't exist
$uploadsDir = 'uploads/profile';
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'view';
$message = '';
$messageType = '';

switch ($action) {
    case 'update_profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = array('success' => false, 'message' => '');
            
            try {
                // Validate form data
                if (empty($_POST['name'])) {
                    throw new Exception('Name is required');
                }
                
                if (empty($_POST['email'])) {
                    throw new Exception('Email is required');
                }
                
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Please enter a valid email address');
                }
                
                // Check if email already exists (for another user)
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$_POST['email'], $userId]);
                if ($stmt->rowCount() > 0) {
                    throw new Exception('This email is already in use by another account');
                }
                
                // Initialize update data array
                $updateData = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email']
                ];
                
                // Handle profile image upload
                $profile_image = null;
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
                    // Validate file
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    $maxSize = 2 * 1024 * 1024; // 2MB
                    
                    if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                        throw new Exception('Invalid file type. Allowed types: JPG, PNG, GIF');
                    }
                    
                    if ($_FILES['profile_image']['size'] > $maxSize) {
                        throw new Exception('File size exceeded. Maximum size: 2MB');
                    }
                    
                    // Create uploads directory if it doesn't exist
                    $uploadDir = 'uploads/profile/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $filename = 'user_' . $userId . '_' . time() . '_' . basename($_FILES['profile_image']['name']);
                    $targetPath = $uploadDir . $filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                        $profile_image = $targetPath;
                        $updateData['profile_image'] = $profile_image;
                        
                        // Log successful upload
                        error_log("Profile image uploaded successfully: {$targetPath}");
                    } else {
                        throw new Exception('Failed to upload profile image');
                    }
                }
                
                // Build the SQL query dynamically based on what fields to update
                $sql = "UPDATE users SET ";
                $updateFields = [];
                $params = [];
                
                foreach ($updateData as $field => $value) {
                    $updateFields[] = "{$field} = ?";
                    $params[] = $value;
                }
                
                $sql .= implode(", ", $updateFields);
                $sql .= " WHERE id = ?";
                $params[] = $userId;
                
                // Log the query and parameters for debugging
                error_log("Update SQL: {$sql}");
                error_log("Update params: " . print_r($params, true));
                
                // Execute the update
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute($params);
                
                if (!$result) {
                    // Log the error
                    error_log("Database error: " . print_r($stmt->errorInfo(), true));
                    throw new Exception('Failed to update profile: Database error');
                }
                
                // Verify the update was successful
                if ($stmt->rowCount() === 0) {
                    // Log a warning - no rows were updated
                    error_log("Warning: No rows updated for user {$userId}");
                    
                    // Check if user exists
                    $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
                    $checkStmt->execute([$userId]);
                    if ($checkStmt->rowCount() === 0) {
                        throw new Exception('User not found');
                    }
                }
                
                // If we got here, the update was successful
                // Update session data
                $_SESSION['user']['name'] = $_POST['name'];
                $_SESSION['user']['email'] = $_POST['email'];
                
                if (isset($updateData['profile_image'])) {
                    $_SESSION['user']['profile_image'] = $updateData['profile_image'];
                }
                
                // Create a notification for profile updates
                $notificationType = 'profile_update';
                $notificationContent = 'You have successfully updated your profile';
                
                if (isset($updateData['profile_image'])) {
                    $notificationContent = 'You have successfully updated your profile picture';
                }
                
                // Insert notification
                $notifyStmt = $conn->prepare("
                    INSERT INTO notifications (user_id, type, reference_id, content, is_read, created_at) 
                    VALUES (:user_id, :type, :reference_id, :content, 0, NOW())
                ");
                $notifyStmt->bindParam(':user_id', $userId);
                $notifyStmt->bindParam(':type', $notificationType);
                $notifyStmt->bindParam(':reference_id', $userId); // Reference to the user themselves
                $notifyStmt->bindParam(':content', $notificationContent);
                $notifyStmt->execute();
                
                $response['success'] = true;
                $response['message'] = 'Profile updated successfully';
                
                // Include updated profile data in response
                $response['name'] = $_POST['name'];
                $response['email'] = $_POST['email'];
                if (isset($updateData['profile_image'])) {
                    $response['profile_image'] = $updateData['profile_image'];
                }
            } catch (Exception $e) {
                $response['message'] = $e->getMessage();
                error_log("Profile update error: " . $e->getMessage());
            }
            
            // Clean output buffer before sending JSON
            if (ob_get_length()) {
                ob_clean();
            }
            
            // Set headers
            header('Content-Type: application/json');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            
            // Log the response being sent
            error_log("Sending response: " . json_encode($response));
            
            // Send JSON response for AJAX requests
            echo json_encode($response);
            exit;
        }
        break;
        
    case 'change_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $errors = [];
            
            if (empty($currentPassword)) {
                $errors[] = 'Current password is required.';
            }
            
            if (empty($newPassword)) {
                $errors[] = 'New password is required.';
            } elseif (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters long.';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation do not match.';
            }
            
            if (empty($errors)) {
                // Verify current password
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
                $storedHash = $stmt->fetchColumn();
                
                if (password_verify($currentPassword, $storedHash)) {
                    // Update to new password
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                    $stmt->bindParam(':password', $newHash);
                    $stmt->bindParam(':id', $userId);
                    $result = $stmt->execute();
                    
                    if ($result) {
                        setFlashMessage('success', 'Password changed successfully.');
                    } else {
                        setFlashMessage('danger', 'Failed to change password. Please try again.');
                    }
                } else {
                    setFlashMessage('danger', 'Current password is incorrect.');
                }
            } else {
                setFlashMessage('danger', implode('<br>', $errors));
            }
            
            redirect('profile');
        }
        break;
        
    case 'view':
    default:
        // Just display the profile
        break;
}

// Load profile view
require_once 'views/profile.php'; 