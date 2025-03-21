<div class="dashboard-container">
    <div class="welcome-banner">
        <h1>Your Submissions</h1>
        <p>View and manage targets and status updates for your agency's programs</p>
    </div>
    
    <!-- Drafts Section -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-file-alt"></i> My Drafts</h3>
        </div>
        <div class="table-container">
            <table class="data-table" id="draftsTable">
                <thead>
                    <tr>
                        <th>Program <i class="fas fa-sort"></i></th>
                        <th>Year/Quarter <i class="fas fa-sort"></i></th>
                        <th>Category <i class="fas fa-sort"></i></th>
                        <th>Target Summary <i class="fas fa-sort"></i></th>
                        <th>Last Updated <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Draft data will be loaded dynamically -->
                </tbody>
            </table>
            <div id="noDraftsMessage" class="no-data-message">
                <i class="fas fa-info-circle"></i> You don't have any drafts saved yet.
            </div>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-filter"></i> Filter Submissions</h3>
        </div>
        <div class="filter-container">
            <div class="filter-group">
                <label for="viewYear">Year</label>
                <select id="viewYear">
                    <option value="">All Years</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="viewQuarter">Quarter</label>
                <select id="viewQuarter">
                    <option value="">All Quarters</option>
                    <option value="Q1">Q1</option>
                    <option value="Q2">Q2</option>
                    <option value="Q3">Q3</option>
                    <option value="Q4">Q4</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="viewMetricType">Category</label>
                <select id="viewMetricType">
                    <option value="">All Categories</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            <div class="filter-group">
                <label for="viewAgency">Agency</label>
                <select id="viewAgency">
                    <option value="">All Agencies</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            <div class="filter-actions">
                <button type="button" id="refreshSubmissions" class="secondary-button"><i class="fas fa-sync-alt"></i> Refresh</button>
            </div>
        </div>
    </div>
    
    <!-- Submissions Section -->
    <div class="dashboard-section">
        <div class="table-container">
            <table class="data-table" id="submissionsTable">
                <thead>
                    <tr>
                        <th>Program <i class="fas fa-sort"></i></th>
                        <th>Year/Quarter <i class="fas fa-sort"></i></th>
                        <th>Category <i class="fas fa-sort"></i></th>
                        <th>Sector <i class="fas fa-sort"></i></th>
                        <th>Agency <i class="fas fa-sort"></i></th>
                        <th>Status <i class="fas fa-sort"></i></th>
                        <th>Last Updated <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Submission data will be loaded dynamically -->
                </tbody>
            </table>
            <div id="noDataMessage" class="no-data-message">
                <i class="fas fa-info-circle"></i> No submissions match your filters.
            </div>
        </div>
        <div class="section-actions">
            <a href="target_status.php" class="primary-button">
                <i class="fas fa-plus-circle"></i> Add New Submission
            </a>
        </div>
    </div>
</div>

<!-- Modal Structure -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Details</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be dynamically inserted here -->
        </div>
    </div>
</div>
