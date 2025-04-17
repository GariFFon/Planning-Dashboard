<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Event Dashboard'; ?></title>
    
    <!-- Load the modern CSS -->
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- Add Inter font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    
    <!-- Add Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Add Alpine.js for lightweight interactive components -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Add optional page-specific styles -->
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Main navigation -->
    <nav class="navbar">
        <div class="container mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center">
                <a href="dashboard.php" class="flex items-center gap-2 text-primary-color font-bold text-xl">
                    <span class="material-icons-round">event</span>
                    <span>EventDash</span>
                </a>
            </div>
            
            <div class="hidden md:flex items-center space-x-2">
                <a href="dashboard.php" class="nav-item <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                    <span class="material-icons-round mr-1" style="font-size: 18px; vertical-align: text-bottom;">dashboard</span>
                    Dashboard
                </a>
                <a href="create_event.php" class="nav-item <?php echo $current_page === 'create_event.php' ? 'active' : ''; ?>">
                    <span class="material-icons-round mr-1" style="font-size: 18px; vertical-align: text-bottom;">add_circle</span>
                    Create Event
                </a>
                <a href="create_task.php" class="nav-item <?php echo $current_page === 'create_task.php' ? 'active' : ''; ?>">
                    <span class="material-icons-round mr-1" style="font-size: 18px; vertical-align: text-bottom;">add_task</span>
                    Create Task
                </a>
                <a href="home.php#testimonials-section" class="nav-item <?php echo $current_page === 'home.php' && isset($_GET['section']) && $_GET['section'] === 'testimonials' ? 'active' : ''; ?>">
                    <span class="material-icons-round mr-1" style="font-size: 18px; vertical-align: text-bottom;">format_quote</span>
                    Testimonials
                </a>
            </div>
            
            <div class="flex items-center gap-4">
                <?php if ($is_logged_in): ?>
                    <div x-data="{ profileOpen: false }" class="relative">
                        <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 bg-white rounded-full shadow-sm p-1 pr-3 hover:shadow-md transition-all duration-200">
                            <div class="avatar">
                                <?php 
                                    // Get first letter of first name and last name
                                    $first_initial = isset($_SESSION['first_name']) ? mb_substr($_SESSION['first_name'], 0, 1) : '';
                                    $last_initial = isset($_SESSION['last_name']) ? mb_substr($_SESSION['last_name'], 0, 1) : '';
                                    echo $first_initial . $last_initial;
                                ?>
                            </div>
                            <span class="text-sm font-medium truncate max-w-[100px]">
                                <?php echo htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username']); ?>
                            </span>
                            <span class="material-icons-round" style="font-size: 18px;">expand_more</span>
                        </button>
                        
                        <div x-show="profileOpen" 
                             @click.away="profileOpen = false" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 scale-95" 
                             x-transition:enter-end="opacity-100 scale-100" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 scale-100" 
                             x-transition:leave-end="opacity-0 scale-95" 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                             style="display: none;">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">Your Profile</a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
                
                <!-- Mobile menu button -->
                <button type="button" class="md:hidden bg-white rounded-md p-2 shadow-sm" 
                        x-data="{mobileMenuOpen: false}" 
                        @click="mobileMenuOpen = !mobileMenuOpen; $dispatch('toggle-mobile-menu', {open: mobileMenuOpen})">
                    <span class="material-icons-round">menu</span>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- Mobile navigation menu -->
    <div x-data="{ open: false }" 
         @toggle-mobile-menu.window="open = $event.detail.open"
         x-show="open"
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 transform -translate-y-4" 
         x-transition:enter-end="opacity-100 transform translate-y-0" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 transform translate-y-0" 
         x-transition:leave-end="opacity-0 transform -translate-y-4"
         class="md:hidden bg-white shadow-lg fixed top-16 left-0 right-0 z-40 px-4 py-2"
         style="display: none;">
        <a href="dashboard.php" class="block py-3 px-4 rounded-md hover:bg-gray-100 transition-colors <?php echo $current_page === 'dashboard.php' ? 'text-primary' : ''; ?>">
            <span class="material-icons-round mr-2" style="vertical-align: middle;">dashboard</span>
            Dashboard
        </a>
        <a href="create_event.php" class="block py-3 px-4 rounded-md hover:bg-gray-100 transition-colors <?php echo $current_page === 'create_event.php' ? 'text-primary' : ''; ?>">
            <span class="material-icons-round mr-2" style="vertical-align: middle;">add_circle</span>
            Create Event
        </a>
        <a href="create_task.php" class="block py-3 px-4 rounded-md hover:bg-gray-100 transition-colors <?php echo $current_page === 'create_task.php' ? 'text-primary' : ''; ?>">
            <span class="material-icons-round mr-2" style="vertical-align: middle;">add_task</span>
            Create Task
        </a>
        <a href="home.php#testimonials-section" class="block py-3 px-4 rounded-md hover:bg-gray-100 transition-colors <?php echo $current_page === 'home.php' && isset($_GET['section']) && $_GET['section'] === 'testimonials' ? 'text-primary' : ''; ?>">
            <span class="material-icons-round mr-2" style="vertical-align: middle;">format_quote</span>
            Testimonials
        </a>
    </div>
    
    <!-- Page content container -->
    <main class="container mx-auto px-4 py-6">
        <?php if (!empty($page_header)): ?>
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2"><?php echo $page_header; ?></h1>
            <?php if (!empty($page_description)): ?>
                <p class="text-muted"><?php echo $page_description; ?></p>
            <?php endif; ?>
        </header>
        <?php endif; ?>
        
        <!-- Breadcrumbs -->
        <?php if (!empty($breadcrumbs)): ?>
        <div class="flex items-center text-sm text-muted mb-6">
            <a href="dashboard.php" class="hover:text-primary transition-colors">Home</a>
            <span class="mx-2">/</span>
            <?php 
            $count = count($breadcrumbs);
            $i = 0;
            foreach ($breadcrumbs as $label => $url): 
                $i++;
                if ($i === $count): 
            ?>
                <span class="font-medium text-text-color"><?php echo $label; ?></span>
            <?php else: ?>
                <a href="<?php echo $url; ?>" class="hover:text-primary transition-colors"><?php echo $label; ?></a>
                <span class="mx-2">/</span>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
        <?php endif; ?>
    </main>
</body>
</html> 