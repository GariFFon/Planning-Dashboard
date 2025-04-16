<?php
// Start session
session_start();

// Include database connection
require_once 'includes/db_connect.php';

// Initialize variables
$error = '';

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
                
                // Redirect to dashboard
                header("Location: dashboard.php");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Dashboard - Your All-in-One Event Management Solution</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Include Tailwind CSS via CDN for quick development -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: {
                            900: '#0F0F12',
                            800: '#1A1A23',
                            700: '#22222D',
                            600: '#2C2C3A',
                        },
                        accent: {
                            blue: '#2563EB',
                            purple: '#8B5CF6',
                            pink: '#EC4899',
                            teal: '#14B8A6',
                            amber: '#F59E0B',
                        }
                    },
                    animation: {
                        'gradient-shift': 'gradient-shift 15s ease infinite',
                    },
                    keyframes: {
                        'gradient-shift': {
                            '0%, 100%': { 'background-position': '0% 50%' },
                            '50%': { 'background-position': '100% 50%' },
                        }
                    }
                }
            }
        }
    </script>
    <!-- Include HeroIcons -->
    <script src="https://unpkg.com/@heroicons/v2/outline/20/esm/index.js"></script>
    <!-- Include GSAP for animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <!-- Include Framer Motion for modern animations -->
    <script src="https://unpkg.com/framer-motion@10.16.4/dist/framer-motion.js"></script>
    <style>
        :root {
            --background-gradient: linear-gradient(135deg, #0F172A, #1E293B, #334155);
            --accent-gradient: linear-gradient(135deg, #2563EB, #8B5CF6, #EC4899);
            --glass-bg: rgba(15, 23, 42, 0.6);
            --card-border: 1px solid rgba(255, 255, 255, 0.08);
            --card-bg: rgba(30, 41, 59, 0.3);
        }
        
        body {
            background: var(--background-gradient);
            background-size: 400%;
            animation: AnimateBackground 15s ease infinite;
        }
        
        @keyframes AnimateBackground {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .floating-blob {
            position: absolute;
            border-radius: 50%;
            opacity: 0.4;
            filter: blur(120px);
            z-index: 0;
            mix-blend-mode: overlay;
        }
        
        .feature-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: var(--card-border);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .feature-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(40, 50, 70, 0.4);
        }
        
        .feature-icon {
            background: var(--accent-gradient);
            background-size: 150% 150%;
            animation: AnimateBackground 5s ease infinite;
        }
        
        .login-container {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            border: var(--card-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-container:hover {
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7);
        }
        
        .glow-effect {
            position: relative;
        }
        
        .glow-effect::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: var(--accent-gradient);
            filter: blur(20px);
            opacity: 0;
            z-index: -1;
            border-radius: inherit;
            transition: opacity 0.3s ease;
        }
        
        .glow-effect:hover::after {
            opacity: 0.5;
        }
        
        .btn-primary {
            background-image: var(--accent-gradient);
            background-size: 150% 150%;
            animation: AnimateBackground 5s ease infinite;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
        }
        
        input:focus {
            border-color: rgba(99, 102, 241, 0.8);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }
        
        .animated-text {
            background-image: var(--accent-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            background-size: 300% 300%;
            animation: AnimateBackground 5s ease infinite;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 overflow-x-hidden">
    <!-- Animated background blobs -->
    <div class="floating-blob bg-blue-600" style="width: 600px; height: 600px; top: -300px; left: -200px;"></div>
    <div class="floating-blob bg-purple-600" style="width: 500px; height: 500px; bottom: -200px; right: -150px;"></div>
    <div class="floating-blob bg-pink-600" style="width: 400px; height: 400px; top: 30%; left: 70%;"></div>
    <div class="floating-blob bg-emerald-600" style="width: 350px; height: 350px; top: 60%; left: 10%;"></div>
    
    <!-- Main content grid -->
    <div class="w-full max-w-7xl grid md:grid-cols-2 gap-12 items-center z-10">
        <!-- Left column: App description -->
        <div class="text-white space-y-8 p-6">
            <div class="fade-in motion-element">
                <h1 class="text-6xl font-extrabold tracking-tight mb-3"><span class="animated-text">Event Dashboard</span></h1>
                <p class="text-xl text-white/80">Your complete solution for planning and managing events and tasks</p>
            </div>
            
            <div class="fade-in motion-element delay-200">
                <div class="h-1 w-24 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full mb-6"></div>
                <p class="text-lg text-white/90">
                    Event Dashboard helps you organize your professional and personal events with powerful tools designed for efficiency and ease of use.
                </p>
            </div>
            
            <!-- Key features -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-12">
                <div class="feature-card motion-element fade-in delay-300 p-6 rounded-xl">
                    <div class="p-3 feature-icon inline-block rounded-lg mb-4">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Event Management</h3>
                    <p class="text-white/70">Create, organize, and track all your events in one place with customizable details.</p>
                </div>
                
                <div class="feature-card motion-element fade-in delay-400 p-6 rounded-xl">
                    <div class="p-3 feature-icon inline-block rounded-lg mb-4">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Task Tracking</h3>
                    <p class="text-white/70">Manage tasks with priorities, deadlines, and simple completion tracking.</p>
                </div>
                
                <div class="feature-card motion-element fade-in delay-500 p-6 rounded-xl">
                    <div class="p-3 feature-icon inline-block rounded-lg mb-4">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Visual Dashboard</h3>
                    <p class="text-white/70">Monitor your progress with an intuitive, user-friendly visual dashboard.</p>
                </div>
                
                <div class="feature-card motion-element fade-in delay-600 p-6 rounded-xl">
                    <div class="p-3 feature-icon inline-block rounded-lg mb-4">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Reminders</h3>
                    <p class="text-white/70">Get notified of upcoming events and pending tasks to stay on schedule.</p>
                </div>
            </div>
        </div>
        
        <!-- Right column: Login form -->
        <div class="w-full max-w-md mx-auto">
            <div class="login-container motion-element rounded-2xl overflow-hidden p-8 space-y-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-white tracking-tight animated-text">
                        Event Dashboard
                    </h2>
                    <p class="mt-2 text-white/80">
                        Sign in to your account
                    </p>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="bg-red-500/20 text-white p-4 rounded-lg text-center motion-element">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form class="space-y-6" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="motion-element">
                        <label for="username" class="block text-sm font-medium text-white/90">Username</label>
                        <div class="mt-1 relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-white/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input id="username" name="username" type="text" required 
                                class="appearance-none bg-dark-700/50 border border-white/10 text-white block w-full pl-10 px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-accent-blue/50 focus:border-transparent">
                        </div>
                    </div>

                    <div class="motion-element">
                        <label for="password" class="block text-sm font-medium text-white/90">Password</label>
                        <div class="mt-1 relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-white/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" required
                                class="appearance-none bg-dark-700/50 border border-white/10 text-white block w-full pl-10 px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-accent-blue/50 focus:border-transparent">
                        </div>
                    </div>

                    <div class="motion-element">
                        <button type="submit" class="btn-primary glow-effect w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-white/25 transform transition-all duration-300 ease-out hover:scale-105">
                            Sign in
                        </button>
                    </div>
                </form>
                
                <div class="text-center motion-element">
                    <p class="text-sm text-white/70">
                        Don't have an account? 
                        <a href="register.php" class="font-medium text-accent-blue hover:text-accent-purple transition-colors duration-300">
                            Register now
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/app.js"></script>
    <script>
        // Use Framer Motion for animations
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize animations without requiring Framer Motion's global object
            
            // Animate the floating blobs
            const blobs = document.querySelectorAll('.floating-blob');
            blobs.forEach((blob, index) => {
                gsap.to(blob, {
                    x: Math.random() * 80 - 40,
                    y: Math.random() * 80 - 40,
                    duration: 8 + index * 2,
                    repeat: -1,
                    yoyo: true,
                    ease: "sine.inOut"
                });
            });
            
            // Animate motion elements with GSAP instead of Framer Motion
            const motionElements = document.querySelectorAll('.motion-element');
            
            // Helper function to create staggered animations
            const createAnimations = (elements) => {
                elements.forEach((element, index) => {
                    gsap.fromTo(element, 
                        { opacity: 0, y: 20 },
                        { 
                            opacity: 1, 
                            y: 0,
                            duration: 0.6,
                            ease: "power2.out",
                            delay: index * 0.15 // Staggered delay
                        }
                    );
                    
                    // Setup intersection observer for triggering animations when visible
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                gsap.to(entry.target, {
                                    opacity: 1, 
                                    y: 0,
                                    duration: 0.6,
                                    ease: "power2.out"
                                });
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.1 });
                    
                    observer.observe(element);
                });
            };
            
            // Apply animations
            createAnimations(motionElements);
            
            // Enhanced hover effect for login container with GSAP
            const loginContainer = document.querySelector('.login-container');
            const handleMouseMove = (e) => {
                const rect = loginContainer.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const moveX = (x - centerX) / 20;
                const moveY = (y - centerY) / 20;
                
                gsap.to(loginContainer, {
                    rotationY: moveX,
                    rotationX: -moveY,
                    duration: 0.3,
                    ease: "power2.out"
                });
            };
            
            loginContainer.addEventListener('mousemove', handleMouseMove);
            
            loginContainer.addEventListener('mouseleave', () => {
                gsap.to(loginContainer, {
                    rotationY: 0,
                    rotationX: 0,
                    duration: 0.5,
                    ease: "power2.out"
                });
            });
            
            // Add hover animations for feature cards
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    gsap.to(card, {
                        scale: 1.05,
                        y: -8,
                        boxShadow: "0 25px 50px rgba(0,0,0,0.3)",
                        duration: 0.3,
                        ease: "power2.out"
                    });
                });
                
                card.addEventListener('mouseleave', () => {
                    gsap.to(card, {
                        scale: 1,
                        y: 0,
                        boxShadow: "0 10px 30px rgba(0,0,0,0.1)",
                        duration: 0.3,
                        ease: "power2.out"
                    });
                });
            });
        });
    </script>
</body>
</html>