<?php
/**
 * Profile Image Check Tool
 * 
 * This script checks if profile images exist in both the database and filesystem
 * and helps diagnose any issues with profile image uploads.
 */

// Include configuration
require_once 'config/config.php';

// Security check - only allow running in browser
if (php_sapi_name() === 'cli') {
    die("This script should only be run in a web browser for security.\n");
}

// Function to check if a file exists
function fileExists($path) {
    $absolutePath = __DIR__ . '/' . $path;
    $absolutePath = str_replace('\\', '/', $absolutePath);
    return file_exists($absolutePath);
}

// Get database connection
$conn = getDbConnection();

// Get all users
$stmt = $conn->query("SELECT id, name, email, profile_image FROM users ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check directory permissions
$profileDir = __DIR__ . '/uploads/profile';
$profileDir = str_replace('\\', '/', $profileDir);
$directoryExists = file_exists($profileDir);
$directoryWritable = $directoryExists && is_writable($profileDir);

// Count files in directory
$fileCount = 0;
$totalSize = 0;
$profileFiles = [];
if ($directoryExists) {
    $files = scandir($profileDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $fileCount++;
            $filePath = $profileDir . '/' . $file;
            $fileSize = filesize($filePath);
            $totalSize += $fileSize;
            $profileFiles[] = [
                'name' => $file,
                'size' => $fileSize,
                'modified' => date("Y-m-d H:i:s", filemtime($filePath))
            ];
        }
    }
}

// Check for placeholder images
$placeholderSvg = file_exists(__DIR__ . '/assets/images/placeholder.svg');
$placeholderJpg = file_exists(__DIR__ . '/assets/images/placeholder.jpg');

// Process fix action if requested
$fixMessage = '';
if (isset($_GET['fix']) && $_GET['fix'] === 'permissions') {
    if (!$directoryExists) {
        if (mkdir($profileDir, 0777, true)) {
            $fixMessage = "Successfully created profile directory!";
            $directoryExists = true;
            $directoryWritable = true;
        } else {
            $fixMessage = "Failed to create profile directory. Error: " . error_get_last()['message'];
        }
    } elseif (!$directoryWritable) {
        if (chmod($profileDir, 0777)) {
            $fixMessage = "Successfully updated permissions on profile directory!";
            $directoryWritable = true;
        } else {
            $fixMessage = "Failed to update permissions. Error: " . error_get_last()['message'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Image Check Tool</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        .status-ok { color: #198754; }
        .status-warning { color: #ffc107; }
        .status-error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Profile Image Check Tool</h1>
        
        <?php if ($fixMessage): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?= $fixMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">System Check</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Profile Directory Exists
                                <span class="<?= $directoryExists ? 'status-ok' : 'status-error' ?>">
                                    <?= $directoryExists ? '<i class="bi bi-check-circle"></i> Yes' : '<i class="bi bi-x-circle"></i> No' ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Profile Directory Writable
                                <span class="<?= $directoryWritable ? 'status-ok' : 'status-error' ?>">
                                    <?= $directoryWritable ? '<i class="bi bi-check-circle"></i> Yes' : '<i class="bi bi-x-circle"></i> No' ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Profile Files Count
                                <span><?= $fileCount ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Size
                                <span><?= round($totalSize / 1024, 2) ?> KB</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Placeholder SVG
                                <span class="<?= $placeholderSvg ? 'status-ok' : 'status-warning' ?>">
                                    <?= $placeholderSvg ? '<i class="bi bi-check-circle"></i> Exists' : '<i class="bi bi-exclamation-triangle"></i> Missing' ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Placeholder JPG
                                <span class="<?= $placeholderJpg ? 'status-ok' : 'status-warning' ?>">
                                    <?= $placeholderJpg ? '<i class="bi bi-check-circle"></i> Exists' : '<i class="bi bi-exclamation-triangle"></i> Missing' ?>
                                </span>
                            </li>
                        </ul>
                        
                        <?php if (!$directoryExists || !$directoryWritable): ?>
                        <div class="mt-3">
                            <a href="?fix=permissions" class="btn btn-primary">Fix Directory Permissions</a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$placeholderSvg || !$placeholderJpg): ?>
                        <div class="mt-3">
                            <a href="generate_placeholder.php" class="btn btn-primary">Generate Placeholder Images</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (count($profileFiles) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Files in Profile Directory</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Filename</th>
                                        <th>Size</th>
                                        <th>Modified</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($profileFiles as $file): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($file['name']) ?></td>
                                        <td><?= round($file['size'] / 1024, 2) ?> KB</td>
                                        <td><?= $file['modified'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">User Profile Images</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Profile Image</th>
                                        <th>File Exists</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td>
                                            <?php if (!empty($user['profile_image'])): ?>
                                                <small class="text-truncate d-inline-block" style="max-width: 150px;"><?= $user['profile_image'] ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Not set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['profile_image'])): ?>
                                                <?php $fileExists = fileExists($user['profile_image']); ?>
                                                <span class="<?= $fileExists ? 'status-ok' : 'status-error' ?>">
                                                    <?= $fileExists ? '<i class="bi bi-check-circle"></i> Yes' : '<i class="bi bi-x-circle"></i> No' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Test Tools</h5>
                    </div>
                    <div class="card-body">
                        <p>Use these tools to test and fix profile image functionality:</p>
                        <div class="mb-3">
                            <a href="test_profile_upload.php" class="btn btn-primary">Test Profile Upload</a>
                            <a href="?refresh=<?= time() ?>" class="btn btn-secondary">Refresh Data</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 