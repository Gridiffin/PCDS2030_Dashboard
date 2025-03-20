<?php
session_start();

// Show current session status
echo "<h2>Current Session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        button {
            padding: 10px 15px;
            background: #A49885;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h1>Logout Test Page</h1>
    
    <button id="logoutButton">Test Logout</button>
    
    <div id="result" style="margin-top: 20px;"></div>
    
    <script>
        document.getElementById('logoutButton').addEventListener('click', function() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = "Sending logout request...";
            
            fetch('php/auth/logout.php', {
                method: 'POST',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                
                if (data.success) {
                    // Show success and reload after 2 seconds
                    resultDiv.innerHTML += "<p>Logout successful! Reloading in 2 seconds...</p>";
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<p>Error: ${error.message}</p>`;
            });
        });
    </script>
</body>
</html>
