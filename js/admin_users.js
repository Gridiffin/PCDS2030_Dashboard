document.addEventListener('DOMContentLoaded', function() {
    // Get form and table elements
    const userForm = document.getElementById('userForm');
    const usersTableBody = document.querySelector('#usersTable tbody');
    const searchUserInput = document.getElementById('searchUser');
    const filterRoleSelect = document.getElementById('filterRole');
    const filterDepartmentSelect = document.getElementById('filterDepartment');
    const resetFilterButton = document.querySelector('.filter-actions button');
    
    // Load roles dropdown
    loadRoles();
    
    // Load agencies dropdown
    loadAgencies();
    
    // Load sectors for filtering
    loadSectors();
    
    // Load initial user data
    loadUsers();
    
    // Add event listeners
    userForm.addEventListener('submit', handleFormSubmit);
    searchUserInput.addEventListener('input', filterUsers);
    filterRoleSelect.addEventListener('change', filterUsers);
    filterDepartmentSelect.addEventListener('change', filterUsers);
    resetFilterButton.addEventListener('click', resetFilters);
    
    // Password generation and toggle visibility
    const generatePasswordBtn = document.getElementById('generatePassword');
    if (generatePasswordBtn) {
        generatePasswordBtn.addEventListener('click', generatePassword);
    }
    
    const passwordField = document.getElementById('password');
    if (passwordField) {
        const newToggleBtn = document.createElement('button');
        newToggleBtn.id = 'betterTogglePassword';
        newToggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
        newToggleBtn.type = 'button';
        passwordField.parentElement.style.position = 'relative'; // Ensure parent is positioned
        passwordField.parentElement.appendChild(newToggleBtn);

        newToggleBtn.addEventListener('click', function() {
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            this.innerHTML = type === 'text'
                ? '<i class="fas fa-eye-slash"></i>'
                : '<i class="fas fa-eye"></i>';
        });
    }
    
    // Set up role-based permission checkboxes logic
    const roleSelect = document.getElementById('userRole');
    roleSelect.addEventListener('change', updatePermissionCheckboxes);
    
    // Functions
    function handleFormSubmit(e) {
        e.preventDefault();

        // Prepare form data
        const formData = new FormData(userForm);
        formData.append('operation', 'add');

        console.log("Attempting to add user...");
        fetch('/pcds2030_dashboard/php/admin/manage_users.php', {
            method: 'POST',
            body: formData
        })
        .then((response) => {
            console.log("POST response code:", response.status);
            return response.text();
        })
        .then((text) => {
            console.log("Raw response from server:\n", text);
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                throw new Error("Server did not return valid JSON.");
            }

            if (data.success) {
                showNotification(data.message || 'User added successfully.', 'success');
                userForm.reset();
                loadUsers();
            } else {
                showNotification(data.message || 'Could not add user.', 'error');
            }
        })
        .catch((error) => {
            console.error('Add user error:', error);
            showNotification('An error occurred: ' + error.message, 'error');
        });
    }
    
    function validateForm(formData) {
        // Required fields - updated to match users table columns
            const requiredFields = ['username', 'password', 'roleId', 'agencyId'];
        for (const field of requiredFields) {
            if (!formData.get(field) || formData.get(field).trim() === '') {
                showNotification(`Please fill in all required fields: ${field.replace(/([A-Z])/g, ' $1').toLowerCase()} is missing`, 'error');
                return false;
            }
        }
        
        // Password strength check (basic)
        const password = formData.get('password');
        if (password.length < 8) {
            showNotification('Password must be at least 8 characters long', 'error');
            return false;
        }
        
        return true;
    }
    
    function loadUsers() {
        usersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;"><i class="fas fa-spinner fa-spin"></i> Loading users...</td></tr>';
        
        console.log('Loading users data - start of function');
        
        fetch('/pcds2030_dashboard/php/admin/manage_users.php?operation=get')
            .then(response => {
                console.log('Users response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log("Raw user data response preview:", text.substring(0, 500));
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        renderUsersTable(data.data);
                    } else {
                        console.error('Failed to load users:', data.message);
                        usersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Failed to load users: ' + 
                            (data.message || 'Unknown error') + '</td></tr>';
                    }
                } catch (err) {
                    console.error("JSON parse error:", err, "Raw response preview:", text.substring(0, 100));
                    usersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Error parsing user data</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);
                usersTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Error loading users</td></tr>';
            });
    }
    
    function loadRoles() {
        console.log('Loading roles...');
        
        // Show debugging message in the select element
        const roleSelect = document.getElementById('userRole');
        if (roleSelect) {
            roleSelect.innerHTML = '<option value="">Loading roles...</option>';
        }
        
        fetch('/pcds2030_dashboard/php/admin/manage_users.php?operation=getRoles')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                // First get response as text for debugging
                return response.text();
            })
            .then(text => {
                try {
                    // Log raw response for debugging
                    console.log("Raw API response:", text.substring(0, 200) + (text.length > 200 ? "..." : ""));
                    
                    // Try to parse as JSON
                    const data = JSON.parse(text);
                    console.log('Roles data received:', data);
                    
                    if (data.success && data.data) {
                        const roleSelect = document.getElementById('userRole');
                        if (roleSelect) {
                            roleSelect.innerHTML = '<option value="">Select Role</option>';
                            
                            data.data.forEach(role => {
                                const option = document.createElement('option');
                                option.value = role.RoleID;
                                option.textContent = role.RoleName;
                                roleSelect.appendChild(option);
                            });
                        }
                        
                        // Also update the filter dropdown
                        const filterRoleSelect = document.getElementById('filterRole');
                        if (filterRoleSelect) {
                            filterRoleSelect.innerHTML = '<option value="">All Roles</option>';
                            
                            data.data.forEach(role => {
                                const option = document.createElement('option');
                                option.value = role.RoleID;
                                option.textContent = role.RoleName;
                                filterRoleSelect.appendChild(option);
                            });
                        }
                    } else {
                        console.error('Failed to load roles:', data.message || 'Unknown error');
                        showNotification('Could not load roles. Please refresh the page.', 'error');
                    }
                } catch (err) {
                    console.error("JSON parse error:", err, "Raw response preview:", text.substring(0, 100));
                    showNotification('Error loading roles data. Please check console for details.', 'error');
                    throw err;
                }
            })
            .catch(error => {
                console.error('Error loading roles:', error);
                showNotification('Failed to connect to server. Please check your network connection.', 'error');
                
                // Fallback to static options
                const roleSelect = document.getElementById('userRole');
                if (roleSelect) {
                    roleSelect.innerHTML = `
                        <option value="">Select Role</option>
                        <option value="1">Admin</option>
                        <option value="2">User</option>
                    `;
                }
            });
    }
    
    function loadAgencies() {
        console.log('Loading agencies...');
        
        // Show debugging message in the select element
        const agencySelect = document.getElementById('agency');
        if (agencySelect) {
            agencySelect.innerHTML = '<option value="">Loading agencies...</option>';
        }
        
        fetch('/pcds2030_dashboard/php/admin/manage_users.php?operation=getAgencies')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                // First get response as text for debugging
                return response.text();
            })
            .then(text => {
                try {
                    // Log raw response for debugging
                    console.log("Raw API response:", text.substring(0, 200) + (text.length > 200 ? "..." : ""));
                    
                    // Try to parse as JSON
                    const data = JSON.parse(text);
                    console.log('Agencies data received:', data);
                    
                    if (data.success && data.data) {
                        const agencySelect = document.getElementById('agency');
                        if (agencySelect) {
                            agencySelect.innerHTML = '<option value="">Select Agency</option>';
                            
                            data.data.forEach(agency => {
                                const option = document.createElement('option');
                                option.value = agency.AgencyID;
                                option.textContent = agency.AgencyName;
                                agencySelect.appendChild(option);
                            });
                        }
                    } else {
                        console.error('Failed to load agencies:', data.message || 'Unknown error');
                        showNotification('Could not load agencies. Please refresh the page.', 'error');
                    }
                } catch (err) {
                    console.error("JSON parse error:", err, "Raw response preview:", text.substring(0, 100));
                    showNotification('Error loading agencies data. Please check console for details.', 'error');
                    throw err;
                }
            })
            .catch(error => {
                console.error('Error loading agencies:', error);
                showNotification('Failed to connect to server. Please check your network connection.', 'error');
                
                // Fallback to static options
                const agencySelect = document.getElementById('agency');
                if (agencySelect) {
                    agencySelect.innerHTML = `
                        <option value="">Select Agency</option>
                        <option value="1">Agency 1</option>
                        <option value="2">Agency 2</option>
                    `;
                }
            });
    }
    
    // Function to load sectors for filtering
    function loadSectors() {
        console.log('Loading sectors - start of function');
        
        fetch('/pcds2030_dashboard/php/admin/manage_users.php?operation=getSectors')
            .then(response => {
                console.log('Sectors response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    console.log("Raw sectors API response:", text);
                    
                    const data = JSON.parse(text);
                    console.log('Sectors data received:', data);
                    
                    // Update the filter dropdown
                    if (data.success && data.data) {
                        const filterSectorSelect = document.getElementById('filterDepartment');
                        if (filterSectorSelect) {
                            filterSectorSelect.innerHTML = '<option value="">All Sectors</option>';
                            
                            if (data.data.length === 0) {
                                console.warn('No sectors found in database');
                            }
                            
                            data.data.forEach(sector => {
                                const option = document.createElement('option');
                                option.value = sector.id || sector.name;
                                option.textContent = sector.name;
                                filterSectorSelect.appendChild(option);
                            });
                        }
                    } else {
                        console.error('Failed to load sectors:', data.message || 'Unknown error');
                    }
                } catch (err) {
                    console.error("JSON parse error:", err, "Raw response preview:", text);
                }
            })
            .catch(error => {
                console.error('Error loading sectors:', error);
                // Use fallback options for the sectors dropdown
            });
    }
    
    function renderUsersTable(users) {
        usersTableBody.innerHTML = '';
        if (!users || users.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="7" style="text-align: center;">No users found</td>';
            usersTableBody.appendChild(row);
            return;
        }
        
        console.log("User data for debugging:", users);
        
        users.forEach(user => {
            const row = document.createElement('tr');
            const statusClass = user.status === 'active' ? 'status-success' : 'status-warning';
            
            // Access sector name with various case possibilities
            const sectorName = user.SectorName || user.sectorName || user.Sector || user.sector || 'N/A';
            
            row.innerHTML = `
                <td>${escapeHtml(user.username || '')}</td>
                <td>${escapeHtml(user.AgencyName || '')}</td>
                <td>${escapeHtml(sectorName)}</td>
                <td>${escapeHtml(user.RoleName || '')}</td>
                <td>${escapeHtml(user.last_login || 'Never')}</td>
                <td><span class="${statusClass}">${user.status === 'active' ? 'Active' : 'Inactive'}</span></td>
                <td class="action-cell">
                    <button class="icon-button edit-btn" data-id="${user.UserID}" title="Edit User">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="icon-button delete-btn" data-id="${user.UserID}" title="Delete User">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="icon-button reset-pwd-btn" data-id="${user.UserID}" title="Reset Password">
                        <i class="fas fa-key"></i>
                    </button>
                </td>
            `;
            usersTableBody.appendChild(row);
        });
        addTableActionListeners();
    }
    
    function addTableActionListeners() {
        // Add event listeners for delete buttons
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                confirmDeleteUser(userId);
            });
        });
        
        // Add event listeners for edit/view buttons
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                alert('Edit functionality will be implemented soon');
            });
        });
        
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                alert('View functionality will be implemented soon');
            });
        });
    }
    
    function confirmDeleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            deleteUser(userId);
        }
    }
    
    function deleteUser(userId) {
        // Create form data
        const formData = new FormData();
        formData.append('operation', 'delete');
        formData.append('userId', userId);
        
        // Send delete request
        fetch('php/admin/manage_users.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadUsers(); // Reload the user list
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting the user', 'error');
        });
    }
    
    function filterUsers() {
        const searchTerm = searchUserInput.value.toLowerCase();
        const roleFilter = filterRoleSelect.value;
        const sectorFilter = filterDepartmentSelect.value; // This is actually the sector filter
        
        // Get all table rows
        const rows = usersTableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const username = row.cells[0]?.textContent.toLowerCase() || '';
            const agency = row.cells[1]?.textContent.toLowerCase() || '';
            const sector = row.cells[2]?.textContent.toLowerCase() || '';
            const role = row.cells[3]?.textContent.toLowerCase() || '';
            
            // Check if row matches all filters
            const matchesSearch = username.includes(searchTerm) || 
                                agency.includes(searchTerm);
            
            const matchesRole = !roleFilter || 
                               role === document.querySelector(`#filterRole option[value="${roleFilter}"]`)?.textContent.toLowerCase();
            
            const matchesSector = !sectorFilter || 
                                 sector.includes(sectorFilter.toLowerCase());
            
            // Show or hide row
            row.style.display = (matchesSearch && matchesRole && matchesSector) ? '' : 'none';
        });
    }
    
    function resetFilters() {
        searchUserInput.value = '';
        filterRoleSelect.value = '';
        filterDepartmentSelect.value = '';
        
        // Show all rows
        const rows = usersTableBody.querySelectorAll('tr');
        rows.forEach(row => {
            row.style.display = '';
        });
    }
    
    function updatePermissionCheckboxes() {
        const roleId = document.getElementById('userRole').value;
        
        // Define preset permissions based on role
        const permissions = {
            '1': { // Admin
                viewData: true,
                editData: true,
                submitData: true,
                approveData: true,
                exportData: true,
                manageUsers: true
            },
            '2': { // Standard User
                viewData: true,
                editData: true,
                submitData: true,
                approveData: false,
                exportData: true,
                manageUsers: false
            }
            // Add more role presets as needed
        };
        
        // Get default permission set for the selected role or use a safe default
        const permissionSet = permissions[roleId] || { viewData: true };
        
        // Update checkboxes
        Object.keys(permissionSet).forEach(permission => {
            const checkbox = document.getElementById(permission);
            if (checkbox) {
                checkbox.checked = permissionSet[permission];
            }
        });
    }
    
    function generatePassword() {
        // Generate a strong random password
        const length = 12;
        const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-+=';
        let password = '';
        
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            password += charset[randomIndex];
        }
        
        // Set the password field value
        const passwordField = document.getElementById('password');
        passwordField.value = password;
        passwordField.type = 'text'; // Temporarily show the password
        
        // Update toggle button state
        const passwordToggle = document.getElementById('passwordToggle');
        if (passwordToggle) {
            passwordToggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
        }
    }
    
    function showNotification(message, type) {
        // Create notification element if it doesn't exist
        let notification = document.getElementById('notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'notification';
            document.body.appendChild(notification);
            
            // Style the notification
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.padding = '15px 25px';
            notification.style.borderRadius = '8px';
            notification.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
            notification.style.zIndex = '1000';
            notification.style.transition = 'opacity 0.3s ease';
        }
        
        // Set notification style based on type
        switch (type) {
            case 'success':
                notification.style.backgroundColor = '#d4edda';
                notification.style.color = '#155724';
                notification.style.borderLeft = '4px solid #28a745';
                break;
            case 'error':
                notification.style.backgroundColor = '#f8d7da';
                notification.style.color = '#721c24';
                notification.style.borderLeft = '4px solid #dc3545';
                break;
            default:
                notification.style.backgroundColor = '#fff3cd';
                notification.style.color = '#856404';
                notification.style.borderLeft = '4px solid #ffc107';
        }
        
        // Set message and show notification
        notification.textContent = message;
        notification.style.display = 'block';
        notification.style.opacity = '1';
        
        // Hide notification after a delay
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 300);
        }, 3000);
    }
    
    // Helper function to escape HTML and prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
