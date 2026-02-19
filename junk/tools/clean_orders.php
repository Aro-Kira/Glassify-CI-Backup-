<?php
/**
 * ============================================================================
 * GLASSIFY - SAFE ORDER CLEANUP SCRIPT
 * ============================================================================
 * 
 * This script safely removes ALL order-related data from the database
 * to give you a fresh start for testing.
 * 
 * WHAT THIS SCRIPT DELETES:
 * - All appointments (ocular visits and installations)
 * - All order items
 * - All orders (main order table)
 * - All legacy order status tables (pending_review, awaiting_admin, etc.)
 * - All customizations
 * - All quotations
 * - All payments
 * - All cart items
 * - All wishlist items
 * 
 * WHAT THIS SCRIPT PRESERVES:
 * - Users (customers, sales reps, admins)
 * - Products
 * - Inventory items
 * - Product categories and tags
 * - System settings
 * - User addresses
 * 
 * USAGE:
 * 1. Make sure you have a database backup!
 * 2. Open this file in your browser: http://localhost/Glassify-CI/clean_orders.php
 * 3. Click "Clean Orders" button
 * 
 * ============================================================================
 */

// Database connection (adjust credentials as needed)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'latest_glassifydb';

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// HTML Header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glassify - Clean Orders</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .warning h3 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .warning p {
            color: #856404;
            line-height: 1.6;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-box h3 {
            color: #1976D2;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-box ul {
            margin-left: 20px;
            color: #555;
        }
        .info-box li {
            margin: 5px 0;
            line-height: 1.5;
        }
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success-box h3 {
            color: #155724;
            margin-bottom: 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        button {
            flex: 1;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .log {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
        }
        .log-success {
            color: #28a745;
        }
        .log-error {
            color: #dc3545;
        }
        .log-info {
            color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Clean Orders</h1>
        <p class="subtitle">Safely remove all order-related data for fresh testing</p>

        <?php if (!isset($_POST['confirm_clean'])): ?>
            
            <div class="warning">
                <h3>⚠️ Warning: This action cannot be undone!</h3>
                <p>This will permanently delete all order-related data. Make sure you have a database backup before proceeding.</p>
            </div>

            <?php
            // Get current statistics
            $stats = [
                'orders' => $conn->query("SELECT COUNT(*) as count FROM `order`")->fetch_assoc()['count'],
                'order_items' => $conn->query("SELECT COUNT(*) as count FROM `order_items`")->fetch_assoc()['count'],
                'appointments' => $conn->query("SELECT COUNT(*) as count FROM `appointments`")->fetch_assoc()['count'],
                'customizations' => $conn->query("SELECT COUNT(*) as count FROM `customization`")->fetch_assoc()['count'],
                'payments' => $conn->query("SELECT COUNT(*) as count FROM `payment`")->fetch_assoc()['count'],
                'quotations' => $conn->query("SELECT COUNT(*) as count FROM `quotation`")->fetch_assoc()['count'],
                'cart_items' => $conn->query("SELECT COUNT(*) as count FROM `cart`")->fetch_assoc()['count'],
                'wishlist_items' => $conn->query("SELECT COUNT(*) as count FROM `wishlist`")->fetch_assoc()['count']
            ];
            ?>

            <div class="info-box">
                <h3>📊 Current Database Statistics</h3>
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['orders']; ?></div>
                        <div class="stat-label">Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['order_items']; ?></div>
                        <div class="stat-label">Order Items</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['appointments']; ?></div>
                        <div class="stat-label">Appointments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['customizations']; ?></div>
                        <div class="stat-label">Customizations</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['payments']; ?></div>
                        <div class="stat-label">Payments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['quotations']; ?></div>
                        <div class="stat-label">Quotations</div>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <h3>🗑️ What will be deleted:</h3>
                <ul>
                    <li>All orders and order items</li>
                    <li>All appointments (ocular visits and installations)</li>
                    <li>All customizations</li>
                    <li>All quotations</li>
                    <li>All payments</li>
                    <li>All cart and wishlist items</li>
                    <li>Legacy order status tables data</li>
                </ul>
            </div>

            <div class="info-box">
                <h3>✅ What will be preserved:</h3>
                <ul>
                    <li>All users (customers, sales reps, admins)</li>
                    <li>All products and product information</li>
                    <li>All inventory items and stock levels</li>
                    <li>System settings and configurations</li>
                    <li>User addresses</li>
                </ul>
            </div>

            <form method="POST">
                <div class="button-group">
                    <button type="submit" name="confirm_clean" value="1" class="btn-danger" onclick="return confirm('Are you absolutely sure? This will delete ALL orders and related data!')">
                        🧹 Clean Orders Now
                    </button>
                    <button type="button" class="btn-secondary" onclick="window.history.back()">
                        Cancel
                    </button>
                </div>
            </form>

        <?php else: ?>
            
            <div class="success-box">
                <h3>🚀 Cleaning in progress...</h3>
            </div>

            <div class="log">
                <?php
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    echo "<div class='log-info'>Starting cleanup process...</div>\n";
                    
                    // Disable foreign key checks temporarily
                    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                    echo "<div class='log-info'>Disabled foreign key checks</div>\n";
                    
                    // 1. Delete appointments
                    $result = $conn->query("DELETE FROM `appointments`");
                    echo "<div class='log-success'>✓ Deleted " . $conn->affected_rows . " appointments</div>\n";
                    
                    // 2. Delete payments
                    $result = $conn->query("DELETE FROM `payment`");
                    echo "<div class='log-success'>✓ Deleted " . $conn->affected_rows . " payments</div>\n";
                    
                    // 3. Delete quotations
                    $result = $conn->query("DELETE FROM `quotation`");
                    echo "<div class='log-success'>✓ Deleted " . $conn->affected_rows . " quotations</div>\n";
                    
                    // 4. Delete order items
                    $result = $conn->query("DELETE FROM `order_items`");
                    echo "<div class='log-success'>✓ Deleted " . $conn->affected_rows . " order items</div>\n";
                    
                    // 5. Delete legacy order status tables
                    $legacy_tables = [
                        'pending_review_orders',
                        'awaiting_admin_orders',
                        'ready_to_approve_orders',
                        'approved_orders',
                        'disapproved_orders'
                    ];
                    
                    foreach ($legacy_tables as $table) {
                        $result = $conn->query("DELETE FROM `$table`");
                        echo "<div class='log-success'>✓ Deleted " . $conn->affected_rows . " records from $table</div>\n";
                    }
                    
                    // 6. Delete main orders
                    $result = $conn->query("DELETE FROM `order`");
                    echo "<div class='log-success'>✓ Deleted " . $conn->affected_rows . " main orders</div>\n";
                    
                    // 7. Delete customizations (now safe after orders are deleted)
                    $result = $conn->query("DELETE FROM `customization`");
                    echo "<div class='log-success'>✓ Deleted " . $conn->affected_rows . " customizations</div>\n";
                    
                    // 8. Clear cart items
                    $result = $conn->query("DELETE FROM `cart`");
                    echo "<div class='log-success'>✓ Deleted " . $conn->affected_rows . " cart items</div>\n";
                    
                    // 9. Clear wishlist items
                    $result = $conn->query("DELETE FROM `wishlist`");
                    echo "<div class='log-success'>✓ Deleted " . $conn->affected_rows . " wishlist items</div>\n";
                    
                    // 10. Reset auto-increment IDs
                    echo "<div class='log-info'>Resetting auto-increment IDs...</div>\n";
                    
                    $reset_tables = [
                        'order' => 'OrderID',
                        'order_items' => 'OrderItemID',
                        'appointments' => 'AppointmentID',
                        'customization' => 'CustomizationID',
                        'payment' => 'Payment_ID',
                        'quotation' => 'QuotationID',
                        'cart' => 'Cart_ID',
                        'wishlist' => 'Wishlist_ID',
                        'pending_review_orders' => 'PendingOrderID',
                        'awaiting_admin_orders' => 'AwaitingOrderID',
                        'ready_to_approve_orders' => 'ReadyOrderID',
                        'approved_orders' => 'ApprovedOrderID',
                        'disapproved_orders' => 'DisapprovedOrderID'
                    ];
                    
                    foreach ($reset_tables as $table => $id_column) {
                        $conn->query("ALTER TABLE `$table` AUTO_INCREMENT = 1");
                        echo "<div class='log-success'>✓ Reset auto-increment for $table</div>\n";
                    }
                    
                    // Re-enable foreign key checks
                    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                    echo "<div class='log-info'>Re-enabled foreign key checks</div>\n";
                    
                    // Commit transaction
                    $conn->commit();
                    echo "<div class='log-success'><strong>✅ CLEANUP COMPLETED SUCCESSFULLY!</strong></div>\n";
                    echo "<div class='log-info'>All order-related data has been removed.</div>\n";
                    echo "<div class='log-info'>Your database is now clean and ready for testing.</div>\n";
                    
                } catch (Exception $e) {
                    // Rollback on error
                    $conn->rollback();
                    echo "<div class='log-error'>❌ Error: " . $e->getMessage() . "</div>\n";
                    echo "<div class='log-error'>Transaction rolled back. No changes were made.</div>\n";
                }
                ?>
            </div>

            <div class="button-group">
                <button type="button" class="btn-secondary" onclick="window.location.reload()">
                    View Statistics
                </button>
                <button type="button" class="btn-secondary" onclick="window.location.href='index.php'">
                    Go to Homepage
                </button>
            </div>

        <?php endif; ?>

    </div>
</body>
</html>

<?php
$conn->close();
?>
