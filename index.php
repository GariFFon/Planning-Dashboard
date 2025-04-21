<?php
// Start session
session_start();

// Include database connection
require_once 'includes/db_connect.php';

// Initialize variables
$error = '';
$success = '';

// Check if account has been deleted
if (isset($_GET['account_deleted']) && $_GET['account_deleted'] == 1) {
    $success = "Your account has been successfully deleted.";
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        // Check user credentials
        $sql = "SELECT id, username, password, first_name, last_name FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_regenerate_id();
                
                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                // Check if there's a redirect URL stored in session
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect_url = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: $redirect_url");
                } else {
                    // Default redirect to dashboard
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
        
        $stmt->close();
    }
}

// Set page title
$page_title = "Login - Event Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Load the modern CSS -->
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
        }

        .login-image {
            flex: 1;
            background-image: url('https://images.unsplash.com/photo-1682686580391-615b1f28e330?q=80&w=2670&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2rem;
            color: var(--text-on-dark);
        }

        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, rgba(23, 18, 59, 0.7), rgba(23, 18, 59, 0.4));
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
            font-size: 2rem;
            font-weight: 300;
            line-height: 1.4;
            max-width: 80%;
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
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background-color: #17123B;
            color: white;
        }

        .form-box {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 5;
        }

        .form-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: white;
        }

        .form-header p {
            color: var(--text-muted);
            margin: 0;
        }

        .form-header p a {
            color: var(--primary-light);
            text-decoration: none;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: white;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            z-index: 10;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(106, 61, 232, 0.2);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .form-check-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .form-check-input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary-purple);
            position: relative;
            z-index: 10;
        }

        .forgot-link {
            color: var(--primary-light);
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, var(--primary-purple), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 6px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 10;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-purple));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(106, 61, 232, 0.3);
        }

        .social-login {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 10;
        }

        .social-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .social-btn img {
            width: 20px;
            height: 20px;
        }

        .terms-text {
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 2rem;
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
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
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

        /* Media queries for responsive design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-image {
                display: none;
            }
            
            .form-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image animate-fade-in" style="z-index: 1; animation-delay: 0.1s;">
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
        
        <div class="form-container animate-fade-in" style="position: relative; z-index: 2; animation-delay: 0.3s;">
            <div class="moving-bg"></div>
            
            <div class="form-box">
                <div class="form-header">
                    <h1>Welcome Back</h1>
                    <p>Don't have an account? <a href="register.php">Register</a></p>
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
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" id="remember" name="remember" class="form-check-input">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <span class="material-icons-round">login</span>
                        Log in
                    </button>
                    
                    <!-- <div class="social-login">
                        <button type="button" class="social-btn">
                            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google">
                        </button>
                        <button type="button" class="social-btn">
                            <img src="https://www.svgrepo.com/show/452115/apple.svg" alt="Apple">
                        </button>
                    </div> -->
                    
                    <p class="terms-text">
                        By logging in, you agree to our <a href="#" style="color: var(--primary-light);">Terms & Conditions</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Set up animated background
            const movingBg = document.querySelector('.moving-bg');
            if (movingBg) {
                movingBg.style.backgroundImage = "url('https://i.pinimg.com/originals/9a/64/55/9a6455ffeee524d0a4c27f4112f7df3a.gif')";
                
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

            // Add focus to username field to help users start typing immediately
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>