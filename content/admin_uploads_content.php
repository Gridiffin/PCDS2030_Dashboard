<div class="admin-dashboard-container">
    <div class="welcome-banner">
        <h1>Data Management</h1>
        <p>View, approve, and manage all user uploads</p>
    </div>
    
    <div class="admin-dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-chart-line"></i> Upload Statistics</h3>
        </div>
        <div class="overview-cards">
            <div class="overview-card">
                <div class="card-icon">
                    <i class="fas fa-file-upload"></i>
                </div>
                <div class="card-content">
                    <h4>Total Uploads</h4>
                    <p class="card-value">247</p>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="card-content">
                    <h4>Pending Approval</h4>
                    <p class="card-value">12</p>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="card-content">
                    <h4>This Month</h4>
                    <p class="card-value">42</p>
                </div>
            </div>
            <div class="overview-card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-content">
                    <h4>Unique Users</h4>
                    <p class="card-value">18</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-filter"></i> Filter Uploads</h3>
        </div>
        <div class="filter-container">
            <div class="filter-group">
                <label for="filterYear">Year</label>
                <select id="filterYear">
                    <option value="">All Years</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filterQuarter">Quarter</label>
                <select id="filterQuarter">
                    <option value="">All Quarters</option>
                    <option value="Q1">Q1</option>
                    <option value="Q2">Q2</option>
                    <option value="Q3">Q3</option>
                    <option value="Q4">Q4</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filterAgency">Agency</label>
                <select id="filterAgency">
                    <option value="">All Agencies</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            <div class="filter-group">
                <label for="filterStatus">Status</label>
                <select id="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="button" class="secondary-button"><i class="fas fa-undo"></i> Reset</button>
                <button type="button" class="primary-button"><i class="fas fa-filter"></i> Apply Filters</button>
            </div>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="uploadsTable">
                <thead>
                    <tr>
                        <th>ID <i class="fas fa-sort"></i></th>
                        <th>Program <i class="fas fa-sort"></i></th>
                        <th>Year/Quarter <i class="fas fa-sort"></i></th>
                        <th>Agency <i class="fas fa-sort"></i></th>
                        <th>User <i class="fas fa-sort"></i></th>
                        <th>Status <i class="fas fa-sort"></i></th>
                        <th>Submitted <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded dynamically -->
                </tbody>
            </table>
            <div id="noDataMessage" class="no-data-message">
                <i class="fas fa-info-circle"></i> No data found matching your filter criteria.
            </div>
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

<!-- Modal for Upload Details -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Upload Details</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be inserted dynamically -->
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary-button modal-close">Close</button>
            <button type="button" class="primary-button modal-approve">Approve</button>
        </div>
    </div>
</div>
