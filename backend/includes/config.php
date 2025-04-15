<?php
/**
 * Database Configuration File
 * Event Planning Dashboard
 */

// Database credentials
define('DB_HOST', 'localhost');  // Database host
define('DB_NAME', 'event_planning');  // Database name
define('DB_USER', 'root');  // Database username
define('DB_PASS', '');  // Database password

// Application settings
define('APP_NAME', 'Event Planning Dashboard');
define('APP_URL', 'http://localhost');  // Updated to match current local environment
define('TIMEZONE', 'UTC');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting (set to 0 in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Session settings
ini_set('session.cookie_httponly', 1);
session_start();
?>