<?php
// Include configuration
require_once 'config/config.php';

// Function to upload a file to a specific directory
function uploadProfileImage($file, $userId) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: " . $file['error']);
        return false;
    }
    
    // Get file extension
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    // Validate file type
    if (!in_array($fileExtension, $allowedExtensions)) {
        error_log("Invalid file type: " . $fileExtension);
        return false;
    }
    
    // Validate file size
    if ($file['size'] > $maxFileSize) {
        error_log("File too large: " . $file['size'] . " bytes");
        return false;
    }
    
    // Define target directory and file
    $relativeDir = 'uploads/profile';
    $absoluteDir = __DIR__ . '/' . $relativeDir;
    $absoluteDir = str_replace('\\', '/', $absoluteDir);
    
    // Ensure directory exists
    if (!file_exists($absoluteDir)) {
        if (!mkdir($absoluteDir, 0777, true)) {
            error_log("Failed to create directory: " . $absoluteDir);
            return false;
        }
        chmod($absoluteDir, 0777);
    }
    
    // Generate unique filename
    $filename = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
    $absolutePath = $absoluteDir . '/' . $filename;
    $relativePath = $relativeDir . '/' . $filename;
    
    // Debug output
    error_log("Absolute upload path: " . $absolutePath);
    error_log("Relative path for DB: " . $relativePath);
    error_log("Temp file location: " . $file['tmp_name']);
    
    // Attempt to upload the file
    if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
        // Set file permissions to ensure it's readable
        chmod($absolutePath, 0644);
        return $relativePath;
    } else {
        $phpError = error_get_last();
        error_log("Upload failed. PHP error: " . ($phpError ? json_encode($phpError) : 'None'));
        return false;
    }
}

