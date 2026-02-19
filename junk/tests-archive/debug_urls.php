<!DOCTYPE html>
<html>
<head>
    <title>Debug URLs</title>
</head>
<body>
    <h1>URL Debug</h1>
    <p>This should match what the admin orders page sees</p>
    <?php
    // Load CodeIgniter
    require_once('index.php');
    ?>
    <pre>
Base URL: <?php echo base_url(); ?>
Get Orders URL: <?php echo base_url('AdminCon/get_orders_ajax'); ?>
    </pre>
</body>
</html>
