<div class="dashboard-container">
    <div class="welcome-banner">
        <h1>Statistics</h1>
        <p>Submit statistical data for your indicators</p>
    </div>
    
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-chart-bar"></i> Submit Statistical Data</h3>
        </div>
        <form class="data-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="statYear">Year</label>
                    <select id="statYear" required>
                        <option value="">Select Year</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                        <option value="2028">2028</option>
                        <option value="2029">2029</option>
                        <option value="2030">2030</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statQuarter">Quarter</label>
                    <select id="statQuarter" required>
                        <option value="">Select Quarter</option>
                        <option value="Q1">Q1 (Jan-Mar)</option>
                        <option value="Q2">Q2 (Apr-Jun)</option>
                        <option value="Q3">Q3 (Jul-Sep)</option>
                        <option value="Q4">Q4 (Oct-Dec)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statType">Type of Data</label>
                    <select id="statType" required>
                        <option value="">Select Type</option>
                        <option value="environmental">Environmental</option>
                        <option value="economic">Economic</option>
                        <option value="social">Social</option>
                        <option value="agricultural">Agricultural</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="statDescription">Description</label>
                <textarea id="statDescription" rows="3" placeholder="Brief description of the data you're submitting"></textarea>
            </div>
            
            <div class="form-group">
                <label>Data Points</label>
                <div id="dataPointsContainer">
                    <div class="data-point-row">
                        <input type="text" placeholder="Indicator name" required>
                        <input type="number" step="0.01" placeholder="Value" required>
                        <select required>
                            <option value="">Unit</option>
                            <option value="tonnes">tonnes</option>
                            <option value="hectares">hectares</option>
                            <option value="count">count</option>
                            <option value="dollars">dollars</option>
                            <option value="kWh">kWh</option>
                            <option value="%">%</option>
                        </select>
                        <button type="button" class="icon-button remove-btn"><i class="fas fa-minus-circle"></i></button>
                    </div>
                </div>
                <button type="button" class="add-data-point"><i class="fas fa-plus-circle"></i> Add Data Point</button>
            </div>
            
            <div class="form-group">
                <label for="chartUpload">Upload Charts</label>
                <div class="file-upload">
                    <input type="file" id="chartUpload" accept=".png,.jpg,.jpeg,.gif,.pdf" multiple>
                    <label for="chartUpload"><i class="fas fa-chart-pie"></i> Choose Charts</label>
                    <span class="file-info">No files selected</span>
                </div>
                <div class="file-requirements">Accepted formats: PNG, JPG, GIF, PDF (Max 10MB each)</div>
            </div>
            
            <div class="form-group">
                <label for="rawDataUpload">Upload Raw Data (Optional)</label>
                <div class="file-upload">
                    <input type="file" id="rawDataUpload" accept=".csv,.xlsx,.xls">
                    <label for="rawDataUpload"><i class="fas fa-file-excel"></i> Choose File</label>
                    <span class="file-info">No file selected</span>
                </div>
                <div class="file-requirements">Accepted formats: CSV, Excel (Max 20MB)</div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="secondary-button"><i class="fas fa-save"></i> Save as Draft</button>
                <button type="submit" class="primary-button"><i class="fas fa-paper-plane"></i> Submit Data</button>
            </div>
        </form>
    </div>
</div>
