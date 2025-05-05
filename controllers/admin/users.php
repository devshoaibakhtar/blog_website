<?php
/**
 * Admin Users Controller
 * Handles user management functionality for administrators
 */

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You do not have permission to access this page'
    ];
    echo '<script>window.location.href = "' . SITE_URL . '/?page=login";</script>';
    exit;
}

// Get action from URL parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle different actions
switch ($action) {
    case 'add':
        // Handle form submission for adding a new user
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $username = trim($_POST['username'] ?? '');
            $display_name = trim($_POST['display_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $bio = trim($_POST['bio'] ?? '');
            
            $errors = [];
            
            if (empty($username)) {
                $errors[] = 'Username is required.';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors[] = 'Username can only contain letters, numbers, and underscores.';
            }
            
            if (empty($display_name)) {
                $errors[] = 'Display name is required.';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters long.';
            }
            
            // Check if username or email is already in use
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount() > 0) {
                $errors[] = 'Username or email is already in use.';
            }
            
            // Add user if no errors
            if (empty($errors)) {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, name, display_name, email, password, role, bio, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
                ");
                $result = $stmt->execute([$username, $display_name, $display_name, $email, $hashed_password, $role, $bio]);
                
                if ($result) {
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => 'User added successfully'
                    ];
                    echo '<script>window.location.href = "' . SITE_URL . '/?page=admin-users";</script>';
                    exit;
                } else {
                    $_SESSION['flash_message'] = [
                        'type' => 'error',
                        'message' => 'Failed to add user. Please try again'
                    ];
                }
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'message' => implode('<br>', $errors)
                ];
            }
        }
        
        // If we got here, we have errors or it's a GET request
        // Just redirect back to the users list
        if (!empty($errors)) {
            // If there are errors, we'll show the form again with the error message
            require_once 'views/admin/users.php';
        } else {
            echo '<script>window.location.href = "' . SITE_URL . '/?page=admin-users";</script>';
        }
        exit;
        break;
        
    case 'view':
        // Get user details
        $stmt = $pdo->prepare("
            SELECT u.*, 
                (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count,
                (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count
            FROM users u
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'User not found'
            ];
            echo '<script>window.location.href = "' . SITE_URL . '/?page=admin-users";</script>';
            exit;
        }
        
        // Get user's recent posts
        $stmt = $pdo->prepare("
            SELECT p.*, t.name as taxonomy_name
            FROM posts p
            LEFT JOIN taxonomies t ON p.taxonomy_id = t.id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $recentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get user's recent comments
        $stmt = $pdo->prepare("
            SELECT c.*, p.title as post_title
            FROM comments c
            JOIN posts p ON c.post_id = p.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $recentComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Load view user view
        require_once 'views/admin/users/view.php';
        break;
        
    case 'edit':
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $status = isset($_POST['status']) ? 'active' : 'inactive';
            $role = $_POST['role'] ?? 'user';
            
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required.';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }
            
            // Check if email is already in use by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->rowCount() > 0) {
                $errors[] = 'Email is already in use by another account.';
            }
            
            // Update user if no errors
            if (empty($errors)) {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, status = ?, role = ? WHERE id = ?");
                $result = $stmt->execute([$name, $email, $status, $role, $userId]);
                
                if ($result) {
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => 'User updated successfully'
                    ];
                    echo '<script>window.location.href = "' . SITE_URL . '/?page=admin-users";</script>';
                    exit;
                } else {
                    $_SESSION['flash_message'] = [
                        'type' => 'error',
                        'message' => 'Failed to update user. Please try again'
                    ];
                }
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'message' => implode('<br>', $errors)
                ];
            }
        }
        
        // Get user details
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'User not found'
            ];
            echo '<script>window.location.href = "' . SITE_URL . '/?page=admin-users";</script>';
            exit;
        }
        
        // Load edit user view
        require_once 'views/admin/users/edit.php';
        break;
        
    case 'delete':
        // Prevent deleting own account
        if ($userId == $_SESSION['user']['id']) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'You cannot delete your own account'
            ];
            echo '<script>window.location.href = "' . SITE_URL . '/?page=admin-users";</script>';
            exit;
        }
        
        // Check if confirmation is received
        if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
            // Delete user's comments
            $stmt = $pdo->prepare("DELETE FROM comments WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Delete user's posts
            $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Delete user
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$userId]);
            
            if ($result) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'User deleted successfully'
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'error',
                    'message' => 'Failed to delete user. Please try again'
                ];
            }
            
            echo '<script>window.location.href = "' . SITE_URL . '/?page=admin-users";</script>';
            exit;
        }
        
        // Get user details for confirmation
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'User not found'
            ];
            echo '<script>window.location.href = "' . SITE_URL . '/?page=admin-users";</script>';
            exit;
        }
        
        // Load delete confirmation view
        require_once 'views/admin/users/delete.php';
        break;
        
    default: // list
        // Handle search and filters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
        $roleFilter = isset($_GET['role']) ? $_GET['role'] : 'all';
        
        // Build query
        $query = "SELECT u.*, 
                (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count 
                FROM users u 
                WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($statusFilter !== 'all') {
            $query .= " AND u.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($roleFilter !== 'all') {
            $query .= " AND u.role = ?";
            $params[] = $roleFilter;
        }
        
        // Add ordering
        $query .= " ORDER BY u.created_at DESC";
        
        // Pagination
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countStmt = $pdo->prepare(str_replace("u.*, \n                (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count ", "COUNT(*) as total ", $query));
        $countStmt->execute($params);
        $totalUsers = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalUsers / $perPage);
        
        // Add limit and offset
        $query .= " LIMIT $perPage OFFSET $offset";
        
        // Execute query
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Set current page for pagination
        $currentPage = $page;
        
        // Load users list view
        require_once 'views/admin/users.php';
        break;
} 