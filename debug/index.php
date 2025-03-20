<?php
/**
 * Debug Tools Index
 * This page provides links to various debugging tools for the PCDS2030 Dashboard
 */

// If this is a production environment, prevent access
if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false && strpos($_SERVER['SERVER_NAME'], '127.0.0.1') === false) {
    die('Debug tools are only available in development environments.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCDS2030 Debug Tools</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .tool-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .tool-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            width: 350px;
            background-color: #f9f9f9;
        }
        .tool-card h3 {
            margin-top: 0;
            color: #555;
        }
        .tool-card p {
            color: #666;
            font-size: 14px;
        }
        .tool-card a {
            display: inline-block;
            background: #4a453e;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffecb5;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>PCDS2030 Dashboard Debug Tools</h1>
    <div class="warning">
        <strong>Warning:</strong> These tools are for development and debugging purposes only. 
        They should not be accessible in a production environment.
    </div>
    
    <div class="tool-list">
        <div class="tool-card">
            <h3>Database Structure</h3>
            <p>View database tables, columns, and sample data.</p>
            <a href="debug_columns.php">View Structure</a>
        </div>
        
        <div class="tool-card">
            <h3>API Endpoints</h3>
            <p>Test API endpoints and view their responses.</p>
            <a href="debug_endpoints.php">Test Endpoints</a>
        </div>
        
        <div class="tool-card">
            <h3>Metrics System</h3>
            <p>Debug the metrics system configuration and data.</p>
            <a href="debug_metrics.php">Debug Metrics</a>
        </div>
        
        <div class="tool-card">
            <h3>Session Data</h3>
            <p>View current session data and variables.</p>
            <a href="debug_session.php">View Session</a>
        </div>
    </div>
    
    <p style="margin-top: 30px;">
        <a href="../index.php" style="color: #4a453e;">Back to Dashboard</a>
    </p>
</body>
</html>
