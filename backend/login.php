<?php
/**
 * Login API Endpoint
 * Event Planning Dashboard
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/utils.php';

// Only allow POST requests
if (!isPost()) {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Get and sanitize input
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? ''; // Don't sanitize password
$rememberMe = (bool) ($_POST['remember_me'] ?? false);

// Validate input
$validation = validateRequiredFields(
    ['email' => $email, 'password' => $password],
    ['email', 'password']
);

if ($validation !== true) {
    jsonResponse(['error' => 'Validation failed', 'errors' => $validation], 400);
}

// Attempt login
$auth = new Auth();
$user = $auth->login($email, $password);

if ($user) {
    // Set remember me cookie if requested
    if ($rememberMe) {
        $token = generateToken();
        // Store token in database - implementation would be in the Auth class
        // $auth->storeRememberToken($user['id'], $token);
        
        // Set cookie for 30 days
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => $user
    ]);
} else {
    jsonResponse([
        'error' => 'Invalid credentials',
        'message' => 'Email or password is incorrect'
    ], 401);
}
?>