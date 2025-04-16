<?php
// Start session
session_start();

// Use the authentication check include
require_once 'includes/auth_check.php';

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

// Get all events in the system
$sql = "SELECT COUNT(*) as total FROM events";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$all_events_count = $result->fetch_assoc()['total'];

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

// Get all upcoming events with details and creator information
$sql = "SELECT e.id, e.title, e.description, e.location, e.start_date, e.end_date, e.color,
               u.first_name, u.last_name 
        FROM events e
        JOIN users u ON e.created_by = u.id
        ORDER BY e.start_date ASC
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute();
$all_upcoming_events = $stmt->get_result();

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
        
        /* Floating navbar styles - adjusted spacing */
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

        /* Adjusted spacing for stats cards */
        .stats-card {
            padding: 1.5rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Better welcome section spacing */
        .welcome-section {
            padding: 2rem;
            margin-top: 5rem;
        }
        
        /* App name with proper spacing and styling */
        .app-name {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        /* User dropdown styling */
        .user-dropdown {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .user-dropdown:hover {
            background: rgba(255, 255, 255, 0.05);
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
    
    <!-- Floating Navigation -->
    <nav class="floating-navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <div class="text-xl font-bold text-white flex items-center">
                        <span class="gradient-text">EventDash</span>
                    </div>
                </div>
                <div class="flex items-center space-x-1">
                    <a href="home.php" class="nav-item text-white/80">Home</a>
                    <a href="dashboard.php" class="nav-item text-white/90 font-medium">Dashboard</a>
                    <a href="create_event.php" class="nav-item text-white/80">Create Event</a>
                    <a href="create_task.php" class="nav-item text-white/80">Add Task</a>
                    
                    <div class="dropdown relative ml-6">
                        <button type="button" class="flex items-center text-white hover:text-indigo-200 focus:outline-none transition-all duration-300">
                            <span class="mr-2"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 glass-card rounded-lg shadow-xl py-1 z-50">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-white hover:bg-white/10 transition-all duration-300">Profile</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-white hover:bg-white/10 transition-all duration-300">Sign out</a>
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

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10 mt-20 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Event Statistics -->
                <div class="glass-card rounded-xl p-6 hover:shadow-lg transition duration-300 ease-in-out">
                    <h2 class="text-xl font-bold mb-4 text-white">Stats Overview</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-dark-700/50 p-4 rounded-lg flex flex-col items-center justify-center">
                            <div class="text-4xl font-bold text-purple-400 mb-1"><?php echo $total_events; ?></div>
                            <div class="text-white/70 text-sm">Your Events</div>
                        </div>
                        <div class="bg-dark-700/50 p-4 rounded-lg flex flex-col items-center justify-center">
                            <div class="text-4xl font-bold text-blue-400 mb-1"><?php echo $all_events_count; ?></div>
                            <div class="text-white/70 text-sm">All Events</div>
                        </div>
                        <div class="bg-dark-700/50 p-4 rounded-lg flex flex-col items-center justify-center">
                            <div class="text-4xl font-bold text-indigo-400 mb-1"><?php echo $pending_tasks; ?></div>
                            <div class="text-white/70 text-sm">Tasks</div>
                        </div>
                        <div class="bg-dark-700/50 p-4 rounded-lg flex flex-col items-center justify-center">
                            <div class="text-4xl font-bold text-pink-400 mb-1">
                                <?php
                                $sql = "SELECT COUNT(*) as completed FROM tasks WHERE assigned_to = ? AND status = 'completed'";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $completed = $stmt->get_result()->fetch_assoc()['completed'];
                                echo $completed;
                                ?>
                            </div>
                            <div class="text-white/70 text-sm">Completed</div>
                        </div>
                    </div>
                </div>

                <!-- Your Upcoming Events -->
                <div class="glass-card rounded-xl p-6 hover:shadow-lg transition duration-300 ease-in-out">
                    <h2 class="text-xl font-bold mb-4 text-white">Your Upcoming Events</h2>
                    <div class="space-y-3">
                        <?php if ($upcoming_events->num_rows > 0): ?>
                            <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                                <div class="bg-dark-700/50 p-4 rounded-lg flex items-center space-x-4 hover:bg-dark-600/50 transition duration-200 ease-in-out">
                                    <div class="h-12 w-12 rounded-full flex items-center justify-center" style="background-color: <?php echo $event['color']; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-white font-semibold"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <p class="text-sm text-white/70">
                                            <?php
                                            $start_date = new DateTime($event['start_date']);
                                            echo $start_date->format('M j, Y · g:i A');
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="bg-dark-700/50 p-4 rounded-lg text-center text-white/70">
                                No upcoming events. <a href="create_event.php" class="text-blue-400 hover:text-blue-300">Create one now!</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- All Upcoming Events -->
                <div class="glass-card rounded-xl p-6 hover:shadow-lg transition duration-300 ease-in-out">
                    <h2 class="text-xl font-bold mb-4 text-white">All Events</h2>
                    <div class="space-y-3">
                        <?php if ($all_upcoming_events->num_rows > 0): ?>
                            <?php while ($event = $all_upcoming_events->fetch_assoc()): ?>
                                <div class="bg-dark-700/50 p-4 rounded-lg flex items-center space-x-4 hover:bg-dark-600/50 transition duration-200 ease-in-out">
                                    <div class="h-12 w-12 rounded-full flex items-center justify-center" style="background-color: <?php echo $event['color']; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-white font-semibold"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <p class="text-sm text-white/70">
                                            <?php
                                            $start_date = new DateTime($event['start_date']);
                                            echo $start_date->format('M j, Y · g:i A');
                                            ?>
                                            <span class="ml-2 text-white/60">Created by: <?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?></span>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="bg-dark-700/50 p-4 rounded-lg text-center text-white/70">
                                No events in the system. <a href="create_event.php" class="text-blue-400 hover:text-blue-300">Create one now!</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modern Footer -->
    <footer class="relative z-10 mt-20">
        <!-- Wave SVG Divider -->
        <div class="absolute top-0 left-0 w-full overflow-hidden transform -translate-y-full">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-[60px]">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V56.44Z" fill="rgba(15, 23, 42, 0.7)"></path>
            </svg>
        </div>
        
        <!-- Footer Content -->
        <div class="bg-dark-900/80 backdrop-blur-lg pt-16 pb-8 border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Footer Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10 pb-10 border-b border-white/10">
                    <!-- Company Info -->
                    <div>
                        <h3 class="text-xl font-bold gradient-text mb-3">Event Dashboard</h3>
                        <p class="text-white/70 mb-3">Create, manage, and organize your events with our powerful and intuitive dashboard platform.</p>
                        <div class="flex space-x-3 mt-4">
                            <!-- Social Media Icons -->
                            <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center bg-white/5 hover:bg-white/10 text-white transition-all duration-300 hover:scale-110 hover:shadow-glow-sm" aria-label="Facebook">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center bg-white/5 hover:bg-white/10 text-white transition-all duration-300 hover:scale-110 hover:shadow-glow-sm" aria-label="Twitter">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                </svg>
                            </a>
                            <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center bg-white/5 hover:bg-white/10 text-white transition-all duration-300 hover:scale-110 hover:shadow-glow-sm" aria-label="Instagram">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center bg-white/5 hover:bg-white/10 text-white transition-all duration-300 hover:scale-110 hover:shadow-glow-sm" aria-label="LinkedIn">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Quick Links</h3>
                        <ul class="space-y-3">
                            <li>
                                <a href="dashboard.php" class="text-white/70 hover:text-white transition-colors duration-300 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="create_event.php" class="text-white/70 hover:text-white transition-colors duration-300 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    Create Event
                                </a>
                            </li>
                            <li>
                                <a href="create_task.php" class="text-white/70 hover:text-white transition-colors duration-300 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    Create Task
                                </a>
                            </li>
                            <li>
                                <a href="profile.php" class="text-white/70 hover:text-white transition-colors duration-300 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    Profile
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Contact Us</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-indigo-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <p class="text-white/70">123 Event Street, San Francisco, CA 94103</p>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-indigo-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-white/70">contact@eventdashboard.com</p>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-indigo-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <p class="text-white/70">+1 (555) 123-4567</p>
                            </div>
                        </div>
                        <!-- Newsletter Subscription -->
                        <div class="mt-6">
                            <form class="flex mt-2">
                                <input type="email" placeholder="Your Email" class="appearance-none bg-white/5 border border-white/10 text-white block px-3 py-2 rounded-l-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300 w-full">
                                <button type="submit" class="py-2 px-4 border border-transparent rounded-r-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-1 focus:ring-white/25 transition-all duration-300">
                                    Subscribe
                                </button>
                            </form>
                            <p class="text-white/50 text-sm mt-2">Subscribe to our newsletter for updates</p>
                        </div>
                    </div>
                </div>
                
                <!-- Copyright Section -->
                <div class="flex flex-col md:flex-row justify-between items-center pt-5">
                    <p class="text-white/50 text-sm">© 2025 Event Dashboard. All rights reserved.</p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-white/50 hover:text-white text-sm transition-colors duration-300">Privacy Policy</a>
                        <a href="#" class="text-white/50 hover:text-white text-sm transition-colors duration-300">Terms of Service</a>
                        <a href="#" class="text-white/50 hover:text-white text-sm transition-colors duration-300">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating action button -->
    <div class="fixed bottom-6 right-6 z-50">
        <a href="#" class="w-14 h-14 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110" id="back-to-top">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
            </svg>
        </a>
    </div>

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