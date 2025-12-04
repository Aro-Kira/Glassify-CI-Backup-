<?php
/**
 * Password Hash Generator
 * 
 * This script generates a Bcrypt hash for a password.
 * 
 * Usage: php generate_password_hash.php
 * Or access via browser: http://localhost/Glassify-CI/generate_password_hash.php
 */

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $success = true;
} else {
    $hash = null;
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Password Hash</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            padding: 30px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background: #5568d3;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border: 1px solid #4caf50;
            border-radius: 6px;
        }
        .hash {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            word-break: break-all;
            margin-top: 10px;
        }
        .info {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 13px;
            color: #1976D2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Generate Password Hash</h1>
        
        <div class="info">
            <strong>Instructions:</strong> Enter a password to generate a Bcrypt hash. 
            Copy the hash and use it in the SQL script when creating a Sales Representative account.
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Enter Password:</label>
                <input type="password" name="password" required 
                       placeholder="Enter the password for the Sales Rep account">
            </div>
            <button type="submit">Generate Hash</button>
        </form>
        
        <?php if ($success && $hash): ?>
            <div class="result">
                <strong>Password Hash Generated:</strong>
                <div class="hash"><?php echo htmlspecialchars($hash); ?></div>
                <p style="margin-top: 10px; font-size: 12px; color: #666;">
                    Copy this hash and paste it into the SQL script where it says 
                    <code>$2y$10$YourHashedPasswordHere</code>
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

