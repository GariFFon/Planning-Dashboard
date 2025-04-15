<?php
/**
 * Dashboard Statistics API Endpoint
 * Event Planning Dashboard
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/event.php';
require_once '../includes/utils.php';

// Check if user is authenticated
requireAuth();

// Only allow GET requests
if (!isGet()) {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Get current user ID
$userId = getCurrentUserId();

// Initialize event manager
$eventManager = new Event();

// Get event statistics
$stats = $eventManager->getEventStatistics($userId);

// Get upcoming events (limit to 5)
$upcomingEvents = $eventManager->getUpcomingEvents($userId, 5);

// Format upcoming events
$formattedEvents = array_map(function($event) {
    $event['formatted_date'] = formatDate($event['event_date'], 'M d, Y');
    return $event;
}, $upcomingEvents);

// Return dashboard data
jsonResponse([
    'success' => true,
    'stats' => $stats,
    'upcoming_events' => $formattedEvents
]);
?>