<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Include database connection
require_once 'includes/db_connect.php';

// Get user's events
$user_id = $_SESSION['user_id'];
$sql = "SELECT COUNT(*) as total FROM events WHERE created_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_events = $result->fetch_assoc()['total'];

// Get user's pending tasks
$sql = "SELECT COUNT(*) as total FROM tasks 
        WHERE assigned_to = ? AND status != 'completed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_tasks = $result->fetch_assoc()['total'];

// Get user data
$sql = "SELECT first_name, last_name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$first_name = $user['first_name'];
$last_name = $user['last_name'];

// Get user's upcoming events with details
$sql = "SELECT e.id, e.title, e.description, e.location, e.start_date, e.end_date, e.color 
        FROM events e
        WHERE e.created_by = ?
        ORDER BY e.start_date ASC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming_events = $stmt->get_result();

// Get user's pending tasks with details
$sql = "SELECT t.id, t.title, t.description, t.due_date, t.status, t.priority, 
               e.id as event_id, e.title as event_title, e.color as event_color
        FROM tasks t
        LEFT JOIN events e ON t.event_id = e.id
        WHERE t.assigned_to = ? AND t.status != 'completed'
        ORDER BY t.due_date ASC, 
                 FIELD(t.priority, 'high', 'medium', 'low')
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_tasks_list = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Dashboard</title>
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
                    }
                }
            }
        }
    </script>
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
        
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: var(--card-border);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .glass-card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(40, 50, 70, 0.4);
        }
        
        .stat-icon {
            background: var(--accent-gradient);
            background-size: 150% 150%;
            animation: AnimateBackground 5s ease infinite;
        }
        
        .gradient-text {
            background-image: var(--accent-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            background-size: 300% 300%;
            animation: AnimateBackground 5s ease infinite;
        }
        
        .task-status-btn.checked {
            background-image: var(--accent-gradient);
            background-size: 150% 150%;
            animation: AnimateBackground 5s ease infinite;
            border: none;
        }
        
        .nav-item {
            position: relative;
            overflow: hidden;
        }
        
        .nav-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-image: var(--accent-gradient);
            transition: width 0.3s ease;
        }
        
        .nav-item:hover::after {
            width: 100%;
        }
    </style>
