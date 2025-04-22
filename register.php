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
    } elseif (!preg_match("/^[a-zA-Z]+$/", $first_name)) {
        $error = "First name should only contain alphabetic characters";
    } elseif (!preg_match("/^[a-zA-Z]+$/", $last_name)) {
        $error = "Last name should only contain alphabetic characters";
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
    
    <!-- Add Inter font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    
    <!-- Add Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    <style>
        :root {
            --primary-purple: #6a3de8;
            --primary-light: #8a65f0;
            --primary-dark: #4a2ec0;
            --text-on-dark: #ffffff;
            --text-muted: #8b8b8b;
            --form-bg: #f5f5f5;
            --card-border: rgba(255, 255, 255, 0.12);
            --form-spacing: 1rem; /* New spacing variable for consistency */
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            overflow: hidden;
            display: flex;
            background: #17123B;
        }

        .login-container {
            display: flex;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        .login-image {
            flex: 1.2; /* Slightly increased for better proportion */
            background-image: url('https://images.unsplash.com/photo-1682686580391-615b1f28e330?q=80&w=2670&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2rem;
            color: var(--text-on-dark);
            height: 100%;
        }

        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, rgba(23, 18, 59, 0.8), rgba(23, 18, 59, 0.5));
            z-index: 1;
        }

        .login-image * {
            position: relative;
            z-index: 2;
        }

        .logo {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo span {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-purple));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .slogan {
            margin-bottom: 3rem;
            font-size: 2.25rem;
            font-weight: 300;
            line-height: 1.3;
            max-width: 80%;
            letter-spacing: -0.5px;
        }

        .slogan strong {
            font-weight: 600;
        }

        .dots {
            display: flex;
            gap: 0.5rem;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
        }

        .dot.active {
            background-color: white;
            width: 24px;
            border-radius: 4px;
        }

        .form-container {
            flex: 0.8; /* Reduced to make the form section narrower */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
            background-color: #17123B;
            color: white;
            height: 100%;
            overflow-y: auto;
        }

        .form-box {
            width: 100%;
            max-width: 380px; /* Reduced width for more compactness */
            position: relative;
            z-index: 1;
            padding: 0 0.5rem;
            box-sizing: border-box;
        }

        .form-header {
            margin-bottom: 1.5rem; /* Reduced spacing */
            text-align: center;
        }

        .form-header h1 {
            font-size: 1.75rem; /* Smaller heading */
            margin-bottom: 0.5rem;
            color: white;
        }

        .form-header p {
            color: var(--text-muted);
            margin: 0;
            font-size: 0.875rem;
        }

        .form-header p a {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: var(--form-spacing); /* Using the variable */
        }

        .form-row {
            display: flex;
            gap: 0.75rem; /* Slightly reduced gap */
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.375rem; /* Reduced spacing */
            font-size: 0.8125rem; /* Slightly smaller */
            font-weight: 500;
            color: white;
        }

        .form-control {
            width: 100%;
            padding: 0.625rem 0.875rem; /* Reduced padding */
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
            font-family: inherit;
            font-size: 0.9375rem; /* Slightly smaller */
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(106, 61, 232, 0.2);
        }

        .form-control::placeholder {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: var(--form-spacing);
        }

        .form-check-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            font-size: 0.8125rem;
        }

        .form-check-input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary-purple);
            border-radius: 3px;
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem 0; /* Slightly reduced vertical padding */
            background: linear-gradient(135deg, var(--primary-purple), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 600; /* Made bolder */
            letter-spacing: 0.3px; /* Slight letter spacing for better readability */
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem; /* Reduced spacing */
            box-shadow: 0 4px 10px rgba(106, 61, 232, 0.2); /* Added subtle shadow */
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-purple));
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(106, 61, 232, 0.3);
        }

        .social-login {
            display: flex;
            gap: 0.75rem; /* Reduced gap */
            margin-top: 0.75rem; /* Reduced spacing */
        }

        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 0; /* Reduced padding */
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .social-btn img {
            width: 18px; /* Slightly smaller */
            height: 18px;
        }

        .terms-text {
            text-align: center;
            font-size: 0.6875rem; /* Smaller */
            color: var(--text-muted);
            margin-top: 1.5rem; /* Reduced spacing */
            line-height: 1.4;
        }

        .animate-fade-in {
            opacity: 0;
            animation: fadeIn 0.8s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-message {
            background-color: rgba(255, 87, 87, 0.1);
            border: 1px solid rgba(255, 87, 87, 0.2);
            color: #ff5757;
            border-radius: 6px;
            padding: 0.625rem; /* Reduced padding */
            margin-bottom: 1rem; /* Reduced spacing */
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
        }

        .success-message {
            background-color: rgba(39, 174, 96, 0.1);
            border: 1px solid rgba(39, 174, 96, 0.2);
            color: #27ae60;
            border-radius: 6px;
            padding: 0.625rem; /* Reduced padding */
            margin-bottom: 1rem; /* Reduced spacing */
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
        }

        /* Animation for background */
        .moving-bg {
            position: absolute;
            right: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            z-index: 0;
            opacity: 0.05;
        }

        /* Form divider with text */
        .form-divider {
            display: flex;
            align-items: center;
            margin: 0.75rem 0;
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        .form-divider::before, 
        .form-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .form-divider span {
            padding: 0 0.75rem;
        }

        /* Media queries for responsive design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                height: 100vh;
                overflow-y: auto;
            }
            
            .login-image {
                display: none;
            }
            
            .form-container {
                padding: 1.25rem;
                height: auto;
                min-height: 100vh;
            }

            .form-box {
                padding: 0;
                max-width: 100%;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        /* Additional media query for smaller screens */
        @media (max-width: 480px) {
            .form-container {
                padding: 1rem 0.75rem;
            }
            
            .form-header h1 {
                font-size: 1.5rem;
            }
            
            .slogan {
                font-size: 1.75rem;
            }

            .form-group {
                margin-bottom: 0.75rem;
            }
        }
        
        /* For larger screens with shorter height */
        @media (min-height: 700px) and (min-width: 992px) {
            .form-container, .login-image {
                padding-top: 2rem;
                padding-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image animate-fade-in" style="animation-delay: 0.1s;">
            <a href="home.php" class="logo">
                <span class="material-icons-round">event</span>
                <span>Event Dashboard</span>
            </a>
            
            <div class="slogan">
                <strong>Capturing Moments,</strong><br> Creating Memories
            </div>
            
            <div class="dots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
        
        <div class="form-container animate-fade-in" style="animation-delay: 0.3s;">
            <div class="moving-bg"></div>
            
            <div class="form-box">
                <div class="form-header">
                    <h1>Create an account</h1>
                    <p>Already have an account? <a href="index.php">Log in</a></p>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <span class="material-icons-round" style="font-size: 18px;">error_outline</span>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message">
                        <span class="material-icons-round" style="font-size: 18px;">check_circle</span>
                        <?php echo $success; ?>
                        <p class="mt-2">
                            <a href="index.php" style="color: #27ae60; text-decoration: underline;">Go to Login</a>
                        </p>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <input id="first_name" name="first_name" type="text" class="form-control" placeholder="Enter first name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input id="last_name" name="last_name" type="text" class="form-control" placeholder="Enter last name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email" class="form-control" placeholder="Enter your email" required>
                        </div>

                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input id="username" name="username" type="text" class="form-control" placeholder="Choose a username" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" name="password" type="password" class="form-control" placeholder="Create a password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm</label>
                                <input id="confirm_password" name="confirm_password" type="password" class="form-control" placeholder="Confirm password" required>
                            </div>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" required class="form-check-input">
                                <span>I agree to the Terms & Conditions</span>
                            </label>
                        </div>

                        <button type="submit" class="submit-btn">
                            <span class="material-icons-round">person_add</span>
                            Create account
                        </button>
                        
                        <div class="form-divider">
                            <span>or continue with</span>
                        </div>
                        
                        <!-- <div class="social-login">
                            <button type="button" class="social-btn">
                                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google">
                            </button>
                            <button type="button" class="social-btn">
                                <img src="https://www.svgrepo.com/show/452115/apple.svg" alt="Apple">
                            </button>
                        </div> -->
                        
                        <p class="terms-text">
                            By signing up, you agree to our <a href="#" style="color: var(--primary-light);">Terms & Conditions</a> and <a href="#" style="color: var(--primary-light);">Privacy Policy</a>
                        </p>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Set up animated background
            const movingBg = document.querySelector('.moving-bg');
            if (movingBg) {
                movingBg.style.backgroundImage = "url('https://cdn.dribbble.com/users/64533/screenshots/15988309/dribble-planner.gif')";
                
                // Set random initial position
                let xPos = Math.random() * 100;
                let yPos = Math.random() * 100;
                
                // Slow movement effect
                function animateBackground() {
                    xPos += 0.01;
                    yPos += 0.01;
                    
                    if (xPos > 100) xPos = 0;
                    if (yPos > 100) yPos = 0;
                    
                    movingBg.style.backgroundPosition = `${xPos}% ${yPos}%`;
                    requestAnimationFrame(animateBackground);
                }
                
                animateBackground();
            }
            
            // Dots animation for slogan slides
            const dots = document.querySelectorAll('.dot');
            let currentDot = 0;
            
            if (dots.length > 0) {
                setInterval(() => {
                    dots[currentDot].classList.remove('active');
                    currentDot = (currentDot + 1) % dots.length;
                    dots[currentDot].classList.add('active');
                }, 3000);
            }
        });
    </script>
</body>
</html>