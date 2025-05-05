<?php
/**
 * Login Controller
 */

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        setFlashMessage('danger', 'Please fill in all required fields.');
    } else {
        // Check if user exists
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            // Set remember me cookie if checked
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/'); // 30 days
                
                // Store token in database (in a real app, you'd have a separate table for this)
                // For simplicity, we'll skip this step in this example
            }
            
            setFlashMessage('success', 'Login successful. Welcome back, ' . $user['name'] . '!');
            redirect('dashboard');
        } else {
            setFlashMessage('danger', 'Invalid email or password.');
        }
    }
}

// Include login view
require_once 'views/auth/login.php'; 