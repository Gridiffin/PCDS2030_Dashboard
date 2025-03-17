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
    const programSelect = document.getElementById('programSelect');
    const newProgramGroup = document.getElementById('newProgramGroup');
    const newProgramName = document.getElementById('newProgramName');
    const programDescGroup = document.getElementById('programDescGroup');
    const programDesc = document.getElementById('programDesc');
    const metricTypeSelect = document.getElementById('metricType');
    const metricTypeHint = document.getElementById('metricTypeHint');
    const targetForm = document.getElementById('targetForm');
    const targetUnitSelect = document.getElementById('targetUnit');
    const customUnitGroup = document.getElementById('customUnitGroup');
    const statusForm = document.getElementById('statusForm');
    const saveAsDraftBtn = document.getElementById('saveAsDraft');
    const completionPercentage = document.getElementById('completionPercentage');
    const completionOutput = document.querySelector('output[for="completionPercentage"]');
    const detailModal = document.getElementById('detailModal');
    const modalContent = document.getElementById('modalContent');
    const notification = document.getElementById('notification');
    const usernameElement = document.getElementById('username');
    const agencyBadge = document.getElementById('agency-badge');

    // Additional DOM elements for new fields
    const targetTypeSelect = document.getElementById('targetType');
    const quantitativeFields = document.getElementById('quantitativeFields');
    const statusTypeSelect = document.getElementById('statusType');
    const quantitativeStatusFields = document.getElementById('quantitativeStatusFields');

    // Initialize app
    init();

    // Function to get URL parameters
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        const results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }
    
    // Check if we're editing an existing submission
    const editSubmissionId = getUrlParameter('edit');
    if (editSubmissionId) {
        // Load the submission for editing
        editSubmission(editSubmissionId);
    }

    // Event listeners
    programSelect.addEventListener('change', handleProgramSelect);
    targetUnitSelect.addEventListener('change', handleUnitChange);
    completionPercentage.addEventListener('input', updateCompletionOutput);
    statusForm.addEventListener('submit', handleSubmit);
    document.querySelector('.close-modal').addEventListener('click', () => detailModal.classList.remove('active'));
    saveAsDraftBtn.addEventListener('click', saveAsDraft);
    
    // Add event listeners for new fields
    targetTypeSelect.addEventListener('change', handleTargetTypeChange);
    statusTypeSelect.addEventListener('change', handleStatusTypeChange);
    
    // Function to update file upload display
    document.getElementById('supportingFiles').addEventListener('change', function(e) {
        const fileInfo = document.querySelector('.file-info');
        const files = e.target.files;
        
        if (files.length > 0) {
            fileInfo.textContent = files.length === 1 ? 
                `${files[0].name} selected` : 
                `${files.length} files selected`;
        } else {
            fileInfo.textContent = 'No files selected';
        }
    });

    // Functions
    function init() {
        // Get mock data mode status
        const useMockData = window.PCDS_MOCK_DATA === true;
        console.log('Initializing with ' + (useMockData ? 'mock' : 'real') + ' data');

        // Load current user data
        loadCurrentUser();
        
        // The rest of initialization will be done after user data is loaded
    }

    function loadCurrentUser() {
        // Get mock data mode status
        const useMockData = window.PCDS_MOCK_DATA === true;

        // Use the appropriate data source
        const fetchPromise = useMockData ?
            window.fetchCurrentUser() :
            fetch('php/auth/get_current_user.php')
                .then(response => response.json())
                .then(data => data.user);

        fetchPromise
            .then(userData => {
                if (!userData || !userData.id) {
                    showNotification('Failed to load user data', 'error');
                    return;
                }

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
                loadMetricTypes();
                loadPrograms();
            })
            .catch(error => {
                console.error('Error loading user data:', error);
                showNotification('Error loading user data: ' + error.message, 'error');
            });
    }

    function loadMetricTypes() {
        const useMockData = window.PCDS_MOCK_DATA === true;
        
        // Fetch agency-specific allowed metric types
        const fetchPromise = useMockData ?
            window.fetchAgencyMetricTypes(currentUser.agencyId) :
            fetch(`php/metrics/get_agency_metric_types.php?agencyId=${currentUser.agencyId}`)
                .then(response => response.json());

        fetchPromise
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load metric types');
                }
                
                // Store allowed metric types
                currentUser.allowedMetricTypes = data.data;
                
                // Clear existing options except the default one
                metricTypeSelect.innerHTML = '<option value="">-- Select Sector --</option>';
                
                // Add options for each allowed metric type
                data.data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    metricTypeSelect.appendChild(option);
                });
                
                // Update hint text
                metricTypeHint.textContent = `Your agency can only submit data for: ${data.data.map(t => t.name).join(', ')}`;
            })
            .catch(error => {
                console.error('Error loading metric types:', error);
                showNotification('Could not load sector data. Please try again.', 'error');
            });
    }

    function loadPrograms() {
        // Get mock data mode status
        const useMockData = window.PCDS_MOCK_DATA === true;

        // Use the appropriate data source
        const fetchPromise = useMockData ?
            window.fetchPrograms(currentUser.agencyId) :
            fetch(`php/metrics/get_programs.php?agencyId=${currentUser.agencyId}`)
                .then(response => response.json());

        fetchPromise
            .then(data => {
                if (!data.success || !data.data) {
                    showNotification('Failed to load programs', 'error');
                    return;
                }

                // Populate program select
                programSelect.innerHTML = '<option value="">-- Select Program --</option><option value="new">+ Create New Program</option>';
                data.data.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    programSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading programs:', error);
                showNotification('Error loading programs: ' + error.message, 'error');
            });
    }

    function editSubmission(submissionId) {
        // Get mock data mode status
        const useMockData = window.PCDS_MOCK_DATA === true;

        // Use the appropriate data source
        const fetchPromise = useMockData ?
            window.fetchSubmissionDetails(submissionId) :
            fetch(`php/metrics/get_submission_details.php?id=${submissionId}`)
                .then(response => response.json());

        fetchPromise
            .then(data => {
                if (!data.success || !data.data) {
                    showNotification('Failed to load submission for editing', 'error');
                    return;
                }

                // Populate the form fields with the submission data
                const submission = data.data;

                // Set program selection
                const programOption = Array.from(programSelect.options).find(opt => opt.textContent === submission.programName);
                if (programOption) {
                    programSelect.value = programOption.value;
                } else {
                    // If program doesn't exist in the dropdown, add it
                    const newOption = document.createElement('option');
                    newOption.value = `existing_${submission.id}`;
                    newOption.textContent = submission.programName;
                    programSelect.add(newOption, 1); // Add after the default option
                    programSelect.value = newOption.value;
                }

                // Set metric type
                metricTypeSelect.value = submission.metricType;

                // Set target form fields
                document.getElementById('targetYear').value = submission.year;
                document.getElementById('targetQuarter').value = submission.quarter;
                document.getElementById('indicatorName').value = submission.indicator;
                document.getElementById('targetValue').value = submission.targetValue;
                document.getElementById('targetUnit').value = submission.targetUnit === 'other' ? 'other' : submission.targetUnit;

                if (submission.targetUnit === 'other') {
                    document.getElementById('customUnit').value = submission.targetUnit;
                    customUnitGroup.style.display = 'block';
                }

                if (submission.targetDeadline) {
                    document.getElementById('targetDeadline').value = submission.targetDeadline;
                }

                document.getElementById('targetDescription').value = submission.description || '';

                // Set status form fields
                document.getElementById('currentValue').value = submission.currentValue;
                document.getElementById('statusDate').value = submission.statusDate;
                document.getElementById('completionPercentage').value = submission.completionPercentage;
                completionOutput.textContent = `${submission.completionPercentage}%`;
                document.getElementById('statusNotes').value = submission.statusNotes;
                document.getElementById('challenges').value = submission.challenges || '';

                // Set the status color radio button
                if (submission.statusColor) {
                    const colorRadio = document.querySelector(`input[name="statusColor"][value="${submission.statusColor}"]`);
                    if (colorRadio) {
                        colorRadio.checked = true;
                    }
                } else {
                    // Default color based on status
                    let defaultColor;
                    switch (submission.status) {
                        case 'completed':
                            defaultColor = 'completed';
                            break;
                        case 'nearly-complete':
                            defaultColor = 'progress';
                            break;
                        case 'in-progress':
                            defaultColor = 'progress';
                            break;
                        case 'not-started':
                            defaultColor = 'draft';
                            break;
                        case 'delayed':
                            defaultColor = 'warning';
                            break;
                        default:
                            defaultColor = 'draft';
                    }
                    
                    const colorRadio = document.querySelector(`input[name="statusColor"][value="${defaultColor}"]`);
                    if (colorRadio) {
                        colorRadio.checked = true;
                    }
                }

                // Scroll to the program selection section
                document.querySelector('.dashboard-section:nth-child(2)').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                showNotification('Loaded submission for editing. Make your changes and submit.', 'success');
            })
            .catch(error => {
                console.error('Error fetching submission details for editing:', error);
                showNotification('Error loading submission: ' + error.message, 'error');
            });
    }

    function handleProgramSelect() {
        if (programSelect.value === 'new') {
            newProgramGroup.style.display = 'block';
            programDescGroup.style.display = 'block';
            newProgramName.focus();
        } else {
            newProgramGroup.style.display = 'none';
            programDescGroup.style.display = 'none';
        }
    }

    function handleUnitChange() {
        if (targetUnitSelect.value === 'other') {
            customUnitGroup.style.display = 'block';
            document.getElementById('customUnit').focus();
        } else {
            customUnitGroup.style.display = 'none';
        }
    }

    function handleTargetTypeChange() {
        if (targetTypeSelect.value === 'quantitative') {
            quantitativeFields.style.display = 'block';
        } else {
            quantitativeFields.style.display = 'none';
        }
    }
    
    function handleStatusTypeChange() {
        if (statusTypeSelect.value === 'quantitative' || statusTypeSelect.value === 'both') {
            quantitativeStatusFields.style.display = 'block';
        } else {
            quantitativeStatusFields.style.display = 'none';
        }
    }

    function updateCompletionOutput() {
        const value = completionPercentage.value;
        completionOutput.textContent = `${value}%`;
    }

    function handleSubmit(e) {
        e.preventDefault();

        // Validate all required fields
        if (!validateForms()) {
            showNotification('Please fill in all required fields', 'error');
            return;
        }

        // Collect data from forms
        const formData = collectFormData();

        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        // Simulate API call to submit data
        submitData(formData, 'submitted')
            .then(response => {
                if (response.success) {
                    showNotification(response.message || 'Data submitted successfully', 'success');
                    resetForms();
                } else {
                    showNotification(response.message || 'Error submitting data', 'error');
                }
            })
            .catch(error => {
                console.error('Submission error:', error);
                showNotification('Error: ' + error.message, 'error');
            })
            .finally(() => {
                // Restore button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
    }

    function saveAsDraft() {
        // Collect data without complete validation
        const formData = collectFormData(true);

        // Show loading state
        const draftBtn = document.getElementById('saveAsDraft');
        const originalBtnText = draftBtn.innerHTML;
        draftBtn.disabled = true;
        draftBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        // Simulate API call to save as draft
        submitData(formData, 'draft')
            .then(response => {
                if (response.success) {
                    showNotification(response.message || 'Draft saved successfully', 'success');
                } else {
                    showNotification(response.message || 'Error saving draft', 'error');
                }
            })
            .catch(error => {
                console.error('Draft save error:', error);
                showNotification('Error: ' + error.message, 'error');
            })
            .finally(() => {
                // Restore button state
                draftBtn.disabled = false;
                draftBtn.innerHTML = originalBtnText;
            });
    }

    function validateForms() {
        // Get all required fields
        const programForm = document.getElementById('programForm');

        // Check if program is selected or new program name is provided
        if (programSelect.value === '') {
            return false;
        }

        if (programSelect.value === 'new' && !newProgramName.value.trim()) {
            return false;
        }

        // Check if metric type is selected
        if (!metricTypeSelect.value) {
            return false;
        }

        // Validate target form
        const requiredTargetFields = targetForm.querySelectorAll('[required]');
        for (const field of requiredTargetFields) {
            if (!field.value.trim()) {
                return false;
            }
        }
        // Check if we're in mock mode from mock_data.js
        const useMockData = window.PCDS_MOCK_DATA === true;

        if (useMockData) {
            // Use mock data function
            return window.saveSubmission(formData);
        } else {
            // Real API call
            formData.status = status;

            // In a real implementation, we would properly handle file uploads with FormData
            // For this example, we're using JSON
            return fetch('php/metrics/save_target_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json());
        }
    }

    function resetForms() {
        document.getElementById('programForm').reset();
        document.getElementById('targetForm').reset();
        document.getElementById('statusForm').reset();
        customUnitGroup.style.display = 'none';
        newProgramGroup.style.display = 'none';
        programDescGroup.style.display = 'none';
        completionOutput.textContent = '0%';
        document.querySelector('.file-info').textContent = 'No files selected';
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

    function collectFormData(isDraft = false) {
        // Updated to handle qualitative/quantitative data types
        const targetYear = document.getElementById('targetYear').value;
        const targetQuarter = document.getElementById('targetQuarter').value;
        const indicatorName = document.getElementById('indicatorName').value;
        const targetDescription = document.getElementById('targetDescription').value;
        const targetType = document.getElementById('targetType').value;
        const targetDeadline = document.getElementById('targetDeadline').value;
        
        // Only collect these if target type is quantitative
        const targetValue = targetType === 'quantitative' ? document.getElementById('targetValue').value : '';
        const targetUnit = targetType === 'quantitative' ? 
            (targetUnitSelect.value === 'other' ? document.getElementById('customUnit').value : targetUnitSelect.value) : '';
        
        const statusDate = document.getElementById('statusDate').value;
        const statusType = document.getElementById('statusType').value;
        const statusNotes = document.getElementById('statusNotes').value;
        
        // Get the selected status color (the value of the checked radio button)
        const statusColor = document.querySelector('input[name="statusColor"]:checked').value;
        
        // Map statusColor to a status category (for backend compatibility)
        let statusCategory; 
        switch (statusColor) {
            case 'completed':
                statusCategory = 'completed';
                break;
            case 'progress':
                statusCategory = 'in-progress';
                break;
            case 'warning':
                statusCategory = 'delayed';
                break;
            case 'draft':
                statusCategory = 'not-started';
                break;
            default:
                statusCategory = 'in-progress';
        }
        
        // Only collect these if status type includes quantitative
        const currentValue = (statusType === 'quantitative' || statusType === 'both') ? 
            document.getElementById('currentValue').value : '';
        const completionPercent = (statusType === 'quantitative' || statusType === 'both') ? 
            document.getElementById('completionPercentage').value : '';
            
        const challenges = document.getElementById('challenges').value;
        
        // Create a summarized version of the target for display in table
        const targetSummary = targetType === 'quantitative' ? 
            `${targetValue} ${targetUnit}` : 
            (targetDescription.length > 30 ? targetDescription.substring(0, 30) + '...' : targetDescription);
        
        // Create a summarized version of the status for display in table
        const statusSummary = statusNotes.length > 30 ? statusNotes.substring(0, 30) + '...' : statusNotes;

        // Create the data object with new fields
        return {
            programId,
            programName,
            programDescription: programDesc,
            metricType: metricTypeSelect.value,
            year: targetYear,
            quarter: targetQuarter,
            indicator: indicatorName,
            targetDescription,
            targetType,
            targetValue,
            targetUnit,
            targetDeadline,
            targetSummary,
            statusDate,
            statusType,
            statusNotes,
            statusSummary,
            statusCategory, // Derived from statusColor
            statusColor,    // Directly from radio selection
            currentValue,
            completionPercentage: completionPercent,
            challenges,
            isQualitative: targetType === 'qualitative',
            isQuantitative: targetType === 'quantitative',
            isDraft
        };
    }

    function getStatusCategoryName(category) {
        const statusNames = {
            'not-started': 'Not Started',
            'in-progress': 'In Progress',
            'nearly-complete': 'Nearly Complete',
            'completed': 'Completed',
            'delayed': 'Delayed',
            'cancelled': 'Cancelled',
            'draft': 'Draft'
        };
        return statusNames[category] || category;
    }
});