// Handle file upload
$uploadMessage = '';
$uploadSuccess = false;
$uploadedFilePath = '';
$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    // Call our upload function
    $uploadedFilePath = uploadProfileImage($_FILES['profile_image'], $userId);
    
    if ($uploadedFilePath) {
        $uploadSuccess = true;
        $uploadMessage = "File uploaded successfully! Path: " . $uploadedFilePath;
        
        // Try to update the user's profile image in the database
        if (isset($_POST['update_db']) && $_POST['update_db'] == '1') {
            try {
                $conn = getDbConnection();
                
                // First, let's check the current value
                $checkStmt = $conn->prepare("SELECT profile_image FROM users WHERE id = :id");
                $checkStmt->bindParam(':id', $userId);
                $checkStmt->execute();
                $currentImage = $checkStmt->fetchColumn();
                
                // Debug output about the update we're going to perform
                error_log("Updating user ID $userId profile_image from '$currentImage' to '$uploadedFilePath'");
                
                // Update the user's profile image in database
                $stmt = $conn->prepare("UPDATE users SET profile_image = :profile_image WHERE id = :id");
                $stmt->bindParam(':profile_image', $uploadedFilePath);
                $stmt->bindParam(':id', $userId);
                
                if ($stmt->execute()) {
                    // Verify the update by querying again
                    $verifyStmt = $conn->prepare("SELECT profile_image FROM users WHERE id = :id");
                    $verifyStmt->bindParam(':id', $userId);
                    $verifyStmt->execute();
                    $newImage = $verifyStmt->fetchColumn();
                    
                    if ($newImage === $uploadedFilePath) {
                        $uploadMessage .= "<br>Database updated successfully! Value confirmed: $newImage";
                        
                        // Also update session data if this is the current user
                        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
                            $_SESSION['user_profile_image'] = $uploadedFilePath;
                            $uploadMessage .= "<br>Session data also updated.";
                        }
                    } else {
                        $uploadMessage .= "<br>WARNING: Database update may have failed. Expected '$uploadedFilePath' but got '$newImage'";
                        
                        // Try a different update approach as a fallback
                        $directStmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                        $directStmt->execute([$uploadedFilePath, $userId]);
                        
                        // Check again
                        $finalStmt = $conn->prepare("SELECT profile_image FROM users WHERE id = :id");
                        $finalStmt->bindParam(':id', $userId);
                        $finalStmt->execute();
                        $finalImage = $finalStmt->fetchColumn();
                        
                        if ($finalImage === $uploadedFilePath) {
                            $uploadMessage .= "<br>Second attempt succeeded! Value now: $finalImage";
                        } else {
                            $uploadMessage .= "<br>Both update attempts failed. Please check database permissions.";
                        }
                    }
                } else {
                    $uploadMessage .= "<br>Database update failed. Error: " . implode(', ', $stmt->errorInfo());
                }
            } catch (PDOException $e) {
                $uploadMessage .= "<br>Database error: " . $e->getMessage();
            }
        }
    } else {
        $uploadMessage = "File upload failed! Check error log for details.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Image Upload Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Profile Image Upload Test</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($uploadMessage)): ?>
                            <div class="alert alert-<?= $uploadSuccess ? 'success' : 'danger' ?>">
                                <?= $uploadMessage ?>
                            </div>
                            
                            <?php if ($uploadSuccess && $uploadedFilePath): ?>
                                <div class="mb-4 text-center">
                                    <h4>Uploaded Profile Image:</h4>
                                    <img src="<?= SITE_URL . '/' . $uploadedFilePath ?>" 
                                         class="rounded-circle img-thumbnail" 
                                         style="width: 150px; height: 150px; object-fit: cover;"
                                         alt="Uploaded Profile Image">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">User ID:</label>
                                <input type="number" class="form-control" id="user_id" name="user_id" value="<?= $userId ?>" min="1" required>
                                <div class="form-text">Enter the user ID for whom you want to upload a profile image.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Select a profile image to upload:</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*" required>
                                <div class="form-text">Allowed file types: JPG, JPEG, PNG, GIF. Maximum size: 5MB.</div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="update_db" name="update_db" value="1" checked>
                                <label class="form-check-label" for="update_db">Update user profile in database</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Upload Profile Image</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h3 class="mb-0">Directory Information</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $profileDir = __DIR__ . '/uploads/profile';
                        $profileDir = str_replace('\\', '/', $profileDir);
                        
                        echo "<p><strong>Profile directory path:</strong> " . $profileDir . "</p>";
                        echo "<p><strong>Profile directory exists:</strong> " . (file_exists($profileDir) ? 'Yes' : 'No') . "</p>";
                        
                        if (file_exists($profileDir)) {
                            echo "<p><strong>Profile directory writable:</strong> " . (is_writable($profileDir) ? 'Yes' : 'No') . "</p>";
                            
                            // List files in the directory
                            echo "<h5 class='mt-3'>Files in profile directory:</h5>";
                            echo "<ul>";
                            $files = scandir($profileDir);
                            $hasFiles = false;
                            foreach ($files as $file) {
                                if ($file != '.' && $file != '..') {
                                    echo "<li>" . $file . " (size: " . filesize($profileDir . '/' . $file) . " bytes)</li>";
                                    $hasFiles = true;
                                }
                            }
                            if (!$hasFiles) {
                                echo "<li>No files found</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>Trying to create profile directory...</p>";
                            $result = @mkdir($profileDir, 0777, true);
                            echo "<p>Result: " . ($result ? 'Success' : 'Failed - ' . error_get_last()['message']) . "</p>";
                        }
                        ?>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="mb-0">System Information</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                        <p><strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?></p>
                        <p><strong>Server OS:</strong> <?= PHP_OS ?></p>
                        <p><strong>upload_max_filesize:</strong> <?= ini_get('upload_max_filesize') ?></p>
                        <p><strong>post_max_size:</strong> <?= ini_get('post_max_size') ?></p>
                        <p><strong>max_file_uploads:</strong> <?= ini_get('max_file_uploads') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 