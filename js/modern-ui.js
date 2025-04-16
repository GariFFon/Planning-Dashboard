/**
 * Modern UI Interactions - Event Dashboard
 * Enhances the UI with smooth animations, interactions, and visual effects
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeAnimations();
    initializeCardEffects();
    initializeTooltips();
    initNavigationEffects();
    initNotifications();
    
    // Add page transition effects
    addPageTransitions();
});

/**
 * Initialize entrance animations for elements
 */
function initializeAnimations() {
    // Animate elements with fade-in class
    const fadeElements = document.querySelectorAll('.fade-in, .fade-in-up');
    
    // Create an intersection observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    // Observe each element
    fadeElements.forEach(el => {
        el.style.opacity = '0';
        if (el.classList.contains('fade-in-up')) {
            el.style.transform = 'translateY(20px)';
        }
        el.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
        observer.observe(el);
    });
}

/**
 * Enhance form interactions - SIMPLIFIED to prevent input blocking
 */
function initializeFormEffects() {
    // Only add validation feedback and skip any other manipulations
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                // Add loading state
                submitBtn.classList.add('is-loading');
                if (!submitBtn.querySelector('.spinner')) {
                    submitBtn.insertAdjacentHTML('afterbegin', '<span class="spinner"></span>');
                }
            }
        });
    });
}

/**
 * Add hover and interaction effects to cards
 */
function initializeCardEffects() {
    // Add subtle movement to cards on hover
    const cards = document.querySelectorAll('.card, .glass-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = 'var(--shadow-lg)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.boxShadow = '';
        });
        
        // Add subtle tilt effect
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const moveX = (x - centerX) / 20;
            const moveY = (y - centerY) / 20;
            
            card.style.transform = `translateY(-5px) rotateX(${-moveY}deg) rotateY(${moveX}deg)`;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.transition = 'all 0.5s ease';
            setTimeout(() => {
                card.style.transition = 'var(--transition-standard)';
            }, 500);
        });
    });
}

/**
 * Initialize tooltips and popovers
 */
function initializeTooltips() {
    // Add tooltip functionality
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(el => {
        const tooltipText = el.getAttribute('data-tooltip');
        const tooltipPosition = el.getAttribute('data-tooltip-position') || 'top';
        
        // Create tooltip element
        const tooltip = document.createElement('div');
        tooltip.className = `tooltip tooltip-${tooltipPosition}`;
        tooltip.textContent = tooltipText;
        
        // Add tooltip to the document
        document.body.appendChild(tooltip);
        
        // Show tooltip on hover
        el.addEventListener('mouseenter', () => {
            const rect = el.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            
            let left, top;
            
            switch (tooltipPosition) {
                case 'top':
                    left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                    top = rect.top - tooltipRect.height - 10;
                    break;
                case 'bottom':
                    left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                    top = rect.bottom + 10;
                    break;
                case 'left':
                    left = rect.left - tooltipRect.width - 10;
                    top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
                    break;
                case 'right':
                    left = rect.right + 10;
                    top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
                    break;
            }
            
            tooltip.style.left = `${left}px`;
            tooltip.style.top = `${top}px`;
            tooltip.classList.add('show');
        });
        
        // Hide tooltip on mouse leave
        el.addEventListener('mouseleave', () => {
            tooltip.classList.remove('show');
        });
    });
}

/**
 * Add effects to the main navigation
 */
function initNavigationEffects() {
    const navItems = document.querySelectorAll('.nav-item');
    const activeNavItem = document.querySelector('.nav-item.active');
    
    // Highlight current page in navigation
    if (activeNavItem) {
        activeNavItem.classList.add('active');
    } else {
        // Try to match current page with nav items
        const currentPath = window.location.pathname;
        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.endsWith(href)) {
                item.classList.add('active');
            }
        });
    }
    
    // Add smooth hover effect to nav items
    navItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.style.transform = 'translateY(-2px)';
        });
        
        item.addEventListener('mouseleave', () => {
            item.style.transform = '';
        });
    });
}

/**
 * Initialize notification system
 */
