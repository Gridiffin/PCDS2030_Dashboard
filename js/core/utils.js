/**
 * Utility Functions
 * Common utility functions used throughout the application
 */

/**
 * Format a date string into a more readable format
 * @param {string} dateString - The date string to format
 * @return {string} The formatted date
 */
export function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; // Return original if invalid
    
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Escape HTML to prevent XSS attacks
 * @param {string} unsafe - The unsafe HTML string
 * @return {string} The escaped HTML string
 */
export function escapeHtml(unsafe) {
    if (!unsafe) return '';
    
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Get a URL parameter by name
 * @param {string} name - The name of the parameter
 * @return {string|null} The parameter value, or null if not found
 */
export function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

/**
 * Toggle loading state for a button
 * @param {HTMLElement} button - The button element
 * @param {boolean} isLoading - Whether the button should show loading state
 * @param {string} originalText - The original button text
 */
export function toggleButtonLoading(button, isLoading, originalText) {
    if (isLoading) {
        button.disabled = true;
        button.setAttribute('data-original-text', button.innerHTML);
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    } else {
        button.disabled = false;
        button.innerHTML = originalText || button.getAttribute('data-original-text');
    }
}

/**
 * Apply staggered animation to elements
 * @param {NodeList|Array} elements - The elements to animate
 * @param {string} className - The CSS class name to apply
 * @param {number} delayBetween - Delay between animations (ms)
 */
export function animateElements(elements, className, delayBetween = 100) {
    Array.from(elements).forEach((el, index) => {
        setTimeout(() => {
            el.classList.add(className);
        }, index * delayBetween);
    });
}
