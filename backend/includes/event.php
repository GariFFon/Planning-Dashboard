<?php
/**
 * Event Management Class
 * Event Planning Dashboard
 */

require_once 'database.php';

class Event {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all events for a user
     * 
     * @param int $userId User ID
     * @return array Events
     */
    public function getAllEvents($userId) {
        $sql = "SELECT * FROM events WHERE user_id = :userId ORDER BY event_date DESC";
        return $this->db->fetchAll($sql, ['userId' => $userId]);
    }
    
    /**
     * Get upcoming events for a user
     * 
     * @param int $userId User ID
     * @param int $limit Number of events to return
     * @return array Events
     */
    public function getUpcomingEvents($userId, $limit = 5) {
        $sql = "SELECT * FROM events 
                WHERE user_id = :userId AND event_date >= CURDATE() 
                ORDER BY event_date ASC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            'userId' => $userId,
            'limit' => $limit
        ]);
    }
    
    /**
     * Get event by ID
     * 
     * @param int $eventId Event ID
     * @param int $userId User ID
     * @return array|bool Event data or false
     */
    public function getEventById($eventId, $userId) {
        $sql = "SELECT * FROM events WHERE id = :eventId AND user_id = :userId LIMIT 1";
        return $this->db->fetch($sql, [
            'eventId' => $eventId,
            'userId' => $userId
        ]);
    }
    
    /**
     * Add new event
     * 
     * @param array $eventData Event data
     * @return int|bool New event ID or false
     */
    public function addEvent($eventData) {
        $sql = "INSERT INTO events (name, description, event_date, event_time, location, status, user_id, created_at) 
                VALUES (:name, :description, :event_date, :event_time, :location, :status, :user_id, NOW())";
        
        $this->db->query($sql, [
            'name' => $eventData['name'],
            'description' => $eventData['description'],
            'event_date' => $eventData['event_date'],
            'event_time' => $eventData['event_time'],
            'location' => $eventData['location'],
            'status' => $eventData['status'] ?? 'pending',
            'user_id' => $eventData['user_id']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update existing event
     * 
     * @param int $eventId Event ID
     * @param array $eventData Event data
     * @param int $userId User ID
     * @return bool Success status
     */
    public function updateEvent($eventId, $eventData, $userId) {
        // Build fields to update
        $fields = [];
        $params = [
            'eventId' => $eventId,
            'userId' => $userId
        ];
        
        foreach ($eventData as $key => $value) {
            if ($key !== 'id' && $key !== 'user_id' && $key !== 'created_at') {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE events SET " . implode(', ', $fields) . " WHERE id = :eventId AND user_id = :userId";
        $this->db->query($sql, $params);
        
        return true;
    }
    
    /**
     * Delete event
     * 
     * @param int $eventId Event ID
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteEvent($eventId, $userId) {
        $sql = "DELETE FROM events WHERE id = :eventId AND user_id = :userId";
        $this->db->query($sql, [
            'eventId' => $eventId,
            'userId' => $userId
        ]);
        
        return true;
    }
    
    /**
     * Get events by date range
     * 
     * @param int $userId User ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Events
     */
    public function getEventsByDateRange($userId, $startDate, $endDate) {
        $sql = "SELECT * FROM events 
                WHERE user_id = :userId 
                AND event_date BETWEEN :startDate AND :endDate 
                ORDER BY event_date ASC";
                
        return $this->db->fetchAll($sql, [
            'userId' => $userId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    /**
     * Get event statistics for a user
     * 
     * @param int $userId User ID
     * @return array Statistics
     */
    public function getEventStatistics($userId) {
        $stats = [
            'total' => 0,
            'upcoming' => 0,
            'past' => 0,
            'thisMonth' => 0
        ];
        
        // Total events
        $sql = "SELECT COUNT(*) as count FROM events WHERE user_id = :userId";
        $result = $this->db->fetch($sql, ['userId' => $userId]);
        $stats['total'] = $result['count'];
        
        // Upcoming events
        $sql = "SELECT COUNT(*) as count FROM events WHERE user_id = :userId AND event_date >= CURDATE()";
        $result = $this->db->fetch($sql, ['userId' => $userId]);
        $stats['upcoming'] = $result['count'];
        
        // Past events
        $sql = "SELECT COUNT(*) as count FROM events WHERE user_id = :userId AND event_date < CURDATE()";
        $result = $this->db->fetch($sql, ['userId' => $userId]);
        $stats['past'] = $result['count'];
        
        // This month's events
        $sql = "SELECT COUNT(*) as count FROM events 
                WHERE user_id = :userId 
                AND MONTH(event_date) = MONTH(CURDATE()) 
                AND YEAR(event_date) = YEAR(CURDATE())";
        $result = $this->db->fetch($sql, ['userId' => $userId]);
        $stats['thisMonth'] = $result['count'];
        
        return $stats;
    }
}
?>