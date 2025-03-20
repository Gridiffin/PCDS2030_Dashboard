/**
 * API Client
 * Centralized client for making API requests
 */

/**
 * Default options for fetch requests
 */
const defaultOptions = {
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    credentials: 'same-origin'
};

/**
 * API endpoints
 */
const endpoints = {
    // Auth endpoints
    auth: {
        login: 'php/auth/login.php',
        logout: 'php/auth/logout.php',
        getCurrentUser: 'php/auth/get_current_user.php'
    },
    
    // Metrics endpoints
    metrics: {
        getSubmissions: 'php/metrics/get_submissions.php',
        getDrafts: 'php/metrics/get_drafts.php',
        saveTargetStatus: 'php/metrics/save_target_status.php',
        getSubmissionDetails: 'php/metrics/get_submission_details.php',
        deleteSubmission: 'php/metrics/delete_submission.php',
        getPrograms: 'php/metrics/get_programs.php',
        checkDraftExists: 'php/metrics/check_draft_exists.php'
    },
    
    // Custom metrics endpoints
    customMetrics: {
        getMetrics: 'php/metrics/get_custom_metrics.php',
        saveMetric: 'php/metrics/save_custom_metric.php',
        deleteMetric: 'php/metrics/delete_custom_metric.php',
        getReports: 'php/metrics/get_custom_metrics_reports.php',
        saveReport: 'php/metrics/save_custom_metrics_report.php',
        deleteReport: 'php/metrics/delete_custom_metrics_report.php'
    },
    
    // Admin endpoints
    admin: {
        getUsers: 'php/admin/manage_users.php?operation=get',
        getRoles: 'php/admin/manage_users.php?operation=getRoles',
        getAgencies: 'php/admin/manage_users.php?operation=getAgencies',
        addUser: 'php/admin/manage_users.php' // operation=add sent in formData
    }
};

/**
 * Generic error handler for fetch requests
 */
function handleError(error) {
    console.error('API request error:', error);
    throw error;
}

/**
 * API Client object
 */
const apiClient = {
    /**
     * Make a GET request
     * @param {string} url - The URL to request
     * @param {Object} options - Additional fetch options
     * @returns {Promise} The fetch promise
     */
    get: function(url, options = {}) {
        return fetch(url, { 
            ...defaultOptions, 
            ...options, 
            method: 'GET' 
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .catch(handleError);
    },
    
    /**
     * Make a POST request with JSON data
     * @param {string} url - The URL to request
     * @param {Object} data - The data to send
     * @param {Object} options - Additional fetch options
     * @returns {Promise} The fetch promise
     */
    post: function(url, data, options = {}) {
        return fetch(url, {
            ...defaultOptions,
            ...options,
            method: 'POST',
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .catch(handleError);
    },
    
    /**
     * Make a POST request with FormData
     * @param {string} url - The URL to request
     * @param {FormData} formData - The FormData to send
     * @param {Object} options - Additional fetch options
     * @returns {Promise} The fetch promise
     */
    postForm: function(url, formData, options = {}) {
        // Remove Content-Type header so browser can set it with boundary
        const formOptions = { ...defaultOptions };
        delete formOptions.headers['Content-Type'];
        
        return fetch(url, {
            ...formOptions,
            ...options,
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .catch(handleError);
    },

    // Expose endpoints for use
    endpoints
};

// Export the API client
export default apiClient;
