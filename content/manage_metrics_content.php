<div class="dashboard-container">
    <div class="welcome-banner">
        <h1>Custom Metrics</h1>
        <p>Define and report on metrics specific to your agency's reporting needs</p>
    </div>
    
    <!-- Tabs Navigation -->
    <div class="tab-container">
        <button class="tab active" data-tab="define">
            <i class="fas fa-tools"></i> Define Metrics
        </button>
        <button class="tab" data-tab="report">
            <i class="fas fa-chart-bar"></i> Report Data
        </button>
    </div>
    
    <!-- Tab 1: Define Metrics -->
    <div id="define-tab" class="tab-content active">
        <div class="dashboard-section">
            <div class="section-header">
                <h3><i class="fas fa-list"></i> Your Custom Metrics</h3>
            </div>
            
            <div id="metrics-list-container">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div id="metrics-grid" class="metrics-grid" style="display: none;">
                    <!-- Metrics will be loaded here dynamically -->
                </div>
                <div id="no-metrics-message" style="display: none;">
                    <p>You haven't defined any custom metrics yet.</p>
                </div>
            </div>
            
            <button id="add-metric-button" class="primary-button mt-20">
                <i class="fas fa-plus"></i> Add New Metric
            </button>
        </div>
    </div>
    
    <!-- Tab 2: Report Data -->
    <div id="report-tab" class="tab-content">
        <div class="dashboard-section">
            <div class="section-header">
                <h3><i class="fas fa-file-alt"></i> Report Metric Data</h3>
            </div>
            
            <div id="metric-selector-container">
                <div id="metric-selector">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div id="metric-options" class="metric-options" style="display: none;">
                        <!-- Will be populated dynamically -->
                    </div>
                    <div id="no-metrics-available" style="display: none;">
                        <p>No custom metrics found. Please define metrics in the "Define Metrics" tab first.</p>
                    </div>
                </div>
            </div>
            
            <div id="report-form-container" style="display: none;">
                <form id="metric-report-form" class="data-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reportDate">Date</label>
                            <input type="date" id="reportDate" required>
                        </div>
                        <div class="form-group">
                            <label for="reportNotes">Notes</label>
                            <textarea id="reportNotes" rows="2" placeholder="Additional context or observations"></textarea>
                        </div>
                    </div>
                    
                    <div id="metric-values-container">
                        <!-- Metric inputs will be added here dynamically -->
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" id="back-to-metrics" class="secondary-button">
                            <i class="fas fa-arrow-left"></i> Back to Metrics
                        </button>
                        <button type="submit" class="primary-button">
                            <i class="fas fa-paper-plane"></i> Submit Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="dashboard-section">
            <div class="section-header">
                <h3><i class="fas fa-history"></i> Recent Reports</h3>
            </div>
            
            <div id="reports-container">
                <div class="loading-spinner" id="reports-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div id="reports-table-container" style="display: none;">
                    <table class="data-table" id="reports-table">
                        <thead>
                            <tr>
                                <th>Metric Name</th>
                                <th>Date</th>
                                <th>Value</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Reports will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
                <div id="no-reports-message" style="display: none;">
                    <p>No reports have been submitted yet.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for adding/editing metrics -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Metric</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <form id="metric-form" class="data-form">
                <input type="hidden" id="metricId" value="">
                
                <div class="form-group">
                    <label for="metricName">Metric Name <span class="required">*</span></label>
                    <input type="text" id="metricName" name="metricName" required placeholder="e.g., Carbon Emissions">
                </div>
                
                <div class="form-group">
                    <label for="metricUnit">Unit of Measurement <span class="required">*</span></label>
                    <input type="text" id="metricUnit" name="metricUnit" required placeholder="e.g., tonnes, kg, kWh">
                </div>
                
                <div class="form-group">
                    <label for="dataType">Data Type <span class="required">*</span></label>
                    <select id="dataType" name="dataType" required>
                        <option value="">Select Data Type</option>
                        <option value="number">Number</option>
                        <option value="percentage">Percentage</option>
                        <option value="text">Text</option>
                        <option value="boolean">Yes/No</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="metricDescription">Description</label>
                    <textarea id="metricDescription" name="metricDescription" rows="3" placeholder="Describe the purpose of this metric"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="isRequired">Required?</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="isRequired" name="isRequired">
                            <label for="isRequired">This metric is mandatory</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary-button" id="modal-cancel">Cancel</button>
            <button type="button" class="primary-button" id="modal-save">Save Metric</button>
        </div>
    </div>
</div>
