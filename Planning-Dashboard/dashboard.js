// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    checkLoginState();

    // Initialize the dashboard components
    initializeSidebar();
    initializeTaskCheckboxes();
    initializeUserProfile();
    initializeNotifications();
    simulateChartData();
});

// Check login state and update UI accordingly
function checkLoginState() {
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    // Update the selector to match the Tailwind structure in the HTML
    const sidebarFooter = document.querySelector('aside > div:last-child');
    const userActions = document.getElementById('userActions');
    
    if (!sidebarFooter) {
        console.error('Sidebar footer element not found');
        return;
    }
    
    if (isLoggedIn) {
        // User is logged in, show logout button
        sidebarFooter.innerHTML = `
            <a href="login-dashboard.html" class="logout flex items-center opacity-70 hover:opacity-100 transition-all">
                <i class="fas fa-sign-out-alt mr-3"></i>
                <span>Logout</span>
            </a>
        `;
        
        // Enable user profile dropdown and notifications
        if (userActions) {
            userActions.classList.remove('hidden');
        }
    } else {
        // User is not logged in, show login button
        sidebarFooter.innerHTML = `
            <a href="login-dashboard.html" class="login flex items-center opacity-70 hover:opacity-100 transition-all">
                <i class="fas fa-sign-in-alt mr-3"></i>
                <span>Login</span>
            </a>
        `;
        
        // Hide user profile dropdown and notifications when not logged in
        if (userActions) {
            userActions.classList.add('hidden');
        }
    }

    // Add click event for logout button - only if logged in
    if (isLoggedIn) {
        const logoutBtn = document.querySelector('.fa-sign-out-alt')?.parentElement;
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.setItem('isLoggedIn', 'false');
                    return true;
                } else {
                    e.preventDefault();
                    return false;
                }
            });
        }
    }
}

// Sidebar functionality
function initializeSidebar() {
    // Update selector to match the Tailwind structure
    const sidebarItems = document.querySelectorAll('aside nav ul li a');
    
    sidebarItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Remove active class from all items
            sidebarItems.forEach(i => {
                i.classList.remove('border-l-4', 'border-primary-light', 'bg-white', 'bg-opacity-15');
                i.classList.add('border-l-0');
            });
            
            // Add active class to clicked item
            this.classList.add('border-l-4', 'border-primary-light', 'bg-white', 'bg-opacity-15');
            this.classList.remove('border-l-0');
        });
    });
}

// Task checkboxes
function initializeTaskCheckboxes() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskItem = this.closest('.flex');
            const taskLabel = this.nextElementSibling;
            const taskTitle = taskItem.querySelector('h4');
            
            if (this.checked) {
                taskItem.classList.add('opacity-60');
                taskTitle.classList.add('line-through');
                taskLabel.classList.add('bg-primary');
                taskLabel.classList.add('after:content-["✓"]', 'after:absolute', 'after:text-white', 'after:text-sm', 'after:left-1', 'after:top-0');
                
                // Simulate saving to server
                showToast('Task marked as complete');
            } else {
                taskItem.classList.remove('opacity-60');
                taskTitle.classList.remove('line-through');
                taskLabel.classList.remove('bg-primary');
                taskLabel.classList.remove('after:content-["✓"]', 'after:absolute', 'after:text-white', 'after:text-sm', 'after:left-1', 'after:top-0');
                
                // Simulate saving to server
                showToast('Task marked as incomplete');
            }
        });
    });
}

// User profile dropdown
function initializeUserProfile() {
    const userProfile = document.querySelector('.user-profile');
    
    // Create dropdown menu
    const dropdown = document.createElement('div');
    dropdown.className = 'absolute top-full right-0 bg-white bg-opacity-80 backdrop-blur-md rounded-xl shadow-lg border border-white border-opacity-30 w-[200px] z-50 mt-2 overflow-hidden hidden';
    dropdown.innerHTML = `
        <ul>
            <li class="border-b border-gray-200 border-opacity-30"><a href="#" class="px-4 py-3 flex items-center gap-3 text-gray-800 hover:bg-gray-200 hover:bg-opacity-30 transition-colors"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li class="border-b border-gray-200 border-opacity-30"><a href="#" class="px-4 py-3 flex items-center gap-3 text-gray-800 hover:bg-gray-200 hover:bg-opacity-30 transition-colors"><i class="fas fa-cog"></i> Account Settings</a></li>
            <li><a href="login-dashboard.html" class="px-4 py-3 flex items-center gap-3 text-gray-800 hover:bg-gray-200 hover:bg-opacity-30 transition-colors"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    `;
    
    // Add dropdown to the DOM but hide it initially
    userProfile.classList.add('relative');
    userProfile.appendChild(dropdown);
    
    // Toggle dropdown on click
    userProfile.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    });
    
    // Close dropdown when clicking elsewhere
    document.addEventListener('click', function() {
        dropdown.classList.add('hidden');
    });
}

