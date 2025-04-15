<?php
/**
 * Update Event API Endpoint
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

// Get event ID from request
$eventId = (int) sanitizeInput($_POST['event_id'] ?? 0);

if ($eventId <= 0) {
    jsonResponse(['error' => 'Invalid event ID'], 400);
}

// Get current user ID
$userId = getCurrentUserId();

// Get and sanitize input data
$eventData = [
    'name' => sanitizeInput($_POST['name'] ?? ''),
    'description' => sanitizeInput($_POST['description'] ?? ''),
    'event_date' => sanitizeInput($_POST['event_date'] ?? ''),
    'event_time' => sanitizeInput($_POST['event_time'] ?? ''),
    'location' => sanitizeInput($_POST['location'] ?? ''),
    'status' => sanitizeInput($_POST['status'] ?? '')
];

// Remove empty fields
$eventData = array_filter($eventData, function($value) {
    return $value !== '';
});

// Check if there's anything to update
if (empty($eventData)) {
    jsonResponse(['error' => 'No data provided for update'], 400);
}

// Update event
$eventManager = new Event();

// Verify event exists and belongs to the user
$existingEvent = $eventManager->getEventById($eventId, $userId);
if (!$existingEvent) {
    jsonResponse(['error' => 'Event not found or access denied'], 404);
}

// Perform update
$success = $eventManager->updateEvent($eventId, $eventData, $userId);

if ($success) {
    // Get updated event data
    $updatedEvent = $eventManager->getEventById($eventId, $userId);
    
    jsonResponse([
        'success' => true,
        'message' => 'Event updated successfully',
        'event' => $updatedEvent
    ]);
} else {
    jsonResponse([
        'error' => 'Failed to update event',
        'message' => 'An error occurred while updating the event'
    ], 500);
}
?>