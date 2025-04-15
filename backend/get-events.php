<?php
/**
 * Get Events API Endpoint
 * Event Planning Dashboard
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/event.php';
require_once '../includes/utils.php';

// Check if user is authenticated
requireAuth();

// Determine which events to fetch based on parameters
$eventManager = new Event();
$userId = getCurrentUserId();
$type = sanitizeInput($_GET['type'] ?? 'all');
$eventId = sanitizeInput($_GET['event_id'] ?? null);

// Handle single event retrieval
if ($eventId !== null) {
    $event = $eventManager->getEventById($eventId, $userId);
    
    if ($event) {
        jsonResponse(['success' => true, 'event' => $event]);
    } else {
        jsonResponse(['error' => 'Event not found'], 404);
    }
}

// Handle fetching multiple events
switch ($type) {
    case 'upcoming':
        $limit = (int) sanitizeInput($_GET['limit'] ?? 5);
        $events = $eventManager->getUpcomingEvents($userId, $limit);
        break;
        
    case 'date-range':
        $startDate = sanitizeInput($_GET['start_date'] ?? date('Y-m-d'));
        $endDate = sanitizeInput($_GET['end_date'] ?? date('Y-m-d', strtotime('+30 days')));
        $events = $eventManager->getEventsByDateRange($userId, $startDate, $endDate);
        break;
        
    case 'stats':
        $stats = $eventManager->getEventStatistics($userId);
        jsonResponse(['success' => true, 'stats' => $stats]);
        break;
        
    case 'all':
    default:
        $events = $eventManager->getAllEvents($userId);
        break;
}

// Return events with formatted dates
if (isset($events)) {
    $formattedEvents = array_map(function($event) {
        // Format dates for display if needed
        $event['formatted_date'] = formatDate($event['event_date'], 'M d, Y');
        return $event;
    }, $events);
    
    jsonResponse([
        'success' => true, 
        'events' => $formattedEvents,
        'count' => count($formattedEvents)
    ]);
}
?>