<div class="admin-dashboard-container">
    <div class="welcome-banner">
        <h1>Export Reports</h1>
        <p>Generate and download reports in multiple formats</p>
    </div>
    
    <div class="admin-dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-file-export"></i> Generate Report</h3>
        </div>
        <form class="data-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="reportType">Report Type</label>
                    <select id="reportType" required>
                        <option value="">Select Report Type</option>
                        <option value="summary">Summary Report</option>
                        <option value="detailed">Detailed Report</option>
                        <option value="custom">Custom Report</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="exportFormat">Format</label>
                    <select id="exportFormat" required>
                        <option value="">Select Format</option>
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="csv">CSV</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate">
                </div>
                <div class="form-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate">
                </div>
            </div>
            
            <div class="form-group">
                <label for="exportAgency">Agency</label>
                <select id="exportAgency">
                    <option value="">All Agencies</option>
                    <!-- Will be populated dynamically -->
                </select>
            </div>
            
            <div class="form-group">
                <label for="exportMetric">Metric Type</label>
                <select id="exportMetric">
                    <option value="">All Metrics</option>
                    <option value="targets">Targets & Status</option>
                    <option value="statistics">Statistics</option>
                    <option value="custom">Custom Metrics</option>
                </select>
            </div>
            
            <div id="customFields" style="display: none;">
                <div class="form-section-header">
                    <h4>Custom Report Fields</h4>
                </div>
                
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="includeProgram" name="fields[]" value="program" checked>
                        <label for="includeProgram">Program Name</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="includeAgency" name="fields[]" value="agency" checked>
                        <label for="includeAgency">Agency</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="includeTarget" name="fields[]" value="target" checked>
                        <label for="includeTarget">Target</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="includeStatus" name="fields[]" value="status" checked>
                        <label for="includeStatus">Status</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="includeDate" name="fields[]" value="date" checked>
                        <label for="includeDate">Date</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="includeUser" name="fields[]" value="user">
                        <label for="includeUser">Submitted By</label>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" id="previewReport" class="secondary-button"><i class="fas fa-eye"></i> Preview</button>
                <button type="submit" class="primary-button"><i class="fas fa-download"></i> Generate & Download</button>
            </div>
        </form>
    </div>
    
    <div class="admin-dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-history"></i> Recent Reports</h3>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Report Name</th>
                    <th>Type</th>
                    <th>Format</th>
                    <th>Generated On</th>
                    <th>Generated By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Q4 2024 Summary</td>
                    <td>Summary</td>
                    <td>Excel</td>
                    <td>2024-12-15</td>
                    <td>admin</td>
                    <td>
                        <button class="icon-button download-btn" title="Download Again">
                            <i class="fas fa-download"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Annual Review 2024</td>
                    <td>Detailed</td>
                    <td>PDF</td>
                    <td>2024-12-01</td>
                    <td>admin</td>
                    <td>
                        <button class="icon-button download-btn" title="Download Again">
                            <i class="fas fa-download"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Environment Stats 2024</td>
                    <td>Custom</td>
                    <td>CSV</td>
                    <td>2024-11-22</td>
                    <td>admin</td>
                    <td>
                        <button class="icon-button download-btn" title="Download Again">
                            <i class="fas fa-download"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="modal">
    <div class="modal-content large-modal">
        <div class="modal-header">
            <h3>Report Preview</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body" id="previewContent">
            <!-- Preview content will be inserted here dynamically -->
            <div class="preview-placeholder">
                <p>Report preview will appear here...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary-button modal-close">Close</button>
            <button type="button" class="primary-button" id="downloadFromPreview">Download</button>
        </div>
    </div>
</div>
