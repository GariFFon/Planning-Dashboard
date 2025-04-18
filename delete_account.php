<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Process account deletion only if the confirmation form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Include database connection
    require_once 'includes/db_connect.php';
    
    $user_id = $_SESSION['user_id'];
    
    // Start transaction to ensure all deletions happen together
    $conn->begin_transaction();
    
    try {
        // Delete user's events
        $stmt = $conn->prepare("DELETE FROM events WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete user's tasks
        $stmt = $conn->prepare("DELETE FROM tasks WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete any other related user data here
        // ...
        
        // Finally, delete the user account
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Clear all session data and destroy the session
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        // Redirect to home page with a message
        session_start();
        $_SESSION['info_message'] = "Your account has been permanently deleted.";
        header('Location: home.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Redirect back to profile page with error
        $_SESSION['error_message'] = "There was an error deleting your account: " . $e->getMessage();
        header('Location: profile.php');
        exit;
    }
    
} else {
    // If accessed directly without form submission, redirect to profile
    header('Location: profile.php');
    exit;
}
?> 