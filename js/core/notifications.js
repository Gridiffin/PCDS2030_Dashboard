/**
 * Notification System
 * Provides consistent notification display across the application
 */

// Create and export the notification functions
const notifications = {
    /**
     * Show a notification message
     * @param {string} message - The message to display
     * @param {string} type - The type of notification: 'success', 'error', 'warning', 'info'
     * @param {number} duration - How long to show the notification (in ms)
     */
    show: function(message, type = 'info', duration = 5000) {
        const notification = document.getElementById('notification');
        if (!notification) return;
        
        notification.textContent = message;
        notification.className = 'notification'; // Reset classes
        notification.classList.add(type);
        notification.style.display = 'block';
        
        // Hide after specified duration
        setTimeout(() => {
            notification.style.display = 'none';
        }, duration);
    },
    
    /**
     * Show a notification with an action link
     * @param {string} message - The message to display
     * @param {string} type - The type of notification
     * @param {string} linkText - The text for the action link
     * @param {string} linkUrl - The URL for the action link
     * @param {number} duration - How long to show the notification (in ms)
     */
    showWithAction: function(message, type = 'info', linkText, linkUrl, duration = 8000) {
        const notification = document.getElementById('notification');
        if (!notification) return;
        
        notification.innerHTML = `
            ${message} 
            <div class="notification-action">
                <a href="${linkUrl}" class="notification-link">${linkText} <i class="fas fa-arrow-right"></i></a>
            </div>
        `;
        notification.className = 'notification'; // Reset classes
        notification.classList.add(type);
        notification.classList.add('with-action'); // Add a class for styling
        notification.style.display = 'block';
        
        // Hide after specified duration
        setTimeout(() => {
            notification.style.display = 'none';
        }, duration);
    },
    
    /**
     * Hide the current notification
     */
    hide: function() {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }
};

// Export for module usage
export default notifications;
