// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the dashboard components
    initializeSidebar();
    initializeTaskCheckboxes();
    initializeUserProfile();
    initializeNotifications();
    simulateChartData();
});

// Sidebar functionality
function initializeSidebar() {
    const sidebarItems = document.querySelectorAll('.sidebar-nav li');
    
    sidebarItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            sidebarItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
        });
    });
}

// Task checkboxes
function initializeTaskCheckboxes() {
    const checkboxes = document.querySelectorAll('.task-status input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskItem = this.closest('.task-item');
            
            if (this.checked) {
                taskItem.style.opacity = '0.6';
                taskItem.querySelector('.task-details h4').style.textDecoration = 'line-through';
                
                // Simulate saving to server
                showToast('Task marked as complete');
            } else {
                taskItem.style.opacity = '1';
                taskItem.querySelector('.task-details h4').style.textDecoration = 'none';
                
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
    dropdown.className = 'profile-dropdown';
    dropdown.innerHTML = `
        <ul>
            <li><a href="#"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Account Settings</a></li>
            <li><a href="Home.html"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    `;
    
    // Add dropdown to the DOM but hide it initially
    dropdown.style.display = 'none';
    userProfile.appendChild(dropdown);
    
    // Toggle dropdown on click
    userProfile.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (dropdown.style.display === 'none') {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    });
    
    // Close dropdown when clicking elsewhere
    document.addEventListener('click', function() {
        dropdown.style.display = 'none';
    });
}

// Notifications functionality
function initializeNotifications() {
    const notificationBtn = document.querySelector('.notification-btn');
    
    // Create notifications dropdown
    const notificationsDropdown = document.createElement('div');
    notificationsDropdown.className = 'notifications-dropdown';
    notificationsDropdown.innerHTML = `
        <div class="dropdown-header">
            <h3>Notifications</h3>
            <button class="mark-all-read">Mark all as read</button>
        </div>
        <ul>
            <li class="unread">
                <div class="notification-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="notification-content">
                    <p>New event "Johnson Wedding" has been added</p>
                    <span class="notification-time">2 hours ago</span>
                </div>
            </li>
            <li class="unread">
                <div class="notification-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="notification-content">
                    <p>Task "Contact florist" is due tomorrow</p>
                    <span class="notification-time">5 hours ago</span>
                </div>
            </li>
            <li>
                <div class="notification-icon"><i class="fas fa-comment"></i></div>
                <div class="notification-content">
                    <p>New comment from Sarah on "Tech Conference"</p>
                    <span class="notification-time">Yesterday</span>
                </div>
            </li>
        </ul>
        <div class="dropdown-footer">
            <a href="#">View all notifications</a>
        </div>
    `;
    
    // Add dropdown to the DOM but hide it initially
    notificationsDropdown.style.display = 'none';
    notificationBtn.parentNode.appendChild(notificationsDropdown);
    
    // Toggle dropdown on click
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (notificationsDropdown.style.display === 'none') {
            notificationsDropdown.style.display = 'block';
        } else {
            notificationsDropdown.style.display = 'none';
        }
    });
    
    // Close dropdown when clicking elsewhere
    document.addEventListener('click', function() {
        notificationsDropdown.style.display = 'none';
    });
    
    // Mark all as read functionality
    const markAllReadBtn = notificationsDropdown.querySelector('.mark-all-read');
    markAllReadBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        const unreadNotifications = notificationsDropdown.querySelectorAll('.unread');
        unreadNotifications.forEach(notification => {
            notification.classList.remove('unread');
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
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
        
        // Add style for toast container
        const style = document.createElement('style');
        style.textContent = `
            .toast-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 1000;
            }
            
            .toast {
                background-color: var(--primary);
                color: white;
                padding: 12px 20px;
                border-radius: 4px;
                margin-top: 10px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                display: flex;
                align-items: center;
                animation: slideIn 0.3s ease, fadeOut 0.5s ease 2.5s forwards;
                max-width: 300px;
            }
            
            .toast i {
                margin-right: 10px;
            }
            
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Remove toast after animation completes
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Add mobile navigation toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileNavToggle = document.createElement('button');
    mobileNavToggle.className = 'mobile-nav-toggle';
    mobileNavToggle.innerHTML = '<i class="fas fa-bars"></i>';
    
    const topNavbar = document.querySelector('.top-navbar');
    topNavbar.prepend(mobileNavToggle);
    
    const sidebar = document.querySelector('.sidebar');
    
    // Add style for mobile navigation toggle
    const style = document.createElement('style');
    style.textContent = `
        .mobile-nav-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-dark);
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .mobile-nav-toggle {
                display: block;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Toggle sidebar on mobile
    mobileNavToggle.addEventListener('click', function() {
        if (sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            sidebar.style.transform = 'translateX(-100%)';
        } else {
            sidebar.classList.add('active');
            sidebar.style.transform = 'translateX(0)';
        }
    });
    
    // Initially hide sidebar on mobile
    if (window.innerWidth <= 768) {
        sidebar.style.transform = 'translateX(-100%)';
        sidebar.style.transition = 'transform 0.3s ease';
    }
}); 