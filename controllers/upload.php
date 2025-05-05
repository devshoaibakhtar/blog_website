<?php
/**
 * Upload Controller
 * Handles photo uploads
 */

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('danger', 'You must be logged in to upload photos.');
    redirect('login');
}

// Get current user
$userId = getCurrentUserId();
$conn = getDbConnection();

// Create uploads directory if it doesn't exist
$uploadsDir = 'uploads/photos';
if (!file_exists($uploadsDir)) {
    if (!mkdir($uploadsDir, 0777, true)) {
        error_log("Failed to create uploads directory: $uploadsDir");
    } else {
        chmod($uploadsDir, 0777); // Ensure directory is writable
    }
}

// Log directory status
error_log("Upload directory exists: " . (file_exists($uploadsDir) ? 'Yes' : 'No'));
error_log("Upload directory is writable: " . (is_writable($uploadsDir) ? 'Yes' : 'No'));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug information
    error_log("Upload form submitted");
    
    if (isset($_FILES['photo'])) {
        error_log("File upload details: " . json_encode($_FILES['photo']));
    } else {
        error_log("No file uploaded in the request");
    }
    
    // Check if file was uploaded without errors
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        error_log("Photo upload detected");
        
        $uploadedFile = $_FILES['photo'];
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        $errors = [];
        
        // Validate file type
        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions);
        }
        
        // Validate file size
        if ($uploadedFile['size'] > $maxFileSize) {
            $errors[] = 'File size exceeds maximum allowed size (5MB).';
        }
        
        // Validate title
        if (empty($title)) {
            $errors[] = 'Title is required.';
        }
        
        if (empty($errors)) {
            try {
                // Begin transaction
                $conn->beginTransaction();
                
                // Generate unique filename
                $filename = 'photo_' . $userId . '_' . time() . '.' . $fileExtension;
                $relativePath = $uploadsDir . '/' . $filename;
                
                // Try different path approaches
                // Approach 1: Use __DIR__ (current directory)
                $absolutePath1 = __DIR__ . '/../' . $relativePath;
                $absolutePath1 = str_replace('\\', '/', $absolutePath1);
                
                // Approach 2: Use document root
                $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
                $siteDir = parse_url(SITE_URL, PHP_URL_PATH) ?: '';
                $absolutePath2 = $docRoot . $siteDir . '/' . $relativePath;
                $absolutePath2 = str_replace('\\', '/', $absolutePath2);
                $absolutePath2 = str_replace('//', '/', $absolutePath2);
                
                // Log paths for debugging
                error_log("Relative Path: " . $relativePath);
                error_log("Absolute Path 1 (using __DIR__): " . $absolutePath1);
                error_log("Absolute Path 2 (using document root): " . $absolutePath2);
                
                // Choose the directory that exists or can be created
                $absolutePath = $absolutePath1; // Try approach 1 first
                
                // Ensure the upload directory exists
                $dirPath = dirname($absolutePath);
                if (!file_exists($dirPath)) {
                    error_log("Creating directory: " . $dirPath);
                    if (!mkdir($dirPath, 0777, true)) {
                        error_log("Failed to create directory: " . $dirPath);
                        // Try approach 2
                        $dirPath = dirname($absolutePath2);
                        $absolutePath = $absolutePath2;
                        if (!file_exists($dirPath)) {
                            if (!mkdir($dirPath, 0777, true)) {
                                throw new Exception("Failed to create directory: " . $dirPath);
                            }
                        }
                    }
                    // Set proper permissions
                    chmod($dirPath, 0777);
                }
                
                // Debug temporary file
                error_log("Temporary file: " . $uploadedFile['tmp_name']);
                error_log("Temporary file exists: " . (file_exists($uploadedFile['tmp_name']) ? 'Yes' : 'No'));
                
                // Move the uploaded file
                $moveSuccess = false;
                if (move_uploaded_file($uploadedFile['tmp_name'], $absolutePath)) {
                    error_log("File moved successfully to: " . $absolutePath);
                    $moveSuccess = true;
                } else {
                    // Try alternative approach if first one fails
                    error_log("First approach failed, trying alternative path");
                    
                    if ($absolutePath === $absolutePath1) {
                        // Try approach 2
                        $absolutePath = $absolutePath2;
                    } else {
                        // Try approach 1
                        $absolutePath = $absolutePath1;
                    }
                    
                    if (move_uploaded_file($uploadedFile['tmp_name'], $absolutePath)) {
                        error_log("File moved successfully to alternative path: " . $absolutePath);
                        $moveSuccess = true;
                    } else {
                        $phpError = error_get_last();
                        error_log("Failed to move uploaded file. PHP Error: " . ($phpError ? json_encode($phpError) : 'Unknown error'));
                    }
                }
                
                if ($moveSuccess) {
                    // Set appropriate permissions for the uploaded file
                    chmod($absolutePath, 0644);
                    
                    // Insert into database
                    $stmt = $conn->prepare("
                        INSERT INTO photos (user_id, title, description, photo_path, created_at) 
                        VALUES (:user_id, :title, :description, :photo_path, NOW())
                    ");
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':title', $title);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':photo_path', $relativePath);
                    
                    // Execute and check result
                    $dbInsertSuccess = $stmt->execute();
                    
                    if ($dbInsertSuccess) {
                        error_log("Successfully inserted record into database with ID: " . $conn->lastInsertId());
                        
                        // Commit transaction
                        $conn->commit();
                        
                        setFlashMessage('success', 'Photo uploaded successfully!');
                        redirect('upload');
                    } else {
                        error_log("Failed to insert record into database. PDO Error: " . json_encode($stmt->errorInfo()));
                        throw new Exception("Database error: " . $stmt->errorInfo()[2]);
                    }
                } else {
                    throw new Exception("Failed to move uploaded file. Please check server permissions.");
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                
                error_log("Exception caught: " . $e->getMessage());
                setFlashMessage('danger', 'Error uploading photo: ' . $e->getMessage());
            }
        } else {
            error_log("Validation errors: " . implode(', ', $errors));
            setFlashMessage('danger', implode('<br>', $errors));
        }
    } else {
        $errorCode = $_FILES['photo']['error'] ?? 'No file uploaded';
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $errorMessage = is_numeric($errorCode) && isset($errorMessages[$errorCode]) 
            ? $errorMessages[$errorCode] 
            : "Unknown upload error code: $errorCode";
            
        error_log("File upload error: $errorMessage");
        setFlashMessage('danger', 'File upload error: ' . $errorMessage);
    }
}

// Get user's uploaded photos
$stmt = $conn->prepare("
    SELECT * FROM photos 
    WHERE user_id = :user_id 
    ORDER BY created_at DESC
");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load upload view
require_once 'views/upload.php'; 