<?php
/**
 * Network Access Test Page
 * 
 * This file helps you verify that your Glassify-CI application
 * is accessible from the network.
 * 
 * Access this file from another device to test connectivity.
 */

// Get server information
$server_ip = $_SERVER['SERVER_ADDR'] ?? 'Not detected';
$remote_ip = $_SERVER['REMOTE_ADDR'] ?? 'Not detected';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base_url = $protocol . '://' . $host . '/Glassify-CI/';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glassify-CI - Network Access Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .info-box {
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 20px 0;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .info-item {
            margin: 10px 0;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
            font-family: monospace;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border-radius: 4px;
            margin: 10px 5px;
            text-decoration: none;
        }
        .button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌐 Glassify-CI Network Access Test</h1>
        
        <?php if ($remote_ip !== '127.0.0.1' && $remote_ip !== '::1'): ?>
            <div class="info-box">
                <p class="success">✅ SUCCESS! You're accessing this from the network!</p>
                <p>Your connection is working correctly.</p>
            </div>
        <?php else: ?>
            <div class="warning-box">
                <p>⚠️ You're accessing this from localhost.</p>
                <p>To test network access, open this page from another device using your server's IP address.</p>
            </div>
        <?php endif; ?>
        
        <h2>Server Information</h2>
        <div class="info-item">
            <span class="info-label">Server IP:</span>
            <span class="info-value"><?php echo htmlspecialchars($server_ip); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Your IP (Remote):</span>
            <span class="info-value"><?php echo htmlspecialchars($remote_ip); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Host:</span>
            <span class="info-value"><?php echo htmlspecialchars($host); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Protocol:</span>
            <span class="info-value"><?php echo htmlspecialchars($protocol); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Base URL:</span>
            <span class="info-value"><?php echo htmlspecialchars($base_url); ?></span>
        </div>
        
        <h2>Quick Links</h2>
        <a href="<?php echo htmlspecialchars($base_url); ?>" class="button">Go to Glassify-CI</a>
        <a href="http://<?php echo htmlspecialchars($host); ?>/Glassify-CI/" class="button">HTTP Version</a>
        
        <h2>Next Steps</h2>
        <div class="info-box">
            <p><strong>To access from another laptop:</strong></p>
            <ol>
                <li>Make sure both devices are on the same Wi-Fi/network</li>
                <li>On the other laptop, open a browser</li>
                <li>Go to: <code>http://<?php echo htmlspecialchars($server_ip !== 'Not detected' ? $server_ip : 'YOUR_IP'); ?>/Glassify-CI/</code></li>
                <li>If it doesn't work, check the NETWORK_SETUP_GUIDE.md file</li>
            </ol>
        </div>
        
        <div class="warning-box">
            <p><strong>⚠️ Security Note:</strong></p>
            <p>This setup is for local network use only. Do not expose this to the internet without proper security measures.</p>
        </div>
    </div>
</body>
</html>
