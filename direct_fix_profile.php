<?php
/**
 * Direct Profile Image Fix Tool
 * 
 * This tool directly updates the profile_image field in the database
 * for a specific user, bypassing any potential issues with the regular
 * profile update process.
 */

// Include configuration
require_once 'config/config.php';

// Security check
if (php_sapi_name() === 'cli') {
    die("This script should only be run in a web browser for security.\n");
}

// Initialize variables
$userId = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : null;
$message = '';
$error = '';
$success = false;
$userInfo = null;
$imageFiles = [];

// Get database connection
$conn = getDbConnection();

// Get profile directory path
$profileDir = __DIR__ . '/uploads/profile';
$profileDir = str_replace('\\', '/', $profileDir);

// Get list of available profile images
if (file_exists($profileDir)) {
    $files = scandir($profileDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($profileDir . '/' . $file)) {
            $imageFiles[] = $file;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile_image' && $userId > 0) {
        try {
            $imagePath = $_POST['profile_image'];
            
            // Validate path
            if (empty($imagePath)) {
                $error = "Please select a profile image path.";
            } else {
                // Update the database directly
                $stmt = $conn->prepare("UPDATE users SET profile_image = :profile_image WHERE id = :id");
                $stmt->bindParam(':profile_image', $imagePath);
                $stmt->bindParam(':id', $userId);
                
                // Execute with error handling
                if ($stmt->execute()) {
                    $message = "Successfully updated profile image for user ID {$userId} to: {$imagePath}";
                    $success = true;
                    
                    // Also update session for current user if applicable
                    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
                        $_SESSION['user_profile_image'] = $imagePath;
                        $message .= "<br>Session data also updated.";
                    }
                } else {
                    $error = "Database update failed: " . implode(', ', $stmt->errorInfo());
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get user information if ID is provided
if ($userId > 0) {
    try {
        $stmt = $conn->prepare("SELECT id, name, email, profile_image FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userInfo) {
            $error = "User ID {$userId} not found.";
            $userId = null;
        }
    } catch (Exception $e) {
        $error = "Error retrieving user information: " . $e->getMessage();
    }
}

// Get list of all users for dropdown
$allUsers = [];
try {
    $stmt = $conn->query("SELECT id, name, email FROM users ORDER BY id");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error retrieving user list: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Profile Image Fix</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Direct Profile Image Fix</h1>
        
        <div class="mb-4">
            <a href="check_profile_images.php" class="btn btn-secondary">Profile Check</a>
            <a href="fix_profile_images.php" class="btn btn-secondary">Auto Fix Tool</a>
            <a href="test_profile_upload.php" class="btn btn-primary">Test Upload</a>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            <?= $message ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Select User</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Select User to Fix</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">-- Select User --</option>
                                    <?php foreach ($allUsers as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= ($userId == $user['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['name']) ?> (ID: <?= $user['id'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Select User</button>
                        </form>
                    </div>
                </div>
                
                <?php if ($userInfo): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Current User Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>User ID:</strong> <?= $userInfo['id'] ?>
                        </div>
                        <div class="mb-3">
                            <strong>Name:</strong> <?= htmlspecialchars($userInfo['name']) ?>
                        </div>
                        <div class="mb-3">
                            <strong>Email:</strong> <?= htmlspecialchars($userInfo['email']) ?>
                        </div>
                        <div class="mb-3">
                            <strong>Current Profile Image:</strong> 
                            <?= !empty($userInfo['profile_image']) ? htmlspecialchars($userInfo['profile_image']) : '<span class="text-danger">Not set</span>' ?>
                        </div>
                        
                        <?php if (!empty($userInfo['profile_image'])): ?>
                        <div class="mb-3">
                            <strong>Image Preview:</strong><br>
                            <img src="<?= SITE_URL . '/' . $userInfo['profile_image'] ?>" 
                                 class="rounded-circle img-thumbnail" 
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 alt="Current profile image"
                                 onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.svg'; this.onerror=null;">
                        </div>
                        
                        <div class="mb-3">
                            <strong>File Exists:</strong>
                            <?php 
                            $filePath = __DIR__ . '/' . $userInfo['profile_image'];
                            $filePath = str_replace('\\', '/', $filePath);
                            $exists = file_exists($filePath);
                            ?>
                            <span class="<?= $exists ? 'text-success' : 'text-danger' ?>">
                                <?= $exists ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($userInfo): ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Fix Profile Image</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update_profile_image">
                            <input type="hidden" name="user_id" value="<?= $userInfo['id'] ?>">
                            
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Profile Image Path</label>
                                <div class="input-group">
                                    <span class="input-group-text">uploads/profile/</span>
                                    <select class="form-select" id="profile_image_select">
                                        <option value="">-- Select Existing File --</option>
                                        <?php foreach ($imageFiles as $file): ?>
                                        <option value="uploads/profile/<?= $file ?>"><?= $file ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-text">Select from existing files or enter a custom path below</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="profile_image_custom" class="form-label">Custom Path</label>
                                <input type="text" class="form-control" id="profile_image_custom" name="profile_image" 
                                       value="<?= htmlspecialchars($userInfo['profile_image'] ?? '') ?>">
                                <div class="form-text">Full path relative to site root (e.g., uploads/profile/profile_1_123456789.jpg)</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Profile Image</button>
                            
                            <?php if (!empty($userInfo['profile_image'])): ?>
                            <a href="<?= '?user_id=' . $userInfo['id'] . '&action=remove' ?>" class="btn btn-danger">
                                Remove Profile Image
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <?php if (!empty($imageFiles)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Available Profile Images</h5>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-2 row-cols-md-3 g-3">
                            <?php foreach ($imageFiles as $index => $file): ?>
                            <?php if ($index < 12): // Limit to 12 images ?>
                            <div class="col">
                                <div class="card h-100">
                                    <img src="<?= SITE_URL . '/uploads/profile/' . $file ?>" 
                                        class="card-img-top" 
                                        alt="<?= $file ?>"
                                        style="height: 100px; object-fit: cover;"
                                        onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.svg'; this.onerror=null;">
                                    <div class="card-body p-2">
                                        <p class="card-text small text-truncate"><?= $file ?></p>
                                        <button class="btn btn-sm btn-primary select-image" 
                                                data-image="uploads/profile/<?= $file ?>">
                                            Select
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($imageFiles) > 12): ?>
                        <div class="mt-3 text-center">
                            <p class="text-muted">Showing 12 of <?= count($imageFiles) ?> images</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle image selection from dropdown
        const selectElement = document.getElementById('profile_image_select');
        const inputElement = document.getElementById('profile_image_custom');
        
        if (selectElement && inputElement) {
            selectElement.addEventListener('change', function() {
                if (this.value) {
                    inputElement.value = this.value;
                }
            });
        }
        
        // Handle selection from the image grid
        const selectButtons = document.querySelectorAll('.select-image');
        selectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const imagePath = this.getAttribute('data-image');
                if (inputElement && imagePath) {
                    inputElement.value = imagePath;
                    // Scroll to the form
                    const formElement = document.querySelector('form[name="action"]');
                    if (formElement) {
                        formElement.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });
    });
    </script>
</body>
</html> 