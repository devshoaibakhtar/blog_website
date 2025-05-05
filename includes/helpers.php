<?php
/**
 * Helper functions for the Blog Management System
 */

/**
 * Safely redirect to a page
 */
function redirect($page) {
    header("Location: " . SITE_URL . "/?page=" . $page);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate a slug from a string
 */
function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * Format date in a readable format
 */
function formatDate($date) {
    return date("F j, Y, g:i a", strtotime($date));
}

/**
 * Truncate text to a specific length
 */
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    return $text . '...';
}

/**
 * Flash messages
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Upload file
 */
function uploadFile($file, $directory = 'uploads') {
    // Check if file was actually uploaded and there's no error
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        error_log("File upload error: " . $file['error']);
        return false;
    }
    
    // Normalize directory path for cross-platform compatibility
    $directory = trim($directory, '/\\');
    
    // Create directory path if it doesn't exist
    $upload_path = rtrim(UPLOAD_DIR, '/\\') . '/' . $directory;
    $upload_path = str_replace('\\', '/', $upload_path);
    
    error_log("Upload path: " . $upload_path);
    
    if (!file_exists($upload_path)) {
        error_log("Creating directory: " . $upload_path);
        if (!mkdir($upload_path, 0777, true)) {
            error_log("Failed to create directory: " . $upload_path . ". Error: " . error_get_last()['message']);
            return false;
        }
    }
    
    // Generate unique filename and sanitize it
    $original_filename = basename($file['name']);
    $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-\_]/', '', $original_filename);
    $target = $upload_path . '/' . $filename;
    
    error_log("Target file path: " . $target);
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        error_log("File size too large: " . $file['size'] . " bytes. Max allowed: " . MAX_FILE_SIZE . " bytes");
        return false;
    }
    
    // Check file extension
    $ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        error_log("Invalid file extension: " . $ext . ". Allowed: " . implode(', ', ALLOWED_EXTENSIONS));
        return false;
    }
    
    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Return the relative path from the web root
        $relative_path = $directory . '/' . $filename;
        error_log("File uploaded successfully: " . $relative_path);
        return $relative_path;
    }
    
    error_log("Failed to move uploaded file from " . $file['tmp_name'] . " to " . $target . ". Error: " . error_get_last()['message']);
    return false;
}

/**
 * Send email notification
 */
function sendEmail($to, $subject, $message) {
    // We'll use PHPMailer in production, but for now, let's use mail() function
    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Get tags for use in forms, filtering out default/system tags
 * 
 * @return array Filtered array of tags
 */
function getFilteredTags() {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM tags ORDER BY name ASC");
    $stmt->execute();
    $allTags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // List of default tags to filter out
    $defaultTags = ['html', 'css', 'javascript', 'php', 'mysql', 'web development'];
    
    // Filter out default tags
    $filteredTags = array_filter($allTags, function($tag) use ($defaultTags) {
        return !in_array(strtolower($tag['name']), $defaultTags);
    });
    
    return $filteredTags;
} 