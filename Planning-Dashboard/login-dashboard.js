// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginPassword = document.getElementById('loginPassword');
    const registerPassword = document.getElementById('registerPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    const strengthMeter = document.querySelector('.password-strength-meter');
    const strengthSections = document.querySelectorAll('.strength-section');
    const passwordFeedback = document.querySelector('.password-feedback');

    // Tab switching
    loginTab.addEventListener('click', () => {
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
    });

    registerTab.addEventListener('click', () => {
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
        registerForm.classList.add('active');
        loginForm.classList.remove('active');
    });

    // Toggle password visibility
    togglePasswordBtns.forEach(btn => {
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

    // Password strength meter
    registerPassword.addEventListener('input', updatePasswordStrength);

    function updatePasswordStrength() {
        const password = registerPassword.value;
        const strength = checkPasswordStrength(password);
        
        // Reset all sections
        strengthSections.forEach(section => {
            section.className = 'strength-section';
        });
        
        // Update feedback
        if (password.length === 0) {
            passwordFeedback.textContent = 'Password should be at least 8 characters';
            return;
        }
        
        // Update sections based on strength
        if (strength >= 1) {
            strengthSections[0].classList.add('weak');
            passwordFeedback.textContent = 'Password is too weak';
        }
        
        if (strength >= 2) {
            strengthSections[1].classList.add('weak');
            passwordFeedback.textContent = 'Password is weak';
        }
        
        if (strength >= 3) {
            strengthSections[2].classList.add('medium');
            passwordFeedback.textContent = 'Password is medium strength';
        }
        
        if (strength >= 4) {
            strengthSections[3].classList.add('strong');
            passwordFeedback.textContent = 'Password is strong';
        }
    }

    function checkPasswordStrength(password) {
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength++;
        
        // Lowercase letters
        if (password.match(/[a-z]+/)) strength++;
        
        // Uppercase letters
        if (password.match(/[A-Z]+/)) strength++;
        
        // Numbers
        if (password.match(/[0-9]+/)) strength++;
        
        // Special characters
        if (password.match(/[^a-zA-Z0-9]+/)) strength++;
        
        return Math.min(strength, 4);
    }

    // Confirm password validation
    confirmPassword.addEventListener('input', () => {
        if (confirmPassword.value !== registerPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    });

    // Login form submission
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('.submit-btn');
        const email = document.getElementById('loginEmail').value;
        const password = loginPassword.value;
        const rememberMe = document.getElementById('rememberMe').checked;
        
        // Add loading state
        submitBtn.classList.add('loading');
        
        try {
            // Simulate API call with delay
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Here you would typically make an API call to authenticate
            console.log('Login attempt:', { email, password, rememberMe });
            
            // If successful, redirect to dashboard
            window.location.href = 'dashboard.html';
        } catch (error) {
            // Handle errors
            console.error('Login error:', error);
            showError('Invalid email or password. Please try again.');
        } finally {
            submitBtn.classList.remove('loading');
        }
    });

    // Register form submission
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('.submit-btn');
        const name = document.getElementById('registerName').value;
        const email = document.getElementById('registerEmail').value;
        const password = registerPassword.value;
        const agreeTerms = document.getElementById('agreeTerms').checked;
        
        // Add loading state
        submitBtn.classList.add('loading');
        
        try {
            // Validate password strength
            if (checkPasswordStrength(password) < 3) {
                showError('Please choose a stronger password');
                return;
            }
            
            // Validate confirm password
            if (password !== confirmPassword.value) {
                showError('Passwords do not match');
                return;
            }
            
            // Simulate API call with delay
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Here you would typically make an API call to register
            console.log('Registration attempt:', { name, email, password, agreeTerms });
            
            // If successful, show success and switch to login
            showSuccess('Account created successfully! Please sign in.');
            
            // Clear form
            registerForm.reset();
            
            // Switch to login tab
            loginTab.click();
        } catch (error) {
            // Handle errors
            console.error('Registration error:', error);
            showError('Registration failed. Please try again.');
        } finally {
            submitBtn.classList.remove('loading');
        }
    });

    // Error and success notifications
    function showError(message) {
        showNotification(message, 'error');
    }
    
    function showSuccess(message) {
        showNotification(message, 'success');
    }
    
    function showNotification(message, type) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-icon">
                <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>
            </div>
            <div class="notification-message">${message}</div>
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add to DOM
        document.body.appendChild(notification);
        
        // Show with animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Add event listener for close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
        
        // Auto close after 5 seconds
        setTimeout(() => {
            if (document.body.contains(notification)) {
                notification.classList.remove('show');
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
}); 