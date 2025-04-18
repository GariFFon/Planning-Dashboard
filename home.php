<?php
// Start session
session_start();

// Include database connection
require_once 'includes/db_connect.php';

// Check if user is already logged in
$is_logged_in = isset($_SESSION['user_id']);

// If already logged in, get user name for personalized greeting
if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT first_name, last_name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $first_name = $user['first_name'];
    $last_name = $user['last_name'];
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
        }
        
        body {
            background: var(--background-gradient);
            background-size: 400%;
            animation: AnimateBackground 15s ease infinite;
            position: relative;
            min-height: 100vh;
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
        
        .feature-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: var(--card-border);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            z-index: 1;
        }
        
        .feature-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(40, 50, 70, 0.4);
        }
        
        /* Card highlight effect */
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.05),
                transparent
            );
            transition: 0.5s;
            z-index: -1;
        }
        
        .feature-card:hover::before {
            left: 100%;
        }
        
        /* Shimmer animation for cards */
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .shimmer-effect {
            position: relative;
            overflow: hidden;
        }
        
        .shimmer-effect::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: translateX(-100%);
            animation: shimmer 3s infinite;
        }
        
        .feature-icon {
            background: var(--accent-gradient);
            background-size: 150% 150%;
            animation: AnimateBackground 5s ease infinite;
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
        
        .animated-text {
            background-image: var(--accent-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            background-size: 300% 300%;
            animation: AnimateBackground 5s ease infinite;
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2), 
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
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3), 
                        0 0 0 1px rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
        }
        
        .nav-item {
            position: relative;
            overflow: hidden;
            padding: 0.75rem 1.25rem;
            margin: 0 0.25rem;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            color: white;
            transform: translateY(-2px);
        }
        
        .nav-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #2563EB, #8B5CF6);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-item:hover::after {
            width: 70%;
        }
        
        .hero-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            border: var(--card-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hero-card:hover {
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.5);
        }
        
        /* Dashboard Preview Styles */
        .dashboard-preview {
            background: linear-gradient(135deg, #1E293B, #0F172A);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .preview-header {
            background: rgba(15, 23, 42, 0.7);
            padding: 12px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .preview-title {
            font-weight: bold;
            color: white;
            font-size: 16px;
        }
        
        .preview-content {
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .preview-card {
            background: rgba(30, 41, 59, 0.5);
            border-radius: 10px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .preview-card-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .preview-event {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding: 10px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
        }
        
        .preview-event-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 12px;
        }
        
        .preview-event-details {
            flex: 1;
        }
        
        .preview-event-title {
            font-weight: 500;
            margin-bottom: 4px;
            font-size: 13px;
            color: white;
        }
        
        .preview-event-date {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .preview-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .preview-stat-card {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        
        .preview-stat-value {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #2563EB, #8B5CF6, #EC4899);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .preview-stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        @media (max-width: 768px) {
            .preview-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="font-sans text-white">
    <!-- Particles.js background -->
    <div id="particles-js"></div>
    
    <!-- Animated background overlay -->
    <div class="bg-animation"></div>
    
    <!-- Animated gradient orbs -->
    <div class="gradient-orb" style="--orb-color-center: rgba(37, 99, 235, 0.3); --orb-color-outer: rgba(37, 99, 235, 0); width: 800px; height: 800px; top: -200px; left: -200px;"></div>
    <div class="gradient-orb" style="--orb-color-center: rgba(139, 92, 246, 0.3); --orb-color-outer: rgba(139, 92, 246, 0); width: 600px; height: 600px; bottom: -100px; right: -100px;"></div>
    <div class="gradient-orb" style="--orb-color-center: rgba(236, 72, 153, 0.3); --orb-color-outer: rgba(236, 72, 153, 0); width: 500px; height: 500px; top: 40%; left: 60%;"></div>
    <div class="gradient-orb" style="--orb-color-center: rgba(20, 184, 166, 0.2); --orb-color-outer: rgba(20, 184, 166, 0); width: 400px; height: 400px; top: 65%; left: 25%;"></div>

    <!-- Original floating blobs -->
    <div class="floating-blob bg-blue-600" style="width: 600px; height: 600px; top: -300px; left: -200px;"></div>
    <div class="floating-blob bg-purple-600" style="width: 500px; height: 500px; bottom: -200px; right: -150px;"></div>
    <div class="floating-blob bg-pink-600" style="width: 400px; height: 400px; top: 30%; left: 70%;"></div>
    <div class="floating-blob bg-emerald-600" style="width: 350px; height: 350px; top: 60%; left: 10%;"></div>
    
    <!-- Navigation -->
    <nav class="floating-navbar py-4 px-6">
        <div class="flex justify-between items-center">
            <!-- Logo & Brand -->
            <div class="flex items-center">
                <div class="text-2xl font-bold text-white flex items-center">
                    <span class="animated-text">EventDash</span>
                </div>
            </div>
            
            <!-- Navigation Items -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="home.php" class="nav-item text-white/90 font-medium">Home</a>
                <a href="#testimonials-section" class="nav-item text-white/80">Testimonials</a>
                <a href="#events-section" class="nav-item text-white/80">Events</a>
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="nav-item text-white/80">Dashboard</a>
                    <a href="create_event.php" class="nav-item text-white/80">Create Event</a>
                    <a href="logout.php" class="nav-item text-white/80">Logout</a>
                <?php else: ?>
                    <a href="index.php" class="nav-item text-white/80">Login</a>
                    <a href="register.php" class="nav-item text-white/80">Register</a>
                <?php endif; ?>
            </div>
            
            <!-- CTA Button -->
            <div>
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="btn-primary text-white font-medium px-5 py-2.5 rounded-lg">My Dashboard</a>
                <?php else: ?>
                    <a href="register.php" class="btn-primary text-white font-medium px-5 py-2.5 rounded-lg">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 px-4 md:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- Hero Text -->
                <div class="space-y-8 text-white">
                    <h1 class="text-5xl md:text-6xl font-bold tracking-tight">
                        <?php if ($is_logged_in): ?>
                            Welcome back, <span class="animated-text"><?php echo $first_name; ?>!</span>
                        <?php else: ?>
                            Make Your <span class="animated-text">Dream Events</span> Reality
                        <?php endif; ?>
                    </h1>
                    
                    <p class="text-xl text-white/80 leading-relaxed">
                        Your complete solution for planning, managing, and executing exceptional events - from intimate gatherings to grand celebrations.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row flex-wrap gap-4">
                        <?php if ($is_logged_in): ?>
                            <a href="dashboard.php" class="btn-primary text-white font-medium px-8 py-3 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                                Go to Dashboard
                            </a>
                            <a href="create_event.php" class="bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white font-medium px-8 py-3 rounded-lg transition-all flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Create New Event
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn-primary text-white font-medium px-8 py-3 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                                </svg>
                                Sign Up Free
                            </a>
                            <a href="index.php" class="bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white font-medium px-8 py-3 rounded-lg transition-all flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Login
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="pt-4">
                        <div class="flex items-center space-x-6">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="text-white/70 ml-2 text-sm">5,000+ Events</span>
                            </div>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-white/70 ml-2 text-sm">Trusted Service</span>
                            </div>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                </svg>
                                <span class="text-white/70 ml-2 text-sm">10k+ Happy Clients</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Image/Card -->
                <div class="hero-card rounded-2xl overflow-hidden p-6 hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-500">
                    <div class="dashboard-preview">
                        <div class="preview-header">
                            <div class="preview-title">Event Dashboard</div>
                        </div>
                        <div class="preview-content">
                            <div class="preview-card">
                                <div class="preview-card-title">Upcoming Events</div>
                                <div class="preview-event">
                                    <div class="preview-event-color" style="background: #2563EB;"></div>
                                    <div class="preview-event-details">
                                        <div class="preview-event-title">Team Meeting</div>
                                        <div class="preview-event-date">Today, 2:00 PM</div>
                                    </div>
                                </div>
                                <div class="preview-event">
                                    <div class="preview-event-color" style="background: #8B5CF6;"></div>
                                    <div class="preview-event-details">
                                        <div class="preview-event-title">Product Launch</div>
                                        <div class="preview-event-date">Tomorrow, 10:00 AM</div>
                                    </div>
                                </div>
                                <div class="preview-event">
                                    <div class="preview-event-color" style="background: #EC4899;"></div>
                                    <div class="preview-event-details">
                                        <div class="preview-event-title">Client Presentation</div>
                                        <div class="preview-event-date">Sep 28, 1:00 PM</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="preview-card">
                                <div class="preview-card-title">Stats Overview</div>
                                <div class="preview-stats">
                                    <div class="preview-stat-card">
                                        <div class="preview-stat-value">12</div>
                                        <div class="preview-stat-label">Total Events</div>
                                    </div>
                                    <div class="preview-stat-card">
                                        <div class="preview-stat-value">5</div>
                                        <div class="preview-stat-label">This Week</div>
                                    </div>
                                    <div class="preview-stat-card">
                                        <div class="preview-stat-value">8</div>
                                        <div class="preview-stat-label">Tasks</div>
                                    </div>
                                    <div class="preview-stat-card">
                                        <div class="preview-stat-value">3</div>
                                        <div class="preview-stat-label">Completed</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="py-20 px-4 md:px-8 relative" id="testimonials-section">
        <!-- Decorative elements -->
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/0 via-slate-900/30 to-slate-900/0 pointer-events-none"></div>
        <div class="absolute left-0 right-0 top-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
        
        <div class="max-w-7xl mx-auto relative">
            <div class="text-center mb-16">
                <span class="bg-white/10 text-white/90 px-4 py-1.5 rounded-full text-sm font-medium inline-block mb-4">Testimonials</span>
                <h2 class="text-4xl font-bold text-white mb-4">What Our Clients Say</h2>
                <p class="text-xl text-white/70 max-w-3xl mx-auto">
                    Don't just take our word for it. Here's what event planners and organizers have to say about EventDash.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-8 border border-white/10 hover:border-white/20 transition-all hover:bg-white/10">
                    <div class="flex items-center mb-6">
                        <div class="text-amber-400 flex">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    
                    <blockquote class="text-white/80 mb-6 relative">
                        <svg class="absolute top-0 left-0 -mt-6 -ml-4 h-10 w-10 text-white/10 transform -rotate-180" fill="currentColor" viewBox="0 0 32 32" aria-hidden="true">
                            <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z" />
                        </svg>
                        <p class="relative z-10 italic">
                            EventDash has transformed how I manage corporate events. The intuitive interface and comprehensive features have saved me countless hours of work and eliminated stress.
                        </p>
                    </blockquote>
                    
                    <div class="flex items-center">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-xl font-bold text-white">SJ</div>
                        <div class="ml-4">
                            <h4 class="text-white font-semibold">Akash K Singh</h4>
                            <p class="text-white/60 text-sm">Corporate Event Manager</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-8 border border-white/10 hover:border-white/20 transition-all hover:bg-white/10">
                    <div class="flex items-center mb-6">
                        <div class="text-amber-400 flex">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    
                    <blockquote class="text-white/80 mb-6 relative">
                        <svg class="absolute top-0 left-0 -mt-6 -ml-4 h-10 w-10 text-white/10 transform -rotate-180" fill="currentColor" viewBox="0 0 32 32" aria-hidden="true">
                            <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z" />
                        </svg>
                        <p class="relative z-10 italic">
                            Planning my wedding was so much easier with EventDash. From guest management to vendor coordination, everything was centralized and perfectly organized.
                        </p>
                    </blockquote>
                    
                    <div class="flex items-center">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-pink-500 to-purple-500 flex items-center justify-center text-xl font-bold text-white">MP</div>
                        <div class="ml-4">
                            <h4 class="text-white font-semibold">Udit Bansal</h4>
                            <p class="text-white/60 text-sm">Wedding Planner</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-8 border border-white/10 hover:border-white/20 transition-all hover:bg-white/10">
                    <div class="flex items-center mb-6">
                        <div class="text-amber-400 flex">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    
                    <blockquote class="text-white/80 mb-6 relative">
                        <svg class="absolute top-0 left-0 -mt-6 -ml-4 h-10 w-10 text-white/10 transform -rotate-180" fill="currentColor" viewBox="0 0 32 32" aria-hidden="true">
                            <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z" />
                        </svg>
                        <p class="relative z-10 italic">
                            As a conference organizer, I needed a system that could handle multiple events with complex schedules. EventDash exceeded my expectations and made our annual tech conference run flawlessly.
                        </p>
                    </blockquote>
                    
                    <div class="flex items-center">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-teal-500 flex items-center justify-center text-xl font-bold text-white">AR</div>
                        <div class="ml-4">
                            <h4 class="text-white font-semibold">Sahil Sharma</h4>
                            <p class="text-white/60 text-sm">Conference Director</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Events Section -->
    <section id="events-section" class="py-20 px-4 md:px-8 bg-gradient-to-b from-transparent to-slate-900/30">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <span class="bg-white/10 text-white/90 px-4 py-1.5 rounded-full text-sm font-medium inline-block mb-4">Event Management</span>
                <h2 class="text-4xl font-bold text-white mb-4">Create Memorable Events</h2>
                <p class="text-xl text-white/70 max-w-3xl mx-auto">
                    <?php if ($is_logged_in): ?>
                        Plan, manage, and execute exceptional events with our powerful tools.
                    <?php else: ?>
                        Sign up today to start creating and managing your dream events.
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if ($is_logged_in): ?>
                <!-- Event options for logged-in users -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <a href="create_event.php" class="feature-card rounded-xl p-8 text-center group hover:scale-105 transition-all">
                        <div class="feature-icon w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3 group-hover:text-blue-400 transition-colors">Create New Event</h3>
                        <p class="text-white/70 mb-6">
                            Start planning your next event with our intuitive event creation tool.
                        </p>
                        <span class="inline-flex items-center text-blue-400 group-hover:text-white">
                            Get Started
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-2 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </a>
                    
                    <a href="dashboard.php" class="feature-card rounded-xl p-8 text-center group hover:scale-105 transition-all">
                        <div class="feature-icon w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3 group-hover:text-purple-400 transition-colors">Manage Your Events</h3>
                        <p class="text-white/70 mb-6">
                            View and manage all your events from your personalized dashboard.
                        </p>
                        <span class="inline-flex items-center text-purple-400 group-hover:text-white">
                            Go to Dashboard
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-2 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </a>
                </div>
                
                <!-- Popular Event Types -->
                <div class="mt-16">
                    <h3 class="text-2xl font-bold text-white mb-8 text-center">Popular Event Types</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white/5 hover:bg-white/10 backdrop-blur-sm rounded-xl p-5 text-center border border-white/10 hover:border-white/20 transition-all">
                            <div class="bg-blue-500/20 p-3 rounded-full inline-flex mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-white">Corporate</h4>
                        </div>
                        <div class="bg-white/5 hover:bg-white/10 backdrop-blur-sm rounded-xl p-5 text-center border border-white/10 hover:border-white/20 transition-all">
                            <div class="bg-pink-500/20 p-3 rounded-full inline-flex mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-white">Weddings</h4>
                        </div>
                        <div class="bg-white/5 hover:bg-white/10 backdrop-blur-sm rounded-xl p-5 text-center border border-white/10 hover:border-white/20 transition-all">
                            <div class="bg-amber-500/20 p-3 rounded-full inline-flex mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-white">Parties</h4>
                        </div>
                        <div class="bg-white/5 hover:bg-white/10 backdrop-blur-sm rounded-xl p-5 text-center border border-white/10 hover:border-white/20 transition-all">
                            <div class="bg-emerald-500/20 p-3 rounded-full inline-flex mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-white">Conferences</h4>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Event options for non-logged-in users -->
                <div class="hero-card rounded-2xl p-8 text-center">
                    <h3 class="text-2xl font-bold text-white mb-6">Ready to manage your dream events?</h3>
                    <p class="text-white/70 mb-8 max-w-2xl mx-auto">
                        Create an account to start planning and managing your events. 
                        It's free and only takes a minute to get started.
                    </p>
                    
                    <!-- Event Types Showcase -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-5 text-center border border-white/10">
                            <div class="bg-blue-500/20 p-3 rounded-full inline-flex mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-white">Corporate</h4>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-5 text-center border border-white/10">
                            <div class="bg-pink-500/20 p-3 rounded-full inline-flex mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-white">Weddings</h4>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-5 text-center border border-white/10">
                            <div class="bg-amber-500/20 p-3 rounded-full inline-flex mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-white">Parties</h4>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-5 text-center border border-white/10">
                            <div class="bg-emerald-500/20 p-3 rounded-full inline-flex mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-white">Conferences</h4>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap justify-center gap-6">
                        <a href="register.php" class="btn-primary text-white font-medium px-8 py-3 rounded-lg flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                            </svg>
                            Sign Up Now
                        </a>
                        <a href="index.php" class="bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white font-medium px-8 py-3 rounded-lg transition-all flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Login
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="py-12 px-4 md:px-8 bg-slate-900/40 backdrop-blur-lg border-t border-white/10">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-10">
                <div class="col-span-2">
                    <h3 class="text-2xl font-bold text-white mb-4"><span class="animated-text">EventDash</span></h3>
                    <p class="text-white/70 mb-6">
                        Your all-in-one solution for event planning and management. We make it easy to create, manage, and execute exceptional events.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                            </svg>
                        </a>
                        <a href="#" class="bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="home.php" class="text-white/70 hover:text-white transition-colors flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            Home
                        </a></li>
                        <li><a href="#testimonials-section" class="text-white/70 hover:text-white transition-colors flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            Testimonials
                        </a></li>
                        <li><a href="#events-section" class="text-white/70 hover:text-white transition-colors flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            Events
                        </a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Account</h4>
                    <ul class="space-y-2">
                        <?php if ($is_logged_in): ?>
                            <li><a href="dashboard.php" class="text-white/70 hover:text-white transition-colors flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Dashboard
                            </a></li>
                            <li><a href="create_event.php" class="text-white/70 hover:text-white transition-colors flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Create Event
                            </a></li>
                            <li><a href="logout.php" class="text-white/70 hover:text-white transition-colors flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Logout
                            </a></li>
                        <?php else: ?>
                            <li><a href="index.php" class="text-white/70 hover:text-white transition-colors flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Login
                            </a></li>
                            <li><a href="register.php" class="text-white/70 hover:text-white transition-colors flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Register
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Contact Us</h4>
                    <ul class="space-y-2">
                        <li class="text-white/70 flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                            <span>gaurav.dash05@gmail.com</span>
                        </li>
                        <li class="text-white/70 flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                            </svg>
                            <span>Live chat available 24/7</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-white/10 mt-10 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-white/50 text-sm mb-4 md:mb-0">
                    &copy; <?php echo date('Y'); ?> EventDash. All rights reserved.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-white/50 hover:text-white text-sm transition-colors">Privacy Policy</a>
                    <a href="#" class="text-white/50 hover:text-white text-sm transition-colors">Terms of Service</a>
                    <a href="#" class="text-white/50 hover:text-white text-sm transition-colors">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        // Smooth scrolling for anchor links
        function scrollToSection(id) {
            const element = document.getElementById(id);
            if (element) {
                window.scrollTo({
                    top: element.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Animate elements on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.feature-card, .hero-card').forEach(element => {
                observer.observe(element);
            });
            
            // Add animation class for fade-in effect
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .animate-fade-in {
                    animation: fadeIn 0.6s ease-out forwards;
                }
            `;
            document.head.appendChild(style);
        });
        
        // Authentication check for event-related links
        const protectedLinks = document.querySelectorAll('.protected-link');
        protectedLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                <?php if (!$is_logged_in): ?>
                    e.preventDefault();
                    window.location.href = 'index.php';
                <?php endif; ?>
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                            "enable": true,
                            "mode": "grab"
                        },
                        "onclick": {
                            "enable": true,
                            "mode": "push"
                        },
                        "resize": true
                    },
                    "modes": {
                        "grab": {
                            "distance": 140,
                            "line_linked": {
                                "opacity": 0.3
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
            
            // Initialize Framer Motion if available
            const motion = window.framerMotion?.motion;
            
            // Animate the floating blobs with more dynamic movement
            const blobs = document.querySelectorAll('.floating-blob');
            blobs.forEach((blob, index) => {
                gsap.to(blob, {
                    x: `random(-100, 100, 5)`,
                    y: `random(-100, 100, 5)`,
                    scale: `random(0.8, 1.2, 0.05)`,
                    duration: 10 + index * 2,
                    repeat: -1,
                    yoyo: true,
                    ease: "sine.inOut"
                });
            });
            
            // Animate the gradient orbs with rotation and pulsing
            const orbs = document.querySelectorAll('.gradient-orb');
            orbs.forEach((orb, index) => {
                // Create a timeline for more complex animations
                const tl = gsap.timeline({ repeat: -1, yoyo: true });
                
                // Random movement
                tl.to(orb, {
                    x: `random(-150, 150, 10)`,
                    y: `random(-150, 150, 10)`,
                    scale: `random(0.85, 1.15, 0.05)`,
                    rotation: `random(-15, 15, 1)`,
                    duration: 12 + index * 3,
                    ease: "sine.inOut"
                });
                
                // Set different opacity for each orb to create depth
                gsap.set(orb, { opacity: 0.1 + (index * 0.05) });
            });
            
            // Add parallax effect to the background
            document.addEventListener('mousemove', function(e) {
                const mouseX = e.clientX;
                const mouseY = e.clientY;
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;
                
                // Calculate movement percentage
                const moveX = (mouseX - (windowWidth / 2)) / (windowWidth / 2) * -15;
                const moveY = (mouseY - (windowHeight / 2)) / (windowHeight / 2) * -15;
                
                // Apply parallax to gradient orbs
                gsap.to('.gradient-orb', {
                    x: (i) => moveX * (i * 0.5 + 1),
                    y: (i) => moveY * (i * 0.5 + 1),
                    duration: 1,
                    ease: "power1.out"
                });
            });
            
            // Add shimmer effect to feature cards
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach(card => {
                card.classList.add('shimmer-effect');
            });
        });
    </script>
</body>
</html> 