function initNotifications() {
    // Create notification container if it doesn't exist
    let notificationContainer = document.querySelector('.notification-container');
    
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.className = 'notification-container';
        document.body.appendChild(notificationContainer);
    }
    
    // Global function to show notifications
    window.showNotification = function(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} fade-in`;
        
        let icon = '';
        switch (type) {
            case 'success':
                icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" /></svg>';
                break;
            case 'error':
                icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" /></svg>';
                break;
            case 'warning':
                icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>';
                break;
            default: // info
                icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 01.67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 11-.671-1.34l.041-.022zM12 9a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>';
        }
        
        notification.innerHTML = `
            <div class="notification-icon">${icon}</div>
            <div class="notification-content">${message}</div>
            <button class="notification-close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                    <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
        `;
        
        notificationContainer.appendChild(notification);
        
        // Add close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.classList.add('fade-out');
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
        
        // Auto remove after duration
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, duration);
    };
}

/**
 * Add smooth page transitions
 */
function addPageTransitions() {
    // Create page transition overlay
    const transitionOverlay = document.createElement('div');
    transitionOverlay.className = 'page-transition-overlay';
    document.body.appendChild(transitionOverlay);
    
    // Add transition to all internal links
    document.querySelectorAll('a').forEach(link => {
        // Skip external links, anchor links, and links with target="_blank"
        if (
            link.hostname !== window.location.hostname || 
            link.getAttribute('href').startsWith('#') || 
            link.target === '_blank' ||
            link.getAttribute('data-no-transition') === 'true'
        ) {
            return;
        }
        
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const href = link.getAttribute('href');
            
            // Start page transition
            transitionOverlay.classList.add('active');
            
            // Navigate after transition completes
            setTimeout(() => {
                window.location.href = href;
            }, 400);
        });
    });
    
    // Hide overlay when page loads
    window.addEventListener('pageshow', () => {
        transitionOverlay.classList.remove('active');
    });
}

// Add styles for notifications and tooltips
const styleElement = document.createElement('style');
styleElement.textContent = `
    /* Notification Styles */
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 350px;
    }
    
    .notification {
        background: white;
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-lg);
        padding: 12px 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        opacity: 0;
        transform: translateX(30px);
        animation: slideIn 0.3s forwards;
    }
    
    .notification.fade-out {
        animation: slideOut 0.3s forwards;
    }
    
    .notification-icon {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification-content {
        flex-grow: 1;
    }
    
    .notification-close {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        transition: color 0.2s;
    }
    
    .notification-close:hover {
        color: var(--text-color);
    }
    
    .notification-success {
        border-left: 4px solid var(--success-color);
    }
    .notification-success .notification-icon {
        color: var(--success-color);
    }
    
    .notification-error {
        border-left: 4px solid var(--danger-color);
    }
    .notification-error .notification-icon {
        color: var(--danger-color);
    }
    
    .notification-warning {
        border-left: 4px solid var(--warning-color);
    }
    .notification-warning .notification-icon {
        color: var(--warning-color);
    }
    
    .notification-info {
        border-left: 4px solid var(--primary-color);
    }
    .notification-info .notification-icon {
        color: var(--primary-color);
    }
    
    /* Tooltip Styles */
    .tooltip {
        position: fixed;
        background: var(--text-color);
        color: white;
        padding: 6px 10px;
        border-radius: var(--border-radius-sm);
        font-size: 0.75rem;
        z-index: 1000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s;
        max-width: 200px;
        text-align: center;
    }
    
    .tooltip::after {
        content: '';
        position: absolute;
        border: 5px solid transparent;
    }
    
    .tooltip-top::after {
        border-top-color: var(--text-color);
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
    }
    
    .tooltip-bottom::after {
        border-bottom-color: var(--text-color);
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
    }
    
    .tooltip-left::after {
        border-left-color: var(--text-color);
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .tooltip-right::after {
        border-right-color: var(--text-color);
        right: 100%;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .tooltip.show {
        opacity: 0.9;
    }
    
    /* Animations */
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    @keyframes slideOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(30px); }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }
    
    .shake {
        animation: shake 0.5s;
    }
    
    /* Form floating label */
    .form-floating {
        position: relative;
    }
    
    .form-floating label {
        position: absolute;
        top: 0.75rem;
        left: 1rem;
        transition: all 0.2s ease;
        pointer-events: none;
        color: var(--text-secondary);
    }
    
    .form-floating.focused label {
        top: -0.5rem;
        left: 0.5rem;
        font-size: 0.75rem;
        background-color: white;
        padding: 0 0.25rem;
        color: var(--primary-color);
    }
    
    /* Button loading state */
    .btn.is-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.8;
    }
    
    .btn.is-loading .spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
        margin-right: 0.5rem;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Page transition overlay */
    .page-transition-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--background-color);
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.4s ease;
    }
    
    .page-transition-overlay.active {
        opacity: 1;
        pointer-events: all;
    }
`;

document.head.appendChild(styleElement); 