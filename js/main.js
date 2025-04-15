/**
 * EventPro - Main JavaScript file
 * Contains common functionality for the event planning dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initNavbar();
    initAnimations();
    initEventListeners();
    initNotifications();
});

/**
 * Initialize the navbar functionality
 */
function initNavbar() {
    // Handle navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Handle mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('open');
            // Toggle aria-expanded attribute for accessibility
            const expanded = mobileMenuButton.getAttribute('aria-expanded') === 'true' || false;
            mobileMenuButton.setAttribute('aria-expanded', !expanded);
        });
    }

    // Set active nav link based on current page
    const currentPath = window.location.pathname;
    const filename = currentPath.substring(currentPath.lastIndexOf('/') + 1);
    
    const navLinks = document.querySelectorAll('.navbar-link');
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === filename || 
            (filename === '' && href === 'index.html') || 
            (filename === '/' && href === 'index.html')) {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
        }
    });
}

/**
 * Initialize scroll-based animations
 */
function initAnimations() {
    // Animate elements when they come into view
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    if (animatedElements.length > 0) {
        // Function to check if element is in viewport
        const isInViewport = function(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.85 &&
                rect.bottom >= 0
            );
        };
        
        // Initial check for elements in viewport
        animatedElements.forEach(element => {
            if (isInViewport(element)) {
                element.classList.add('animate');
            }
        });
        
        // Check on scroll
        window.addEventListener('scroll', function() {
            animatedElements.forEach(element => {
                if (isInViewport(element) && !element.classList.contains('animate')) {
                    element.classList.add('animate');
                }
            });
        });
    }

    // Animate stat counters
    animateStatCounters();
}

/**
 * Animate stat counter numbers
 */
function animateStatCounters() {
    const counters = document.querySelectorAll('.stat-counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 1500; // ms
        const step = target / (duration / 16); // 60 FPS
        
        let current = 0;
        const counterInterval = setInterval(() => {
            current += step;
            if (current >= target) {
                counter.textContent = target;
                clearInterval(counterInterval);
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 16);
    });
}

/**
 * Initialize event listeners for interactive elements
 */
function initEventListeners() {
    // Add event listener for the search input
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            // Implement search functionality here
            console.log('Searching for:', e.target.value);
        });
    }
    
    // Add event listeners for form submission
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const formAction = form.getAttribute('action');
            if (!formAction || formAction === '#') {
                e.preventDefault();
                showNotification('Form submission is disabled in this demo.', 'info');
            }
        });
    });
}

/**
 * Initialize the notification system
 */
function initNotifications() {
    // Create notification area if it doesn't exist
    let notificationArea = document.getElementById('notification-area');
    if (!notificationArea) {
        notificationArea = document.createElement('div');
        notificationArea.id = 'notification-area';
        notificationArea.className = 'fixed top-5 right-5 z-50 space-y-3';
        document.body.appendChild(notificationArea);
    }
}

/**
 * Show a notification message
 * @param {string} message - The message to display
 * @param {string} type - The type of notification ('info', 'success', 'warning', 'error')
 * @param {number} duration - Duration in milliseconds to show the notification
 */
function showNotification(message, type = 'info', duration = 5000) {
    const notificationArea = document.getElementById('notification-area');
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification transition-all transform duration-300 ease-in-out shadow-lg rounded-lg p-4 flex items-start max-w-xs backdrop-blur-sm border`;
    
    // Set background color based on type
    switch(type) {
        case 'success':
            notification.classList.add('bg-emerald-900/90', 'border-emerald-700', 'text-white');
            break;
        case 'warning':
            notification.classList.add('bg-amber-900/90', 'border-amber-700', 'text-white');
            break;
        case 'error':
            notification.classList.add('bg-rose-900/90', 'border-rose-700', 'text-white');
            break;
        default: // info
            notification.classList.add('bg-gray-800/90', 'border-gray-700', 'text-white');
    }
    
    // Create icon based on type
    const icon = document.createElement('span');
    icon.className = 'flex-shrink-0 mr-3 mt-0.5';
    
    switch(type) {
        case 'success':
            icon.innerHTML = '<i class="fas fa-check-circle text-emerald-400"></i>';
            break;
        case 'warning':
            icon.innerHTML = '<i class="fas fa-exclamation-triangle text-amber-400"></i>';
            break;
        case 'error':
            icon.innerHTML = '<i class="fas fa-exclamation-circle text-rose-400"></i>';
            break;
        default: // info
            icon.innerHTML = '<i class="fas fa-info-circle text-violet-400"></i>';
    }
    
    // Create text container
    const text = document.createElement('div');
    text.className = 'flex-1';
    text.textContent = message;
    
    // Create close button
    const closeButton = document.createElement('button');
    closeButton.className = 'ml-4 flex-shrink-0 text-white opacity-70 hover:opacity-100 transition duration-200';
    closeButton.innerHTML = '<i class="fas fa-times"></i>';
    closeButton.addEventListener('click', function() {
        closeNotification(notification);
    });
    
    // Assemble notification
    notification.appendChild(icon);
    notification.appendChild(text);
    notification.appendChild(closeButton);
    
    // Add to notification area
    notificationArea.appendChild(notification);
    
    // Auto remove after duration
    setTimeout(() => {
        closeNotification(notification);
    }, duration);
}

/**
 * Close and remove a notification
 * @param {HTMLElement} notification - The notification element to close
 */
function closeNotification(notification) {
    notification.classList.add('opacity-0', 'translate-x-4');
    
    // Remove the element after transition completes
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300); // Match the duration in the transition class
}

/**
 * Format date to a readable string
 * @param {Date|string} date - Date object or date string
 * @param {boolean} includeTime - Whether to include time
 * @returns {string} Formatted date string
 */
function formatDate(date, includeTime = false) {
    const d = new Date(date);
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric'
    };
    
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    
    return d.toLocaleDateString('en-US', options);
}

/**
 * Fetch data from a backend endpoint
 * @param {string} url - The URL to fetch data from
 * @param {Object} options - Fetch options
 * @returns {Promise} Promise with the fetched data
 */
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        showNotification(`Failed to fetch data: ${error.message}`, 'error');
        throw error;
    }
}

/**
 * Toggle the visibility of an element
 * @param {string} elementId - The ID of the element to toggle
 */
function toggleElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.toggle('hidden');
    }
}

/**
 * Generate a gradient background for a category
 * @param {string} category - The category name
 * @returns {string} CSS gradient background
 */
function getCategoryGradient(category) {
    const categories = {
        'Meeting': 'from-violet-600 to-violet-800',
        'Conference': 'from-blue-600 to-blue-800',
        'Workshop': 'from-cyan-600 to-cyan-800',
        'Webinar': 'from-emerald-600 to-emerald-800',
        'Party': 'from-pink-600 to-pink-800',
        'Festival': 'from-amber-600 to-amber-800',
        'Exhibition': 'from-teal-600 to-teal-800',
        'Concert': 'from-rose-600 to-rose-800'
    };
    
    return categories[category] || 'from-gray-600 to-gray-800';
}