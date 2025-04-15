<?php
/**
 * User Authentication Helper
 * Event Planning Dashboard
 */

require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array|bool User data or false
     */
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $user = $this->db->fetch($sql, ['email' => $email]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from user data
            unset($user['password']);
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            return $user;
        }
        
        return false;
    }
    
    /**
     * Register a new user
     * 
     * @param array $userData User data
     * @return int|bool User ID or false
     */
    public function register($userData) {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $exists = $this->db->fetch($sql, ['email' => $userData['email']]);
        
        if ($exists) {
            return false;
        }
        
        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $sql = "INSERT INTO users (name, email, password, role, created_at) 
                VALUES (:name, :email, :password, :role, NOW())";
                
        $params = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => $hashedPassword,
            'role' => $userData['role'] ?? 'user'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Log out current user
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
    }
    
    /**
     * Get current user data
     * 
     * @return array|null User data or null
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $sql = "SELECT id, name, email, role, created_at FROM users WHERE id = :id LIMIT 1";
        return $this->db->fetch($sql, ['id' => $_SESSION['user_id']]);
    }
    
    /**
     * Update user profile
     * 
     * @param array $userData User data to update
     * @return bool Success status
     */
    public function updateProfile($userData) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $fields = [];
        $params = ['id' => $_SESSION['user_id']];
        
        // Build update fields
        foreach ($userData as $key => $value) {
            if ($key !== 'id' && $key !== 'password') {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        // Handle password separately if provided
        if (isset($userData['password']) && !empty($userData['password'])) {
            $fields[] = "password = :password";
            $params['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->query($sql, $params);
        
        return true;
    }
}
?>