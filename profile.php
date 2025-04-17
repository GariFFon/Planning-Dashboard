<?php
// Start session
session_start();

// Include database connection
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';
$userId = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email, first_name, last_name, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Update Profile
    if (isset($_POST['update_profile'])) {
        $firstName = $conn->real_escape_string($_POST['first_name']);
        $lastName = $conn->real_escape_string($_POST['last_name']);
        $username = $conn->real_escape_string($_POST['username']);
        
        // Check if username is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $userId);
        $stmt->execute();
        $checkResult = $stmt->get_result();
        $stmt->close();
        
        if ($checkResult->num_rows > 0) {
            $error = "Username already taken by another user";
        } else {
            // Update profile details
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ? WHERE id = ?");
            $stmt->bind_param("sssi", $firstName, $lastName, $username, $userId);
            
            if ($stmt->execute()) {
                // Update session variables
                $_SESSION['username'] = $username;
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                
                $success = "Profile updated successfully";
                
                // Refresh user data after update
                $userData['first_name'] = $firstName;
                $userData['last_name'] = $lastName;
                $userData['username'] = $username;
            } else {
                $error = "Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    // Change Password
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (!password_verify($currentPassword, $user['password'])) {
            $error = "Current password is incorrect";
        } elseif ($newPassword != $confirmPassword) {
            $error = "New passwords do not match";
        } elseif (strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters long";
        } else {
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if ($stmt->execute()) {
                $success = "Password changed successfully";
            } else {
                $error = "Error changing password: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $filesize = $_FILES['profile_image']['size'];
        $filetype = $_FILES['profile_image']['type'];
        $tempFile = $_FILES['profile_image']['tmp_name'];
        
        // Get file extension
        $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Validate file extension
        if (!in_array($fileExt, $allowed)) {
            $error = "Invalid file format. Allowed formats: JPG, JPEG, PNG, GIF";
        } 
        // Validate file size (limit to 5MB)
        elseif ($filesize > 5242880) {
            $error = "File size must be less than 5MB";
        } else {
            // Create unique filename
            $newFilename = 'user_' . $userId . '_' . time() . '.' . $fileExt;
            $uploadPath = 'images/profiles/' . $newFilename;
            
            // Create directory if it doesn't exist
            if (!file_exists('images/profiles/')) {
                mkdir('images/profiles/', 0777, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($tempFile, $uploadPath)) {
                // Delete old profile image if exists
                if (!empty($userData['profile_image']) && file_exists($userData['profile_image'])) {
                    unlink($userData['profile_image']);
                }
                
                // Update database with new image path
                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->bind_param("si", $uploadPath, $userId);
                
                if ($stmt->execute()) {
                    $success = "Profile image updated successfully";
                    $userData['profile_image'] = $uploadPath;
                } else {
                    $error = "Error updating profile image: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error uploading file";
            }
        }
    }
    
    // Delete Account
    if (isset($_POST['delete_account'])) {
        $password = $_POST['delete_password'];
        
        // Verify password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (!password_verify($password, $user['password'])) {
            $error = "Password is incorrect";
        } else {
            // Delete user account
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            
            if ($stmt->execute()) {
                // Delete profile image if exists
                if (!empty($userData['profile_image']) && file_exists($userData['profile_image'])) {
                    unlink($userData['profile_image']);
                }
                
                // Destroy session and redirect to login page
                session_destroy();
                header("Location: index.php?account_deleted=1");
                exit();
            } else {
                $error = "Error deleting account: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Set page title
$page_title = "My Profile";

// Include header
include 'includes/modern_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Event Dashboard</title>
    
    <!-- Include Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Include GSAP for animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <!-- Include Particles.js for interactive background -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <style>
        :root {
            --background-gradient: linear-gradient(135deg, #0F172A, #1E293B, #334155);
            --accent-gradient: linear-gradient(135deg, #2563EB, #8B5CF6, #EC4899);
            --glass-bg: rgba(15, 23, 42, 0.6);
            --card-border: 1px solid rgba(255, 255, 255, 0.08);
            --card-bg: rgba(30, 41, 59, 0.3);
            --shadow-color: rgba(0, 0, 0, 0.3);
            --glow-color: rgba(139, 92, 246, 0.15);
            --primary-gradient: linear-gradient(135deg, #4361ee, #3a86ff);
            --danger-gradient: linear-gradient(135deg, #ef476f, #d00000);
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            --transition-bounce: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
        }
        
        body {
            background: var(--background-gradient);
            background-size: 400%;
            animation: AnimateBackground 15s ease infinite;
            color: white;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        @keyframes AnimateBackground {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Enhanced animated background */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
        }
        
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
            opacity: 0.6;
        }
        
        .bg-animation::before,
        .bg-animation::after {
            content: '';
            position: absolute;
            width: 300%;
            height: 300%;
            top: -100%;
            left: -100%;
            z-index: -1;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, rgba(139, 92, 246, 0.05) 25%, rgba(236, 72, 153, 0.05) 50%, rgba(20, 184, 166, 0.05) 75%, rgba(245, 158, 11, 0.1) 100%);
            animation: rotateBackground 60s linear infinite;
        }
        
        .bg-animation::after {
            filter: blur(30px);
            opacity: 0.5;
            animation-duration: 45s;
            animation-direction: reverse;
        }
        
        @keyframes rotateBackground {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Animated gradient orbs */
        .gradient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            opacity: 0.3;
            background-image: radial-gradient(circle, var(--orb-color-center) 0%, var(--orb-color-outer) 70%);
            mix-blend-mode: screen;
            pointer-events: none;
            transform-origin: center;
            z-index: 0;
        }

        .floating-blob {
            position: absolute;
            border-radius: 50%;
            opacity: 0.4;
            filter: blur(120px);
            z-index: 0;
            mix-blend-mode: overlay;
        }
        
        .profile-header {
            background: var(--primary-gradient);
            height: 200px;
            border-radius: 0 0 30px 30px;
            position: relative;
            margin-bottom: 60px;
            z-index: 1;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid white;
            box-shadow: var(--card-shadow);
            background-size: cover;
            background-position: center;
            transition: transform 0.3s ease;
            z-index: 2;
        }
        
        .profile-avatar:hover {
            transform: translateX(-50%) scale(1.05);
        }
        
        .profile-tab {
            padding: 12px 24px;
            font-weight: 500;
            color: #cbd5e1;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }
        
        .profile-tab.active {
            color: #2563EB;
            border-bottom: 2px solid #2563EB;
        }
        
        .profile-tab:hover:not(.active) {
            color: #2563EB;
            border-bottom: 2px solid rgba(37, 99, 235, 0.3);
        }
        
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: var(--card-border);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: var(--transition-bounce);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        
        .form-input {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            transition: all 0.2s ease;
            background: rgba(15, 23, 42, 0.3);
            color: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .form-input:disabled {
            background: rgba(15, 23, 42, 0.5);
            color: rgba(255, 255, 255, 0.6);
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: rgba(30, 41, 59, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .btn-outline:hover {
            border-color: #4361ee;
            background: rgba(67, 97, 238, 0.1);
        }
        
        .btn-danger {
            background: var(--danger-gradient);
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            box-shadow: 0 4px 12px rgba(239, 71, 111, 0.3);
            transform: translateY(-2px);
        }
        
        .section-fade {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }
        
        .fade-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.3);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Animation for alerts */
        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .alert {
            animation: slideInDown 0.3s ease forwards;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(30, 41, 59, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .alert-success {
            border: 1px solid rgba(46, 196, 182, 0.3);
            color: #4ade80;
        }
        
        .alert-danger {
            border: 1px solid rgba(239, 71, 111, 0.3);
            color: #f87171;
        }
        
        /* Floating navbar styles */
        .floating-navbar {
            position: fixed;
            top: 20px;
            left: 0;
            right: 0;
            margin: 0 auto;
            width: 90%;
            max-width: 1280px;
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 10px 30px var(--shadow-color), 
                        0 0 0 1px rgba(255, 255, 255, 0.1);
            z-index: 100;
            transform: translateY(0);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: navbarFadeIn 1s ease forwards;
        }
        
        @keyframes navbarFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .floating-navbar:hover {
            box-shadow: 0 15px 35px var(--shadow-color), 
                        0 0 0 1px rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
        }
        
        .nav-item {
            position: relative;
            overflow: hidden;
            padding: 0.75rem 1.25rem;
            margin: 0 0.25rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.05);
            transform: translateY(-2px);
        }
        
        .nav-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--accent-gradient);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-item:hover::after {
            width: 60%;
        }

        .gradient-text {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Particles.js background -->
    <div id="particles-js"></div>
    
    <!-- Animated background overlay -->
    <div class="bg-animation"></div>
    
    <!-- Animated gradient orbs -->
    <div class="gradient-orb" style="--orb-color-center: rgba(37, 99, 235, 0.3); --orb-color-outer: rgba(37, 99, 235, 0); width: 800px; height: 800px; top: -200px; left: -200px;"></div>
    <div class="gradient-orb" style="--orb-color-center: rgba(139, 92, 246, 0.3); --orb-color-outer: rgba(139, 92, 246, 0); width: 600px; height: 600px; bottom: -100px; right: -100px;"></div>
    <div class="gradient-orb" style="--orb-color-center: rgba(236, 72, 153, 0.3); --orb-color-outer: rgba(236, 72, 153, 0); width: 500px; height: 500px; top: 40%; left: 60%;"></div>
    <div class="gradient-orb" style="--orb-color-center: rgba(20, 184, 166, 0.2); --orb-color-outer: rgba(20, 184, 166, 0); width: 400px; height: 400px; top: 65%; left: 25%;"></div>

    <!-- Floating blobs -->
    <div class="floating-blob bg-blue-600" style="width: 600px; height: 600px; top: -300px; left: -200px;"></div>
    <div class="floating-blob bg-purple-600" style="width: 500px; height: 500px; bottom: -200px; right: -150px;"></div>
    <div class="floating-blob bg-pink-600" style="width: 400px; height: 400px; top: 30%; left: 70%;"></div>
    
    <!-- Floating Navigation -->
    <nav class="floating-navbar py-4 px-6">
        <div class="flex justify-between items-center">
            <!-- Logo & Brand -->
            <div class="flex items-center">
                <div class="text-xl font-bold text-white flex items-center">
                    <span class="gradient-text">EventDash</span>
                </div>
            </div>
            
            <!-- Navigation Items -->
            <div class="flex items-center space-x-1">
                <a href="home.php" class="nav-item text-white/80">Home</a>
                <a href="dashboard.php" class="nav-item text-white/80">Dashboard</a>
                <a href="create_event.php" class="nav-item text-white/80">Create Event</a>
                <a href="create_task.php" class="nav-item text-white/80">Add Task</a>
                <a href="profile.php" class="nav-item text-white/90 font-medium">Profile</a>
                <a href="logout.php" class="nav-item text-white/80">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 pb-12 relative z-10 pt-24">
        <!-- Profile header with avatar -->
        <div class="profile-header mb-8">
            <div class="profile-avatar" style="<?php echo !empty($userData['profile_image']) ? 
            'background-image: url(\'' . $userData['profile_image'] . '\');' : 
            'background: var(--accent-gradient); display: flex; justify-content: center; align-items: center; color: white; font-size: 48px;' ?>">
                <?php if (empty($userData['profile_image'])): ?>
                    <?php echo strtoupper(substr($userData['first_name'], 0, 1) . substr($userData['last_name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            
            <!-- Avatar upload form -->
            <form action="" method="post" enctype="multipart/form-data" class="absolute right-4 bottom-4">
                <label for="profile_image" class="btn btn-outline bg-white cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Change Photo
                </label>
                <input type="file" name="profile_image" id="profile_image" class="hidden" onchange="this.form.submit()">
            </form>
        </div>
        
        <!-- User name and info -->
        <div class="text-center mt-16 mb-8">
            <h1 class="text-2xl font-bold"><?php echo $userData['first_name'] . ' ' . $userData['last_name']; ?></h1>
            <p class="text-gray-300">@<?php echo $userData['username']; ?></p>
            <p class="text-gray-300"><?php echo $userData['email']; ?></p>
        </div>
        
        <!-- Alert messages -->
        <?php if (!empty($error) || !empty($success)): ?>
            <div class="max-w-2xl mx-auto mb-6">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Tabs navigation -->
        <div class="max-w-4xl mx-auto mb-6">
            <div class="flex border-b border-gray-700 mb-6" id="profileTabs">
                <button class="profile-tab active" data-tab="profile">Profile Information</button>
                <button class="profile-tab" data-tab="security">Security Settings</button>
                <button class="profile-tab" data-tab="danger">Account Management</button>
            </div>
            
            <!-- Profile Information Tab -->
            <div class="tab-content section-fade fade-in" id="profileTab">
                <div class="card p-6">
                    <h2 class="text-xl font-semibold mb-4">Edit Profile</h2>
                    
                    <form action="" method="post">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-1">First Name</label>
                                <input type="text" name="first_name" class="form-input" required value="<?php echo $userData['first_name']; ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-1">Last Name</label>
                                <input type="text" name="last_name" class="form-input" required value="<?php echo $userData['last_name']; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-200 mb-1">Username</label>
                            <input type="text" name="username" class="form-input" required value="<?php echo $userData['username']; ?>">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-200 mb-1">Email</label>
                            <input type="email" class="form-input bg-gray-800" disabled value="<?php echo $userData['email']; ?>">
                            <p class="text-xs text-gray-400 mt-1">Email cannot be changed.</p>
                        </div>
                        
                        <div class="text-right">
                            <button class="btn btn-primary" type="submit" name="update_profile">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Security Settings Tab -->
            <div class="tab-content section-fade hidden" id="securityTab">
                <div class="card p-6">
                    <h2 class="text-xl font-semibold mb-4">Change Password</h2>
                    
                    <form action="" method="post">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-200 mb-1">Current Password</label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-1">New Password</label>
                                <input type="password" name="new_password" class="form-input" required>
                                <p class="text-xs text-gray-400 mt-1">Password must be at least 6 characters long.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-input" required>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <button class="btn btn-primary" type="submit" name="change_password">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="card p-6 mt-6">
                    <h2 class="text-xl font-semibold mb-4">Two-Factor Authentication</h2>
                    <p class="text-gray-300 mb-4">Enhance your account security by enabling two-factor authentication.</p>
                    
                    <button class="btn btn-outline" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Enable 2FA (Coming Soon)
                    </button>
                </div>
            </div>
            
            <!-- Account Management Tab -->
            <div class="tab-content section-fade hidden" id="dangerTab">
                <div class="card p-6 border border-red-800">
                    <h2 class="text-xl font-semibold mb-2 text-red-400">Delete Account</h2>
                    <p class="text-gray-300 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
                    
                    <form action="" method="post" id="deleteAccountForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-200 mb-1">Confirm Password</label>
                            <input type="password" name="delete_password" class="form-input" required>
                        </div>
                        
                        <div class="flex items-center mb-6">
                            <input type="checkbox" id="confirm_delete" class="mr-2" required>
                            <label for="confirm_delete" class="text-sm text-gray-300">I understand that this action is irreversible</label>
                        </div>
                        
                        <button class="btn btn-danger" type="button" onclick="confirmDelete()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete My Account
                        </button>
                    </form>
                </div>
                
                <div class="card p-6 mt-6">
                    <h2 class="text-xl font-semibold mb-4">Download Your Data</h2>
                    <p class="text-gray-300 mb-4">Get a copy of your data from our system.</p>
                    
                    <button class="btn btn-outline" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export Data (Coming Soon)
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Particles.js
            particlesJS('particles-js', {
                "particles": {
                    "number": {
                        "value": 80,
                        "density": {
                            "enable": true,
                            "value_area": 800
                        }
                    },
                    "color": {
                        "value": "#ffffff"
                    },
                    "shape": {
                        "type": "circle",
                        "stroke": {
                            "width": 0,
                            "color": "#000000"
                        },
                        "polygon": {
                            "nb_sides": 5
                        }
                    },
                    "opacity": {
                        "value": 0.2,
                        "random": true,
                        "anim": {
                            "enable": true,
                            "speed": 1,
                            "opacity_min": 0.1,
                            "sync": false
                        }
                    },
                    "size": {
                        "value": 3,
                        "random": true,
                        "anim": {
                            "enable": true,
                            "speed": 2,
                            "size_min": 0.3,
                            "sync": false
                        }
                    },
                    "line_linked": {
                        "enable": true,
                        "distance": 150,
                        "color": "#ffffff",
                        "opacity": 0.1,
                        "width": 1
                    },
                    "move": {
                        "enable": true,
                        "speed": 1,
                        "direction": "none",
                        "random": true,
                        "straight": false,
                        "out_mode": "out",
                        "bounce": false,
                        "attract": {
                            "enable": false,
                            "rotateX": 600,
                            "rotateY": 1200
                        }
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": {
                            "enable": false,
                            "mode": "grab"
                        },
                        "onclick": {
                            "enable": false,
                            "mode": "push"
                        },
                        "resize": true
                    },
                    "modes": {
                        "grab": {
                            "distance": 140,
                            "line_linked": {
                                "opacity": 1
                            }
                        },
                        "bubble": {
                            "distance": 400,
                            "size": 40,
                            "duration": 2,
                            "opacity": 8,
                            "speed": 3
                        },
                        "repulse": {
                            "distance": 200,
                            "duration": 0.4
                        },
                        "push": {
                            "particles_nb": 4
                        },
                        "remove": {
                            "particles_nb": 2
                        }
                    }
                },
                "retina_detect": true
            });
            
            // Tab switching functionality
            const tabs = document.querySelectorAll('.profile-tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    tab.classList.add('active');
                    
                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                        content.classList.remove('fade-in');
                    });
                    
                    // Show the selected tab content
                    const tabId = tab.getAttribute('data-tab') + 'Tab';
                    const selectedTab = document.getElementById(tabId);
                    selectedTab.classList.remove('hidden');
                    
                    // Trigger animation
                    setTimeout(() => {
                        selectedTab.classList.add('fade-in');
                    }, 50);
                });
            });
            
            // Animate sections on page load
            const sections = document.querySelectorAll('.section-fade');
            
            sections.forEach((section, index) => {
                setTimeout(() => {
                    section.classList.add('fade-in');
                }, 100 * (index + 1));
            });
        });
        
        // Confirm account deletion
        function confirmDelete() {
            const checkbox = document.getElementById('confirm_delete');
            
            if (!checkbox.checked) {
                alert('Please confirm that you understand this action is irreversible.');
                return;
            }
            
            if (confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
                document.getElementById('deleteAccountForm').submit();
            }
        }
    </script>
</body>
</html>

<?php
// Include footer
include 'includes/modern_footer.php';
?> 