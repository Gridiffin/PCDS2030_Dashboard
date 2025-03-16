document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');
    const notification = document.getElementById('notification');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

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

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = usernameInput.value;
        const password = passwordInput.value;

        // Simple validation
        if (!username || !password) {
            showError('Please fill in all fields');
            return;
        }

        // Show loading state on the button
        const submitButton = loginForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        submitButton.disabled = true;

        // Make AJAX request to the server
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);

        fetch('php/auth/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message, data.redirect);
            } else {
                showError(data.message);
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        })
        .catch(error => {
            showError('An error occurred. Please try again.');
            console.error('Error:', error);
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 3000);
    }

    function showNotification(message, redirectUrl) {
        notification.textContent = message;
        notification.style.display = 'block';
        loadingSpinner.style.display = 'inline-block';
        setTimeout(() => {
            notification.style.display = 'none';
            loadingSpinner.style.display = 'none';
            window.location.href = redirectUrl;
        }, 2000);
    }

    // Add password toggle functionality - improved with animation
    const passwordToggle = document.getElementById('passwordToggle');

    passwordToggle.addEventListener('click', function() {
        // Toggle the password visibility
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle the eye icon with a smooth transition
        if (type === 'text') {
            this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            this.classList.add('visible');
            this.title = "Hide password";
        } else {
            this.innerHTML = '<i class="fas fa-eye"></i>';
            this.classList.remove('visible');
            this.title = "Show password";
        }
        
        // Add focus back to password field for better UX
        passwordInput.focus();
    });
});