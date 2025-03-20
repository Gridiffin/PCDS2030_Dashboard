<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If this is a production environment, prevent access
if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false && strpos($_SERVER['SERVER_NAME'], '127.0.0.1') === false) {
    die('Debug tools are only available in development environments.');
}

// Function to print pretty variables
function prettyPrint($var) {
    echo '<pre>';
    if (is_array($var) || is_object($var)) {
        print_r($var);
    } else {
        var_dump($var);
    }
    echo '</pre>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug - PCDS2030</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2, h3 {
            color: #333;
        }
        h1 {
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .section {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .header-links {
            margin-bottom: 20px;
        }
        .header-links a {
            margin-right: 15px;
            color: #0066cc;
        }
        pre {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="header-links">
        <a href="index.php">&lt; Back to Debug Tools</a>
        <a href="../index.php">Back to Dashboard</a>
    </div>

    <h1>Session Debug Information</h1>
    
    <div class="section">
        <h2>Session Variables</h2>
        <?php
        if (empty($_SESSION)) {
            echo '<p>No session variables found.</p>';
        } else {
            prettyPrint($_SESSION);
        }
        ?>
    </div>
    
    <div class="section">
        <h2>Session Information</h2>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
        <p><strong>Session Name:</strong> <?php echo session_name(); ?></p>
        <p><strong>Session Status:</strong> 
            <?php 
                $status = session_status();
                switch ($status) {
                    case PHP_SESSION_DISABLED:
                        echo 'Disabled';
                        break;
                    case PHP_SESSION_NONE:
                        echo 'None';
                        break;
                    case PHP_SESSION_ACTIVE:
                        echo 'Active';
                        break;
                }
            ?>
        </p>
        <p><strong>Session Cookie Parameters:</strong></p>
        <?php prettyPrint(session_get_cookie_params()); ?>
    </div>
    
    <div class="section">
        <h2>Authentication Status</h2>
        <p>
            <strong>Is Authenticated:</strong> 
            <?php echo (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) ? 'Yes' : 'No'; ?>
        </p>
        <p>
            <strong>Is Admin:</strong>
            <?php 
                require_once '../includes/template_manager.php';
                echo is_admin() ? 'Yes' : 'No';
            ?>
        </p>
    </div>
    
    <div class="section">
        <h2>Other Server Variables</h2>
        <h3>SERVER</h3>
        <?php prettyPrint($_SERVER); ?>
        
        <h3>COOKIES</h3>
        <?php prettyPrint($_COOKIE); ?>
    </div>
</body>
</html>
