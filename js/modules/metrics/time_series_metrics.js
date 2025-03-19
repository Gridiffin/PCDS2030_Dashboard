/**
 * Time Series Metrics functionality
 * Handles the entry, editing, and visualization of monthly/quarterly data
 */
import { currentUser, showNotification, escapeHtml, formatDate, loadCurrentUser } from './metrics_core.js';

export default function TimeSeriesMetrics() {
    // DOM elements
    const elements = {
        metricSelector: document.getElementById('metricSelector'),
        yearSelector: document.getElementById('yearSelector'),
        timeSeriesForm: document.getElementById('timeSeriesForm'),
        timeSeriesTable: document.getElementById('timeSeriesTable'),
        backToMetricsBtn: document.getElementById('backToMetricsBtn'),
        metricSelectorSection: document.getElementById('metricSelectorSection'),
        timeSeriesEntrySection: document.getElementById('timeSeriesEntrySection'),
        selectedMetricInfo: document.getElementById('selectedMetricInfo'),
        selectedYear: document.getElementById('selectedYear'),
        totalValue: document.getElementById('totalValue'),
        unitDisplays: document.querySelectorAll('.unit-display'),
        annualNotes: document.getElementById('annualNotes'),
        saveAsDraftBtn: document.getElementById('saveAsDraftBtn'),
        dataEntryLoading: document.getElementById('dataEntry-loading'),
        chartContainer: document.getElementById('chartContainer'),
        reportsTable: document.getElementById('reportsTable')?.querySelector('tbody'),
        reportsTableContainer: document.getElementById('reportsTableContainer'),
        reportsLoading: document.getElementById('reports-loading'),
        noReportsMessage: document.getElementById('noReportsMessage'),
        noMetricsMessage: document.getElementById('noMetricsMessage')
    };

    // State tracking
    const state = {
        selectedMetricId: null,
        selectedYear: null,
        selectedMetricType: null,
        selectedMetricUnit: null,
        monthlyData: {},
        chartInstance: null,
        reportId: null
    };

    // Calculate the total of all values
    function calculateTotal() {
        const valueInputs = document.querySelectorAll('.month-value');
        let total = 0;
        
        valueInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });
        
        // Update the total display with formatting
        elements.totalValue.textContent = formatNumberWithCommas(total);
    }

    // Format number with commas for thousands
    function formatNumberWithCommas(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Delete a report
    async function deleteReport(reportId) {
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        
        try {
            // Show loading state
            confirmDeleteBtn.disabled = true;
            cancelDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            
            // Send delete request to server
            const response = await fetch('php/metrics/delete_time_series_report.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: reportId })
            });
            
            const result = await response.json();
            
            // Close modal
            document.getElementById('detailModal').classList.remove('active');
            
            if (result.success) {
                showNotification(
                    result.wasDraft ? 'Draft deleted successfully' : 'Report deleted successfully', 
                    'success'
                );
                loadPreviousReports();
            } else {
                showNotification(result.message || 'Error deleting report', 'error');
            }
        } catch (error) {
            console.error('Error deleting report:', error);
            document.getElementById('detailModal').classList.remove('active');
            showNotification('Error: ' + error.message, 'error');
        }
    }

    // View report details
    async function viewReport(reportId) {
        try {
            const response = await fetch(`php/metrics/get_time_series_report.php?id=${reportId}`);
            const data = await response.json();
            
            if (!data.success || !data.data) {
                showNotification('Failed to load report', 'error');
                return;
            }
            
            const report = data.data;
            
            // Build chart data from monthly values
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const values = months.map(month => {
                const key = month.toLowerCase();
                return report.monthlyData[key] && report.monthlyData[key].value ? 
                    parseFloat(report.monthlyData[key].value) : 0;
            });
            
            // Create modal content
            let tableRows = '';
            let total = 0;
            
            months.forEach((month, index) => {
                const key = month.toLowerCase();
                if (report.monthlyData[key]) {
                    const value = parseFloat(report.monthlyData[key].value) || 0;
                    total += value;
                    
                    tableRows += `
                        <tr>
                            <td>${month}</td>
                            <td>${formatNumberWithCommas(value)}${report.unit ? ' ' + report.unit : ''}</td>
                            <td>${report.monthlyData[key].notes ? escapeHtml(report.monthlyData[key].notes) : ''}</td>
                        </tr>
                    `;
                }
            });
            
            const content = `
                <div class="modal-section">
                    <h4>Report Information</h4>
                    <p><strong>Metric:</strong> ${escapeHtml(report.metricName)}</p>
                    <p><strong>Year:</strong> ${report.year}</p>
                    <p><strong>Status:</strong> ${report.isDraft ? 
                        '<span class="status-badge draft">Draft</span>' : 
                        '<span class="status-badge completed">Submitted</span>'}</p>
                    <p><strong>Last Updated:</strong> ${formatDate(report.lastUpdated)}</p>
                    ${report.annualNotes ? `<p><strong>Annual Notes:</strong> ${escapeHtml(report.annualNotes)}</p>` : ''}
                </div>
                
                <div class="modal-section">
                    <h4>Monthly Values</h4>
                    <div class="table-container">
                        <table class="time-series-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Value</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows || '<tr><td colspan="3" style="text-align:center;">No data entered</td></tr>'}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td><strong>${formatNumberWithCommas(total)}${report.unit ? ' ' + report.unit : ''}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="modal-section">
                    <h4>Chart Visualization</h4>
                    <div style="height: 300px;">
                        <canvas id="modalChart"></canvas>
                    </div>
                </div>
            `;
            
            // Show modal
            document.getElementById('modalTitle').textContent = `${report.metricName} - ${report.year}`;
            document.getElementById('modalBody').innerHTML = content;
            document.getElementById('detailModal').classList.add('active');
            
            // Create chart in modal
            setTimeout(() => {
                const ctx = document.getElementById('modalChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: report.metricName,
                            data: values,
                            backgroundColor: 'rgba(166, 155, 139, 0.2)',
                            borderColor: 'rgba(166, 155, 139, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: 'rgba(166, 155, 139, 1)',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: report.unit || 'Value'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }, 100);
        } catch (error) {
            console.error('Error viewing report:', error);
            showNotification('Error: ' + error.message, 'error');
        }
    }

    // Confirm deletion of a report
    function confirmDeleteReport(reportId) {
        const content = `
            <div class="modal-section">
                <h4>Delete Report</h4>
                <p>Are you sure you want to delete this report?</p>
                <p class="warning-text"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                <div class="form-actions" style="justify-content: flex-end;">
                    <button id="cancelDeleteBtn" class="secondary-button">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button id="confirmDeleteBtn" class="delete-button">
                        <i class="fas fa-trash"></i> Delete Report
                    </button>
                </div>
            </div>
        `;
        
        // Show modal
        document.getElementById('modalTitle').textContent = 'Confirm Deletion';
        document.getElementById('modalBody').innerHTML = content;
        document.getElementById('detailModal').classList.add('active');
        
        // Add button listeners
        document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
            document.getElementById('detailModal').classList.remove('active');
        });
        
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            deleteReport(reportId);
        });
    }
    
    // Initialize the module
    async function init() {
        console.log("Initializing time series metrics...");
        
        // Load current user
        await loadCurrentUser();
        
        // Setup event listeners
        setupEventListeners();
        
        // Load available time series metrics
        loadAvailableMetrics();
        
        // Load previous reports
        loadPreviousReports();
    }
    
    // Set up event listeners
    function setupEventListeners() {
        // Metric and year selection
        if (elements.metricSelector) {
            elements.metricSelector.addEventListener('change', handleMetricSelection);
        }
        
        if (elements.yearSelector) {
            elements.yearSelector.addEventListener('change', handleYearSelection);
        }
        
        // Back button
        if (elements.backToMetricsBtn) {
            elements.backToMetricsBtn.addEventListener('click', showMetricSelector);
        }
        
        // Form submission
        if (elements.timeSeriesForm) {
            elements.timeSeriesForm.addEventListener('submit', handleFormSubmit);
        }
        
        // Save as draft
        if (elements.saveAsDraftBtn) {
            elements.saveAsDraftBtn.addEventListener('click', saveAsDraft);
        }
    }
    
    // Handle metric selection
    function handleMetricSelection() {
        const selectedOption = elements.metricSelector.options[elements.metricSelector.selectedIndex];
        
        if (!elements.metricSelector.value) return;
        
        state.selectedMetricId = elements.metricSelector.value;
        state.selectedMetricName = selectedOption.textContent;
        state.selectedMetricUnit = selectedOption.dataset.unit || '';
        state.selectedMetricType = selectedOption.dataset.type || 'number';
        
        // If year is also selected, proceed to load data entry
        if (elements.yearSelector.value) {
            state.selectedYear = elements.yearSelector.value;
            loadTimeSeriesEntry();
        } else {
            // Focus on year selector
            elements.yearSelector.focus();
        }
    }
    
    // Handle year selection
    function handleYearSelection() {
        if (!elements.yearSelector.value) return;
        
        state.selectedYear = elements.yearSelector.value;
        
        // If metric is also selected, proceed to load data entry
        if (elements.metricSelector.value) {
            state.selectedMetricId = elements.metricSelector.value;
            const selectedOption = elements.metricSelector.options[elements.metricSelector.selectedIndex];
            state.selectedMetricName = selectedOption.textContent;
            state.selectedMetricUnit = selectedOption.dataset.unit || '';
            state.selectedMetricType = selectedOption.dataset.type || 'number';
            
            loadTimeSeriesEntry();
        } else {
            // Focus on metric selector
            elements.metricSelector.focus();
        }
    }
    
    // Load available time series metrics for the selector
    async function loadAvailableMetrics() {
        try {
            // Show loading message while waiting
            elements.noMetricsMessage.style.display = 'none';
            
            // Get time series metrics type ID from server
            const timeSeriesTypeResponse = await fetch('php/metrics/get_metric_type_id.php?key=time_series');
            const timeSeriesData = await timeSeriesTypeResponse.json();
            
            if (!timeSeriesData.success) {
                throw new Error(timeSeriesData.message || 'Could not get time series metric type');
            }
            
            const timeSeriesTypeId = timeSeriesData.typeId;
            
            // Now get metrics of this type
            const response = await fetch(`php/metrics/get_sector_metrics.php?typeId=${timeSeriesTypeId}`);
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                // Clear any existing options
                elements.metricSelector.innerHTML = '<option value="">Select a metric</option>';
                
                // Add metrics to selector
                data.data.forEach(metric => {
                    const option = document.createElement('option');
                    option.value = metric.MetricID;
                    option.textContent = metric.MetricName;
                    option.dataset.unit = metric.Unit || '';
                    option.dataset.type = metric.DataType || 'number';
                    elements.metricSelector.appendChild(option);
                });
                
                // Show selector section
                elements.metricSelectorSection.style.display = 'block';
            } else {
                // No metrics available
                elements.noMetricsMessage.style.display = 'block';
            }
            
            return true;
        } catch (error) {
            console.error('Error loading available metrics:', error);
            showNotification('Error loading metrics: ' + error.message, 'error');
            elements.noMetricsMessage.style.display = 'block';
            return false;
        }
    }
    
    // Load time series data entry form
    function loadTimeSeriesEntry() {
        if (!state.selectedMetricId || !state.selectedYear) {
            return;
        }
        
        // Show the data entry section
        elements.metricSelectorSection.style.display = 'none';
        elements.timeSeriesEntrySection.style.display = 'block';
        
        // Update display information
        elements.selectedMetricInfo.textContent = state.selectedMetricName;
        elements.selectedYear.textContent = state.selectedYear;
        
        // Set unit display
        updateUnitDisplay();
        
        // Show loading
        elements.dataEntryLoading.style.display = 'flex';
        elements.timeSeriesForm.style.display = 'none';
        
        // Check for existing data
        checkExistingData(state.selectedMetricId, state.selectedYear).then(existingData => {
            if (existingData) {
                populateExistingData(existingData);
            } else {
                createEmptyDataTable();
            }
            
            // Hide loading and show form
            elements.dataEntryLoading.style.display = 'none';
            elements.timeSeriesForm.style.display = 'block';
        });
    }
    
    // Show metric selector
    function showMetricSelector() {
        elements.metricSelectorSection.style.display = 'block';
        elements.timeSeriesEntrySection.style.display = 'none';
        
        // Load all previous reports
        loadPreviousReports();
    }
    
    // Set a specific metric
    function setSelectedMetric(metricId, metricName) {
        if (elements.metricSelector) {
            elements.metricSelector.value = metricId;
            
            // Trigger change event to handle selection
            const event = new Event('change');
            elements.metricSelector.dispatchEvent(event);
        }
    }

    // Update all unit display elements
    function updateUnitDisplay() {
        elements.unitDisplays.forEach(el => {
            el.textContent = state.selectedMetricUnit ? `(${state.selectedMetricUnit})` : '';
        });
    }

    // Check for existing data
    async function checkExistingData(metricId, year) {
        try {
            const response = await fetch(`php/metrics/get_time_series_data.php?metricId=${metricId}&year=${year}`);
            const data = await response.json();
            
            if (data.success && data.data) {
                return data.data;
            }
            
            return null;
        } catch (error) {
            console.error('Error checking for existing data:', error);
            return null;
        }
    }

    // Populate existing data
    function populateExistingData(data) {
        // Create the table
        createTimeSeriesTable();
        
        // Populate monthly values
        if (data.monthlyData) {
            state.monthlyData = data.monthlyData;
            
            Object.keys(data.monthlyData).forEach(month => {
                const input = document.querySelector(`input[name="value_${month}"]`);
                const notesInput = document.querySelector(`input[name="notes_${month}"]`);
                
                if (input && data.monthlyData[month].value !== undefined) {
                    input.value = data.monthlyData[month].value;
                }
                
                if (notesInput && data.monthlyData[month].notes) {
                    notesInput.value = data.monthlyData[month].notes;
                }
            });
        }
        
        // Populate annual notes
        if (data.annualNotes) {
            elements.annualNotes.value = data.annualNotes;
        }
        
        // Update totals
        calculateTotal();
        
        // Create/update chart
        updateChart();
    }

    // Create empty data table
    function createEmptyDataTable() {
        createTimeSeriesTable();
        calculateTotal();
        updateChart();
    }
    
    // Create the time series table
    function createTimeSeriesTable() {
        const tableBody = elements.timeSeriesTable.querySelector('tbody');
        tableBody.innerHTML = '';
        
        const months = [
            'January', 'February', 'March', 'April', 'May', 'June', 
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        let currentQuarter = 1;
        
        months.forEach((month, index) => {
            // Add quarter separator every 3 months
            if (index % 3 === 0) {
                const quarterRow = document.createElement('tr');
                quarterRow.className = 'quarter-separator';
                quarterRow.innerHTML = `<td colspan="3">Q${currentQuarter}</td>`;
                tableBody.appendChild(quarterRow);
                currentQuarter++;
            }
            
            // Create row for this month
            const row = document.createElement('tr');
            row.dataset.month = index + 1;
            
            // Determine month abbreviation (Jan, Feb, etc)
            const monthAbbr = month.substring(0, 3).toLowerCase();
            
            row.innerHTML = `
                <td><span class="month-name">${month}</span></td>
                <td>
                    <input type="number" 
                        name="value_${monthAbbr}" 
                        step="${state.selectedMetricType === 'currency' ? '0.01' : '1'}"
                        placeholder="Enter value" 
                        class="month-value">
                </td>
                <td>
                    <input type="text"
                        name="notes_${monthAbbr}"
                        placeholder="Optional notes"
                        class="month-notes">
                </td>
            `;
            
            tableBody.appendChild(row);
        });
        
        // Add event listeners to value inputs for live updates
        const valueInputs = tableBody.querySelectorAll('.month-value');
        valueInputs.forEach(input => {
            input.addEventListener('input', () => {
                calculateTotal();
                updateChart();
            });
            
            input.addEventListener('change', () => {
                markChanged(input);
            });
        });
    }

    // Handle form submission
    async function handleFormSubmit(e) {
        e.preventDefault();
        
        if (!validateForm()) return;
        
        const reportData = collectFormData(false);
        await submitData(reportData, e.submitter);
    }
    
    // Save as draft
    async function saveAsDraft() {
        const reportData = collectFormData(true);
        await submitData(reportData, elements.saveAsDraftBtn);
    }
    
    // Validate the form
    function validateForm() {
        // Check if at least one month has data
        const valueInputs = document.querySelectorAll('.month-value');
        let hasData = false;
        
        valueInputs.forEach(input => {
            if (input.value.trim() !== '') {
                hasData = true;
            }
        });
        
        if (!hasData) {
            showNotification('Please enter at least one month\'s data', 'warning');
            return false;
        }
        
        return true;
    }
    
    // Collect form data
    function collectFormData(isDraft) {
        const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 
                       'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        
        const monthlyData = {};
        
        // Collect monthly values and notes
        months.forEach(month => {
            const valueInput = document.querySelector(`input[name="value_${month}"]`);
            const notesInput = document.querySelector(`input[name="notes_${month}"]`);
            
            if (valueInput && valueInput.value.trim() !== '') {
                monthlyData[month] = {
                    value: parseFloat(valueInput.value),
                    notes: notesInput ? notesInput.value : ''
                };
            }
        });
        
        return {
            metricId: state.selectedMetricId,
            metricName: state.selectedMetricName,
            year: state.selectedYear,
            monthlyData: monthlyData,
            annualNotes: elements.annualNotes.value,
            isDraft: isDraft,
            lastUpdated: new Date().toISOString()
        };
    }
    
    // Submit data to server
    async function submitData(reportData, button) {
        try {
            // Show loading state
            const originalButtonText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + 
                (reportData.isDraft ? 'Saving...' : 'Submitting...');
            
            const response = await fetch('php/metrics/save_time_series_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(reportData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification(
                    reportData.isDraft ? 'Draft saved successfully' : 'Data submitted successfully', 
                    'success'
                );
                
                // If it was a final submission (not draft), return to selector
                if (!reportData.isDraft) {
                    setTimeout(() => {
                        showMetricSelector();
                    }, 1000);
                }
                
                // Reload reports table
                loadPreviousReports();
            } else {
                showNotification(result.message || 'Error saving data', 'error');
            }
        } catch (error) {
            console.error('Error submitting data:', error);
            showNotification('Error: ' + error.message, 'error');
        } finally {
            // Restore button state
            button.disabled = false;
            button.innerHTML = reportData.isDraft ? 
                '<i class="fas fa-save"></i> Save as Draft' : 
                '<i class="fas fa-paper-plane"></i> Submit Data';
        }
    }
    
    // Load previous reports
    async function loadPreviousReports() {
        try {
            // Show loading
            elements.reportsLoading.style.display = 'flex';
            elements.reportsTableContainer.style.display = 'none';
            elements.noReportsMessage.style.display = 'none';
            
            let url = 'php/metrics/get_time_series_reports.php';
            if (state.selectedMetricId) {
                url += `?metricId=${state.selectedMetricId}`;
            }
            
            const response = await fetch(url);
            const data = await response.json();
            
            // Hide loading
            elements.reportsLoading.style.display = 'none';
            elements.reportsTableContainer.style.display = 'block';
            
            if (!data.success || !data.data || data.data.length === 0) {
                elements.noReportsMessage.style.display = 'block';
                return;
            }
            
            // Clear table
            elements.reportsTable.innerHTML = '';
            
            // Add rows
            data.data.forEach((report, index) => {
                addReportRow(report, index);
            });
            
            // Add action listeners
            addReportActionListeners();
            
        } catch (error) {
            console.error('Error loading reports:', error);
            elements.reportsLoading.style.display = 'none';
            elements.noReportsMessage.style.display = 'block';
            showNotification('Error loading reports: ' + error.message, 'error');
        }
    }
    
    // Add a row to the reports table
    function addReportRow(report, index) {
        const row = document.createElement('tr');
        row.className = 'animated-row';
        row.style.animationDelay = `${index * 50}ms`;
        
        const statusBadge = report.isDraft ? 
            '<span class="status-badge draft">Draft</span>' : 
            '<span class="status-badge completed">Submitted</span>';
        
        const actionButtons = `
            <button class="icon-button view-btn" data-id="${report.id}" title="View Report">
                <i class="fas fa-eye"></i>
            </button>
            ${report.isDraft ? `
                <button class="icon-button edit-btn" data-id="${report.id}" title="Edit Draft">
                    <i class="fas fa-edit"></i>
                </button>
            ` : ''}
            <button class="icon-button delete-btn" data-id="${report.id}" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        `;
        
        row.innerHTML = `
            <td>${escapeHtml(report.metricName)}</td>
            <td>${report.year}</td>
            <td>${formatDate(report.lastUpdated)}</td>
            <td>${statusBadge}</td>
            <td class="action-cell">${actionButtons}</td>
        `;
        
        elements.reportsTable.appendChild(row);
    }

    // Add listeners to report action buttons
    function addReportActionListeners() {
        document.querySelectorAll('#reportsTable .view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const reportId = btn.getAttribute('data-id');
                viewReport(reportId);
            });
        });
        
        document.querySelectorAll('#reportsTable .edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const reportId = btn.getAttribute('data-id');
                editReport(reportId);
            });
        });
        
        document.querySelectorAll('#reportsTable .delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const reportId = btn.getAttribute('data-id');
                confirmDeleteReport(reportId);
            });
        });
    }

    // Mark inputs as changed
    function markChanged(input) {
        input.classList.add('changed');
        setTimeout(() => {
            input.classList.remove('changed');
        }, 3000);
    }

    // Update chart with current data
    function updateChart() {
        // Destroy any existing chart
        if (state.chartInstance) {
            state.chartInstance.destroy();
        }
        
        // Get values
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const values = months.map(month => {
            const input = document.querySelector(`input[name="value_${month.toLowerCase()}"]`);
            return input ? parseFloat(input.value) || 0 : 0;
        });
        
        // Create chart
        const ctx = document.getElementById('timeSeriesChart').getContext('2d');
        state.chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: state.selectedMetricName,
                    data: values,
                    backgroundColor: 'rgba(166, 155, 139, 0.2)',
                    borderColor: 'rgba(166, 155, 139, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(166, 155, 139, 1)',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: state.selectedMetricUnit || 'Value'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: `${state.selectedMetricName} (${state.selectedYear})`,
                        font: { size: 16 }
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Edit a report
    async function editReport(reportId) {
        try {
            const response = await fetch(`php/metrics/get_time_series_report.php?id=${reportId}`);
            const data = await response.json();
            
            if (!data.success || !data.data) {
                showNotification('Failed to load report', 'error');
                return;
            }
            
            const report = data.data;
            
            // Set form fields
            state.selectedMetricId = report.metricId;
            state.selectedMetricName = report.metricName;
            state.selectedYear = report.year;
            state.selectedMetricUnit = report.unit || '';
            state.reportId = reportId; // Store report ID for updating
            
            // Update selectors to match the report
            elements.metricSelector.value = report.metricId;
            elements.yearSelector.value = report.year;
            
            // Load the form
            loadTimeSeriesEntry();
        } catch (error) {
            console.error('Error editing report:', error);
            showNotification('Error: ' + error.message, 'error');
        }
    }
    
    // Return all the public functions
    return {
        init,
        loadAvailableMetrics,
        loadPreviousReports,
        showMetricSelector,
        setSelectedMetric
    };
}
