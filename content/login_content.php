<div class="home-container">
    <div class="welcome-section">
        <div class="welcome-logo">
            <img src="assets/images/logo.png" alt="PCDS2030 Logo">
        </div>
        <h1>Welcome to PCDS2030 Dashboard</h1>
        <p>Track, monitor, and manage sustainable development metrics</p>
        
        <div class="features-list">
            <div class="feature-item">
                <i class="fas fa-chart-line"></i>
                <span>Data-driven decision making</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-tasks"></i>
                <span>Track targets and indicators</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-file-alt"></i>
                <span>Generate custom reports</span>
            </div>
        </div>
        
        <div class="copyright">&copy; 2025 PCDS2030. All rights reserved.</div>
    </div>

    <div class="login-section">
        <!-- Decorative shapes -->
        <div class="shape1"></div>
        <div class="shape2"></div>
        <div class="shape3"></div>
        
        <!-- Remove the action attribute to prevent default submission -->
        <form id="loginForm" method="post">
            <div class="form-logo">
                <img class="form-logo-img" src="assets/images/logo_small.png" alt="PCDS2030">
            </div>
            <h2>Sign In</h2>
            <p class="login-subtitle">Enter your credentials to access the dashboard</p>
            
            <div class="input-group">
                <div class="input-with-icon">
                    <input type="text" id="username" name="username" placeholder="Username" required>
                    <i class="fas fa-user"></i>
                </div>
            </div>
            
            <div class="input-group">
                <div class="input-with-icon">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>
            
            <div id="errorMessage" class="error-message"></div>
            
            <button type="submit">
                Login
                <i class="fas fa-arrow-right"></i>
                <div id="loadingSpinner" class="loading-spinner"></div>
            </button>
        </form>
    </div>
</div>
