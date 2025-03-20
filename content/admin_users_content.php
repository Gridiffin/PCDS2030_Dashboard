<div class="admin-dashboard-container">
    <div class="welcome-banner">
        <h1>User Management</h1>
        <p>Add, edit, or remove system users</p>
    </div>
    
    <div class="admin-dashboard-row">
        <div class="admin-dashboard-section">
            <div class="section-header">
                <h3><i class="fas fa-user-plus"></i> Add New User</h3>
            </div>
            <form class="data-form" id="userForm">
                <div class="form-section-header">
                    <h4>User Information</h4>
                    <small>All fields are stored in the users table</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" placeholder="Enter username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="Enter email address" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                        <button type="button" id="generatePassword" class="link-button">Generate</button>
                    </div>
                    <div class="form-group">
                        <label for="userRole">Role <span class="required">*</span></label>
                        <select id="userRole" name="roleId" required>
                            <option value="">Select Role</option>
                            <!-- These options will be replaced by JavaScript if it works properly -->
                            <option value="1">Administrator</option>
                            <option value="2">Regular User</option>
                            <option value="3">Viewer</option>
                        </select>
                        <small>References roles table (RoleID)</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="agency">Agency <span class="required">*</span></label>
                        <select id="agency" name="agencyId" required>
                            <option value="">Select Agency</option>
                            <!-- These options will be replaced by JavaScript if it works properly -->
                            <option value="1">Main Agency</option>
                        </select>
                        <small>References agencies table (AgencyID)</small>
                    </div>
                </div>
                
                <!-- Permissions section -->
                <div class="form-group">
                    <label>Access Permissions</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="viewData" name="permissions[]" value="viewData" checked>
                            <label for="viewData">View Data</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="editData" name="permissions[]" value="editData">
                            <label for="editData">Edit Data</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="submitData" name="permissions[]" value="submitData">
                            <label for="submitData">Submit Data</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="approveData" name="permissions[]" value="approveData">
                            <label for="approveData">Approve Data</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="exportData" name="permissions[]" value="exportData">
                            <label for="exportData">Export Data</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="manageUsers" name="permissions[]" value="manageUsers">
                            <label for="manageUsers">Manage Users</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="secondary-button"><i class="fas fa-times"></i> Clear Form</button>
                    <button type="submit" class="primary-button"><i class="fas fa-user-plus"></i> Add User</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="admin-dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-users"></i> Manage Existing Users</h3>
        </div>
        
        <div class="filter-container">
            <div class="filter-group">
                <label for="searchUser">Search Users</label>
                <div class="search-input">
                    <input type="text" id="searchUser" placeholder="Search by name, email or department...">
                    <button type="button" class="search-button"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="filter-group">
                <label for="filterRole">Role</label>
                <select id="filterRole">
                    <option value="">All Roles</option>
                    <option value="admin">Administrator</option>
                    <option value="user">Standard User</option>
                    <option value="viewer">Viewer</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filterDepartment">Sector</label>
                <select id="filterDepartment">
                    <option value="">All Sectors</option>
                    <!-- Options will be populated by JavaScript -->
                </select>
            </div>
            <div class="filter-actions">
                <button class="secondary-button"><i class="fas fa-sync-alt"></i> Reset</button>
            </div>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="usersTable">
                <thead>
                    <tr>
                        <th>Username <i class="fas fa-sort"></i></th>
                        <th>Agency <i class="fas fa-sort"></i></th>
                        <th>Sector <i class="fas fa-sort"></i></th>
                        <th>Role <i class="fas fa-sort"></i></th>
                        <th>Last Login <i class="fas fa-sort"></i></th>
                        <th>Status <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- User data will be loaded dynamically -->
                </tbody>
            </table>
        </div>
        
        <div class="pagination">
            <button class="pagination-button" disabled><i class="fas fa-chevron-left"></i> Previous</button>
            <div class="page-numbers">
                <button class="page-number active">1</button>
                <button class="page-number">2</button>
                <button class="page-number">3</button>
            </div>
            <button class="pagination-button">Next <i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<!-- Add the notification div for showing messages -->
<div id="notification" style="display: none;"></div>
