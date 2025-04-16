<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Include database connection
require_once '../includes/db_connect.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate task ID and status
$task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Validate inputs
if (empty($task_id) || empty($status)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate status value
$valid_statuses = ['pending', 'in-progress', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

// Get user ID
$user_id = $_SESSION['user_id'];

// First check if the task belongs to the user
$sql = "SELECT id FROM tasks WHERE id = ? AND (assigned_to = ? OR created_by = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $task_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Task not found or you do not have permission']);
    exit;
}

// Update task status
$sql = "UPDATE tasks SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $status, $task_id);
$success = $stmt->execute();

$stmt->close();

// Return response
header('Content-Type: application/json');
if ($success) {
    echo json_encode(['success' => true, 'message' => 'Task status updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update task: ' . $conn->error]);
}
?>