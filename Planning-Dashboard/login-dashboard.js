// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tab switching
    initTabs();
    
    // Initialize form submissions
    initForms();
    
    // Initialize password toggles and strength meter
    initPasswordFields();
});

// Tab switching functionality
function initTabs() {
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    loginTab.addEventListener('click', function() {
        // Update tab styles
        loginTab.classList.add('active', 'text-primary', 'border-b-2', 'border-primary');
        registerTab.classList.remove('active', 'text-primary', 'border-b-2', 'border-primary');
        registerTab.classList.add('text-gray-500');
        
        // Show/hide forms
        loginForm.classList.remove('hidden');
        loginForm.classList.add('block');
        registerForm.classList.add('hidden');
        registerForm.classList.remove('block');
    });
    
    registerTab.addEventListener('click', function() {
        // Update tab styles
        registerTab.classList.add('active', 'text-primary', 'border-b-2', 'border-primary');
        loginTab.classList.remove('active', 'text-primary', 'border-b-2', 'border-primary');
        loginTab.classList.add('text-gray-500');
        
        // Show/hide forms
        registerForm.classList.remove('hidden');
        registerForm.classList.add('block');
        loginForm.classList.add('hidden');
        loginForm.classList.remove('block');
    });
}

// Form submission handling
function initForms() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    // Login form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const rememberMe = document.getElementById('rememberMe')?.checked || false;
        
        // Get the button
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.classList.add('relative', 'text-transparent');
        submitBtn.insertAdjacentHTML('beforeend', `
            <span class="absolute inset-0 flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
        `);
        
        console.log("Login attempt with:", email);
        
        // Simulate login API call
        setTimeout(() => {
            // Set login state in localStorage
            localStorage.setItem('isLoggedIn', 'true');
            localStorage.setItem('userEmail', email);
            if (rememberMe) {
                localStorage.setItem('rememberUser', 'true');
            }
            
            console.log("Login successful, auth state set to:", localStorage.getItem('isLoggedIn'));
            
            // Redirect to dashboard page
            window.location.href = 'dashboard.html';
        }, 1500);
    });
    
    // Registration form submission
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('registerName').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Basic validation
        if (password !== confirmPassword) {
            showNotification('Passwords do not match!', 'error');
            return;
        }
        
        // Get the button
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.classList.add('relative', 'text-transparent');
        submitBtn.insertAdjacentHTML('beforeend', `
            <span class="absolute inset-0 flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
        `);
        
        // Simulate registration API call
        setTimeout(() => {
            // Reset button state
            submitBtn.classList.remove('relative', 'text-transparent');
            submitBtn.querySelector('span').remove();
            
            // Show success notification
            showNotification('Registration successful! You can now log in.', 'success');
            
            // Switch to login tab
            document.getElementById('loginTab').click();
            
            // Pre-fill email field
            document.getElementById('loginEmail').value = email;
            
            // Clear registration form
            registerForm.reset();
        }, 1500);
    });
}

// Password fields functionality
function initPasswordFields() {
    // Toggle password visibility
    const toggleBtns = document.querySelectorAll('.toggle-password');
    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Password strength meter for registration
    const passwordInput = document.getElementById('registerPassword');
    const strengthSections = document.querySelectorAll('.strength-section');
    const strengthFeedback = document.querySelector('.password-feedback');
    
    if (passwordInput && strengthSections.length) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updateStrengthMeter(strength, strengthSections, strengthFeedback);
        });
    }
    
    // Password confirmation matching
    const confirmInput = document.getElementById('confirmPassword');
    if (passwordInput && confirmInput) {
        confirmInput.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('border-red-500');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
            }
        });
    }
}

// Helper function to calculate password strength
function calculatePasswordStrength(password) {
    if (!password) return 0;
    
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;
    
    // Complexity checks
    if (/[0-9]/.test(password)) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
    
    // Normalize to 0-3 range
    return Math.min(3, Math.floor(strength / 2));
}

// Helper function to update the strength meter
function updateStrengthMeter(strength, sections, feedback) {
    if (!sections || !feedback) return;
    
    // Reset all sections
    sections.forEach(section => {
        section.className = 'strength-section h-1 flex-1 rounded';
        section.classList.add('bg-gray-200');
    });
    
    // Update sections based on strength
    for (let i = 0; i < strength + 1; i++) {
        if (i >= sections.length) break;
        
        sections[i].classList.remove('bg-gray-200');
        
        if (strength === 0) {
            sections[i].classList.add('weak', 'bg-red-500');
        } else if (strength === 1) {
            sections[i].classList.add('medium', 'bg-yellow-500');
        } else {
            sections[i].classList.add('strong', 'bg-green-500');
        }
    }
    
    // Update feedback text
    if (password.length === 0) {
        feedback.textContent = 'Password should be at least 8 characters';
        feedback.className = 'password-feedback text-xs text-gray-500 mt-1';
    } else if (strength === 0) {
        feedback.textContent = 'Password is too weak';
        feedback.className = 'password-feedback text-xs text-red-500 mt-1';
    } else if (strength === 1) {
        feedback.textContent = 'Password is acceptable';
        feedback.className = 'password-feedback text-xs text-yellow-500 mt-1';
    } else if (strength === 2) {
        feedback.textContent = 'Password is strong';
        feedback.className = 'password-feedback text-xs text-green-500 mt-1';
    } else {
        feedback.textContent = 'Password is very strong';
        feedback.className = 'password-feedback text-xs text-green-500 mt-1';
    }
}

// Error and success notifications
function showNotification(message, type) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-5 right-5 bg-white rounded-lg shadow-lg flex items-center p-4 max-w-sm transform translate-x-full opacity-0 transition-all duration-300 z-50`;
    
    // Add type-specific styling
    if (type === 'error') {
        notification.classList.add('border-l-4', 'border-red-500');
    } else {
        notification.classList.add('border-l-4', 'border-green-500');
    }
    
    // Add notification content
    notification.innerHTML = `
        <div class="mr-3 text-${type === 'error' ? 'red' : 'green'}-500">
            <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'} text-xl"></i>
        </div>
        <div class="flex-1">
            <p class="text-sm text-gray-800">${message}</p>
        </div>
        <button class="ml-4 text-gray-400 hover:text-gray-700 transition-colors">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to DOM
    document.body.appendChild(notification);
    
    // Show with animation
    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
    }, 10);
    
    // Add event listener for close button
    notification.querySelector('button').addEventListener('click', () => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
    
    // Auto close after 5 seconds
    setTimeout(() => {
        if (document.body.contains(notification)) {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Add notification styles
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: white;
        border-radius: var(--radius);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        padding: 16px;
        max-width: 350px;
        transform: translateX(400px);
        opacity: 0;
        transition: transform 0.3s ease, opacity 0.3s ease;
        z-index: 1000;
        gap: 12px;
    }
    
    .notification.show {
        transform: translateX(0);
        opacity: 1;
    }
    
    .notification.error .notification-icon {
        color: var(--danger);
    }
    
    .notification.success .notification-icon {
        color: var(--success);
    }
    
    .notification-icon i {
        font-size: 1.5rem;
    }
    
    .notification-message {
        flex: 1;
        font-size: 0.9rem;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
    }
    
    .notification-close:hover {
        color: var(--text-dark);
    }
`;
document.head.appendChild(style); 