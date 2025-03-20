// User Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard components
    fetchSubmissions();
    
    // Set up event listeners
    setupEventListeners();
});

/**
 * Fetch recent submissions for the dashboard
 */
function fetchSubmissions() {
    // Use the existing submission container if it exists
    const submissionsContainer = document.getElementById('recentSubmissions');
    
    // Check if container exists before trying to manipulate it
    if (!submissionsContainer) {
        console.log("Recent submissions container not found on this page");
        return; // Exit the function if container doesn't exist
    }
    
    // Show loading state
    submissionsContainer.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading submissions...</div>';
    
    // Fetch submissions data
    fetch('php/metrics/get_submissions.php?limit=5')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Clear loading state
            submissionsContainer.innerHTML = '';
            
            // Check if we have submissions
            if (data.submissions && data.submissions.length > 0) {
                // Process each submission
                data.submissions.forEach(submission => {
                    const submissionEl = document.createElement('div');
                    submissionEl.className = 'submission-item';
                    submissionEl.innerHTML = `
                        <div class="submission-title">
                            <span>${submission.metric_name}</span>
                            <span class="submission-date">${formatDate(submission.submission_date)}</span>
                        </div>
                        <div class="submission-status">
                            <span class="status-${submission.status.toLowerCase()}">${submission.status}</span>
                        </div>
                    `;
                    submissionsContainer.appendChild(submissionEl);
                });
            } else {
                // Show no data message
                submissionsContainer.innerHTML = '<div class="no-data">No recent submissions found.</div>';
            }
        })
        .catch(error => {
            console.error('Error fetching submissions:', error);
            submissionsContainer.innerHTML = '<div class="error">Error loading submissions. Please try again.</div>';
        });
}

/**
 * Format a date string to a more user-friendly format
 * @param {string} dateString - The date string to format
 * @returns {string} Formatted date string
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Set up event listeners for dashboard interactions
 */
function setupEventListeners() {
    const dashboardButtons = document.querySelectorAll('.dashboard-button');
    
    // Add hover effect to dashboard buttons
    dashboardButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Check for chart container to initialize charts
    const chartContainer = document.getElementById('summaryChart');
    if (chartContainer) {
        // Initialize charts or other visualizations
        initializeDashboardCharts();
    }
}

/**
 * Initialize dashboard charts and visualizations
 */
function initializeDashboardCharts() {
    // This is a placeholder for chart initialization
    // You would typically use a library like Chart.js or D3.js here
    console.log('Charts would be initialized here');
    
    // If chart libraries are available, add your chart code here
    
    // Example using a simple placeholder:
    const chartContainer = document.getElementById('summaryChart');
    if (chartContainer) {
        chartContainer.innerHTML = `
            <div class="chart-placeholder">
                <img src="assets/images/chart_placeholder.png" alt="Summary Chart">
            </div>
        `;
    }
}
