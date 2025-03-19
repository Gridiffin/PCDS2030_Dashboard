/**
 * Metrics Reporting tab functionality
 * Handles reporting data for custom metrics
 */
import { currentUser, showNotification, escapeHtml, formatDate } from './metrics_core.js';

export default function initMetricsReportingTab() {
    // DOM elements
    const elements = {
        reportForm: document.getElementById('reportForm'),
        reportLoading: document.getElementById('report-loading'),
        metricValuesContainer: document.getElementById('metricValuesContainer'),
        noMetricsMessage: document.getElementById('noMetricsForReportMessage'),
        reportsTable: document.getElementById('reportsTable')?.querySelector('tbody'),
        reportsContainer: document.getElementById('reportsTableContainer'),
        reportsLoading: document.getElementById('reports-loading'),
        noReportsMessage: document.getElementById('noReportsMessage'),
        saveAsDraftBtn: document.getElementById('saveAsDraftBtn'),
        selectedMetricInfo: document.getElementById('selectedMetricInfo'),
        metricSelector: document.getElementById('metricSelector'),
        backToMetricsBtn: document.getElementById('backToMetricsBtn')
    };
    
    // State tracking
    const state = {
        metricsLoaded: false,
        loadingInProgress: false,
        selectedMetricId: null
    };
    
    /**
     * INITIALIZATION & SETUP
     */
    
    // Initialize the module
    function init() {
        console.log("Initializing metrics reporting tab...");
        setupEventListeners();
        
        // Check for selected metric from definition tab
        const selectedMetricId = sessionStorage.getItem('selectedMetricId');
        const selectedMetricName = sessionStorage.getItem('selectedMetricName');
        
        if (selectedMetricId && selectedMetricName) {
            // Clear session storage to avoid persisting between page loads
            sessionStorage.removeItem('selectedMetricId');
            sessionStorage.removeItem('selectedMetricName');
            
            // Set as selected metric and skip the metric selector entirely
            state.selectedMetricId = selectedMetricId;
            setSelectedMetric(selectedMetricId, selectedMetricName);
        } else {
            // Show metric selector mode (only when there's no specific metric selected)
            showMetricSelector();
        }
        
        // Initialize if already on the report tab
        if (document.getElementById('report-tab')?.classList.contains('active')) {
            setTimeout(() => {
                loadAvailableMetrics();
                loadPreviousReports();
            }, 100);
        }
    }
    
    // Set up event listeners
    function setupEventListeners() {
        // Form submission events
        if (elements.reportForm) {
            elements.reportForm.addEventListener('submit', handleReportSubmit);
        }
        
        if (elements.saveAsDraftBtn) {
            elements.saveAsDraftBtn.addEventListener('click', saveReportAsDraft);
        }
        
        // Back button to return to metric selector
        if (elements.backToMetricsBtn) {
            elements.backToMetricsBtn.addEventListener('click', showMetricSelector);
        }
        
        // Metric selector change
        if (elements.metricSelector) {
            elements.metricSelector.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (this.value) {
                    setSelectedMetric(this.value, selectedOption.textContent);
                }
            });
        }
        
        // Tab activation
        document.addEventListener('tabactivated', (e) => {
            if (e.detail.tabId === 'report') {
                loadAvailableMetrics();
                loadPreviousReports();
            }
        });
    }
    
    // Load available metrics for the selector - modified to return a Promise
    async function loadAvailableMetrics() {
        try {
            const response = await fetch('php/metrics/manage_custom_metrics.php?operation=getMetrics');
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                // Store metrics for later use
                currentUser.customMetrics = data.data;
                console.log(`Loaded ${data.data.length} metrics`, data.data);
                
                // Update the metric selector dropdown
                if (elements.metricSelector) {
                    // Clear existing options except the default
                    elements.metricSelector.innerHTML = '<option value="">Select a metric to report</option>';
                    
                    // Add metrics to selector
                    data.data.forEach(metric => {
                        const option = document.createElement('option');
                        option.value = metric.MetricID;
                        option.textContent = metric.MetricName;
                        elements.metricSelector.appendChild(option);
                    });
                    
                    // Show metric selector section only if no specific metric is selected
                    if (!state.selectedMetricId) {
                        document.getElementById('metricSelectorSection').style.display = 'block';
                    }
                    
                    // Hide any error messages
                    if (document.getElementById('noMetricsForSelectorMessage')) {
                        document.getElementById('noMetricsForSelectorMessage').style.display = 'none';
                    }
                }
            } else {
                // No metrics available
                console.log('No metrics available or error in response');
                if (elements.noMetricsMessage) {
                    elements.noMetricsMessage.style.display = 'block';
                }
                document.getElementById('metricSelectorSection').style.display = 'block';
                document.getElementById('noMetricsForSelectorMessage').style.display = 'block';
            }
            
            return true;
        } catch (error) {
            console.error('Error loading available metrics:', error);
            showNotification('Error loading metrics: ' + error.message, 'error');
            return false;
        }
    }
    
    // Show the metric selector view
    function showMetricSelector() {
        // Hide report form and show selector
        state.selectedMetricId = null;
        document.getElementById('metricSelectorSection').style.display = 'block';
        document.getElementById('singleMetricReportSection').style.display = 'none';
        
        // Reset form if it exists
        if (elements.reportForm) {
            elements.reportForm.reset();
        }
    }
    
    // Set the selected metric and show the report form
    function setSelectedMetric(metricId, metricName) {
        state.selectedMetricId = metricId;
        
        // Update the selected metric info display
        if (elements.selectedMetricInfo) {
            elements.selectedMetricInfo.textContent = metricName;
        }
        
        // Show the report section and hide selector - always hide selector when a metric is selected
        document.getElementById('metricSelectorSection').style.display = 'none';
        document.getElementById('singleMetricReportSection').style.display = 'block';
        
        // Show loading state while rendering the field
        if (elements.reportLoading) {
            elements.reportLoading.style.display = 'flex';
        }
        
        if (elements.reportForm) {
            elements.reportForm.style.display = 'none';
        }
        
        // Ensure metrics are loaded even if we bypassed the selector
        if (!currentUser.customMetrics || currentUser.customMetrics.length === 0) {
            loadAvailableMetrics().then(() => {
                renderSelectedMetricField();
            });
        } else {
            // Add a slight delay to ensure DOM updates
            setTimeout(() => {
                renderSelectedMetricField();
            }, 50);
        }
        
        // Also load previous reports filtered for this specific metric
        loadPreviousReports();
    }
    
    // Render the input field for the selected metric
    function renderSelectedMetricField() {
        if (!elements.metricValuesContainer || !state.selectedMetricId) {
            console.error("Missing container element or metric ID");
            return;
        }
        
        console.log(`Rendering field for metric ID: ${state.selectedMetricId}`);
        
        // Clear the container
        elements.metricValuesContainer.innerHTML = '';
        
        // Find the selected metric in our stored metrics
        const metric = currentUser.customMetrics.find(m => m.MetricID == state.selectedMetricId);
        
        if (!metric) {
            console.error('Selected metric not found in currentUser.customMetrics');
            elements.metricValuesContainer.innerHTML = '<p>Error: Selected metric not found</p>';
            
            // Hide loading and show form even on error
            if (elements.reportLoading) {
                elements.reportLoading.style.display = 'none';
            }
            
            if (elements.reportForm) {
                elements.reportForm.style.display = 'block';
            }
            
            return;
        }
        
        console.log('Found metric:', metric);
        
        // Create a card for this metric
        createMetricCard(metric, 0);
        
        // Hide loading and show form
        if (elements.reportLoading) {
            elements.reportLoading.style.display = 'none';
        }
        
        if (elements.reportForm) {
            elements.reportForm.style.display = 'block';
        }
        
        // Auto-set today's date if the date field is empty
        const dateField = document.getElementById('reportDate');
        if (dateField && !dateField.value) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            dateField.value = `${yyyy}-${mm}-${dd}`;
        }
        
        // Focus on the first input field
        setTimeout(() => {
            const firstInput = elements.metricValuesContainer.querySelector('input, textarea, select');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }
    
    // Create a single metric card
    function createMetricCard(metric, index) {
        // Skip if card already exists
        const existingCard = document.querySelector(`.metric-card[data-metric-id="${metric.MetricID}"]`);
        if (existingCard) {
            console.log(`Metric card ${metric.MetricID} already exists, skipping`);
            return;
        }
        
        console.log(`Creating card for metric: ${metric.MetricName}`);
        
        const metricCard = document.createElement('div');
        metricCard.className = 'metric-card animated-row';
        metricCard.setAttribute('data-metric-id', metric.MetricID);
        
        // Generate card content
        const titleHtml = generateTitleHtml(metric);
        const inputHtml = generateInputHtml(metric);
        const descriptionHtml = metric.Description ? 
            `<small class="form-hint">${escapeHtml(metric.Description)}</small>` : '';
        
        metricCard.innerHTML = `
            <h4>${titleHtml}</h4>
            ${inputHtml}
            ${descriptionHtml}
        `;
        
        // Add to container with animation
        elements.metricValuesContainer.appendChild(metricCard);
        applyCardAnimation(metricCard, index);
    }
    
    // Generate metric title with required indicator if needed
    function generateTitleHtml(metric) {
        let title = metric.MetricName;
        if (metric.IsRequired == 1) {
            title += '<span class="required"> *</span>';
        }
        return title;
    }
    
    // Generate input field HTML based on data type
    function generateInputHtml(metric) {
        const required = metric.IsRequired == 1 ? 'required' : '';
        
        switch (metric.DataType) {
            case 'number':
            case 'currency':
                if (metric.Unit) {
                    return `
                        <div class="input-group">
                            <input type="number" 
                                id="metric_${metric.MetricID}" 
                                name="metric_${metric.MetricKey}" 
                                step="${metric.DataType === 'currency' ? '0.01' : '1'}" 
                                placeholder="Enter value"
                                ${required}>
                            <span class="input-group-text">${escapeHtml(metric.Unit)}</span>
                        </div>
                    `;
                } else {
                    return `
                        <input type="number" 
                            id="metric_${metric.MetricID}" 
                            name="metric_${metric.MetricKey}" 
                            step="${metric.DataType === 'currency' ? '0.01' : '1'}" 
                            placeholder="Enter value"
                            ${required}>
                    `;
                }
                
            case 'text':
                return `
                    <textarea id="metric_${metric.MetricID}" 
                        name="metric_${metric.MetricKey}" 
                        rows="2" 
                        placeholder="Enter value"
                        ${required}></textarea>
                `;
                
            case 'percentage':
                return `
                    <div class="input-group">
                        <input type="number" 
                            id="metric_${metric.MetricID}" 
                            name="metric_${metric.MetricKey}" 
                            min="0" 
                            max="100" 
                            step="0.01" 
                            placeholder="Enter percentage"
                            ${required}>
                        <span class="input-group-text">%</span>
                    </div>
                `;
                
            case 'date':
                return `
                    <input type="date" 
                        id="metric_${metric.MetricID}" 
                        name="metric_${metric.MetricKey}"
                        ${required}>
                `;
                
            default:
                return `
                    <input type="text" 
                        id="metric_${metric.MetricID}" 
                        name="metric_${metric.MetricKey}" 
                        placeholder="Enter value"
                        ${required}>
                `;
        }
    }
    
    // Apply animation to a card
    function applyCardAnimation(card, index) {
        card.style.opacity = '0';
        setTimeout(() => {
            card.style.opacity = '1';
        }, 50 * index);
    }
    
    /**
     * FORM SUBMISSION
     */
    
    // Handle report form submission
    async function handleReportSubmit(e) {
        e.preventDefault();
        
        if (!validateReportForm()) return false;
        
        const reportData = collectReportData(false);
        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        try {
            // Set loading state
            toggleButtonLoading(submitBtn, true, '<i class="fas fa-spinner fa-spin"></i> Submitting...');
            
            // Submit data
            const response = await submitReport(reportData);
            
            if (response.success) {
                showNotification('Report submitted successfully', 'success');
                elements.reportForm.reset();
                
                // Reset to metric selector after successful submission
                setTimeout(() => {
                    showMetricSelector();
                    loadPreviousReports();
                }, 500);
            } else {
                showNotification(response.message || 'Error submitting report', 'error');
            }
        } catch (error) {
            console.error('Report submission error:', error);
            showNotification('Error: ' + error.message, 'error');
        } finally {
            toggleButtonLoading(submitBtn, false, '<i class="fas fa-paper-plane"></i> Submit Report');
        }
    }
    
    // Save report as draft
    async function saveReportAsDraft() {
        const reportData = collectReportData(true);
        
        try {
            // Set loading state
            toggleButtonLoading(elements.saveAsDraftBtn, true, 
                '<i class="fas fa-spinner fa-spin"></i> Saving...');
            
            // Submit data
            const response = await submitReport(reportData);
            
            if (response.success) {
                showNotification('Draft saved successfully', 'success');
                setTimeout(loadPreviousReports, 500);
            } else {
                showNotification(response.message || 'Error saving draft', 'error');
            }
        } catch (error) {
            console.error('Draft save error:', error);
            showNotification('Error: ' + error.message, 'error');
        } finally {
            toggleButtonLoading(elements.saveAsDraftBtn, false, 
                '<i class="fas fa-save"></i> Save as Draft');
        }
    }
    
    // Toggle button loading state
    function toggleButtonLoading(button, isLoading, text) {
        button.disabled = isLoading;
        button.innerHTML = text;
    }
    
    // Validate report form
    function validateReportForm() {
        const requiredFields = elements.reportForm.querySelectorAll('[required]');
        for (const field of requiredFields) {
            if (!field.value.trim()) {
                showNotification(`Please fill in all required fields`, 'error');
                field.focus();
                return false;
            }
        }
        return true;
    }
    
    // Collect report form data - modified to include only the selected metric
    function collectReportData(isDraft) {
        const data = {
            year: document.getElementById('reportYear').value,
            quarter: document.getElementById('reportQuarter').value,
            reportDate: document.getElementById('reportDate').value,
            notes: document.getElementById('reportNotes').value,
            metricsData: {},
            isDraft,
            metricId: state.selectedMetricId // Add the specific metric ID
        };
        
        // Add report ID if editing a draft
        const reportId = document.getElementById('reportId')?.value;
        if (reportId) {
            data.reportId = reportId;
        }
        
        // Only collect data for the selected metric
        const selectedMetric = currentUser.customMetrics.find(m => m.MetricID == state.selectedMetricId);
        if (selectedMetric) {
            const field = document.getElementById(`metric_${selectedMetric.MetricID}`);
            if (field && field.value.trim() !== '') {
                data.metricsData[selectedMetric.MetricKey] = field.value.trim();
            }
        }
        
        return data;
    }
    
    // Submit report data to server
    function submitReport(reportData) {
        return fetch('php/metrics/save_custom_metrics_report.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(reportData)
        })
        .then(response => response.json());
    }
    
    /**
     * PREVIOUS REPORTS TABLE
     */
    
    // Load previous reports - modified to filter by selected metric when one is selected
    async function loadPreviousReports() {
        if (!elements.reportsTable) return;
        
        try {
            // Show loading state
            elements.reportsLoading.style.display = 'flex';
            elements.reportsContainer.style.display = 'none';
            
            // Fetch reports, include metric ID if one is selected
            let url = 'php/metrics/get_custom_metrics_reports.php';
            if (state.selectedMetricId) {
                url += `?metricId=${state.selectedMetricId}`;
            }
            
            const data = await fetch(url).then(response => response.json());
            
            // Hide loading state
            elements.reportsLoading.style.display = 'none';
            elements.reportsContainer.style.display = 'block';
            
            // Clear existing rows
            elements.reportsTable.innerHTML = '';
            
            // Handle no reports case
            if (!data.success || !data.data || data.data.length === 0) {
                if (elements.noReportsMessage) {
                    elements.noReportsMessage.style.display = 'block';
                }
                return;
            }
            
            // Hide no reports message
            if (elements.noReportsMessage) {
                elements.noReportsMessage.style.display = 'none';
            }
            
            // Render reports
            renderReportsTable(data.data);
            
        } catch (error) {
            console.error('Error loading reports:', error);
            elements.reportsLoading.style.display = 'none';
            elements.reportsTable.innerHTML = `<tr><td colspan="5">Error loading reports: ${error.message}</td></tr>`;
        }
    }
    
    // Render reports table
    function renderReportsTable(reports) {
        reports.forEach((report, index) => {
            const row = createReportRow(report, index);
            elements.reportsTable.appendChild(row);
        });
        
        addReportActionListeners();
    }
    
    // Create a row for a report
    function createReportRow(report, index) {
        const row = document.createElement('tr');
        row.classList.add('animated-row');
        row.style.animationDelay = `${index * 50}ms`;
        
        const statusBadge = report.isDraft ? 
            '<span class="status-badge draft">Draft</span>' : 
            '<span class="status-badge completed">Submitted</span>';
        
        const actionButtons = `
            <button type="button" class="icon-button view-btn" data-id="${report.id}" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            ${report.isDraft ? `
                <button type="button" class="icon-button edit-btn" data-id="${report.id}" title="Edit Draft">
                    <i class="fas fa-edit"></i>
                </button>
            ` : ''}
            <button type="button" class="icon-button delete-btn" data-id="${report.id}" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        `;
        
        row.innerHTML = `
            <td>${escapeHtml(report.metricName || 'Unknown Metric')}</td>
            <td>${report.year} ${report.quarter}</td>
            <td>${formatDate(report.reportDate)}</td>
            <td>${statusBadge}</td>
            <td class="action-cell">${actionButtons}</td>
        `;
        
        return row;
    }
    
    // Add event listeners to report table buttons
    function addReportActionListeners() {
        // View buttons
        document.querySelectorAll('#reportsTable .view-btn').forEach(button => {
            button.addEventListener('click', function() {
                viewReportDetails(this.getAttribute('data-id'));
            });
        });
        
        // Edit buttons
        document.querySelectorAll('#reportsTable .edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                editReport(this.getAttribute('data-id'));
            });
        });
        
        // Delete buttons
        document.querySelectorAll('#reportsTable .delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                confirmDeleteReport(this.getAttribute('data-id'));
            });
        });
    }
    
    /**
     * REPORT OPERATIONS
     */
    
    // View report details in modal
    async function viewReportDetails(reportId) {
        try {
            const data = await fetch(`php/metrics/get_custom_metrics_report.php?id=${reportId}`)
                .then(response => response.json());
                
            if (!data.success || !data.data) {
                showNotification('Failed to load report details', 'error');
                return;
            }
            
            const report = data.data;
            const content = await buildReportDetailsContent(report);
            
            // Set modal content
            document.getElementById('modalTitle').textContent = `${report.year} ${report.quarter} Report`;
            document.getElementById('modalBody').innerHTML = content;
            
            // Show modal
            document.getElementById('detailModal').classList.add('active');
        } catch (error) {
            console.error('Error loading report details:', error);
            showNotification('Error loading report: ' + error.message, 'error');
        }
    }
    
    // Build report details content for modal
    async function buildReportDetailsContent(report) {
        let content = `
            <div class="modal-section">
                <h4>Report Information</h4>
                <p><strong>Period:</strong> ${report.year} ${report.quarter}</p>
                <p><strong>Report Date:</strong> ${formatDate(report.reportDate)}</p>
                <p><strong>Status:</strong> ${report.isDraft ? 
                    '<span class="status-badge draft">Draft</span>' : 
                    '<span class="status-badge completed">Submitted</span>'}</p>
                ${report.notes ? `<p><strong>Notes:</strong> ${report.notes}</p>` : ''}
            </div>
            
            <div class="modal-section">
                <h4>Metrics</h4>
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Add metric values
        if (report.metricsData && Object.keys(report.metricsData).length > 0) {
            const metricsMap = await getMetricsMap();
            content += buildMetricRowsHtml(report.metricsData, metricsMap);
        } else {
            content += `<tr><td colspan="2" style="text-align:center;">No metrics data available</td></tr>`;
        }
        
        content += `</tbody></table></div>`;
        return content;
    }
    
    // Get a map of metric keys to names/units
    async function getMetricsMap() {
        try {
            const response = await fetch('php/metrics/manage_custom_metrics.php?operation=getMetrics');
            const data = await response.json();
            
            if (data.success && data.data) {
                const metricMap = {};
                data.data.forEach(metric => {
                    metricMap[metric.MetricKey] = {
                        name: metric.MetricName,
                        unit: metric.Unit
                    };
                });
                return metricMap;
            }
        } catch (error) {
            console.error('Error fetching metrics:', error);
        }
        
        return {}; // Return empty object if fetch fails
    }
    
    // Build HTML for metric rows in report details
    function buildMetricRowsHtml(metricsData, metricMap) {
        let html = '';
        
        Object.keys(metricsData).forEach(key => {
            const metricName = metricMap[key]?.name || key;
            const metricUnit = metricMap[key]?.unit || '';
            const value = metricsData[key];
            
            html += `
                <tr>
                    <td>${escapeHtml(metricName)}</td>
                    <td>${escapeHtml(value)} ${metricUnit ? escapeHtml(metricUnit) : ''}</td>
                </tr>
            `;
        });
        
        return html;
    }
    
    // Edit a report (for drafts only)
    async function editReport(reportId) {
        try {
            const response = await fetch(`php/metrics/get_custom_metrics_report.php?id=${reportId}`);
            const data = await response.json();
            
            if (!data.success || !data.data) {
                showNotification('Failed to load report for editing', 'error');
                return;
            }
            
            const report = data.data;
            
            // Only allow editing drafts
            if (!report.isDraft) {
                showNotification('Only draft reports can be edited', 'warning');
                return;
            }
            
            // Populate form fields
            populateReportForm(report);
            
            // Scroll to form
            elements.reportForm.scrollIntoView({ behavior: 'smooth' });
            showNotification('Draft loaded for editing', 'success');
        } catch (error) {
            console.error('Error loading report for editing:', error);
            showNotification('Error: ' + error.message, 'error');
        }
    }
    
    // Populate the form with report data
    function populateReportForm(report) {
        // Set basic fields
        document.getElementById('reportYear').value = report.year;
        document.getElementById('reportQuarter').value = report.quarter;
        document.getElementById('reportDate').value = report.reportDate;
        document.getElementById('reportNotes').value = report.notes;
        
        // Add report ID field
        let reportIdField = document.getElementById('reportId');
        if (!reportIdField) {
            reportIdField = document.createElement('input');
            reportIdField.type = 'hidden';
            reportIdField.id = 'reportId';
            elements.reportForm.appendChild(reportIdField);
        }
        reportIdField.value = report.id;
        
        // Populate metric values when available
        const checkMetricsInterval = setInterval(() => {
            if (document.querySelectorAll('#metricValuesContainer .metric-card').length > 0) {
                clearInterval(checkMetricsInterval);
                populateMetricValues(report.metricsData);
            }
        }, 100);
    }
    
    // Populate metric values in the form
    function populateMetricValues(metricsData) {
        if (!metricsData) return;
        
        Object.keys(metricsData).forEach(key => {
            const metric = currentUser.customMetrics.find(m => m.MetricKey === key);
            if (metric) {
                const field = document.getElementById(`metric_${metric.MetricID}`);
                if (field) {
                    field.value = metricsData[key];
                }
            }
        });
    }
    
    // Confirm report deletion
    async function confirmDeleteReport(reportId) {
        try {
            const response = await fetch(`php/metrics/get_custom_metrics_report.php?id=${reportId}`);
            const data = await response.json();
            
            if (!data.success || !data.data) {
                showNotification('Failed to load report details', 'error');
                return;
            }
            
            const report = data.data;
            
            // Build confirmation modal
            showDeleteConfirmation(report, reportId);
        } catch (error) {
            console.error('Error fetching report details:', error);
            showNotification('Error: ' + error.message, 'error');
        }
    }
    
    // Show delete confirmation modal
    function showDeleteConfirmation(report, reportId) {
        const title = report.isDraft ? 'Delete Draft' : 'Delete Report';
        const content = `
            <div class="modal-section">
                <h4>${title}</h4>
                <p>Are you sure you want to delete the ${report.isDraft ? 'draft' : 'report'} for <strong>${report.year} ${report.quarter}</strong>?</p>
                <p class="warning-text"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                <div class="form-actions" style="justify-content: flex-end;">
                    <button id="cancelDelete" class="secondary-button">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button id="confirmDelete" class="delete-button">
                        <i class="fas fa-trash"></i> Delete ${report.isDraft ? 'Draft' : 'Report'}
                    </button>
                </div>
            </div>
        `;
        
        // Display modal
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalBody').innerHTML = content;
        document.getElementById('detailModal').classList.add('active');
        
        // Add button event listeners
        document.getElementById('cancelDelete').addEventListener('click', () => {
            document.getElementById('detailModal').classList.remove('active');
        });
        
        document.getElementById('confirmDelete').addEventListener('click', () => {
            deleteReport(reportId);
        });
    }
    
    // Delete a report
    async function deleteReport(reportId) {
        const deleteBtn = document.getElementById('confirmDelete');
        const cancelBtn = document.getElementById('cancelDelete');
        
        try {
            // Show loading state
            deleteBtn.disabled = true;
            cancelBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            
            // Send delete request
            const response = await fetch('php/metrics/delete_custom_metrics_report.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: reportId })
            }).then(res => res.json());
            
            // Close modal
            document.getElementById('detailModal').classList.remove('active');
            
            if (response.success) {
                showNotification(response.message || 'Report deleted successfully', 'success');
                loadPreviousReports();
            } else {
                showNotification(response.message || 'Error deleting report', 'error');
            }
        } catch (error) {
            console.error('Error deleting report:', error);
            document.getElementById('detailModal').classList.remove('active');
            showNotification('Error: ' + error.message, 'error');
        }
    }
    
    // Reset the metrics state for forced refresh
    function resetMetricsState() {
        state.metricsLoaded = false;
        state.loadingInProgress = false;
        state.selectedMetricId = null;
    }
    
    // Return public API
    return {
        init,
        loadPreviousReports,
        resetMetricsState,
        showMetricSelector,
        setSelectedMetric
    };
}
