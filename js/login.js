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
        // Clear previous errors
        if (errorMessage) {
            errorMessage.textContent = '';
            errorMessage.style.display = 'none';
        }
        
        // Get form data
        const formData = new FormData(loginForm);
        const submitButton = loginForm.querySelector('button[type="submit"]');
        
        // Save original button text and show loading state
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        
        // Debug info to console
        console.log('Login form submitted');
        console.log('Username:', formData.get('username'));
        console.log('Password length:', formData.get('password').length);
        
        // Send login request with more error handling
        fetch('php/auth/login.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin' // Ensure cookies are sent and received
        })
        .then(response => {
            console.log('Response status:', response.status);
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
                
                // Use a simpler, more direct approach to redirecting
                setTimeout(() => {
                    // Try direct window.location approach (most reliable)
                    window.location = redirectUrl;
                    
                    // Fallback: If after 500ms we're still on the same page, try another approach
                    setTimeout(() => {
                        if (window.location.href.includes('login.php')) {
                            console.log('First redirect attempt failed, trying alternative...');
                            window.location.href = redirectUrl;
                            
                            // Last resort - create and click a link
                            setTimeout(() => {
                                if (window.location.href.includes('login.php')) {
                                    console.log('Second redirect attempt failed, using link click...');
                                    const link = document.createElement('a');
                                    link.href = redirectUrl;
                                    link.style.display = 'none';
                                    document.body.appendChild(link);
                                    link.click();
                                }
                            }, 500);
                        }
                    }, 500);
                }, 1000);
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
            console.error('Login error:', error.message);
            
            // Show detailed error notification
            notifications.show('Error connecting to server: ' + error.message, 'error');
            
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
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