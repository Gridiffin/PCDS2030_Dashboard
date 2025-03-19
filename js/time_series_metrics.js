import { showNotification, escapeHtml, formatDate } from './modules/metrics/metrics_core.js';
import TimeSeriesMetrics from './modules/metrics/time_series_metrics.js';

document.addEventListener('DOMContentLoaded', async function() {
    // Initialize the time series metrics functionality
    const timeSeriesApp = TimeSeriesMetrics();
    timeSeriesApp.init();
    
    // Handle modal close
    const modal = document.getElementById('detailModal');
    const closeBtn = document.querySelector('.close-modal');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }
    
    // Close modal when clicking the footer button
    const footerBtn = document.querySelector('.modal-footer .modal-button');
    if (footerBtn) {
        footerBtn.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside content
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    });
});
