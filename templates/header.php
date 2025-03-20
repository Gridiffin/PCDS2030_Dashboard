<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'PCDS2030 Dashboard'; ?></title>
    
    <!-- Preconnect to font domains to improve performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Use CDN for Font Awesome instead of local files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Web fonts with display=swap for better loading performance -->
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap" rel="stylesheet">
    
    <!-- Base CSS for all pages -->
    <link rel="stylesheet" href="css/base.css">
    
    <!-- Module-specific CSS -->
    <?php if (isset($includeForms) && $includeForms): ?>
    <link rel="stylesheet" href="css/forms.css">
    <?php endif; ?>
    
    <?php if (isset($includeTables) && $includeTables): ?>
    <link rel="stylesheet" href="css/tables.css">
    <?php endif; ?>
    
    <!-- Modal CSS for components that use modals -->
    <link rel="stylesheet" href="css/modal.css">
    
    <!-- Additional CSS files -->
    <?php if (isset($additionalCss) && is_array($additionalCss)): ?>
        <?php foreach($additionalCss as $cssFile): ?>
            <link rel="stylesheet" href="<?php echo $cssFile; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Responsive CSS - loaded last for proper overrides -->
    <?php if (isset($includeResponsive) && $includeResponsive): ?>
    <link rel="stylesheet" href="css/responsive.css">
    <?php endif; ?>
</head>
<body>
    <?php if (!isset($hideHeader) || !$hideHeader): ?>
    <header>
        <div class="header-left">
            <a href="<?php echo isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1 ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>">
                <img class="logo" src="assets/images/logo.png" alt="PCDS2030 Logo">
            </a>
        </div>
        <div class="header-right">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>
                    <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>
                    <?php if (isset($_SESSION['agency_name']) && isset($showAgencyBadge) && $showAgencyBadge): ?>
                        <span class="agency-badge"><?php echo htmlspecialchars($_SESSION['agency_name']); ?></span>
                    <?php endif; ?>
                </span>
            </div>
            
            <?php if (isset($additionalNavButtons) && is_array($additionalNavButtons)): ?>
                <?php foreach($additionalNavButtons as $button): ?>
                    <a href="<?php echo $button['href']; ?>" class="cta">
                        <button>
                            <?php if (isset($button['icon'])): ?>
                                <i class="fas fa-<?php echo $button['icon']; ?>"></i>
                            <?php endif; ?>
                            <?php echo $button['text']; ?>
                        </button>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (isset($showLogout) && $showLogout): ?>
            <a href="javascript:void(0);" class="cta" id="logoutButton">
                <button>
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($includeMobileJs) && $includeMobileJs): ?>
        <button class="mobile-nav-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <?php endif; ?>
    </header>
    <?php endif; ?>

    <!-- Logout script for all pages with the logout button -->
    <?php if (isset($showLogout) && $showLogout): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoutButton = document.getElementById('logoutButton');
        if (logoutButton) {
            logoutButton.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Logout clicked');
                
                fetch('php/auth/logout.php', {
                    method: 'POST',
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Logout response:', data);
                    if (data.success) {
                        // Show a success message if notification element exists
                        const notification = document.getElementById('notification');
                        if (notification) {
                            notification.innerHTML = 'Logout successful. Redirecting...';
                            notification.style.display = 'block';
                            notification.className = 'notification success';
                        }
                        
                        // Redirect to login page
                        setTimeout(() => {
                            window.location.href = data.redirect || 'login.php';
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    // Fallback to direct redirect on error
                    window.location.href = 'login.php';
                });
            });
        }
    });
    </script>
    <?php endif; ?>
</body>
</html>
