<?php
/**
 * Add Event API Endpoint
 * Event Planning Dashboard
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/event.php';
require_once '../includes/utils.php';

// Check if user is authenticated
requireAuth();

// Only allow POST requests
if (!isPost()) {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Get and sanitize input
$eventData = [
    'name' => sanitizeInput($_POST['name'] ?? ''),
    'description' => sanitizeInput($_POST['description'] ?? ''),
    'event_date' => sanitizeInput($_POST['event_date'] ?? ''),
    'event_time' => sanitizeInput($_POST['event_time'] ?? ''),
    'location' => sanitizeInput($_POST['location'] ?? ''),
    'status' => sanitizeInput($_POST['status'] ?? 'pending'),
    'user_id' => getCurrentUserId()
];

// Validate required fields
$requiredFields = ['name', 'event_date', 'event_time', 'location'];
$validation = validateRequiredFields($eventData, $requiredFields);

if ($validation !== true) {
    jsonResponse(['error' => 'Validation failed', 'errors' => $validation], 400);
}

// Add event
$eventManager = new Event();
$eventId = $eventManager->addEvent($eventData);

if ($eventId) {
    jsonResponse([
        'success' => true,
        'message' => 'Event created successfully',
        'event_id' => $eventId
    ]);
} else {
    jsonResponse([
        'error' => 'Failed to create event',
        'message' => 'An error occurred while creating the event'
    ], 500);
}
?>