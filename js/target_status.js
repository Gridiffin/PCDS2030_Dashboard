document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const form = document.getElementById('targetStatusForm');
    const programIdField = document.getElementById('programId');
    const saveAsDraftBtn = document.getElementById('saveAsDraftBtn');
    const submitBtn = document.getElementById('submitBtn');
    const pageTitle = document.getElementById('pageTitle');
    const notification = document.getElementById('notification');
    
    // State
    const urlParams = new URLSearchParams(window.location.search);
    const programId = urlParams.get('program');
    let isEditing = false;
    
    // Initialize form
    initializeForm();
    
    // Event listeners
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
    
    if (saveAsDraftBtn) {
        saveAsDraftBtn.addEventListener('click', saveAsDraft);
    }
    
    // Functions
    function initializeForm() {
        // Populate year dropdown with options from current year to 2030
        const yearSelect = document.getElementById('year');
        if (yearSelect) {
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year <= 2030; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }
        }
        
        // Default status date to today
        const statusDateField = document.getElementById('statusDate');
        if (statusDateField) {
            statusDateField.value = new Date().toISOString().split('T')[0];
        }
        
        // Set program ID if editing
        if (programId && programIdField) {
            programIdField.value = programId;
            isEditing = true;
            if (pageTitle) pageTitle.textContent = 'Edit Target Status';
            
            // Fetch existing data and populate form
            fetchProgramData(programId);
        } else if (programIdField) {
            // Generate a new unique ID for new programs
            programIdField.value = 'new_' + Date.now();
        }
    }
    
    function fetchProgramData(programId) {
        // Show loading state
        showNotification('Loading program data...', 'info');
        
        fetch(`php/metrics/get_program.php?id=${programId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load program data');
                }
                
                populateForm(data.data);
                showNotification('Program data loaded successfully', 'success');
            })
            .catch(error => {
                console.error('Error loading program data:', error);
                showNotification('Error loading program data: ' + error.message, 'error');
            });
    }
    
    function populateForm(data) {
        // Program information
        const programNameField = document.getElementById('programName');
        if (programNameField) programNameField.value = data.programName || '';
        
        // Target details (simplified for the new structure)
        const targetTextField = document.getElementById('targetText');
        if (targetTextField) targetTextField.value = data.targetText || '';
        
        // Status details
        const statusTextField = document.getElementById('statusText');
        if (statusTextField) statusTextField.value = data.statusText || '';
        
        const statusDateField = document.getElementById('statusDate');
        if (statusDateField) statusDateField.value = data.statusDate || '';
        
        // Set the status color radio button
        if (data.statusColor) {
            const colorRadio = document.querySelector(`input[name="statusColor"][value="${data.statusColor}"]`);
            if (colorRadio) {
                colorRadio.checked = true;
            }
        }
        
        // Reporting period
        const quarterField = document.getElementById('quarter');
        if (quarterField) quarterField.value = data.quarter || 'Q1';
        
        const yearField = document.getElementById('year');
        if (yearField) yearField.value = data.year || new Date().getFullYear();
    }
    
    function collectFormData(isDraft = false) {
        const formData = {
            programId: document.getElementById('programId').value,
            programName: document.getElementById('programName').value,
            targetText: document.getElementById('targetText').value,
            statusText: document.getElementById('statusText').value,
            statusDate: document.getElementById('statusDate').value,
            statusColor: document.querySelector('input[name="statusColor"]:checked').value,
            quarter: document.getElementById('quarter').value,
            year: document.getElementById('year').value,
            isDraft: isDraft,
            metricType: document.getElementById('metricType').value,
            lastUpdated: new Date().toISOString()
        };
        
        return formData;
    }
    
    function validateForm() {
        // Required fields: programName, targetText, statusText, statusColor
        const requiredFields = [
            { id: 'programName', label: 'Program Name' },
            { id: 'targetText', label: 'Target' },
            { id: 'statusText', label: 'Status Description' },
            { id: 'quarter', label: 'Quarter' },
            { id: 'year', label: 'Year' }
        ];
        
        for (const field of requiredFields) {
            const element = document.getElementById(field.id);
            if (!element || !element.value.trim()) {
                showNotification(`${field.label} is required`, 'error');
                if (element) element.focus();
                return false;
            }
        }
        
        // Check that a status color is selected
        if (!document.querySelector('input[name="statusColor"]:checked')) {
            showNotification('Please select a status indicator', 'error');
            return false;
        }
        
        return true;
    }
    
    function handleSubmit(e) {
        e.preventDefault();
        
        if (!validateForm()) return;
        
        // Collect form data
        const formData = collectFormData(false);
        
        // Show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        }
        
        // Submit data to server
        submitFormData(formData)
            .then(response => {
                if (response.success) {
                    // Show success notification with link instead of redirecting
                    showNotificationWithLink(
                        'Target status submitted successfully!', 
                        'View all submissions', 
                        'view_uploads.php',
                        'success'
                    );
                    
                    // Reset form if desired
                    // form.reset();
                    
                    // Re-enable the button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Submit';
                    }
                } else {
                    throw new Error(response.message || 'Failed to submit form');
                }
            })
            .catch(error => {
                console.error('Form submission error:', error);
                showNotification('Error: ' + error.message, 'error');
                
                // Reset button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit';
                }
            });
    }
    
    function saveAsDraft() {
        // Validate minimal data requirements
        const programNameField = document.getElementById('programName');
        if (!programNameField || !programNameField.value.trim()) {
            showNotification('Program Name is required, even for drafts', 'error');
            if (programNameField) programNameField.focus();
            return;
        }
        
        // Collect form data with isDraft flag
        const formData = collectFormData(true);
        
        // Show loading state
        if (saveAsDraftBtn) {
            saveAsDraftBtn.disabled = true;
            saveAsDraftBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        }
        
        // Submit data to server
        submitFormData(formData)
            .then(response => {
                if (response.success) {
                    showNotification('Draft saved successfully', 'success');
                    
                    // Update URL if this was a new program
                    if (!isEditing && response.programId) {
                        window.history.replaceState(null, '', `target_status.php?program=${response.programId}`);
                        if (programIdField) programIdField.value = response.programId;
                        isEditing = true;
                        if (pageTitle) pageTitle.textContent = 'Edit Target Status';
                    }
                } else {
                    throw new Error(response.message || 'Failed to save draft');
                }
            })
            .catch(error => {
                console.error('Save draft error:', error);
                showNotification('Error: ' + error.message, 'error');
            })
            .finally(() => {
                // Reset button
                if (saveAsDraftBtn) {
                    saveAsDraftBtn.disabled = false;
                    saveAsDraftBtn.innerHTML = 'Save as Draft';
                }
            });
    }
    
    function submitFormData(formData) {
        return fetch('php/metrics/save_target_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        });
    }
    
    function showNotification(message, type) {
        if (!notification) return;
        
        // Reset any existing styles and classes
        notification.className = 'notification';
        notification.classList.add(type);
        
        // Set message
        notification.textContent = message;
        notification.style.display = 'block';
        
        // Hide after 3 seconds
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    // Add a new function to show notification with link
    function showNotificationWithLink(message, linkText, linkUrl, type) {
        if (!notification) return;
        
        // Reset any existing styles and classes
        notification.className = 'notification';
        notification.classList.add(type);
        notification.classList.add('with-action');
        
        // Create notification content with link
        notification.innerHTML = `
            <div>${message}</div>
            <div class="notification-action">
                <a href="${linkUrl}" class="notification-link">${linkText} <i class="fas fa-arrow-right"></i></a>
            </div>
        `;
        
        // Show notification
        notification.style.display = 'block';
        
        // Make notification width appropriate for content
        notification.style.maxWidth = '320px';
        
        // Hide after 10 seconds (longer since user might want to click the link)
        setTimeout(() => {
            notification.style.display = 'none';
        }, 10000);
    }
});