<?php
/**
 * Get User Profile API Endpoint
 * Event Planning Dashboard
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/utils.php';

// Check if user is authenticated
requireAuth();

// Only allow GET requests
if (!isGet()) {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Get current user
$auth = new Auth();
$currentUser = $auth->getCurrentUser();

if (!$currentUser) {
    jsonResponse(['error' => 'User not found'], 404);
}

// Return user data
jsonResponse([
    'success' => true,
    'user' => $currentUser
]);
?>