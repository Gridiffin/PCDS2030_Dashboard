<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .test-form {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
        }
        .result {
            background-color: #f5f5f5;
            padding: 15px;
            border: 1px solid #ddd;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>Login API Test</h1>
    
    <div class="test-form">
        <h2>Test Login API</h2>
        <form id="testForm">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="user" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="user123" required>
            </div>
            <button type="submit">Test Login</button>
        </form>
    </div>
    
    <div>
        <h3>API Response:</h3>
        <pre id="apiResult" class="result">Response will appear here...</pre>
    </div>
    
    <div>
        <h3>Current Session Data:</h3>
        <pre id="sessionData" class="result"><?php echo json_encode($_SESSION, JSON_PRETTY_PRINT); ?></pre>
    </div>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultElem = document.getElementById('apiResult');
            
            resultElem.textContent = 'Sending request...';
            
            fetch('php/auth/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                resultElem.textContent = JSON.stringify(data, null, 2);
                
                // Reload page after successful login to show updated session
                if (data.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                resultElem.textContent = `Error: ${error.message}`;
            });
        });
    </script>
</body>
</html>
