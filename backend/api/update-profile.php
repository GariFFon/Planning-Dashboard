<?php
/**
 * Update Profile API Endpoint
 * Event Planning Dashboard
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/utils.php';

// Check if user is authenticated
requireAuth();

// Only allow POST requests
if (!isPost()) {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Get current user
$auth = new Auth();
$currentUser = $auth->getCurrentUser();

if (!$currentUser) {
    jsonResponse(['error' => 'User not found'], 404);
}

// Get and sanitize input data
$userData = [
    'name' => sanitizeInput($_POST['name'] ?? ''),
    'email' => sanitizeInput($_POST['email'] ?? ''),
];

// Handle password change if provided
if (!empty($_POST['password']) && !empty($_POST['current_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['password'];
    
    // Verify current password (you'll need to extend the Auth class for this)
    if (!$auth->verifyPassword($currentUser['id'], $currentPassword)) {
        jsonResponse(['error' => 'Current password is incorrect'], 400);
    }
    
    $userData['password'] = $newPassword;
}

// Filter out empty fields
$userData = array_filter($userData, function($value) {
    return $value !== '';
});

// Check if there's anything to update
if (empty($userData)) {
    jsonResponse(['error' => 'No data provided for update'], 400);
}

// Update profile
$success = $auth->updateProfile($userData);

if ($success) {
    // Get updated user data
    $updatedUser = $auth->getCurrentUser();
    
    jsonResponse([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => $updatedUser
    ]);
} else {
    jsonResponse([
        'error' => 'Failed to update profile',
        'message' => 'An error occurred while updating your profile'
    ], 500);
}
?>