// Notifications functionality
function initializeNotifications() {
    const notificationBtn = document.querySelector('.notification-btn');
    
    if (!notificationBtn) {
        console.warn('Notification button not found');
        return;
    }
    
    // Create notifications dropdown
    const notificationsDropdown = document.createElement('div');
    notificationsDropdown.className = 'absolute top-full right-[-100px] bg-white bg-opacity-80 backdrop-blur-md rounded-xl shadow-lg border border-white border-opacity-30 w-[300px] z-50 mt-2 overflow-hidden hidden';
    notificationsDropdown.innerHTML = `
        <div class="flex justify-between items-center p-4 border-b border-gray-200 border-opacity-30">
            <h3 class="text-base font-medium text-gray-800">Notifications</h3>
            <button class="text-xs text-primary bg-transparent border-0 cursor-pointer">Mark all as read</button>
        </div>
        <ul class="max-h-[300px] overflow-y-auto">
            <li class="p-4 flex gap-4 border-b border-gray-200 border-opacity-30 hover:bg-gray-200 hover:bg-opacity-30 transition-colors bg-primary-lightest bg-opacity-30">
                <div class="w-9 h-9 bg-primary-lightest bg-opacity-20 rounded-full flex items-center justify-center text-primary">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <p class="text-sm mb-1">New event "Johnson Wedding" has been added</p>
                    <span class="text-xs text-gray-500">2 hours ago</span>
                </div>
            </li>
            <li class="p-4 flex gap-4 border-b border-gray-200 border-opacity-30 hover:bg-gray-200 hover:bg-opacity-30 transition-colors bg-primary-lightest bg-opacity-30">
                <div class="w-9 h-9 bg-primary-lightest bg-opacity-20 rounded-full flex items-center justify-center text-primary">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <p class="text-sm mb-1">Task "Contact florist" is due tomorrow</p>
                    <span class="text-xs text-gray-500">5 hours ago</span>
                </div>
            </li>
            <li class="p-4 flex gap-4 hover:bg-gray-200 hover:bg-opacity-30 transition-colors">
                <div class="w-9 h-9 bg-primary-lightest bg-opacity-20 rounded-full flex items-center justify-center text-primary">
                    <i class="fas fa-comment"></i>
                </div>
                <div>
                    <p class="text-sm mb-1">New comment from Sarah on "Tech Conference"</p>
                    <span class="text-xs text-gray-500">Yesterday</span>
                </div>
            </li>
        </ul>
        <div class="p-3 text-center border-t border-gray-200 border-opacity-30">
            <a href="#" class="text-sm text-primary">View all notifications</a>
        </div>
    `;
    
    // Add dropdown to the DOM but hide it initially
    notificationBtn.parentNode.classList.add('relative');
    notificationBtn.parentNode.appendChild(notificationsDropdown);
    
    // Toggle dropdown on click
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (notificationsDropdown.classList.contains('hidden')) {
            notificationsDropdown.classList.remove('hidden');
        } else {
            notificationsDropdown.classList.add('hidden');
        }
    });
    
    // Close dropdown when clicking elsewhere
    document.addEventListener('click', function() {
        notificationsDropdown.classList.add('hidden');
    });
    
    // Mark all as read functionality
    const markAllReadBtn = notificationsDropdown.querySelector('button');
    markAllReadBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        const unreadNotifications = notificationsDropdown.querySelectorAll('li.bg-primary-lightest');
        unreadNotifications.forEach(notification => {
            notification.classList.remove('bg-primary-lightest', 'bg-opacity-30');
        });
        
        // Update notification count
        document.querySelector('.notification-count').textContent = '0';
        
        showToast('All notifications marked as read');
    });
}

// Simulate chart data and animations
function simulateChartData() {
    // Animate bar chart on load
    const bars = document.querySelectorAll('.bar-value');
    let delay = 100;
    
    bars.forEach(bar => {
        // Store the target height
        const targetHeight = bar.style.height;
        // Start from 0
        bar.style.height = '0%';
        
        // Animate to target height with delay
        setTimeout(() => {
            bar.style.transition = 'height 0.8s ease';
            bar.style.height = targetHeight;
        }, delay);
        
        delay += 100;
    });
}

// Toast notification system
function showToast(message) {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'fixed bottom-5 right-5 z-50';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'bg-primary bg-opacity-90 backdrop-blur-md text-white py-3 px-5 rounded-xl shadow-lg flex items-center mb-3 max-w-xs border border-white border-opacity-20 animate-fadeIn';
    toast.innerHTML = `<i class="fas fa-info-circle mr-3"></i> ${message}`;
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Add animation classes
    toast.classList.add('animate-fadeIn');
    
    // Remove toast after animation completes
    setTimeout(() => {
        toast.classList.add('animate-fadeOut');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Add mobile navigation toggle
document.addEventListener('DOMContentLoaded', function() {
    const topNavbar = document.querySelector('header');
    
    if (!topNavbar) {
        console.error('Header element not found');
        return;
    }
    
    // Create mobile nav toggle button
    const mobileNavToggle = document.createElement('button');
    mobileNavToggle.className = 'md:hidden text-xl text-gray-700 cursor-pointer';
    mobileNavToggle.innerHTML = '<i class="fas fa-bars"></i>';
    
    // Add to DOM
    topNavbar.prepend(mobileNavToggle);
    
    const sidebar = document.querySelector('aside');
    
    if (!sidebar) {
        console.error('Sidebar element not found');
        return;
    }
    
    // Toggle sidebar on mobile
    mobileNavToggle.addEventListener('click', function() {
        if (sidebar.classList.contains('translate-x-0')) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
        } else {
            sidebar.classList.add('translate-x-0');
            sidebar.classList.remove('-translate-x-full');
        }
    });
    
    // Add responsive classes to sidebar
    if (window.innerWidth <= 768) {
        sidebar.classList.add('w-[70px]', '-translate-x-full', 'transition-transform', 'duration-300');
        
        const sidebarTexts = sidebar.querySelectorAll('span');
        sidebarTexts.forEach(span => {
            span.classList.add('md:inline', 'hidden');
        });
        
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.classList.add('justify-center', 'md:justify-start');
        });
        
        const sidebarLogo = sidebar.querySelector('h1');
        if (sidebarLogo) {
            sidebarLogo.classList.add('md:block', 'hidden');
        }
    }
}); 