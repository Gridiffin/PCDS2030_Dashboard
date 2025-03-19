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
    const targetForm = document.getElementById('targetForm');
    const statusForm = document.getElementById('statusForm');
    const saveAsDraftBtn = document.getElementById('saveAsDraft');
    const detailModal = document.getElementById('detailModal');
    const modalContent = document.getElementById('modalContent');
    const notification = document.getElementById('notification');
    const usernameElement = document.getElementById('username');
    const agencyBadge = document.getElementById('agency-badge');

    // Modal elements for the simple modal
    const modalTitle = document.getElementById('modalTitle');
    const closeModalBtn = document.querySelector('.simple-close');
    const modalFooterBtn = document.querySelector('.simple-modal-footer .simple-button');

    // Initialize app
    init();

    // Event listeners
    programSelect.addEventListener('change', handleProgramSelect);
    statusForm.addEventListener('submit', handleSubmit);
    saveAsDraftBtn.addEventListener('click', saveAsDraft);
    
    // Set up modal event listeners
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            detailModal.classList.remove('active');
        });
    }
    
    if (modalFooterBtn) {
        modalFooterBtn.addEventListener('click', function() {
            detailModal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside the content
    window.addEventListener('click', function(event) {
        if (event.target === detailModal) {
            detailModal.classList.remove('active');
        }
    });
    
    // Function to get URL parameters
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        const results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }
    
    // Check if we're editing an existing submission or continuing a draft
    const editSubmissionId = getUrlParameter('edit');
    const draftId = getUrlParameter('draft');
    
    if (editSubmissionId) {
        // Load the submission for editing
        editSubmission(editSubmissionId);
    } else if (draftId) {
        // Load the draft for continuation
        loadDraft(draftId);
    }

    // Functions
    function init() {
        // Load current user data
        loadCurrentUser();
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
                loadPrograms();
            })
            .catch(error => {
                console.error('Error loading user data:', error);
                showNotification('Error loading user data: ' + error.message, 'error');
            });
    }

    function loadPrograms() {
        // Fetch programs for this agency from database
        fetch(`php/metrics/get_programs.php?agencyId=${currentUser.agencyId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success || !data.data) {
                    showNotification('Failed to load programs', 'error');
                    return;
                }

                // Populate program select with only the default and "Create New" options
                programSelect.innerHTML = '<option value="">-- Select Program --</option><option value="new">+ Create New Program</option>';

                // Add a divider before drafts if there are any
                if (data.drafts && data.drafts.length > 0) {
                    const draftsOptGroup = document.createElement('optgroup');
                    draftsOptGroup.label = '--- My Drafts ---';
                    
                    // Add draft options
                    data.drafts.forEach(draft => {
                        const option = document.createElement('option');
                        option.value = `draft_${draft.id}`;
                        option.textContent = `📝 ${draft.programName} (Draft)`;
                        option.className = 'draft-option';
                        draftsOptGroup.appendChild(option);
                    });
                    
                    programSelect.appendChild(draftsOptGroup);
                }
            })
            .catch(error => {
                console.error('Error loading programs:', error);
                showNotification('Error loading programs: ' + error.message, 'error');
            });
    }

    function editSubmission(submissionId) {
        // Fetch submission details from database
        fetch(`php/metrics/get_submission_details.php?id=${submissionId}`)
            .then(response => response.json())
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

                // Set target form fields
                document.getElementById('targetYear').value = submission.year;
                document.getElementById('targetQuarter').value = submission.quarter;
                
                // Set target description if it exists
                if (submission.description) {
                    document.getElementById('targetDescription').value = submission.description;
                }
                
                // Set target deadline if it exists
                if (submission.targetDeadline) {
                    document.getElementById('targetDeadline').value = submission.targetDeadline;
                }

                // Set status form fields
                document.getElementById('statusDate').value = submission.statusDate;
                document.getElementById('statusNotes').value = submission.statusNotes;

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

    function loadDraft(draftId) {
        // Fetch draft details from database
        fetch(`php/metrics/get_submission_details.php?id=${draftId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success || !data.data) {
                    showNotification('Failed to load draft', 'error');
                    return;
                }

                // Populate the form fields with the draft data
                const draft = data.data;

                // For drafts, replace the program selector with a text input for the program name
                const programSelectParent = programSelect.parentElement;
                
                // Hide the dropdown select
                programSelect.style.display = 'none';
                
                // Create a text input for program name if it doesn't exist yet
                let programNameInput = document.getElementById('draftProgramName');
                if (!programNameInput) {
                    programNameInput = document.createElement('input');
                    programNameInput.type = 'text';
                    programNameInput.id = 'draftProgramName';
                    programNameInput.placeholder = 'Enter program name';
                    programNameInput.required = true;
                    programSelectParent.appendChild(programNameInput);
                }
                
                // Set the current program name
                programNameInput.value = draft.programName || '';
                
                // Add a note about editing the program name
                const noteElement = document.createElement('small');
                noteElement.className = 'form-hint';
                noteElement.textContent = 'You can edit the program name for this draft';
                programSelectParent.appendChild(noteElement);
                
                // Hide new program fields since we're using the direct input
                newProgramGroup.style.display = 'none';
                
                // Keep program description field visible and populated
                programDescGroup.style.display = 'block';
                programDesc.value = draft.description || '';

                // Set target form fields
                if (draft.year) document.getElementById('targetYear').value = draft.year;
                if (draft.quarter) document.getElementById('targetQuarter').value = draft.quarter;
                
                if (draft.targetDescription) document.getElementById('targetDescription').value = draft.targetDescription;
                if (draft.targetDeadline) document.getElementById('targetDeadline').value = draft.targetDeadline;
                
                // Set status form fields
                if (draft.statusDate) document.getElementById('statusDate').value = draft.statusDate;
                if (draft.statusNotes) document.getElementById('statusNotes').value = draft.statusNotes;
                
                // Set the status color radio button if it exists
                if (draft.statusColor) {
                    const colorRadio = document.querySelector(`input[name="statusColor"][value="${draft.statusColor}"]`);
                    if (colorRadio) {
                        colorRadio.checked = true;
                    }
                }

                showNotification('Draft loaded successfully. Continue your work and submit when ready.', 'success');
            })
            .catch(error => {
                console.error('Error fetching draft details:', error);
                showNotification('Error loading draft: ' + error.message, 'error');
            });
    }

    function handleProgramSelect() {
        // Check if selected value is a draft
        if (programSelect.value && programSelect.value.startsWith('draft_')) {
            // Extract the draft ID and redirect to edit that draft
            const draftId = programSelect.value.replace('draft_', '');
            window.location.href = `target_status.html?draft=${draftId}`;
            return;
        }
        
        // Regular handling for new programs
        if (programSelect.value === 'new') {
            newProgramGroup.style.display = 'block';
            programDescGroup.style.display = 'block';
            newProgramName.focus();
        } else {
            newProgramGroup.style.display = 'none';
            programDescGroup.style.display = 'none';
        }
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

        // Submit data to database
        submitData(formData, 'submitted')
            .then(response => {
                if (response.success) {
                    // Check if this was a draft being submitted (which would have drafts deleted)
                    const draftSubmitted = getUrlParameter('draft') ? true : false;
                    if (draftSubmitted && response.deletedDraftsCount) {
                        // Notify all tabs that drafts have been updated
                        localStorage.setItem('draftsUpdated', Date.now().toString());
                    }
                    
                    // Enhanced success message with link to view submissions
                    showEnhancedNotification(response.message || 'Data submitted successfully', 'success');
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
    
    // Add enhanced notification with a link to view submissions
    function showEnhancedNotification(message, type) {
        notification.innerHTML = `
            ${message} 
            <div class="notification-action">
                <a href="view_uploads.html" class="notification-link">View All Submissions <i class="fas fa-arrow-right"></i></a>
            </div>
        `;
        notification.className = 'notification'; // Reset classes
        notification.classList.add(type);
        notification.classList.add('with-action'); // Add a class for styling
        notification.style.display = 'block';

        // Hide after 8 seconds (longer due to action link)
        setTimeout(() => {
            notification.style.display = 'none';
        }, 8000);
    }

    function saveAsDraft() {
        // Collect data without complete validation
        const formData = collectFormData(true);

        // Show loading state
        const draftBtn = document.getElementById('saveAsDraft');
        const originalBtnText = draftBtn.innerHTML;
        draftBtn.disabled = true;
        draftBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        // Submit as draft
        submitData(formData, 'draft')
            .then(response => {
                if (response.success) {
                    // Include the draft ID in the notification to allow continuing later
                    showEnhancedDraftNotification(response.message || 'Draft saved successfully', 'success', response.metricId);
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
    
    // Add enhanced notification with option to view drafts
    function showEnhancedDraftNotification(message, type, draftId) {
        notification.innerHTML = `
            ${message} 
            <div class="notification-action">
                <a href="view_uploads.html#drafts" class="notification-link">View All Drafts <i class="fas fa-arrow-right"></i></a>
            </div>
        `;
        notification.className = 'notification'; // Reset classes
        notification.classList.add(type);
        notification.classList.add('with-action'); // Add a class for styling
        notification.style.display = 'block';

        // If this was a newly created draft, update URL to include draft ID
        if (draftId && !window.location.href.includes('draft=')) {
            window.history.replaceState(
                null, 
                document.title, 
                window.location.href.split('?')[0] + '?draft=' + draftId
            );
        }

        // Hide after 8 seconds (longer due to action link)
        setTimeout(() => {
            notification.style.display = 'none';
        }, 8000);
    }

    function validateForms() {
        // Get program name - different depending on if we're in draft mode or regular mode
        const draftProgramNameInput = document.getElementById('draftProgramName');
        
        // Check program selection - handle both regular dropdown and draft input modes
        if (draftProgramNameInput) {
            // Draft mode - check the text input
            if (!draftProgramNameInput.value.trim()) {
                showNotification('Please enter a program name', 'error');
                return false;
            }
        } else {
            // Regular mode - check dropdown
            if (programSelect.value === '' || programSelect.options[programSelect.selectedIndex].text === '-- Select Program --') {
                showNotification('Please select a program or create a new one', 'error');
                return false;
            }

            // Check if new program name is provided when creating new program
            if (programSelect.value === 'new' && !newProgramName.value.trim()) {
                showNotification('Please enter a name for the new program', 'error');
                return false;
            }
        }

        // Validate target form
        const requiredTargetFields = targetForm.querySelectorAll('[required]');
        for (const field of requiredTargetFields) {
            if (!field.value.trim()) {
                showNotification(`Please fill in the required field: ${field.previousElementSibling ? field.previousElementSibling.textContent.replace(' *', '') : 'Required field'}`, 'error');
                field.focus();
                return false;
            }
        }

        // Check status form required fields
        const requiredStatusFields = statusForm.querySelectorAll('[required]');
        for (const field of requiredStatusFields) {
            if (!field.value.trim()) {
                showNotification(`Please fill in the required field: ${field.previousElementSibling ? field.previousElementSibling.textContent.replace(' *', '') : 'Required field'}`, 'error');
                field.focus();
                return false;
            }
        }

        // If we got here, all required fields are filled
        return true;
    }

    function resetForms() {
        document.getElementById('programForm').reset();
        document.getElementById('targetForm').reset();
        document.getElementById('statusForm').reset();
        
        // Reset UI state - remove reference to customUnitGroup
        newProgramGroup.style.display = 'none';
        programDescGroup.style.display = 'none';
        
        // Remove the draft program name input if it exists
        const draftProgramNameInput = document.getElementById('draftProgramName');
        if (draftProgramNameInput) {
            draftProgramNameInput.parentElement.removeChild(draftProgramNameInput);
            // Show the program select dropdown again
            programSelect.style.display = 'block';
            // Remove any form hints we added
            const hints = programSelect.parentElement.querySelectorAll('.form-hint');
            hints.forEach(hint => hint.remove());
        }
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
        // Updated to handle form data without target value and unit
        const targetYear = document.getElementById('targetYear').value;
        const targetQuarter = document.getElementById('targetQuarter').value;
        const targetDescription = document.getElementById('targetDescription').value;
        const targetDeadline = document.getElementById('targetDeadline').value;
        
        // Extract program information correctly
        let programId = '';
        let programName = '';
        let programDescription = '';
        
        // Check if we're editing a draft (draftProgramName field exists)
        const draftProgramNameInput = document.getElementById('draftProgramName');
        if (draftProgramNameInput) {
            // Get program info from the draft program name input
            programId = 'existing_draft'; // Use a special identifier for existing drafts
            programName = draftProgramNameInput.value.trim();
            programDescription = document.getElementById('programDesc').value;
        } else if (programSelect.value === 'new') {
            // Creating a new program
            programId = 'new_' + Date.now(); // Generate a temporary ID for new programs
            programName = document.getElementById('newProgramName').value;
            programDescription = document.getElementById('programDesc').value;
        } else if (programSelect.value.startsWith('existing_')) {
            // Handle case where we're editing and selected an existing program not in dropdown
            programId = programSelect.value;
            programName = programSelect.options[programSelect.selectedIndex].text;
        } else {
            // Get existing program info from selected option
            programId = programSelect.value;
            programName = programSelect.options[programSelect.selectedIndex].text;
        }
        
        const statusDate = document.getElementById('statusDate').value;
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
        
        // Create a summarized version of the target for display in table
        const targetSummary = (targetDescription.length > 30) ? 
            targetDescription.substring(0, 30) + '...' : 
            targetDescription;
        
        // Create a summarized version of the status for display in table
        const statusSummary = statusNotes.length > 30 ? statusNotes.substring(0, 30) + '...' : statusNotes;

        // Create the data object without target value and unit and without challenges field
        const formData = {
            programId,
            programName,
            programDescription,
            metricType: currentUser.allowedMetricTypes.length > 0 ? 
                currentUser.allowedMetricTypes[0].id : 
                currentUser.agencyId.toString(), // Use agency ID as fallback if no metric types,
            year: targetYear,
            quarter: targetQuarter,
            indicator: targetDescription.substring(0, 50), // Use first 50 chars of description as the indicator
            targetDescription,
            targetDeadline,
            targetSummary,
            statusDate,
            statusNotes,
            statusSummary,
            statusCategory, // Derived from statusColor
            statusColor,    // Directly from radio selection
            isDraft,
            sectorID: document.getElementById('sectorSelect')?.value || null
        };

        // When saving a draft, include the draft ID if we're editing an existing draft
        if (isDraft) {
            // Get draft ID from URL if available
            const draftId = getUrlParameter('draft');
            if (draftId) {
                formData.draftId = draftId; // Include the draft ID to update existing draft
            }
        }
        
        return formData;
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

    function submitData(formData, status) {
        // Set the status
        formData.status = status;
        
        // Just send the JSON data directly - no more file uploads
        return fetch('php/metrics/save_target_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json());
    }

    // When document is loaded, add listener for localStorage changes
    window.addEventListener('storage', function(e) {
        // Check if the drafts have been updated in another tab/window
        if (e.key === 'draftsUpdated') {
            // If we were editing a draft that's now deleted, redirect to the form
            const draftId = getUrlParameter('draft');
            if (draftId) {
                // Check if this draft still exists
                fetch(`php/metrics/check_draft_exists.php?id=${draftId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.exists) {
                            // This draft no longer exists, redirect to clean form
                            window.location.href = 'target_status.html';
                        }
                    })
                    .catch(err => console.error('Error checking if draft exists:', err));
            }
        }
    });

    function closeModal() {
        detailModal.classList.remove('active');
    }
    
    // Function to open modal (can be called from other parts of your code)
    window.openModal = function(title, content) {
        if (modalTitle) modalTitle.textContent = title;
        if (modalContent) modalContent.innerHTML = content;
        if (detailModal) detailModal.classList.add('active');
    };
});

// Update modal functionality to work with the new modal design
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality for the redesigned modal
    const modal = document.getElementById("detailModal");
    const closeBtn = document.querySelector(".close-modal");
    const footerBtn = document.querySelector(".modal-footer .modal-button");
    
    // Close when clicking the x button
    if (closeBtn) {
        closeBtn.addEventListener("click", function() {
            modal.classList.remove("active");
        });
    }
    
    // Close when clicking the footer button
    if (footerBtn) {
        footerBtn.addEventListener("click", function() {
            modal.classList.remove("active");
        });
    }
    
    // Close when clicking outside the modal content
    window.addEventListener("click", function(event) {
        if (event.target == modal) {
            modal.classList.remove("active");
        }
    });
    
    // Function to open modal (can be called from other parts of your code)
    window.openModal = function(title, content) {
        document.getElementById("modalTitle").textContent = title;
        document.getElementById("modalBody").innerHTML = content; // Insert content into the body section
        modal.classList.add("active");
    };
    
    // Add any other modal-related functionality...
});

function processResponse(data) {
    // ...existing code...
    
    // If accessing sector data, make sure to use sectorID, not sector
    const sectorID = data.sectorID || null;
    
    // ...existing code...
}