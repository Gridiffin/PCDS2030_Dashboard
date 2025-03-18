/**
 * Metrics Definition tab functionality
 * Handles the creation, editing, and deletion of custom metrics
 */
import { currentUser, showNotification, escapeHtml, formatDataType, loadCurrentUser } from './metrics_core.js';

export default function initMetricsDefinitionTab() {
    // DOM elements
    const metricsTable = document.getElementById('metricsTable').querySelector('tbody');
    const noMetricsMessage = document.getElementById('noMetricsMessage');
    const metricsTableContainer = document.getElementById('metricsTableContainer');
    const metricsLoading = document.getElementById('metrics-loading');
    const addMetricBtn = document.getElementById('addMetricBtn');
    const metricForm = document.getElementById('metricForm');
    const metricFormSection = document.getElementById('metricFormSection');
    const formTitle = document.getElementById('formTitle');
    const cancelMetricBtn = document.getElementById('cancelMetricBtn');
    
    // Set up event listeners
    function setupEventListeners() {
        // Add metric button
        addMetricBtn.addEventListener('click', showAddMetricForm);
        
        // Cancel button
        cancelMetricBtn.addEventListener('click', hideMetricForm);
        
        // Form submission
        metricForm.addEventListener('submit', handleFormSubmit);
        
        // Listen for tab activation
        document.addEventListener('tabactivated', (e) => {
            if (e.detail.tabId === 'define') {
                // Reload metrics when tab becomes active
                loadMetrics();
            }
        });
    }
    
    // Load metrics from the server
    async function loadMetrics() {
        try {
            // Show loading state
            metricsLoading.style.display = 'flex';
            metricsTableContainer.style.display = 'none';
            noMetricsMessage.style.display = 'none';
            
            const response = await fetch('php/metrics/manage_custom_metrics.php?operation=getMetrics');
            const data = await response.json();
            
            // Hide loading state
            metricsLoading.style.display = 'none';
            
            if (!data.success || !data.data || data.data.length === 0) {
                noMetricsMessage.style.display = 'block';
                return;
            }
            
            metricsTableContainer.style.display = 'block';
            renderMetricsTable(data.data);
        } catch (error) {
            console.error('Error loading metrics:', error);
            showNotification('Error loading metrics: ' + error.message, 'error');
            
            // Hide loading state
            metricsLoading.style.display = 'none';
        }
    }
    
    // Render the metrics table
    function renderMetricsTable(metrics) {
        metricsTable.innerHTML = '';
        
        metrics.forEach(metric => {
            const row = document.createElement('tr');
            row.classList.add('animated-row');
            
            row.innerHTML = `
                <td>${escapeHtml(metric.MetricName)}</td>
                <td>${formatDataType(metric.DataType)}</td>
                <td>${escapeHtml(metric.Unit || '-')}</td>
                <td>${metric.IsRequired ? '<span class="status-success">Required</span>' : 'Optional'}</td>
                <td>${escapeHtml(metric.Description || '-')}</td>
                <td class="action-cell">
                    <button type="button" class="icon-button edit-btn" data-id="${metric.MetricID}" title="Edit Metric">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="icon-button report-btn" data-id="${metric.MetricID}" 
                            data-name="${escapeHtml(metric.MetricName)}" title="Report Data">
                        <i class="fas fa-chart-line"></i>
                    </button>
                    <button type="button" class="icon-button delete-btn" data-id="${metric.MetricID}" 
                            data-name="${escapeHtml(metric.MetricName)}" title="Delete Metric">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            metricsTable.appendChild(row);
        });
        
        // Add event listeners to action buttons
        addTableEventListeners();
    }
    
    // Add event listeners to table action buttons
    function addTableEventListeners() {
        // Add edit button listeners
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const metricId = this.getAttribute('data-id');
                editMetric(metricId);
            });
        });
        
        // Add delete button listeners
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const metricId = this.getAttribute('data-id');
                const metricName = this.getAttribute('data-name');
                confirmDeleteMetric(metricId, metricName);
            });
        });
        
        // Add report data button listeners
        document.querySelectorAll('.report-btn').forEach(button => {
            button.addEventListener('click', function() {
                const metricId = this.getAttribute('data-id');
                const metricName = this.getAttribute('data-name');
                redirectToReportForm(metricId, metricName);
            });
        });
    }
    
    // Function to redirect to the report form for a specific metric
    function redirectToReportForm(metricId, metricName) {
        // Navigate to the report tab with the selected metric ID
        const reportTab = document.querySelector('.tab[data-tab="report"]');
        if (reportTab) {
            // Store selected metric in sessionStorage
            sessionStorage.setItem('selectedMetricId', metricId);
            sessionStorage.setItem('selectedMetricName', metricName);
            
            // Trigger click on the report tab
            reportTab.click();
        } else {
            console.error('Report tab not found');
        }
    }
    
    // Form functions
    function showAddMetricForm() {
        // Reset form if it had previous data
        metricForm.reset();
        document.getElementById('metricId').value = '';
        
        // Update form title and show the form section
        formTitle.innerHTML = '<i class="fas fa-plus"></i> Add New Metric';
        metricFormSection.style.display = 'block';
        
        // Scroll to the form
        metricFormSection.scrollIntoView({ behavior: 'smooth' });
        
        // Focus on the first field
        document.getElementById('metricName').focus();
    }
    
    function hideMetricForm() {
        metricFormSection.style.display = 'none';
        metricForm.reset();
    }
    
    async function handleFormSubmit(e) {
        e.preventDefault();
        
        // Get form values
        const metricId = document.getElementById('metricId').value;
        const metricName = document.getElementById('metricName').value;
        const dataType = document.getElementById('metricType').value;
        const unit = document.getElementById('metricUnit').value;
        const isRequired = document.getElementById('isRequired').checked;
        const description = document.getElementById('metricDescription').value;
        
        // Validate required fields
        if (!metricName || !dataType) {
            showNotification('Please fill in all required fields', 'error');
            return;
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('metricName', metricName);
        formData.append('dataType', dataType);
        formData.append('unit', unit);
        formData.append('isRequired', isRequired ? '1' : '0');
        formData.append('description', description);
        
        // Determine if this is a new metric or an update
        const isUpdate = metricId !== '';
        
        if (isUpdate) {
            formData.append('metricId', metricId);
            formData.append('operation', 'updateMetric');
        } else {
            formData.append('operation', 'addMetric');
        }
        
        try {
            // Show loading state
            const submitBtn = metricForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            // Send form data to server
            const response = await fetch('php/metrics/manage_custom_metrics.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(data.message, 'success');
                hideMetricForm();
                await loadMetrics();
            } else {
                showNotification(data.message || 'Error saving metric', 'error');
            }
        } catch (error) {
            console.error('Error saving metric:', error);
            showNotification('Error: ' + error.message, 'error');
        } finally {
            // Restore button state
            const submitBtn = metricForm.querySelector('button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Metric';
        }
    }
    
    // Functions for editing metrics
    async function editMetric(metricId) {
        try {
            // Show loading state for the form
            formTitle.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading metric...';
            metricFormSection.style.display = 'block';
            
            // Fetch metric details
            const response = await fetch(`php/metrics/manage_custom_metrics.php?operation=getMetrics&id=${metricId}`);
            const data = await response.json();
            
            if (!data.success || !data.data) {
                showNotification('Failed to load metric details', 'error');
                hideMetricForm();
                return;
            }
            
            // Find the specific metric in the response
            const metric = data.data.find(m => m.MetricID == metricId);
            
            if (!metric) {
                showNotification('Metric not found', 'error');
                hideMetricForm();
                return;
            }
            
            // Populate form with metric data
            document.getElementById('metricId').value = metric.MetricID;
            document.getElementById('metricName').value = metric.MetricName;
            document.getElementById('metricType').value = metric.DataType;
            document.getElementById('metricUnit').value = metric.Unit || '';
            document.getElementById('isRequired').checked = metric.IsRequired == 1;
            document.getElementById('metricDescription').value = metric.Description || '';
            
            // Update form title
            formTitle.innerHTML = '<i class="fas fa-edit"></i> Edit Metric';
            
            // Scroll to form
            metricFormSection.scrollIntoView({ behavior: 'smooth' });
        } catch (error) {
            console.error('Error loading metric details:', error);
            showNotification('Error: ' + error.message, 'error');
            hideMetricForm();
        }
    }
    
    // Functions for deleting metrics
    function confirmDeleteMetric(metricId, metricName) {
        if (confirm(`Are you sure you want to delete the metric "${metricName}"? This may affect existing reports using this metric.`)) {
            deleteMetric(metricId);
        }
    }
    
    async function deleteMetric(metricId) {
        try {
            const formData = new FormData();
            formData.append('metricId', metricId);
            formData.append('operation', 'deleteMetric');
            
            const response = await fetch('php/metrics/manage_custom_metrics.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(data.message, 'success');
                await loadMetrics();
            } else {
                showNotification(data.message || 'Error deleting metric', 'error');
            }
        } catch (error) {
            console.error('Error deleting metric:', error);
            showNotification('Error: ' + error.message, 'error');
        }
    }
    
    // Initialize the module
    function init() {
        setupEventListeners();
        loadMetrics();
    }
    
    return {
        init,
        loadMetrics
    };
}
