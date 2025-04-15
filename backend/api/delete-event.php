<?php
/**
 * Delete Event API Endpoint
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

// Delete event
$eventManager = new Event();

// Verify event exists and belongs to the user
$existingEvent = $eventManager->getEventById($eventId, $userId);
if (!$existingEvent) {
    jsonResponse(['error' => 'Event not found or access denied'], 404);
}

// Perform deletion
$success = $eventManager->deleteEvent($eventId, $userId);

if ($success) {
    jsonResponse([
        'success' => true,
        'message' => 'Event deleted successfully'
    ]);
} else {
    jsonResponse([
        'error' => 'Failed to delete event',
        'message' => 'An error occurred while deleting the event'
    ], 500);
}
?>