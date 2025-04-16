<?php
// Start session
session_start();

// Include database connection
require_once 'includes/db_connect.php';

// Initialize variables
$error = '';
$success = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name)) {
        $error = "All fields are required";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if username or email already exists
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $sql = "INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $first_name, $last_name);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed: " . $stmt->error;
            }
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Event Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Include Tailwind CSS via CDN for quick development -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Include HeroIcons -->
    <script src="https://unpkg.com/@heroicons/v2/outline/20/esm/index.js"></script>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-purple-800 to-pink-700 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl overflow-hidden p-6 space-y-8 border border-white/20">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-white tracking-tight">
                    Create an Account
                </h2>
                <p class="mt-2 text-white/80">
                    Join our event planning platform
                </p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-500/20 text-white p-3 rounded-lg text-center">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="bg-green-500/20 text-white p-3 rounded-lg text-center">
                    <?php echo $success; ?>
                    <p class="mt-2">
                        <a href="index.php" class="font-medium underline">Go to Login</a>
                    </p>
                </div>
            <?php else: ?>
                <form class="space-y-6" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-white/90">First Name</label>
                            <div class="mt-1">
                                <input id="first_name" name="first_name" type="text" required 
                                      class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-white/25 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-white/90">Last Name</label>
                            <div class="mt-1">
                                <input id="last_name" name="last_name" type="text" required 
                                      class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-white/25 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-white/90">Username</label>
                        <div class="mt-1">
                            <input id="username" name="username" type="text" required 
                                  class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-white/25 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-white/90">Email</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" required 
                                  class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-white/25 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-white/90">Password</label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" required
                                  class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-white/25 focus:border-transparent">
                        </div>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-white/90">Confirm Password</label>
                        <div class="mt-1">
                            <input id="confirm_password" name="confirm_password" type="password" required
                                  class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-white/25 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-white/25 transform transition duration-150 hover:scale-105">
                            Register
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="text-center">
                <p class="text-sm text-white/70">
                    Already have an account? 
                    <a href="index.php" class="font-medium text-indigo-300 hover:text-indigo-200 transition">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="js/app.js"></script>
</body>
</html>