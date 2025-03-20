<div class="dashboard-container">
    <div class="welcome-banner">
        <h1>Target & Status</h1>
        <p>Submit and track targets and status updates for your agency's programs</p>
    </div>
    
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-folder-plus"></i> Program Selection</h3>
        </div>
        <form id="programForm" class="data-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="programSelect">Select Existing Program</label>
                    <select id="programSelect">
                        <option value="">Choose a program or create new...</option>
                        <!-- Programs will be loaded dynamically -->
                    </select>
                </div>
            </div>
        </form>
    </div>
    
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-bullseye"></i> Define Target</h3>
        </div>
        <form id="targetForm" class="data-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="targetYear">Year</label>
                    <select id="targetYear" required>
                        <option value="">Select Year</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="targetQuarter">Quarter</label>
                    <select id="targetQuarter" required>
                        <option value="">Select Quarter</option>
                        <option value="Q1">Q1 (Jan-Mar)</option>
                        <option value="Q2">Q2 (Apr-Jun)</option>
                        <option value="Q3">Q3 (Jul-Sep)</option>
                        <option value="Q4">Q4 (Oct-Dec)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="metricType">Category</label>
                    <select id="metricType" required>
                        <option value="">Select Category</option>
                        <!-- These will be populated dynamically -->
                        <option value="environmental">Environmental</option>
                        <option value="social">Social</option>
                        <option value="economic">Economic</option>
                        <option value="governance">Governance</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="targetDescription">Target Description</label>
                <textarea id="targetDescription" rows="3" placeholder="Describe the target to be achieved" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="targetValue">Target Value (if applicable)</label>
                    <input type="number" id="targetValue" step="0.01" placeholder="e.g., 500">
                </div>
                <div class="form-group">
                    <label for="targetUnit">Unit</label>
                    <input type="text" id="targetUnit" placeholder="e.g., tonnes, hectares, etc.">
                </div>
            </div>
            
            <div class="form-group">
                <label>Target Completion Date</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="useQuarterEnd" name="useQuarterEnd">
                        <label for="useQuarterEnd">Use quarter end date</label>
                    </div>
                </div>
                <input type="date" id="targetDate" class="mt-8">
                <small class="form-hint">If not selected, the end of the selected quarter will be used</small>
            </div>
            
            <div class="form-section-header">
                <h4>Status Update</h4>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="statusCategory">Current Status</label>
                    <select id="statusCategory" required>
                        <option value="not-started">Not Started</option>
                        <option value="in-progress">In Progress</option>
                        <option value="nearly-complete">Nearly Complete</option>
                        <option value="completed">Completed</option>
                        <option value="delayed">Delayed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Color</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" name="statusColor" id="statusColorGreen" value="success" checked>
                            <label for="statusColorGreen" class="color-success">Success</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" name="statusColor" id="statusColorYellow" value="warning">
                            <label for="statusColorYellow" class="color-warning">Warning</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" name="statusColor" id="statusColorBlue" value="progress">
                            <label for="statusColorBlue" class="color-progress">Progress</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" name="statusColor" id="statusColorGray" value="draft">
                            <label for="statusColorGray" class="color-draft">Draft</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="statusValue">Status Value</label>
                <input type="number" id="statusValue" step="0.01" placeholder="Current progress value, if applicable">
                <small class="form-hint">Leave blank if not applicable</small>
            </div>
            
            <div class="form-group">
                <label for="statusNotes">Status Notes</label>
                <textarea id="statusNotes" rows="3" placeholder="Provide details about current progress"></textarea>
            </div>
            
            <div class="form-group">
                <label for="nextSteps">Next Steps</label>
                <textarea id="nextSteps" rows="2" placeholder="What's planned next?"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" id="saveDraftBtn" class="secondary-button"><i class="fas fa-save"></i> Save as Draft</button>
                <button type="submit" class="primary-button"><i class="fas fa-paper-plane"></i> Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for confirmations and messages -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Confirmation</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be inserted dynamically -->
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-button">Close</button>
        </div>
    </div>
</div>
