document.addEventListener('DOMContentLoaded', function() {
    // User and agency information
    const currentUser = {
        id: null,
        username: '',
        agencyId: null,
        agencyName: '',
        allowedMetricTypes: []
    };

    // DOM elements
    const submissionsTable = document.getElementById('submissionsTable').querySelector('tbody');
    const noDataMessage = document.getElementById('noDataMessage');
    const detailModal = document.getElementById('detailModal');
    const modalContent = document.getElementById('modalContent');
    const notification = document.getElementById('notification');
    const refreshSubmissionsBtn = document.getElementById('refreshSubmissions');
    const viewMetricTypeSelect = document.getElementById('viewMetricType');
    const viewAgencySelect = document.getElementById('viewAgency');
    const usernameElement = document.getElementById('username');
    const agencyBadge = document.getElementById('agency-badge');

    // Initialize app
    init();

    // Event listeners
    document.querySelector('.close-modal').addEventListener('click', () => detailModal.classList.remove('active'));
    refreshSubmissionsBtn.addEventListener('click', loadSubmissions);
    document.getElementById('viewYear').addEventListener('change', loadSubmissions);
    document.getElementById('viewQuarter').addEventListener('change', loadSubmissions);
    viewMetricTypeSelect.addEventListener('change', loadSubmissions);
    viewAgencySelect.addEventListener('change', loadSubmissions);

    // Functions
    function init() {
        // Load current user data
        loadCurrentUser();
        
        // The rest of initialization will be done after user data is loaded
    }

    function loadCurrentUser() {
        // Connect to the database through PHP endpoint
        fetch('php/auth/get_current_user.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success || !data.user) {
                    showNotification('Failed to load user data', 'error');
                    return;
                }

                const userData = data.user;

                // Set current user data
                currentUser.id = userData.id;
                currentUser.username = userData.username;
                currentUser.agencyId = userData.agencyId;
                currentUser.agencyName = userData.agencyName;
                currentUser.allowedMetricTypes = userData.allowedMetricTypes;

                // Update UI with user data
                usernameElement.textContent = currentUser.username;
                agencyBadge.textContent = currentUser.agencyName;
                
                // Now that we have user data, load everything else
                loadAgencies();
                loadAllMetricTypes();
                loadSubmissions();
            })
            .catch(error => {
                console.error('Error loading user data:', error);
                showNotification('Error loading user data: ' + error.message, 'error');
            });
    }

    function loadAllMetricTypes() {
        fetch('php/metrics/get_metric_types.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load all metric types');
                }
                
                // Clear existing options except the default one
                viewMetricTypeSelect.innerHTML = '<option value="">All Sectors</option>';
                
                // Add options for each metric type
                data.data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    viewMetricTypeSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading all metric types:', error);
            });
    }

    function loadAgencies() {
        fetch('php/admin/manage_users.php?operation=getAgencies')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load agencies');
                }
                
                // Clear existing options except the default one
                viewAgencySelect.innerHTML = '<option value="">All Agencies</option>';
                
                // Add options for each agency
                data.data.forEach(agency => {
                    const option = document.createElement('option');
                    option.value = agency.AgencyID;
                    option.textContent = agency.AgencyName;
                    viewAgencySelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading agencies:', error);
            });
    }

    function loadSubmissions() {
        // Get filters
        const filters = {
            year: document.getElementById('viewYear').value,
            quarter: document.getElementById('viewQuarter').value,
            metricType: viewMetricTypeSelect.value,
            agencyId: viewAgencySelect.value
        };

        // Build query parameters
        const queryParams = new URLSearchParams();
        if (filters.year) queryParams.append('year', filters.year);
        if (filters.quarter) queryParams.append('quarter', filters.quarter);
        if (filters.metricType) queryParams.append('metricType', filters.metricType);
        if (filters.agencyId) queryParams.append('agencyId', filters.agencyId);

        // Fetch data from database
        fetch('php/metrics/get_submissions.php?' + queryParams.toString())
            .then(response => response.json())
            .then(data => {
                if (!data.success || !data.data) {
                    showNotification('Failed to load submissions', 'error');
                    return;
                }

                // Clear existing rows
                submissionsTable.innerHTML = '';

                // Show or hide no data message
                if (data.data.length === 0) {
                    noDataMessage.style.display = 'block';
                    return;
                } else {
                    noDataMessage.style.display = 'none';
                }

                // Add rows for each submission
                data.data.forEach(submission => {
                    const row = document.createElement('tr');

                    // Use the statusColor if provided, otherwise fall back to status mapping
                    let statusClass, statusText;
                    
                    if (submission.statusColor) {
                        // Use the explicitly set color
                        statusClass = submission.statusColor;
                    } else {
                        // Fall back to traditional status mapping
                        switch (submission.statusCategory || submission.status) {
                            case 'completed':
                                statusClass = 'completed';
                                statusText = 'Monthly target achieved';
                                break;
                            case 'nearly-complete':
                                statusClass = 'progress';
                                statusText = 'Miss in target but still on track';
                                break;
                            case 'in-progress':
                                statusClass = 'progress';
                                statusText = 'In Progress';
                                break;
                            case 'not-started':
                                statusClass = 'draft';
                                statusText = 'Not Started';
                                break;
                            case 'delayed':
                                statusClass = 'warning';
                                statusText = 'Severe delays';
                                break;
                            case 'cancelled':
                                statusClass = 'draft';
                                statusText = 'Cancelled';
                                break;
                            default:
                                statusClass = 'draft';
                                statusText = submission.status || 'Unknown';
                        }
                    }

                    // Create action buttons based on editability
                    const actionButtons = `
                        <button type="button" class="icon-button view-btn" data-id="${submission.id}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${submission.isEditable ? `
                            <a href="target_status.html?edit=${submission.id}" class="icon-button edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        ` : `
                            <button type="button" class="icon-button edit-btn" disabled title="Can't edit (different agency)">
                                <i class="fas fa-edit"></i>
                            </button>
                        `}
                    `;

                    // Display either the quantitative value or a qualitative summary
                    let statusDisplay = submission.currentValue;
                    if (submission.isQualitative) {
                        // For qualitative status, show a truncated version of the status notes
                        const maxLength = 30;
                        statusDisplay = submission.statusSummary || 
                            (submission.statusNotes && submission.statusNotes.length > maxLength ? 
                            submission.statusNotes.substring(0, maxLength) + '...' : 
                            submission.statusNotes || 'No update');
                    }

                    row.innerHTML = `
                        <td>${escapeHtml(submission.programName)}</td>
                        <td>${submission.year} ${submission.quarter}</td>
                        <td>${escapeHtml(submission.metricTypeName)}</td>
                        <td>${escapeHtml(submission.agencyName)}</td>
                        <td>${escapeHtml(submission.targetSummary || submission.targetValue)}</td>
                        <td><div class="status-circle ${statusClass}" title="${statusText}"></div> ${escapeHtml(statusDisplay)}</td>
                        <td>${formatDate(submission.lastUpdated)}</td>
                        <td class="action-cell">${actionButtons}</td>
                    `;

                    submissionsTable.appendChild(row);
                });

                // Add event listeners to the buttons
                addActionListeners();
            })
            .catch(error => {
                console.error('Error loading submissions:', error);
                submissionsTable.innerHTML = '<tr><td colspan="8" style="text-align: center;">Error loading submissions: ' + error.message + '</td></tr>';
                noDataMessage.style.display = 'none';
            });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return isNaN(date) ? dateString : date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function addActionListeners() {
        // Add listeners for view buttons
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const submissionId = this.getAttribute('data-id');
                viewSubmissionDetails(submissionId);
            });
        });
    }

    function viewSubmissionDetails(submissionId) {
        // Fetch submission details from database
        fetch(`php/metrics/get_submission_details.php?id=${submissionId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success || !data.data) {
                    showNotification('Failed to load submission details', 'error');
                    return;
                }

                const submission = data.data;

                // Set modal title
                document.getElementById('modalTitle').textContent = submission.programName;

                // Format modal content - adjust for qualitative targets/statuses
                const content = `
                    <div class="modal-section">
                        <h4>Program Information</h4>
                        <p><strong>Description:</strong> ${submission.description || 'No description provided'}</p>
                        <p><strong>Sector:</strong> ${submission.metricTypeName}</p>
                        <p><strong>Agency:</strong> ${submission.agencyName}</p>
                        <p><strong>Period:</strong> ${submission.year} ${submission.quarter}</p>
                    </div>

                    <div class="modal-section">
                        <h4>Target Information</h4>
                        <p><strong>Target:</strong> ${submission.indicator}</p>
                        ${submission.targetDescription ? `<p><strong>Details:</strong> ${submission.targetDescription}</p>` : ''}
                        ${submission.isQuantitative ? `
                            <p><strong>Target Value:</strong> ${submission.targetValue} ${submission.targetUnit}</p>
                        ` : ''}
                        ${submission.targetDeadline ? `<p><strong>Deadline:</strong> ${formatDate(submission.targetDeadline)}</p>` : ''}
                    </div>

                    <div class="modal-section">
                        <h4>Current Status</h4>
                        ${submission.statusNotes ? `<p><strong>Status Update:</strong> ${submission.statusNotes}</p>` : ''}
                        ${submission.isQuantitative ? `
                            <p><strong>Current Value:</strong> ${submission.currentValue} ${submission.targetUnit} (as of ${formatDate(submission.statusDate)})</p>
                            <p><strong>Completion:</strong> ${submission.completionPercentage}%</p>
                            <div class="progress-bar">
                                <div class="progress" style="width: ${submission.completionPercentage}%"></div>
                                <span>${submission.completionPercentage}%</span>
                            </div>
                        ` : `
                            <p><strong>Status Date:</strong> ${formatDate(submission.statusDate)}</p>
                        `}
                        ${submission.challenges ? `<p><strong>Challenges:</strong> ${submission.challenges}</p>` : ''}
                    </div>

                    ${submission.supportingFiles && submission.supportingFiles.length > 0 ? `
                        <div class="modal-section">
                            <h4>Supporting Documents</h4>
                            <ul class="file-list">
                                ${submission.supportingFiles.map(file => `
                                    <li><a href="${file.url}" target="_blank"><i class="fas fa-file"></i> ${file.name}</a></li>
                                `).join('')}
                            </ul>
                        </div>
                    ` : ''}
                    
                    <div class="modal-actions">
                        ${submission.isEditable ? `
                            <a href="target_status.html?edit=${submission.id}" class="secondary-button">
                                <i class="fas fa-edit"></i> Edit Submission
                            </a>
                        ` : ''}
                    </div>
                `;

                // Set modal content
                modalContent.innerHTML = content;

                // Show modal
                detailModal.classList.add('active');
            })
            .catch(error => {
                console.error('Error fetching submission details:', error);
                showNotification('Error loading details: ' + error.message, 'error');
            });
    }

    function showNotification(message, type) {
        notification.textContent = message;
        notification.className = 'notification'; // Reset classes
        notification.classList.add(type);
        notification.style.display = 'block';

        // Hide after 5 seconds
        setTimeout(() => {
            notification.style.display = 'none';
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
});
