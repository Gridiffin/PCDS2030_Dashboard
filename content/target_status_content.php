<div class="dashboard-container">
    <div class="welcome-banner">
        <h1 id="pageTitle">New Target Status</h1>
        <p>Enter information about your program target and current status</p>
    </div>
    
    <div class="form-container">
        <form id="targetStatusForm">
            <input type="hidden" id="programId" name="programId">
            <input type="hidden" id="metricType" name="metricType" value="governance">
            
            <div class="form-section">
                <h3>Program Information</h3>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="programName">Program Name <span class="required">*</span></label>
                        <input type="text" id="programName" name="programName" required>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Target Details</h3>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="targetText">Target <span class="required">*</span></label>
                        <textarea id="targetText" name="targetText" rows="3" required></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="quarter">Reporting Quarter <span class="required">*</span></label>
                        <select id="quarter" name="quarter" required>
                            <option value="Q1">Quarter 1</option>
                            <option value="Q2">Quarter 2</option>
                            <option value="Q3">Quarter 3</option>
                            <option value="Q4">Quarter 4</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year">Reporting Year <span class="required">*</span></label>
                        <select id="year" name="year" required>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Current Status</h3>
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="statusText">Status Description <span class="required">*</span></label>
                        <textarea id="statusText" name="statusText" rows="3" required></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="statusDate">Status Date</label>
                        <input type="date" id="statusDate" name="statusDate">
                    </div>
                    <div class="form-group">
                        <label>Status Indicator <span class="required">*</span></label>
                        <div class="status-buttons">
                            <label class="status-btn">
                                <input type="radio" name="statusColor" value="completed" required>
                                <span class="status-indicator completed">Completed</span>
                            </label>
                            <label class="status-btn">
                                <input type="radio" name="statusColor" value="progress">
                                <span class="status-indicator progress">In Progress</span>
                            </label>
                            <label class="status-btn">
                                <input type="radio" name="statusColor" value="warning">
                                <span class="status-indicator warning">Delayed</span>
                            </label>
                            <label class="status-btn">
                                <input type="radio" name="statusColor" value="not-started" checked>
                                <span class="status-indicator not-started">Not Started</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" id="saveAsDraftBtn" class="secondary-button">Save as Draft</button>
                <button type="submit" id="submitBtn" class="primary-button">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Added notification container -->
<div id="notification" class="notification" style="display: none;"></div>
