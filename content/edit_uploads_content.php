<div class="dashboard-container">
    <div class="welcome-banner">
        <h1>Edit Uploads</h1>
        <p>Review and modify your previously submitted data</p>
    </div>
    
    <div class="dashboard-section">
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
                <label for="filterCategory">Category</label>
                <select id="filterCategory">
                    <option value="">All Categories</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            <div class="filter-actions">
                <button type="button" id="resetFilters" class="secondary-button"><i class="fas fa-undo"></i> Reset</button>
                <button type="button" id="applyFilters" class="primary-button"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="uploadsTable">
                <thead>
                    <tr>
                        <th>Program <i class="fas fa-sort"></i></th>
                        <th>Year/Quarter <i class="fas fa-sort"></i></th>
                        <th>Category <i class="fas fa-sort"></i></th>
                        <th>Target <i class="fas fa-sort"></i></th>
                        <th>Status <i class="fas fa-sort"></i></th>
                        <th>Last Updated <i class="fas fa-sort"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Upload data will be loaded dynamically -->
                </tbody>
            </table>
            <div id="noDataMessage" class="no-data-message">
                <i class="fas fa-info-circle"></i> No submissions found matching your filter criteria.
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

<!-- Modal for viewing details -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Submission Details</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be inserted dynamically -->
        </div>
        <div class="modal-footer">
            <!-- Footer intentionally left empty - removed all buttons for cleaner interface -->
        </div>
    </div>
</div>
