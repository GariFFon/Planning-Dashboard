<?php
/**
 * Calendar Events API Endpoint
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

// Get request parameters
$month = (int) sanitizeInput($_GET['month'] ?? date('m'));
$year = (int) sanitizeInput($_GET['year'] ?? date('Y'));

// Validate input
if ($month < 1 || $month > 12) {
    $month = date('m');
}

if ($year < 2000 || $year > 2100) {
    $year = date('Y');
}

// Get user ID
$userId = getCurrentUserId();

// Calculate date range for the requested month
$startDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

// Get events for the month
$eventManager = new Event();
$events = $eventManager->getEventsByDateRange($userId, $startDate, $endDate);

// Format events for calendar view
$calendarEvents = [];

foreach ($events as $event) {
    $eventDate = $event['event_date'];
    $day = date('j', strtotime($eventDate));
    
    if (!isset($calendarEvents[$day])) {
        $calendarEvents[$day] = [];
    }
    
    $calendarEvents[$day][] = [
        'id' => $event['id'],
        'name' => $event['name'],
        'time' => $event['event_time'],
        'location' => $event['location'],
        'status' => $event['status']
    ];
}

// Return calendar data with metadata
jsonResponse([
    'success' => true,
    'month' => $month,
    'year' => $year,
    'events' => $calendarEvents,
    'firstDay' => date('w', strtotime($startDate)),
    'daysInMonth' => date('t', mktime(0, 0, 0, $month, 1, $year))
]);
?>