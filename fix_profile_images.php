<?php
/**
 * Fix Profile Images
 * 
 * This script repairs issues with profile images by:
 * 1. Finding uploaded profile images in the uploads/profile directory
 * 2. Parsing the filenames to extract user IDs
 * 3. Updating the database to associate images with the correct users
 */

// Include configuration
require_once 'config/config.php';

// Security check
if (php_sapi_name() === 'cli') {
    die("This script should only be run in a web browser for security.\n");
}

// Get database connection
$conn = getDbConnection();
$updates = [];
$errors = [];
$messages = [];

// Check if we're in "apply" mode
$applyChanges = isset($_GET['apply']) && $_GET['apply'] === 'true';

// Function to extract user ID from a profile image filename
function extractUserIdFromFilename($filename) {
    // Format: profile_USER_ID_TIMESTAMP.extension
    if (preg_match('/^profile_(\d+)_\d+\.[a-zA-Z]+$/', $filename, $matches)) {
        return intval($matches[1]);
    }
    return false;
}

// Get profile directory
$profileDir = __DIR__ . '/uploads/profile';
$profileDir = str_replace('\\', '/', $profileDir);

// Check if directory exists
if (!file_exists($profileDir)) {
    $errors[] = "Profile directory does not exist at {$profileDir}";
} else {
    // List all files in the directory
    $files = scandir($profileDir);
    $profileImages = [];
    
    // Filter for profile image files and extract user IDs
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $userId = extractUserIdFromFilename($file);
        if ($userId !== false) {
            // If there are multiple images for a user, get the most recent one
            // (which should have the highest timestamp in filename)
            if (!isset($profileImages[$userId]) || 
                strcmp($file, $profileImages[$userId]['filename']) > 0) {
                $profileImages[$userId] = [
                    'filename' => $file,
                    'path' => 'uploads/profile/' . $file,
                    'fullpath' => $profileDir . '/' . $file
                ];
            }
        }
    }
    
    $messages[] = "Found " . count($profileImages) . " profile images";
    
    // Compare with database entries
    foreach ($profileImages as $userId => $imageInfo) {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, name, profile_image FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $errors[] = "User ID {$userId} not found in database but has profile image {$imageInfo['filename']}";
            continue;
        }
        
        // Check if profile image needs to be updated
        if (empty($user['profile_image']) || $user['profile_image'] !== $imageInfo['path']) {
            $updates[] = [
                'user_id' => $userId,
                'user_name' => $user['name'],
                'current_image' => $user['profile_image'] ?: '[None]',
                'new_image' => $imageInfo['path']
            ];
            
            // Apply the change if in apply mode
            if ($applyChanges) {
                try {
                    $updateStmt = $conn->prepare("UPDATE users SET profile_image = :profile_image WHERE id = :id");
                    $updateStmt->bindParam(':profile_image', $imageInfo['path']);
                    $updateStmt->bindParam(':id', $userId);
                    $updateResult = $updateStmt->execute();
                    
                    if ($updateResult) {
                        $messages[] = "Updated profile image for user {$user['name']} (ID: {$userId})";
                    } else {
                        $errors[] = "Failed to update profile image for user {$user['name']} (ID: {$userId})";
                    }
                } catch (Exception $e) {
                    $errors[] = "Error updating user {$userId}: " . $e->getMessage();
                }
            }
        }
    }
}

// Direct fix for a specific user
if (isset($_GET['fix_user']) && is_numeric($_GET['fix_user'])) {
    $userId = intval($_GET['fix_user']);
    $imageFile = isset($_GET['image']) ? $_GET['image'] : null;
    
    if ($imageFile) {
        $imagePath = 'uploads/profile/' . $imageFile;
        $fullPath = $profileDir . '/' . $imageFile;
        
        if (file_exists($fullPath)) {
            try {
                $updateStmt = $conn->prepare("UPDATE users SET profile_image = :profile_image WHERE id = :id");
                $updateStmt->bindParam(':profile_image', $imagePath);
                $updateStmt->bindParam(':id', $userId);
                $updateResult = $updateStmt->execute();
                
                if ($updateResult) {
                    $messages[] = "Manually updated profile image for user ID {$userId} to {$imagePath}";
                } else {
                    $errors[] = "Failed to manually update profile image for user ID {$userId}";
                }
            } catch (Exception $e) {
                $errors[] = "Error manually updating user {$userId}: " . $e->getMessage();
            }
        } else {
            $errors[] = "Image file {$fullPath} does not exist";
        }
    } else {
        $errors[] = "No image file specified";
    }
}

