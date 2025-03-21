/**
 * Login Page JavaScript
 * Handles the login form submission and validation
 */
import notifications from './core/notifications.js';
import apiClient from './core/api-client.js';

// The rest of the code should be at the top level as well
// This is required for ES modules
const domReady = function() {
    const passwordInput = document.getElementById('password');
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');
    const notification = document.getElementById('notification');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const usernameInput = document.getElementById('username');

    // Add input event listeners for interactive validation
    usernameInput.addEventListener('input', function() {
        validateField(this);
    });

    passwordInput.addEventListener('input', function() {
        validateField(this);
    });

    // Add focus/blur effects for improved interactivity
    const inputs = document.querySelectorAll('.input-with-icon input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
            validateField(this);
        });
    });

    function validateField(field) {
        // Simple validation to add visual feedback
        if (field.value.trim() !== '') {
            field.classList.add('valid');
            field.classList.remove('invalid');
        } else {
            field.classList.add('invalid');
            field.classList.remove('valid');
        }
        
        // For password, add more complex validation if needed
        if (field.id === 'password' && field.value.length > 0 && field.value.length < 4) {
            field.classList.add('invalid');
            field.classList.remove('valid');
        }
    }

    if (loginForm) {
        // Ensure this event listener uses preventDefault()
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault(); // This prevents the form from submitting traditionally
            handleLogin(e);
        });
    }
    
    /**
     * Handle login form submission
     * @param {Event} e - The submit event
     */
    function handleLogin(e) {
        e.preventDefault();
        
        // Get form data
        const username = usernameInput.value.trim();
        const password = passwordInput.value;
        const submitButton = loginForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        // Basic validation
        if (!username || !password) {
            notifications.show('Please enter both username and password', 'error');
            return;
        }
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        
        // Clear previous errors
        if (errorMessage) {
            errorMessage.textContent = '';
            errorMessage.style.display = 'none';
        }
        
        // Prepare login data as JSON
        const loginData = {
            username: username,
            password: password
        };
        
        console.log('Login attempt for:', username);
        
        // First try using Fetch API directly with more detailed error handling
        fetch('php/auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(loginData),
            credentials: 'same-origin' // Important for session cookies
        })
        .then(response => {
            console.log('Response received:', response);
            console.log('Response status:', response.status);
            console.log('Response OK:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Login response data:', data);
            
            if (data.success) {
                notifications.show('Login successful! Redirecting...', 'success');
                
                // Get the redirect URL from the response
                const redirectUrl = data.redirect || 'user_dashboard.php';
                console.log('Redirecting to:', redirectUrl);
                
                // Redirect to the appropriate page
                window.location.href = redirectUrl;
            } else {
                // Show error message
                if (errorMessage) {
                    errorMessage.textContent = data.message || 'Login failed. Please check your credentials.';
                    errorMessage.style.display = 'block';
                } else {
                    notifications.show(data.message || 'Login failed. Please check your credentials.', 'error');
                }
                
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            
            // Fallback to form submission if fetch fails
            console.log('Trying alternative login method...');
            
            // Create a form and submit it directly (old-school but reliable)
            const fallbackForm = document.createElement('form');
            fallbackForm.method = 'POST';
            fallbackForm.action = 'php/auth/login.php';
            fallbackForm.style.display = 'none';
            
            const usernameInput = document.createElement('input');
            usernameInput.name = 'username';
            usernameInput.value = username;
            
            const passwordInput = document.createElement('input');
            passwordInput.name = 'password';
            passwordInput.value = password;
            
            fallbackForm.appendChild(usernameInput);
            fallbackForm.appendChild(passwordInput);
            
            document.body.appendChild(fallbackForm);
            
            // Show error notification
            notifications.show('Using alternative login method due to an error: ' + error.message, 'warning');
            
            // Submit the form after a short delay
            setTimeout(() => {
                fallbackForm.submit();
            }, 500);
        });
    }

    // Add password toggle functionality - only keep this implementation
    if (passwordInput) {
        // Get the existing button if it exists
        let toggleBtn = document.getElementById('betterTogglePassword');
        
        // Only create a new button if one doesn't exist
        if (!toggleBtn) {
            toggleBtn = document.createElement('button');
            toggleBtn.id = 'betterTogglePassword';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            toggleBtn.type = 'button';
            toggleBtn.className = 'password-toggle';
            passwordInput.parentElement.style.position = 'relative'; // Ensure parent is positioned
            passwordInput.parentElement.appendChild(toggleBtn);
        }

        // Add event listener to the toggle button
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            
            // Toggle between eye and eye-slash icons
            if (type === 'text') {
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                this.classList.add('visible');
            } else {
                this.innerHTML = '<i class="fas fa-eye"></i>';
                this.classList.remove('visible');
            }
        });
    }
    
    // Handle URL parameters (to fix any existing URLs with query params)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('username')) {
        const username = urlParams.get('username');
        const password = urlParams.get('password');
        
        // Clear URL parameters without refreshing
        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        // If we have both username and password, auto-fill and submit
        if (username && password) {
            usernameInput.value = username;
            passwordInput.value = password;
            
            // Trigger validation
            validateField(usernameInput);
            validateField(passwordInput);
            
            // Submit the form automatically after a small delay
            setTimeout(() => handleLogin(new Event('submit')), 500);
        }
    }
};

// Use 'DOMContentLoaded' event or execute immediately if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', domReady);
} else {
    domReady();
}