</head>
<body class="min-h-screen relative overflow-x-hidden">
    <!-- Animated background blobs -->
    <div class="floating-blob bg-blue-600" style="width: 600px; height: 600px; top: -300px; left: -200px;"></div>
    <div class="floating-blob bg-purple-600" style="width: 500px; height: 500px; bottom: -200px; right: -150px;"></div>
    <div class="floating-blob bg-pink-600" style="width: 400px; height: 400px; top: 30%; left: 70%;"></div>
    
    <!-- Navigation -->
    <nav class="glass-card border-b border-white/20 z-10 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-2xl font-bold gradient-text">Event Dashboard</a>
                </div>
                <div class="flex items-center space-x-6">
                    <!-- Navigation Links -->
                    <a href="create_event.php" class="text-white hover:text-indigo-200 font-medium transition-all duration-300">Create Event</a>
                    <a href="create_task.php" class="text-white hover:text-indigo-200 font-medium transition-all duration-300">Create Task</a>
                    
                    <div class="dropdown relative ml-6">
                        <button type="button" class="flex items-center text-white hover:text-indigo-200 focus:outline-none transition-all duration-300">
                            <span class="mr-2"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 glass-card rounded-lg shadow-xl py-1 z-50">
                            <a href="logout.php" class="block px-4 py-2 text-sm text-white hover:bg-white/10 transition-all duration-300">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        <!-- Welcome Section -->
        <div class="welcome-section glass-card rounded-xl shadow-xl p-6 mb-8 motion-element">
            <h1 class="text-3xl font-bold text-white">Welcome, <span class="gradient-text"><?php echo htmlspecialchars($first_name); ?></span>!</h1>
            <p class="text-white/80 mt-2">Manage your events and tasks all in one place.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <!-- Total Events Card -->
            <div class="glass-card rounded-xl shadow-xl p-6 motion-element">
                <div class="flex items-center">
                    <div class="p-3 rounded-full stat-icon mr-4">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm">Total Events</div>
                        <div class="text-white text-2xl font-bold counter-animate"><?php echo $total_events; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Tasks Card -->
            <div class="glass-card rounded-xl shadow-xl p-6 motion-element delay-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full stat-icon mr-4">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm">Pending Tasks</div>
                        <div class="text-white text-2xl font-bold counter-animate"><?php echo $pending_tasks; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Current Date Card -->
            <div class="glass-card rounded-xl shadow-xl p-6 motion-element delay-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full stat-icon mr-4">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm">Current Date</div>
                        <div class="text-white text-2xl font-bold gradient-text"><?php echo date('M j, Y'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Events and Tasks Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            <!-- Upcoming Events Section -->
            <div class="glass-card rounded-xl shadow-xl overflow-hidden motion-element delay-300">
                <div class="p-6 border-b border-white/20 flex justify-between items-center">
                    <h2 class="text-xl font-semibold gradient-text">Upcoming Events</h2>
                    <a href="create_event.php" class="text-indigo-300 hover:text-indigo-200 text-sm flex items-center hover:scale-105 transition-transform duration-300">
                        <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add New
                    </a>
                </div>
                <div class="divide-y divide-white/10">
                    <?php if ($upcoming_events->num_rows > 0): ?>
                        <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                            <div class="p-6 hover:bg-white/5 transition-all duration-300 event-item">
                                <div class="flex items-start">
                                    <div class="h-4 w-4 rounded-full mt-1 mr-3" style="background-color: <?php echo htmlspecialchars($event['color']); ?>"></div>
                                    <div class="flex-1">
                                        <h3 class="text-white text-lg font-medium">
                                            <?php echo htmlspecialchars($event['title']); ?>
                                        </h3>
                                        <?php if (!empty($event['description'])): ?>
                                            <p class="text-white/70 mt-1 text-sm">
                                                <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . (strlen($event['description']) > 100 ? '...' : ''); ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="mt-2 grid grid-cols-2 gap-2">
                                            <?php if (!empty($event['location'])): ?>
                                                <div class="flex items-center text-white/70 text-sm">
                                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <?php echo htmlspecialchars($event['location']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex items-center text-white/70 text-sm">
                                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <?php echo date('M j, Y', strtotime($event['start_date'])); ?>
                                            </div>
                                            <div class="flex items-center text-white/70 text-sm">
                                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <?php echo date('g:i A', strtotime($event['start_date'])) . ' - ' . date('g:i A', strtotime($event['end_date'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" class="ml-4 p-2 text-white/50 hover:text-white hover:bg-white/5 rounded-lg transition-all duration-300 hover:rotate-12">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-6 text-center text-white/70 italic">
                            No upcoming events. <a href="create_event.php" class="text-indigo-300 hover:text-indigo-200 transition-all duration-300">Create one?</a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($total_events > 5): ?>
                    <div class="p-4 border-t border-white/20 text-center">
                        <a href="#" class="text-indigo-300 hover:text-indigo-200 text-sm hover:underline transition-all duration-300">View all events</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pending Tasks Section -->
            <div class="glass-card rounded-xl shadow-xl overflow-hidden motion-element delay-400">
                <div class="p-6 border-b border-white/20 flex justify-between items-center">
                    <h2 class="text-xl font-semibold gradient-text">Pending Tasks</h2>
                    <a href="create_task.php" class="text-indigo-300 hover:text-indigo-200 text-sm flex items-center hover:scale-105 transition-transform duration-300">
                        <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add New
                    </a>
                </div>
                <div class="divide-y divide-white/10">
                    <?php if ($pending_tasks_list->num_rows > 0): ?>
                        <?php while ($task = $pending_tasks_list->fetch_assoc()): ?>
                            <div class="p-6 hover:bg-white/5 transition-all duration-300 task-item">
                                <div class="flex items-start">
                                    <button class="task-status-btn h-5 w-5 rounded-full border-2 border-white/30 hover:border-white/90 transition-all duration-300 mr-3 mt-0.5" 
                                           data-task-id="<?php echo $task['id']; ?>">
                                    </button>
                                    <div class="flex-1">
                                        <h3 class="text-white text-lg font-medium">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                            
                                            <?php if ($task['priority'] === 'high'): ?>
                                                <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-red-500/20 text-red-300 rounded-full">High</span>
                                            <?php elseif ($task['priority'] === 'medium'): ?>
                                                <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-yellow-500/20 text-yellow-300 rounded-full">Medium</span>
                                            <?php else: ?>
                                                <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-blue-500/20 text-blue-300 rounded-full">Low</span>
                                            <?php endif; ?>
                                        </h3>
                                        
                                        <?php if (!empty($task['description'])): ?>
                                            <p class="text-white/70 mt-1 text-sm">
                                                <?php echo htmlspecialchars(substr($task['description'], 0, 100)) . (strlen($task['description']) > 100 ? '...' : ''); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="mt-2 space-y-2">
                                            <?php if (!empty($task['due_date'])): ?>
                                                <div class="flex items-center text-white/70 text-sm">
                                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Due: <?php echo date('M j, Y', strtotime($task['due_date'])) . 
                                                               (date('H:i:s', strtotime($task['due_date'])) != '23:59:59' ? ' at ' . date('g:i A', strtotime($task['due_date'])) : ''); ?>
                                                    
                                                    <?php 
                                                        $due_timestamp = strtotime($task['due_date']);
                                                        $now_timestamp = time();
                                                        $days_left = floor(($due_timestamp - $now_timestamp) / (60 * 60 * 24));
                                                        
                                                        if ($days_left < 0) {
                                                            echo '<span class="ml-2 text-red-400 font-medium">Overdue</span>';
                                                        } elseif ($days_left == 0) {
                                                            echo '<span class="ml-2 text-yellow-400 font-medium">Today</span>';
                                                        } elseif ($days_left == 1) {
                                                            echo '<span class="ml-2 text-yellow-400 font-medium">Tomorrow</span>';
                                                        } elseif ($days_left < 7) {
                                                            echo '<span class="ml-2 text-indigo-400 font-medium">In ' . $days_left . ' days</span>';
                                                        }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($task['event_id'])): ?>
                                                <div class="flex items-center">
                                                    <div class="flex items-center text-white/70 text-sm">
                                                        <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        Event: 
                                                        <span class="inline-flex items-center ml-1">
                                                            <span class="h-2 w-2 rounded-full mr-1" style="background-color: <?php echo htmlspecialchars($task['event_color']); ?>"></span>
                                                            <?php echo htmlspecialchars($task['event_title']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-6 text-center text-white/70 italic">
                            No pending tasks. <a href="create_task.php" class="text-indigo-300 hover:text-indigo-200 transition-all duration-300">Add one?</a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($pending_tasks > 10): ?>
                    <div class="p-4 border-t border-white/20 text-center">
                        <a href="#" class="text-indigo-300 hover:text-indigo-200 text-sm hover:underline transition-all duration-300">View all tasks</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="js/app.js"></script>
    <script>
        // Use Framer Motion for animations
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize Framer Motion
            const { motion } = window.framerMotion;
            
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
            
            // Animate motion elements with Framer Motion
            const motionElements = document.querySelectorAll('.motion-element');
            
            // Helper function to create staggered animations
            const createAnimations = (elements, options = {}) => {
                const defaults = {
                    hidden: { opacity: 0, y: 20 },
                    visible: { 
                        opacity: 1, 
                        y: 0,
                        transition: { 
                            duration: 0.6,
                            ease: [0.25, 0.1, 0.25, 1.0]
                        }
                    }
                };
                
                const config = { ...defaults, ...options };
                
                elements.forEach((element, index) => {
                    // Create a new motion component instance
                    const motionInstance = motion(element, {
                        initial: config.hidden,
                        animate: config.visible,
                        transition: {
                            ...config.visible.transition,
                            delay: index * 0.15 // Staggered delay
                        }
                    });
                    
                    // Setup intersection observer for triggering animations when visible
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                motionInstance.start("visible");
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.1 });
                    
                    observer.observe(element);
                });
            };
            
            // Apply animations
            createAnimations(motionElements);
            
            // Animate counter numbers
            const counterElements = document.querySelectorAll('.counter-animate');
            counterElements.forEach(counter => {
                const target = parseInt(counter.innerText);
                const increment = target / 20;
                
                let current = 0;
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.innerText = Math.ceil(current);
                        setTimeout(updateCounter, 50);
                    } else {
                        counter.innerText = target;
                    }
                };
                
                updateCounter();
            });
            
            // Add hover animations for event and task items with Framer Motion
            const eventItems = document.querySelectorAll('.event-item, .task-item');
            eventItems.forEach(item => {
                item.addEventListener('mouseenter', () => {
                    motion(item, {
                        scale: 1.02,
                        transition: {
                            type: "spring",
                            stiffness: 300,
                            damping: 20
                        }
                    });
                });
                
                item.addEventListener('mouseleave', () => {
                    motion(item, {
                        scale: 1,
                        transition: {
                            type: "spring",
                            stiffness: 200,
                            damping: 25
                        }
                    });
                });
            });
            
            // Task completion toggle functionality with animation
            document.querySelectorAll('.task-status-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const taskId = this.dataset.taskId;
                    this.classList.toggle('checked');
                    
                    if (this.classList.contains('checked')) {
                        // Animate the button with Framer Motion
                        motion(this, {
                            scale: [1, 1.5, 1],
                            transition: { duration: 0.4 }
                        });
                        
                        this.innerHTML = `
                            <svg class="h-3 w-3 m-auto text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        `;
                        
                        // Update task status to completed via AJAX
                        fetch('api/update_task_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `task_id=${taskId}&status=completed`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Animate task completion
                                const taskItem = this.closest('.task-item');
                                motion(taskItem, {
                                    opacity: 0.5,
                                    y: [0, -10, 20],
                                    transition: { duration: 0.7 }
                                });
                                
                                setTimeout(() => {
                                    taskItem.remove();
                                }, 700);
                            }
                        });
                    } else {
                        this.innerHTML = '';
                        
                        // Reset task status to pending
                        fetch('api/update_task_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `task_id=${taskId}&status=pending`
                        });
                    }
                });
            });
            
            // Toggle dropdown menu
            document.querySelector('.dropdown button')?.addEventListener('click', function() {
                const dropdown = document.querySelector('.dropdown-menu');
                if (dropdown) {
                    dropdown.classList.toggle('hidden');
                }
            });
    
            // Close dropdown when clicking outside
            window.addEventListener('click', function(event) {
                if (!event.target.closest('.dropdown')) {
                    const dropdown = document.querySelector('.dropdown-menu');
                    if (dropdown) {
                        dropdown.classList.add('hidden');
                    }
                }
            });
        });
    </script>
</body>
</html>