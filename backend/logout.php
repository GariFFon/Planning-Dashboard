<?php
/**
 * Logout API Endpoint
 * Event Planning Dashboard
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/utils.php';

// Initialize authentication
$auth = new Auth();

// Perform logout
$auth->logout();

// Redirect to login page
header('Location: ../pages/login.html');
exit;
?>