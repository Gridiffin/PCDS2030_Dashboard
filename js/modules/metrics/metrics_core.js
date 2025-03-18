/**
 * Core functionality for metrics management
 * Contains shared utilities, state management, and common functions
 */

// User and agency information - central store
const currentUser = {
    id: null,
    username: '',
    agencyId: null,
    agencyName: '',
    customMetrics: []
};

// Toast notification display
function showNotification(message, type) {
    const toastElement = document.getElementById('toast-notification');
    const messageElement = document.getElementById('toast-message');
    
    if (!toastElement || !messageElement) {
        console.error('Toast notification elements not found');
        return;
    }
    
    messageElement.textContent = message;
    
    // Reset classes and add the type
    toastElement.className = 'toast-notification';
    toastElement.classList.add(type);
    
    // Set the proper icon
    const iconElement = toastElement.querySelector('i');
    if (iconElement) {
        iconElement.className = 'fas';
        switch(type) {
            case 'success':
                iconElement.classList.add('fa-check-circle');
                break;
            case 'error':
                iconElement.classList.add('fa-exclamation-circle');
                break;
            case 'warning':
                iconElement.classList.add('fa-exclamation-triangle');
                break;
            default:
                iconElement.classList.add('fa-info-circle');
        }
    }
    
    // Show the notification
    toastElement.classList.add('show');
    
    // Hide after 5 seconds
    setTimeout(() => {
        toastElement.classList.remove('show');
    }, 5000);
}

// Helper function to escape HTML to prevent XSS
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Format date for display
function formatDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) {
        return dateString;
    }
    
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Function to format data type for display
function formatDataType(dataType) {
    switch(dataType) {
        case 'number':
            return 'Number';
        case 'text':
            return 'Text';
        case 'currency':
            return 'Currency';
        case 'percentage':
            return 'Percentage';
        case 'date':
            return 'Date';
        default:
            return dataType;
    }
}

// Load current user data
async function loadCurrentUser() {
    try {
        const response = await fetch('php/auth/get_current_user.php');
        const data = await response.json();
        
        if (!data.success || !data.user) {
            showNotification('Failed to load user data', 'error');
            return false;
        }

        const userData = data.user;

        // Set current user data
        currentUser.id = userData.id;
        currentUser.username = userData.username;
        currentUser.agencyId = userData.agencyId;
        currentUser.agencyName = userData.agencyName;

        // Update UI with user data
        document.getElementById('username').textContent = currentUser.username;
        document.getElementById('agency-badge').textContent = currentUser.agencyName;
        
        return true;
    } catch (error) {
        console.error('Error loading user data:', error);
        showNotification('Error loading user data: ' + error.message, 'error');
        return false;
    }
}

// Tab navigation system with improved handling
function initTabSystem() {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Event listeners - Tab navigation
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent any default action
            const tabId = tab.getAttribute('data-tab');
            switchTab(tabId);
            
            // Add ripple effect for better visual feedback
            createRipple(e, tab);
        });
    });
    
    function switchTab(tabId) {
        // Update active tab button
        tabs.forEach(tab => {
            if(tab.getAttribute('data-tab') === tabId) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });
        
        // Show the active tab content with smooth transitions
        tabContents.forEach(content => {
            if(content.id === tabId + '-tab') {
                // First make it display: block but still invisible
                content.style.display = 'block';
                
                // Force browser reflow
                void content.offsetWidth;
                
                // Now fade it in
                content.classList.add('active');
                
                // Call tab-specific initialization code
                const event = new CustomEvent('tabactivated', { 
                    detail: { tabId: tabId }
                });
                document.dispatchEvent(event);
            } else {
                content.classList.remove('active');
                
                // After transition, set display to none
                setTimeout(() => {
                    if (!content.classList.contains('active')) {
                        content.style.display = 'none';
                    }
                }, 300); // Match the transition duration in CSS
            }
        });
    }
    
    function createRipple(e, element) {
        const circle = document.createElement('span');
        const diameter = Math.max(element.clientWidth, element.clientHeight);
        
        const rect = element.getBoundingClientRect();
        const x = e.clientX - rect.left - (diameter / 2);
        const y = e.clientY - rect.top - (diameter / 2);
        
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${x}px`;
        circle.style.top = `${y}px`;
        circle.classList.add('ripple');
        
        // Remove existing ripples
        const ripple = element.getElementsByClassName('ripple')[0];
        if (ripple) {
            ripple.remove();
        }
        
        element.appendChild(circle);
        
        // Remove the ripple element after animation completes
        setTimeout(() => {
            if (circle.parentElement === element) {
                element.removeChild(circle);
            }
        }, 600);
    }
    
    // Initialize first tab
    if (tabs.length > 0) {
        const firstTabId = tabs[0].getAttribute('data-tab');
        switchTab(firstTabId);
    }
    
    // Return the switchTab function for external use
    return switchTab;
}

// Set up modal functionality with improved handling
function initModal() {
    const modal = document.getElementById("detailModal");
    const closeBtn = document.querySelector(".close-modal");
    const footerBtn = document.querySelector(".modal-footer .modal-button");
    
    // Close when clicking the x button
    if (closeBtn) {
        closeBtn.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            modal.classList.remove("active");
        });
    }
    
    // Close when clicking the footer button
    if (footerBtn) {
        footerBtn.addEventListener("click", (e) => {
            e.preventDefault();
            modal.classList.remove("active");
        });
    }
    
    // Close when clicking outside the modal content
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.classList.remove("active");
        }
    });
    
    return {
        show: function(title, content) {
            document.getElementById("modalTitle").textContent = title;
            document.getElementById("modalBody").innerHTML = content;
            modal.classList.add("active");
        },
        hide: function() {
            modal.classList.remove("active");
        }
    };
}

// Export functions and objects for use in other modules
export {
    currentUser,
    showNotification, 
    escapeHtml,
    formatDate,
    formatDataType,
    loadCurrentUser,
    initTabSystem,
    initModal
};
