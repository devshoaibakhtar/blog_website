<?php
/**
 * Google Authentication Callback Controller
 */

// Check if authorization code is received
if (!isset($_GET['code'])) {
    setFlashMessage('danger', 'Google authentication failed. Please try again.');
    redirect('login');
}

$code = $_GET['code'];

// Exchange authorization code for access token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$tokenData = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

// Initialize cURL session
$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenResponse = curl_exec($ch);
$tokenError = curl_error($ch);
curl_close($ch);

if ($tokenError) {
    error_log('Google OAuth token error: ' . $tokenError);
    setFlashMessage('danger', 'Error connecting to Google. Please try again later.');
    redirect('login');
}

$tokenData = json_decode($tokenResponse, true);

if (!isset($tokenData['access_token'])) {
    $errorMsg = $tokenData['error'] ?? 'Unknown error';
    $errorDesc = $tokenData['error_description'] ?? 'No description available';
    error_log("Google OAuth error: {$errorMsg} - {$errorDesc}");
    setFlashMessage('danger', 'Google authentication failed: ' . $errorDesc);
    redirect('login');
}

// Get user info with the access token
$accessToken = $tokenData['access_token'];
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

$ch = curl_init($userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
$userInfoResponse = curl_exec($ch);
$userInfoError = curl_error($ch);
curl_close($ch);

if ($userInfoError) {
    error_log('Google OAuth user info error: ' . $userInfoError);
    setFlashMessage('danger', 'Error retrieving your information from Google. Please try again later.');
    redirect('login');
}

$userData = json_decode($userInfoResponse, true);

if (!isset($userData['id'])) {
    error_log('Invalid user data from Google: ' . print_r($userData, true));
    setFlashMessage('danger', 'Could not retrieve your Google profile. Please try again later.');
    redirect('login');
}

$googleUserId = 'google_' . $userData['id'];
$googleEmail = $userData['email'] ?? '';
$googleName = $userData['name'] ?? 'Google User';

// Check if user with this Google ID already exists
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE google_id = :google_id");
$stmt->bindParam(':google_id', $googleUserId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // User exists, log them in
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    setFlashMessage('success', 'Login successful. Welcome back, ' . $user['name'] . '!');
    redirect('dashboard');
} else {
    // Check if user with this email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $googleEmail);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Update user with Google ID
        $stmt = $conn->prepare("UPDATE users SET google_id = :google_id WHERE id = :id");
        $stmt->bindParam(':google_id', $googleUserId);
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();
        
        // Log user in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        setFlashMessage('success', 'Your account has been linked with Google. Welcome back, ' . $user['name'] . '!');
        redirect('dashboard');
    } else {
        // Create new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, google_id, role, created_at) VALUES (:name, :email, :google_id, 'user', NOW())");
        $stmt->bindParam(':name', $googleName);
        $stmt->bindParam(':email', $googleEmail);
        $stmt->bindParam(':google_id', $googleUserId);
        
        if ($stmt->execute()) {
            $userId = $conn->lastInsertId();
            
            // Log user in
            $_SESSION['user_id'] = $userId;
            $_SESSION['name'] = $googleName;
            $_SESSION['email'] = $googleEmail;
            $_SESSION['role'] = 'user';
            $_SESSION['last_activity'] = time();
            
            setFlashMessage('success', 'Account created successfully. Welcome, ' . $googleName . '!');
            redirect('dashboard');
        } else {
            error_log('Failed to create user account: ' . print_r($stmt->errorInfo(), true));
            setFlashMessage('danger', 'Failed to create account. Please try again later.');
            redirect('login');
        }
    }
} 