// Manually assign from existing files for a specific user
if (isset($_POST['assign_user_id']) && isset($_POST['assign_file'])) {
    $userId = intval($_POST['assign_user_id']);
    $fileName = $_POST['assign_file'];
    $filePath = 'uploads/profile/' . $fileName;
    
    // Verify the file exists
    if (file_exists($profileDir . '/' . $fileName)) {
        try {
            $stmt = $conn->prepare("UPDATE users SET profile_image = :profile_image WHERE id = :id");
            $stmt->bindParam(':profile_image', $filePath);
            $stmt->bindParam(':id', $userId);
            $result = $stmt->execute();
            
            if ($result) {
                $messages[] = "Successfully assigned {$fileName} to user ID {$userId}";
            } else {
                $errors[] = "Database update failed when assigning image to user ID {$userId}";
            }
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    } else {
        $errors[] = "File {$fileName} does not exist in the uploads/profile directory";
    }
}

// Get all users for dropdown
$allUsers = $conn->query("SELECT id, name, email, profile_image FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Profile Images</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Fix Profile Images</h1>
        
        <div class="mb-4">
            <a href="check_profile_images.php" class="btn btn-secondary">Back to Profile Check</a>
            <a href="test_profile_upload.php" class="btn btn-primary">Test Upload</a>
        </div>
        
        <?php if (!empty($messages)): ?>
            <div class="alert alert-success">
                <h4 class="alert-heading">Success!</h4>
                <ul class="mb-0">
                    <?php foreach ($messages as $message): ?>
                        <li><?= htmlspecialchars($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h4 class="alert-heading">Errors</h4>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Detected Issues</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($updates)): ?>
                            <p class="text-success">No issues detected! All profile images are correctly associated with users.</p>
                        <?php else: ?>
                            <p class="text-warning">Found <?= count($updates) ?> profile images that need to be updated in the database.</p>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>User ID</th>
                                            <th>Name</th>
                                            <th>Current Image</th>
                                            <th>New Image</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($updates as $update): ?>
                                            <tr>
                                                <td><?= $update['user_id'] ?></td>
                                                <td><?= htmlspecialchars($update['user_name']) ?></td>
                                                <td><?= htmlspecialchars($update['current_image']) ?></td>
                                                <td><?= htmlspecialchars($update['new_image']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if (!$applyChanges): ?>
                                <a href="?apply=true" class="btn btn-primary">Apply Changes</a>
                            <?php else: ?>
                                <p class="text-success">Changes have been applied!</p>
                                <a href="?" class="btn btn-secondary">Refresh</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Manual Assignment</h5>
                    </div>
                    <div class="card-body">
                        <p>Assign an existing profile image to a specific user:</p>
                        <form method="post" action="" class="row g-3">
                            <div class="col-md-6">
                                <label for="assign_user_id" class="form-label">User</label>
                                <select class="form-select" id="assign_user_id" name="assign_user_id" required>
                                    <option value="">Select User</option>
                                    <?php foreach ($allUsers as $user): ?>
                                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (ID: <?= $user['id'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="assign_file" class="form-label">Profile Image</label>
                                <select class="form-select" id="assign_file" name="assign_file" required>
                                    <option value="">Select Image</option>
                                    <?php 
                                    if (file_exists($profileDir)) {
                                        $files = scandir($profileDir);
                                        foreach ($files as $file) {
                                            if ($file !== '.' && $file !== '..') {
                                                echo '<option value="' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Assign Image to User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">User Profile Image Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Profile Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allUsers as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= htmlspecialchars($user['name']) ?></td>
                                            <td>
                                                <?php if (!empty($user['profile_image'])): ?>
                                                    <span class="text-success">✓</span>
                                                    <small><?= htmlspecialchars($user['profile_image']) ?></small>
                                                <?php else: ?>
                                                    <span class="text-danger">✗</span>
                                                    <small>No image set</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                // Check if there's an auto-detected image for this user
                                                if (isset($profileImages[$user['id']])) {
                                                    $detectedImage = $profileImages[$user['id']]['filename'];
                                                    echo '<a href="?fix_user=' . $user['id'] . '&image=' . urlencode($detectedImage) . '" class="btn btn-sm btn-primary">Auto-Fix</a>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 