<div class="admin-dashboard-container">
    <div class="welcome-banner">
        <h1>User Management</h1>
        <p>Add, edit, and manage system users</p>
    </div>
    
    <div class="admin-dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-user-plus"></i> Add New User</h3>
        </div>
        
        <form id="userForm" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required>
                        <button type="button" id="betterTogglePassword" class="password-toggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="userRole">Role</label>
                    <select id="userRole" name="roleId" required>
                        <option value="">Select Role</option>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="userAgency">Agency</label>
                    <select id="userAgency" name="agencyId" required>
                        <option value="">Select Agency</option>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" id="generatePassword" class="secondary-button">Generate Password</button>
                <button type="submit" class="primary-button">Add User</button>
            </div>
        </form>
    </div>
    
    <div class="admin-dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-users"></i> Manage Existing Users</h3>
        </div>
        
        <div class="filter-container">
            <div class="filter-group">
                <label for="searchUser">Search</label>
                <input type="text" id="searchUser" placeholder="Search by username...">
            </div>
            <div class="filter-group">
                <label for="filterRole">Role</label>
                <select id="filterRole">
                    <option value="">All Roles</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            <div class="filter-group">
                <label for="filterDepartment">Sector</label>
                <select id="filterDepartment">
                    <option value="">All Sectors</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            <div class="filter-actions">
                <button type="button" class="secondary-button">Reset Filters</button>
            </div>
        </div>
        
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Agency</th>
                    <th>Sector</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- User data will be loaded dynamically -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add the notification div for showing messages -->
<div id="notification" style="display: none;"></div>

<style>
    .password-field {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .password-field input {
        width: 100%;
        padding-right: 40px; /* Make room for the icon */
    }
    
    .password-toggle {
        position: absolute;
        right: 10px;
        background: none;
        border: none;
        cursor: pointer;
        color: #666;
        height: calc(100% - 2px);
    }
    
    .password-toggle:hover {
        color: #333;
    }
</style>
