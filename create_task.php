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

// Initialize variables
$error = '';
$success = '';

// Get all events for the current user
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, title FROM events WHERE created_by = ? ORDER BY start_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$events = $stmt->get_result();
$stmt->close();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $due_time = !empty($_POST['due_time']) ? $_POST['due_time'] : null;
    $priority = $conn->real_escape_string($_POST['priority']);
    $event_id = !empty($_POST['event_id']) ? intval($_POST['event_id']) : null;
    $created_by = $_SESSION['user_id'];
    $assigned_to = $_SESSION['user_id']; // Currently assigning to self, could be extended

    // Create datetime string if date is provided
    $due_datetime = null;
    if ($due_date) {
        $due_datetime = $due_date;
        if ($due_time) {
            $due_datetime .= ' ' . $due_time . ':00';
        } else {
            $due_datetime .= ' 23:59:59'; // End of day if no time specified
        }
    }
    
    // Validate input
    if (empty($title)) {
        $error = "Task title is required";
    } else {
        // Insert new task
        $sql = "INSERT INTO tasks (title, description, due_date, status, priority, event_id, assigned_to, created_by) 
                VALUES (?, ?, ?, 'pending', ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiis", $title, $description, $due_datetime, $priority, $event_id, $assigned_to, $created_by);
        
        if ($stmt->execute()) {
            $success = "Task created successfully!";
            $task_id = $stmt->insert_id;
        } else {
            $error = "Error creating task: " . $stmt->error;
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
    <title>Create Task - Event Dashboard</title>
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
    <!-- Include HeroIcons -->
    <script src="https://unpkg.com/heroicons@2.0.18/dist/heroicons.js"></script>
    <!-- Include Flatpickr for date/time -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
            min-height: 100vh;
            margin: 0;
            padding: 0;
            color: white;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        @keyframes AnimateBackground {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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
        
        /* Scroll effect for navbar */
        .navbar-scroll {
            animation: navbarShrink 0.3s forwards;
        }
        
        @keyframes navbarShrink {
            from {
                padding-top: 15px;
                padding-bottom: 15px;
            }
            to {
                padding-top: 10px;
                padding-bottom: 10px;
            }
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
            -webkit-backdrop-filter: blur(10px);
            border: var(--card-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .gradient-text {
            background-image: var(--accent-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            background-size: 300% 300%;
            animation: AnimateBackground 5s ease infinite;
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
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-2xl font-bold gradient-text">Event Dashboard</a>
                </div>
                <div class="flex items-center space-x-6">
                    <!-- Navigation Links -->
                    <a href="create_event.php" class="nav-item text-white hover:text-indigo-200 font-medium transition-all duration-300">Create Event</a>
                    <a href="create_task.php" class="nav-item text-white hover:text-indigo-200 font-medium transition-all duration-300">Create Task</a>
                    
                    <div class="dropdown relative ml-6">
                        <button type="button" class="flex items-center text-white hover:text-indigo-200 focus:outline-none transition-all duration-300">
                            <span class="mr-2"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
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
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10 mt-20 pt-8">
        <div class="bg-white/10 backdrop-blur-lg rounded-xl shadow-xl overflow-hidden border border-white/20">
            <div class="p-6 border-b border-white/20">
                <h1 class="text-2xl font-bold gradient-text">Create New Task</h1>
            </div>
            
            <div class="p-6">
                <?php if (!empty($error)): ?>
                    <div class="bg-red-500/20 text-white p-4 rounded-lg mb-6">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="bg-green-500/20 text-white p-4 rounded-lg mb-6">
                        <?php echo $success; ?>
                        <div class="mt-2">
                            <a href="dashboard.php" class="text-indigo-300 hover:text-indigo-200 transition-colors duration-300">Return to Dashboard</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                        <div class="form-group">
                            <label for="title" class="block text-sm font-medium text-white/90">Task Title *</label>
                            <div class="mt-1">
                                <input id="title" name="title" type="text" required
                                      class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="block text-sm font-medium text-white/90">Description</label>
                            <div class="mt-1">
                                <textarea id="description" name="description" rows="3"
                                         class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="event_id" class="block text-sm font-medium text-white/90">Associated Event</label>
                            <div class="mt-1">
                                <select id="event_id" name="event_id"
                                       class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                                    <option value="">None</option>
                                    <?php while ($event = $events->fetch_assoc()): ?>
                                        <option value="<?php echo $event['id']; ?>">
                                            <?php echo htmlspecialchars($event['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="due_date" class="block text-sm font-medium text-white/90">Due Date</label>
                                <div class="mt-1">
                                    <input id="due_date" name="due_date" type="date"
                                          class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="due_time" class="block text-sm font-medium text-white/90">Due Time</label>
                                <div class="mt-1">
                                    <input id="due_time" name="due_time" type="time"
                                          class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority" class="block text-sm font-medium text-white/90">Priority</label>
                            <div class="mt-1">
                                <select id="priority" name="priority"
                                       class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <a href="dashboard.php" class="py-3 px-4 border border-white/20 rounded-lg shadow-sm text-sm font-medium text-white hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-white/25 transition-all duration-300 hover:scale-105">
                                Cancel
                            </a>
                            <button type="submit" class="py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-white/25 transition-all duration-300 hover:scale-105">
                                Create Task
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize date/time pickers
            if (typeof flatpickr !== 'undefined') {
                flatpickr('input[type="date"]', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    theme: 'dark'
                });
                
                flatpickr('input[type="time"]', {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: 'H:i',
                    time_24hr: true,
                    allowInput: true,
                    theme: 'dark'
                });
            }

            // Toggle dropdown menu
            const dropdownButton = document.querySelector('.dropdown button');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            if (dropdownButton && dropdownMenu) {
                dropdownButton.addEventListener('click', function() {
                    dropdownMenu.classList.toggle('hidden');
                });
            
                // Close dropdown when clicking outside
                window.addEventListener('click', function(event) {
                    if (!event.target.closest('.dropdown')) {
                        dropdownMenu.classList.add('hidden');
                    }
                });
            }
            
            // Add basic animation for form elements
            const formElements = document.querySelectorAll('.form-group');
            formElements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    el.style.transition = 'all 0.4s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 100 + (index * 100));
            });
        });
    </script>
</body>
</html>