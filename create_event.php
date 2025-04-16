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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $location = $conn->real_escape_string($_POST['location']);
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $end_date = $_POST['end_date'];
    $end_time = $_POST['end_time'];
    $color = $conn->real_escape_string($_POST['color']);
    $created_by = $_SESSION['user_id'];
    
    // Create datetime strings
    $start_datetime = $start_date . ' ' . $start_time . ':00';
    $end_datetime = $end_date . ' ' . $end_time . ':00';
    
    // Validate input
    if (empty($title) || empty($start_date) || empty($start_time) || empty($end_date) || empty($end_time)) {
        $error = "Title, start date, start time, end date, and end time are required";
    } elseif (strtotime($end_datetime) <= strtotime($start_datetime)) {
        $error = "End date must be after start date";
    } else {
        // Insert new event
        $sql = "INSERT INTO events (title, description, location, start_date, end_date, created_by, color) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $title, $description, $location, $start_datetime, $end_datetime, $created_by, $color);
        
        if ($stmt->execute()) {
            $success = "Event created successfully!";
            $event_id = $stmt->insert_id;
        } else {
            $error = "Error creating event: " . $stmt->error;
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
    <title>Create Event - Event Dashboard</title>
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
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        <div class="bg-white/10 backdrop-blur-lg rounded-xl shadow-xl overflow-hidden border border-white/20">
            <div class="p-6 border-b border-white/20">
                <h1 class="text-2xl font-bold text-white">Create New Event</h1>
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
                            <a href="event_details.php?id=<?php echo $event_id; ?>" class="text-indigo-300 hover:text-indigo-200 transition-colors duration-300">View Event Details</a>
                            or
                            <a href="dashboard.php" class="text-indigo-300 hover:text-indigo-200 transition-colors duration-300">Return to Dashboard</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                        <div class="form-group">
                            <label for="title" class="block text-sm font-medium text-white/90">Event Title *</label>
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
                            <label for="location" class="block text-sm font-medium text-white/90">Location</label>
                            <div class="mt-1">
                                <input id="location" name="location" type="text"
                                      class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="start_date" class="block text-sm font-medium text-white/90">Start Date *</label>
                                <div class="mt-1">
                                    <input id="start_date" name="start_date" type="date" required
                                          class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="start_time" class="block text-sm font-medium text-white/90">Start Time *</label>
                                <div class="mt-1">
                                    <input id="start_time" name="start_time" type="time" required
                                          class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="end_date" class="block text-sm font-medium text-white/90">End Date *</label>
                                <div class="mt-1">
                                    <input id="end_date" name="end_date" type="date" required
                                          class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="end_time" class="block text-sm font-medium text-white/90">End Time *</label>
                                <div class="mt-1">
                                    <input id="end_time" name="end_time" type="time" required
                                          class="appearance-none bg-white/5 border border-white/10 text-white block w-full px-3 py-3 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all duration-300">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="color" class="block text-sm font-medium text-white/90">Event Color</label>
                            <div class="mt-1">
                                <div class="flex flex-wrap gap-3 mt-2">
                                    <label class="color-option cursor-pointer">
                                        <input type="radio" name="color" value="#4f46e5" class="sr-only" checked>
                                        <span class="w-8 h-8 rounded-full bg-indigo-600 border-2 border-white flex items-center justify-center transition-all duration-300"></span>
                                    </label>
                                    <label class="color-option cursor-pointer">
                                        <input type="radio" name="color" value="#7c3aed" class="sr-only">
                                        <span class="w-8 h-8 rounded-full bg-purple-600 border-2 border-transparent flex items-center justify-center transition-all duration-300"></span>
                                    </label>
                                    <label class="color-option cursor-pointer">
                                        <input type="radio" name="color" value="#db2777" class="sr-only">
                                        <span class="w-8 h-8 rounded-full bg-pink-600 border-2 border-transparent flex items-center justify-center transition-all duration-300"></span>
                                    </label>
                                    <label class="color-option cursor-pointer">
                                        <input type="radio" name="color" value="#ea580c" class="sr-only">
                                        <span class="w-8 h-8 rounded-full bg-orange-600 border-2 border-transparent flex items-center justify-center transition-all duration-300"></span>
                                    </label>
                                    <label class="color-option cursor-pointer">
                                        <input type="radio" name="color" value="#16a34a" class="sr-only">
                                        <span class="w-8 h-8 rounded-full bg-green-600 border-2 border-transparent flex items-center justify-center transition-all duration-300"></span>
                                    </label>
                                    <label class="color-option cursor-pointer">
                                        <input type="radio" name="color" value="#0284c7" class="sr-only">
                                        <span class="w-8 h-8 rounded-full bg-sky-600 border-2 border-transparent flex items-center justify-center transition-all duration-300"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <a href="dashboard.php" class="py-3 px-4 border border-white/20 rounded-lg shadow-sm text-sm font-medium text-white hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-white/25 transition-all duration-300 hover:scale-105">
                                Cancel
                            </a>
                            <button type="submit" class="py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-white/25 transition-all duration-300 hover:scale-105">
                                Create Event
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Color selector functionality
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.color-option input').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    // Remove selected style from all options
                    document.querySelectorAll('.color-option span').forEach(function(span) {
                        span.classList.remove('border-white');
                        span.classList.add('border-transparent');
                    });
                    
                    // Add selected style to chosen option
                    if (this.checked) {
                        const selectedSpan = this.parentElement.querySelector('span');
                        selectedSpan.classList.remove('border-transparent');
                        selectedSpan.classList.add('border-white');
                    }
                });
            });

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
        });
    </script>
</body>
</html>