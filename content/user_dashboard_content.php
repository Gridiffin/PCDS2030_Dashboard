<div class="dashboard-container">
    <div class="welcome-banner">
        <h1>Welcome to the PCDS2030 Dashboard</h1>
        <p>Track, monitor, and manage sustainable development metrics</p>
    </div>

    <div class="dashboard-row">
        <!-- Dashboard actions section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3><i class="fas fa-tasks"></i> Dashboard Actions</h3>
            </div>
            <div class="dashboard-buttons">
                <!-- Use consistent button width and height with admin dashboard -->
                <a href="manage_metrics.php" class="button-link">
                    <div class="dashboard-button">
                        <i class="fas fa-chart-line"></i>
                        <span>Manage Metrics</span>
                    </div>
                </a>
                
                <a href="target_status.php" class="button-link">
                    <div class="dashboard-button">
                        <i class="fas fa-bullseye"></i>
                        <span>Target Status</span>
                    </div>
                </a>
                
                <!-- Statistics button removed -->
            </div>
        </div>
        
        <!-- Data upload section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3><i class="fas fa-cloud-upload-alt"></i> Data Management</h3>
            </div>
            <div class="dashboard-buttons">
                <a href="view_uploads.php" class="button-link">
                    <div class="dashboard-button">
                        <i class="fas fa-file-alt"></i>
                        <span>View Uploads</span>
                    </div>
                </a>
                
                <a href="edit_uploads.php" class="button-link">
                    <div class="dashboard-button">
                        <i class="fas fa-edit"></i>
                        <span>Edit Uploads</span>
                    </div>
                </a>
                
                <!-- Upload New Data button removed -->
            </div>
        </div>
    </div>
    
    <!-- Summary section -->
    <div class="summary-section">
        <h2><i class="fas fa-chart-pie"></i> Summary Overview</h2>
        <div class="chart-container">
            <div id="summaryChart">
                <!-- Chart will be loaded here by JavaScript -->
            </div>
        </div>
    </div>
</div>
