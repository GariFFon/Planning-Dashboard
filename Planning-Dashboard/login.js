// DOM Elements
const loginSection = document.getElementById('loginSection');
const registerSection = document.getElementById('registerSection');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const showRegisterBtn = document.getElementById('showRegister');
const showLoginBtn = document.getElementById('showLogin');

// Form switching functionality
showRegisterBtn.addEventListener('click', (e) => {
    e.preventDefault();
    loginSection.style.display = 'none';
    registerSection.style.display = 'block';
});

showLoginBtn.addEventListener('click', (e) => {
    e.preventDefault();
    registerSection.style.display = 'none';
    loginSection.style.display = 'block';
});

// Login form submission
loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = loginForm.querySelector('.submit-btn');
    submitBtn.classList.add('loading');

    const email = loginForm.querySelector('input[type="email"]').value;
    const password = loginForm.querySelector('input[type="password"]').value;
    const rememberMe = loginForm.querySelector('input[type="checkbox"]').checked;

    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500));

        // Here you would typically make an API call to your backend
        console.log('Login attempt:', { email, password, rememberMe });

        // Simulate successful login
        alert('Login successful! Redirecting to dashboard...');
        
        // Redirect to dashboard instead of Home.html
        if (window.opener) {
            window.opener.location.href = 'dashboard.html';
            window.close();
        } else {
            window.location.href = 'dashboard.html';
        }
    } catch (error) {
        alert('Login failed. Please try again.');
    } finally {
        submitBtn.classList.remove('loading');
    }
});

// Registration form submission
registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = registerForm.querySelector('.submit-btn');
    submitBtn.classList.add('loading');

    const formData = new FormData(registerForm);
    const data = Object.fromEntries(formData);

    // Validate passwords match
    if (data.password !== data.confirmPassword) {
        alert('Passwords do not match!');
        submitBtn.classList.remove('loading');
        return;
    }

    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500));

        // Here you would typically make an API call to your backend
        console.log('Registration attempt:', data);

        // Simulate successful registration
        alert('Registration successful! Please login.');
        registerSection.style.display = 'none';
        loginSection.style.display = 'block';
    } catch (error) {
        alert('Registration failed. Please try again.');
    } finally {
        submitBtn.classList.remove('loading');
    }
});

// Password strength indicator
const passwordInput = registerForm.querySelector('input[type="password"]');
const confirmPasswordInput = registerForm.querySelector('input[placeholder="Confirm Password"]');

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    return strength;
}

passwordInput.addEventListener('input', (e) => {
    const strength = checkPasswordStrength(e.target.value);
    const strengthIndicator = document.createElement('div');
    strengthIndicator.className = 'password-strength';
    
    let strengthText = '';
    let strengthColor = '';
    
    switch(strength) {
        case 0:
        case 1:
            strengthText = 'Very Weak';
            strengthColor = '#e74c3c';
            break;
        case 2:
            strengthText = 'Weak';
            strengthColor = '#f39c12';
            break;
        case 3:
            strengthText = 'Medium';
            strengthColor = '#f1c40f';
            break;
        case 4:
            strengthText = 'Strong';
            strengthColor = '#2ecc71';
            break;
        case 5:
            strengthText = 'Very Strong';
            strengthColor = '#27ae60';
            break;
    }

    strengthIndicator.style.color = strengthColor;
    strengthIndicator.textContent = strengthText;

    const existingIndicator = passwordInput.parentElement.querySelector('.password-strength');
    if (existingIndicator) {
        existingIndicator.remove();
    }
    passwordInput.parentElement.appendChild(strengthIndicator);
});

// Real-time password match validation
confirmPasswordInput.addEventListener('input', (e) => {
    if (e.target.value !== passwordInput.value) {
        e.target.setCustomValidity('Passwords do not match');
    } else {
        e.target.setCustomValidity('');
    }
});

// Add CSS for password strength indicator
const style = document.createElement('style');
style.textContent = `
    .password-strength {
        font-size: 0.8rem;
        margin-top: 5px;
        transition: color 0.3s ease;
    }
`;
document.head.appendChild(style); 