document.addEventListener('DOMContentLoaded', function() {
    // User and agency information
    const currentUser = {
        id: null,
        username: '',
        agencyId: null,
        agencyName: '',
        allowedMetricTypes: []
    };

    // DOM elements - add safe checks for all elements
    const submissionsTable = document.getElementById('submissionsTable')?.querySelector('tbody');
    const noDataMessage = document.getElementById('noDataMessage');
    const detailModal = document.getElementById('detailModal');
    const modalBody = document.getElementById('modalBody'); 
    const notification = document.getElementById('notification');
    const refreshSubmissionsBtn = document.getElementById('refreshSubmissions');
    const viewMetricTypeSelect = document.getElementById('viewMetricType');
    const viewAgencySelect = document.getElementById('viewAgency');
    const usernameElement = document.getElementById('username');
    const agencyBadge = document.getElementById('agency-badge');
    const draftsTable = document.getElementById('draftsTable')?.querySelector('tbody');
    const noDraftsMessage = document.getElementById('noDraftsMessage');
    
    // Add loading indicators
    const addLoadingSpinner = (element, message = 'Loading...') => {
        if (!element) return;
        element.innerHTML = `<tr><td colspan="8" class="loading-cell"><div class="loading-spinner"></div><p>${message}</p></td></tr>`;
    };

    // Initialize app
    init();

    // Event listeners - add null checks to prevent errors
    if (document.querySelector('.close-modal')) {
        document.querySelector('.close-modal').addEventListener('click', () => {
            if (detailModal) detailModal.classList.remove('active');
        });
    }
    
    if (refreshSubmissionsBtn) {
        refreshSubmissionsBtn.addEventListener('click', () => {
            loadSubmissions(true); // true indicates this is a manual refresh
        });
    }
    
    if (document.getElementById('viewYear')) {
        document.getElementById('viewYear').addEventListener('change', loadSubmissions);
    }
    
    if (document.getElementById('viewQuarter')) {
        document.getElementById('viewQuarter').addEventListener('change', loadSubmissions);
    }
    
    if (viewMetricTypeSelect) {
        viewMetricTypeSelect.addEventListener('change', loadSubmissions);
    }
    
    if (viewAgencySelect) {
        viewAgencySelect.addEventListener('change', loadSubmissions);
    }

    // Functions
    function init() {
        // Add initial loading indicators
        if (submissionsTable) {
            addLoadingSpinner(submissionsTable, 'Loading submissions...');
        }
        
        if (draftsTable) {
            addLoadingSpinner(draftsTable, 'Loading drafts...');
        }

        // Load current user data
        loadCurrentUser();
        
        // Listen for localStorage events to refresh drafts dynamically
        window.addEventListener('storage', function(e) {
            if (e.key === 'draftsUpdated') {
                console.log('Draft updates detected in another tab/window');
                loadDrafts();
            }
        });
        
        // Listen for hash changes to show proper tab
        window.addEventListener('hashchange', handleHashChange);
        
        // Check initial hash
        handleHashChange();
    }
    
    function handleHashChange() {
        // If hash is #drafts, scroll to drafts section
        if (window.location.hash === '#drafts') {
            const draftsSection = document.querySelector('.dashboard-section:nth-child(2)');
            if (draftsSection) {
                draftsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    }

    function loadCurrentUser() {
        // Connect to the database through PHP endpoint
        fetch('php/auth/get_current_user.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success || !data.user) {
                    showNotification('Failed to load user data', 'error');
                    // Still proceed with loading data
                    loadAllMetricTypes();
                    loadAgencies();
                    loadSubmissions();  // Load submissions right away
                    loadDrafts();       // Load drafts right away
                    return;
                }

                const userData = data.user;

                // Set current user data
                currentUser.id = userData.id;
                currentUser.username = userData.username;
                currentUser.agencyId = userData.agencyId;
                currentUser.agencyName = userData.agencyName;
                currentUser.allowedMetricTypes = userData.allowedMetricTypes;

                // Update UI with user data - Add null checks to prevent errors
                if (usernameElement) {
                    usernameElement.textContent = currentUser.username;
                }
                
                if (agencyBadge) {
                    agencyBadge.textContent = currentUser.agencyName;
                }
                
                // Now that we have user data, load everything else
                loadAllMetricTypes();
                loadAgencies();
                
                // Set default agency filter to the user's own agency
                if (viewAgencySelect && currentUser.agencyId) {
                    // Wait for agencies to load before setting the default
                    setTimeout(() => {
                        viewAgencySelect.value = currentUser.agencyId;
                    }, 500);
                }
                
                loadSubmissions();  // Load submissions right away
                loadDrafts();       // Load drafts right away
                
                // Check hash to determine what to load
                handleHashChange();
            })
            .catch(error => {
                console.error('Error loading user data:', error);
                showNotification('Error loading user data: ' + error.message, 'error');
                
                // Still try to load metrics, agencies, and data even if user data fails
                loadAllMetricTypes();
                loadAgencies();
                loadSubmissions();  // Load submissions despite error
                loadDrafts();       // Load drafts despite error
                handleHashChange();
            });
    }

    function loadAllMetricTypes() {
        fetch('php/metrics/get_all_metric_types.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("JSON Parse Error. Raw response:", text.substring(0, 100));
                        throw new Error("Invalid JSON response from server");
                    }
                });
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load all metric types');
                }
                
                // Clear existing options except the default one
                if (viewMetricTypeSelect) {
                    viewMetricTypeSelect.innerHTML = '<option value="">All Sectors</option>';
                    
                    // Add options for each metric type
                    data.data.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = type.name;
                        viewMetricTypeSelect.appendChild(option);
                    });
                }
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
                if (viewAgencySelect) {
                    viewAgencySelect.innerHTML = '<option value="">All Agencies</option>';
                    
                    // Add options for each agency
                    data.data.forEach(agency => {
                        const option = document.createElement('option');
                        option.value = agency.AgencyID;
                        option.textContent = agency.AgencyName;
                        viewAgencySelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading agencies:', error);
            });
    }

    function loadSubmissions(isManualRefresh = false) {
        // Show loading state
        if (submissionsTable) {
            // Only show loading if it's a manual refresh or the table is empty
            if (isManualRefresh || !submissionsTable.querySelector('tr:not(.loading-cell)')) {
                addLoadingSpinner(submissionsTable, 'Loading submissions...');
            }
        }
        
        // Get filters
        const filters = {
            year: document.getElementById('viewYear')?.value,
            quarter: document.getElementById('viewQuarter')?.value,
            metricType: viewMetricTypeSelect?.value,
            agencyId: viewAgencySelect?.value
        };

        // Build query parameters
        const queryParams = new URLSearchParams();
        if (filters.year) queryParams.append('year', filters.year);
        if (filters.quarter) queryParams.append('quarter', filters.quarter);
        if (filters.metricType) queryParams.append('metricType', filters.metricType);
        if (filters.agencyId) queryParams.append('agencyId', filters.agencyId);
        
        // Add a cache-busting parameter when manually refreshing
        if (isManualRefresh) {
            queryParams.append('refresh', Date.now());
        }

        // Fetch data from database
        fetch('php/metrics/get_submissions.php?' + queryParams.toString())
            .then(response => response.json())
            .then(data => {
                if (!data.success || !data.data) {
                    if (isManualRefresh) {
                        showNotification('Failed to load submissions', 'error');
                    }
                    return;
                }

                // Clear existing rows
                if (submissionsTable) {
                    submissionsTable.innerHTML = '';
                }

                // Show or hide no data message
                if (data.data.length === 0 && noDataMessage) {
                    noDataMessage.style.display = 'block';
                    return;
                } else if (noDataMessage) {
                    noDataMessage.style.display = 'none';
                }

                // Add rows for each submission - filter out drafts
                data.data.forEach(submission => {
                    // Skip drafts - they're now in a separate table
                    if (submission.status === 'draft') {
                        return;
                    }

                    const row = document.createElement('tr');
                    
                    // Add 'own-agency' class for visual distinction if this submission belongs to the user's agency
                    if (submission.agencyId === currentUser.agencyId) {
                        row.classList.add('own-agency');
                    }

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

                    // Create action buttons based on editability
                    const actionButtons = `
                        <button type="button" class="icon-button view-btn" data-id="${submission.id}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${submission.isEditable ? `
                            <a href="target_status.php?program=${submission.id}" class="icon-button edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="icon-button delete-btn" data-id="${submission.id}" data-program="${escapeHtml(submission.programName)}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : `
                            <button type="button" class="icon-button edit-btn" disabled title="Can't edit (different agency)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="icon-button delete-btn" disabled title="Can't delete (different agency)">
                                <i class="fas fa-trash"></i>
                            </button>
                        `}
                    `;

                    row.innerHTML = `
                        <td>${escapeHtml(submission.programName)}</td>
                        <td>${submission.year} ${submission.quarter}</td>
                        <td>${escapeHtml(submission.metricTypeName)}</td>
                        <td>${escapeHtml(submission.sectorName || 'Not specified')}</td>
                        <td>${escapeHtml(submission.agencyName)}${submission.agencyId === currentUser.agencyId ? ' <span class="badge-small">yours</span>' : ''}</td>
                        <td><div class="status-circle ${statusClass}" title="${statusText}"></div> ${escapeHtml(statusDisplay)}</td>
                        <td>${formatDate(submission.lastUpdated)}</td>
                        <td class="action-cell">${actionButtons}</td>
                    `;

                    if (submissionsTable) {
                        submissionsTable.appendChild(row);
                    }
                });

                // Add event listeners to the buttons
                addActionListeners();
                
                // Show success notification only on manual refresh
                if (isManualRefresh) {
                    showNotification('Submissions refreshed successfully', 'success');
                }
            })
            .catch(error => {
                console.error('Error loading submissions:', error);
                if (submissionsTable) {
                    submissionsTable.innerHTML = '<tr><td colspan="8" style="text-align: center;">Error loading submissions: ' + error.message + '</td></tr>';
                }
                if (noDataMessage) {
                    noDataMessage.style.display = 'none';
                }
                if (isManualRefresh) {
                    showNotification('Error refreshing submissions: ' + error.message, 'error');
                }
            });
    }

    function loadDrafts() {
        // Show loading state for drafts table
        if (draftsTable && !draftsTable.querySelector('tr:not(.loading-cell)')) {
            addLoadingSpinner(draftsTable, 'Loading drafts...');
        }
        
        fetch('php/metrics/get_drafts.php')
            .then(response => response.json())
            .then(data => {
                // Clear existing rows
                if (draftsTable) {
                    draftsTable.innerHTML = '';
                }

                // Show or hide no data message
                if (!data.success || !data.data || data.data.length === 0) {
                    if (noDraftsMessage) {
                        noDraftsMessage.style.display = 'block';
                    }
                    return;
                } else if (noDraftsMessage) {
                    noDraftsMessage.style.display = 'none';
                }

                // Add rows for each draft
                data.data.forEach(draft => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', draft.id);
                    row.classList.add('draft-row');
                    
                    // Add a subtle fade-in effect for new rows
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.5s ease-in-out';

                    // Create action buttons for drafts
                    const actionButtons = `
                        <button type="button" class="icon-button view-btn" data-id="${draft.id}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="target_status.php?draft=${draft.id}" class="icon-button edit-btn" title="Continue Editing">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <button type="button" class="icon-button delete-btn" data-id="${draft.id}" data-program="${escapeHtml(draft.programName)}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;

                    row.innerHTML = `
                        <td>${escapeHtml(draft.programName)}</td>
                        <td>${draft.year} ${draft.quarter}</td>
                        <td>${escapeHtml(draft.metricTypeName)}</td>
                        <td>${escapeHtml(draft.targetSummary || draft.targetDescription || 'No target yet')}</td>
                        <td>${formatDate(draft.lastUpdated)}</td>
                        <td class="action-cell">${actionButtons}</td>
                    `;

                    if (draftsTable) {
                        draftsTable.appendChild(row);
                        
                        // Trigger reflow and fade in
                        setTimeout(() => {
                            row.style.opacity = '1';
                        }, 10);
                    }
                });

                // Add event listeners to the draft buttons
                addDraftActionListeners();
            })
            .catch(error => {
                console.error('Error loading drafts:', error);
                if (draftsTable) {
                    draftsTable.innerHTML = '<tr><td colspan="6" style="text-align: center;">Error loading drafts: ' + error.message + '</td></tr>';
                }
                if (noDraftsMessage) {
                    noDraftsMessage.style.display = 'none';
                }
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
        
        // Add listeners for delete buttons
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.disabled) {
                    const submissionId = this.getAttribute('data-id');
                    const programName = this.getAttribute('data-program');
                    confirmDelete(submissionId, programName);
                }
            });
        });
    }

    function addDraftActionListeners() {
        // Add listeners for draft view buttons
        document.querySelectorAll('#draftsTable .view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const submissionId = this.getAttribute('data-id');
                viewSubmissionDetails(submissionId);
            });
        });
        
        // Add listeners for draft delete buttons
        document.querySelectorAll('#draftsTable .delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const submissionId = this.getAttribute('data-id');
                const programName = this.getAttribute('data-program');
                confirmDeleteDraft(submissionId, programName);
            });
        });
    }

    function confirmDelete(submissionId, programName) {
        // Create confirmation modal
        const confirmHtml = `
            <div class="modal-section">
                <h4>Delete Submission</h4>
                <p>Are you sure you want to delete the submission for program <strong>${escapeHtml(programName)}</strong>?</p>
                <p class="warning-text"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                <div class="modal-actions">
                    <button class="secondary-button" id="cancelDelete">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="delete-button" id="confirmDelete">
                        <i class="fas fa-trash"></i> Delete Permanently
                    </button>
                </div>
            </div>
        `;
        
        document.getElementById('modalTitle').textContent = "Confirm Deletion";
        modalBody.innerHTML = confirmHtml;
        detailModal.classList.add('active');
        
        // Add event listeners for the buttons
        document.getElementById('cancelDelete').addEventListener('click', () => {
            detailModal.classList.remove('active');
        });
        
        document.getElementById('confirmDelete').addEventListener('click', () => {
            deleteSubmission(submissionId);
        });
    }

    function confirmDeleteDraft(draftId, programName) {
        // Create confirmation modal
        const confirmHtml = `
            <div class="modal-section">
                <h4>Delete Draft</h4>
                <p>Are you sure you want to delete this draft for <strong>${escapeHtml(programName)}</strong>?</p>
                <p class="warning-text"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                <div class="modal-actions">
                    <button class="secondary-button" id="cancelDelete">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="delete-button" id="confirmDelete">
                        <i class="fas fa-trash"></i> Delete Draft
                    </button>
                </div>
            </div>
        `;
        
        document.getElementById('modalTitle').textContent = "Confirm Draft Deletion";
        modalBody.innerHTML = confirmHtml;
        detailModal.classList.add('active');
        
        // Add event listeners
        document.getElementById('cancelDelete').addEventListener('click', () => {
            detailModal.classList.remove('active');
        });
        
        document.getElementById('confirmDelete').addEventListener('click', () => {
            deleteSubmission(draftId); // Reuse existing delete function
        });
    }

    function deleteSubmission(submissionId) {
        // Show loading state
        document.getElementById('confirmDelete').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        document.getElementById('confirmDelete').disabled = true;
        document.getElementById('cancelDelete').disabled = true;
        
        // Send delete request to server
        fetch('php/metrics/delete_submission.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: submissionId })
        })
        .then(response => response.json())
        .then(data => {
            // Close the modal
            detailModal.classList.remove('active');
            
            if (data.success) {
                // Show success message
                showNotification(data.message, 'success');
                
                // Animate the row removal
                if (data.wasDraft) {
                    const draftRow = document.querySelector(`.draft-row[data-id="${submissionId}"]`);
                    if (draftRow) {
                        draftRow.style.transition = 'all 0.5s ease';
                        draftRow.style.opacity = '0';
                        draftRow.style.maxHeight = '0';
                        draftRow.style.overflow = 'hidden';
                        
                        // Remove from DOM after animation completes
                        setTimeout(() => {
                            draftRow.remove();
                            // If no drafts left, show the no drafts message
                            if (document.querySelectorAll('.draft-row').length === 0) {
                                noDraftsMessage.style.display = 'block';
                            }
                        }, 500);
                        
                        // Notify other tabs/windows about the change
                        localStorage.setItem('draftsUpdated', Date.now().toString());
                    } else {
                        loadDrafts();
                    }
                } else {
                    loadSubmissions();
                }
            } else {
                // Show error message
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            detailModal.classList.remove('active');
            console.error('Error deleting submission:', error);
            showNotification('Error: ' + error.message, 'error');
        });
    }

    function viewSubmissionDetails(submissionId) {
        // Show loading in the modal
        document.getElementById('modalTitle').textContent = 'Loading...';
        document.getElementById('modalBody').innerHTML = '<div class="loading-spinner"></div>';
        detailModal.classList.add('active');
        
        // Fetch submission details from database with better error handling
        fetch(`php/metrics/get_submission_details.php?id=${submissionId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("JSON Parse Error. Raw response:", text.substring(0, 100));
                        throw new Error("Invalid JSON response from server");
                    }
                });
            })
            .then(data => {
                if (!data.success || !data.data) {
                    throw new Error(data.message || 'Failed to load submission details');
                }

                const submission = data.data;

                // Set modal title
                document.getElementById('modalTitle').textContent = submission.programName || 'Submission Details';

                // Format modal content - adjust for qualitative targets/statuses
                let content = `
                    <div class="modal-section">
                        <h4>Program Information</h4>
                        <p><strong>Description:</strong> ${submission.description || 'No description provided'}</p>
                        <p><strong>Sector:</strong> ${submission.metricTypeName || 'Not specified'}</p>
                        <p><strong>Agency:</strong> ${submission.agencyName || 'Not specified'}</p>
                        <p><strong>Period:</strong> ${submission.year} ${submission.quarter}</p>
                    </div>

                    <div class="modal-section">
                        <h4>Target Information</h4>
                        <p><strong>Target:</strong> ${submission.indicator || submission.targetText || 'No target specified'}</p>
                        ${submission.targetDescription ? `<p><strong>Details:</strong> ${submission.targetDescription}</p>` : ''}
                        ${submission.targetDeadline ? `<p><strong>Deadline:</strong> ${formatDate(submission.targetDeadline)}</p>` : ''}
                    </div>

                    <div class="modal-section">
                        <h4>Current Status</h4>
                        ${submission.statusNotes || submission.statusText ? `<p><strong>Status Update:</strong> ${submission.statusNotes || submission.statusText}</p>` : '<p>No status information available</p>'}
                        <p><strong>Status Date:</strong> ${formatDate(submission.statusDate || new Date())}</p>
                    </div>
                `;
                
                // Set modal content
                document.getElementById('modalBody').innerHTML = content;
            })
            .catch(error => {
                console.error('Error fetching submission details:', error);
                document.getElementById('modalTitle').textContent = 'Error';
                document.getElementById('modalBody').innerHTML = `
                    <div class="modal-section error">
                        <h4><i class="fas fa-exclamation-circle"></i> Error Loading Details</h4>
                        <p>${error.message}</p>
                        <p>Please try again or contact support if the issue persists.</p>
                    </div>
                `;
            });
    }

    function showNotification(message, type) {
        if (!notification) return;
        
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

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById("detailModal");
    const modalTitle = document.getElementById("modalTitle");
    const modalBody = document.getElementById("modalBody");
    const closeBtn = document.querySelector(".close-modal");
    const cancelBtn = document.querySelector(".modal-footer .modal-cancel");

    // Close when clicking the X button
    if (closeBtn) {
        closeBtn.addEventListener("click", function () {
            modal.classList.remove("active");
        });
    }

    // Close when clicking the Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener("click", function () {
            modal.classList.remove("active");
        });
    }

    // Close when clicking outside the modal content
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.classList.remove("active");
        }
    });

    // Function to open modal (can be called from other parts of your code)
    window.openModal = function (title, content) {
        modalTitle.textContent = title;
        modalBody.innerHTML = content; // Insert content into the body section
        modal.classList.add("active");
    };
});
