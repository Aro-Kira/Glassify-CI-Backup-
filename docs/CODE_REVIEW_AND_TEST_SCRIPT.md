# Code Review & Automated Test Script
## Static Analysis & Test Scripts for Order Flow System

**Created**: 2025-01-08  
**Purpose**: Code review findings and automated test scripts you can run

---

## 🔍 Code Review Findings

### ✅ Strengths Found

1. **Good Transaction Handling**
   - All status-changing operations use `$this->db->trans_start()` and `$this->db->trans_complete()`
   - Proper rollback on errors

2. **Proper Error Handling**
   - Functions return structured responses: `['success' => bool, 'message' => string]`
   - Input validation present

3. **Activity Logging**
   - All actions logged in `system_activity_log`
   - Good audit trail

### ⚠️ Potential Issues Found

#### Issue 1: Order ID Parsing in `request_approval()`
**Location**: `SalesCon.php` line ~1538

**Current Code:**
```php
// Pass the original order_id to the model - it can handle both numeric and OrderNumber format
// The model will try both OrderID (numeric) and OrderNumber (GI001 format)
$result = $this->Order_model->request_admin_approval($order_id, $sales_rep_id, $notes);
```

**Potential Problem**: The `request_admin_approval()` function in Order_model expects a numeric OrderID, but it tries to handle both formats. This could cause issues if OrderNumber format is passed.

**Recommendation**: Use the `parse_order_id()` helper consistently, or ensure the model function handles both formats properly.

---

#### Issue 2: Missing Order Lookup in `request_admin_approval()`
**Location**: `Order_model.php` line ~800

**Check**: The function should verify the order exists and belongs to the sales rep before processing.

**Verification Needed**: 
```php
// Should check:
1. Order exists
2. Order belongs to this sales rep
3. Order status is 'Pending Review'
```

---

#### Issue 3: Payment Method Not Set in `create_payment_record()`
**Location**: `Order_model.php` (need to check)

**Check**: When creating payment record, ensure `PaymentMethod` is copied from order.

---

## 🧪 Automated Test Script

### Test Script 1: Database Verification

**File**: `test_database_setup.php`

```php
<?php
/**
 * Database Verification Test Script
 * Run this to verify your database is set up correctly
 * 
 * Usage: Place in project root, access via browser:
 * http://localhost/Glassify-CI/test_database_setup.php
 */

// Load CodeIgniter
require_once('index.php');

// Or if running standalone:
// require_once('application/config/database.php');

echo "<h2>Database Verification Test</h2>";

// Test database connection
$db = new mysqli('localhost', 'root', '', 'latest_glassifydb');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "<h3>✅ Database Connected</h3>";

// Test 1: Check required tables exist
$required_tables = [
    'order',
    'order_items',
    'payment',
    'awaiting_admin_orders',
    'ready_to_approve_orders',
    'approved_orders',
    'disapproved_orders',
    'system_activity_log',
    'customer',
    'user'
];

echo "<h3>Table Existence Check:</h3><ul>";
foreach ($required_tables as $table) {
    $result = $db->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<li>✅ Table '$table' exists</li>";
    } else {
        echo "<li>❌ Table '$table' MISSING</li>";
    }
}
echo "</ul>";

// Test 2: Check order table structure
echo "<h3>Order Table Structure:</h3>";
$result = $db->query("DESCRIBE `order`");
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table>";

// Test 3: Check for test users
echo "<h3>Test Users:</h3>";
$result = $db->query("SELECT UserID, First_Name, Last_Name, Email, Role FROM user WHERE Role IN ('Customer', 'Sales Representative', 'Admin')");
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['UserID']}</td><td>{$row['First_Name']} {$row['Last_Name']}</td><td>{$row['Email']}</td><td>{$row['Role']}</td></tr>";
}
echo "</table>";

// Test 4: Check order status distribution
echo "<h3>Order Status Distribution:</h3>";
$result = $db->query("SELECT Status, COUNT(*) as Count FROM `order` GROUP BY Status");
echo "<table border='1'><tr><th>Status</th><th>Count</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Status']}</td><td>{$row['Count']}</td></tr>";
}
echo "</table>";

$db->close();
?>
```

---

### Test Script 2: Function Verification (CodeIgniter)

**File**: `application/controllers/TestOrderFlow.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TestOrderFlow extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->database();
    }

    /**
     * Test Order Flow Functions
     * Access: http://localhost/Glassify-CI/TestOrderFlow/index
     */
    public function index()
    {
        echo "<h2>Order Flow Function Tests</h2>";
        echo "<pre>";

        // Test 1: Get Sales Rep Orders
        echo "Test 1: get_sales_rep_orders()\n";
        echo "--------------------------------\n";
        $sales_rep_id = 3; // Change to your test sales rep ID
        $orders = $this->Order_model->get_sales_rep_orders($sales_rep_id);
        echo "Found " . count($orders) . " orders\n";
        if (!empty($orders)) {
            echo "First order: " . json_encode($orders[0], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";

        // Test 2: Get Awaiting Admin Orders
        echo "Test 2: get_awaiting_admin_orders()\n";
        echo "-----------------------------------\n";
        $awaiting = $this->Order_model->get_awaiting_admin_orders();
        echo "Found " . count($awaiting) . " orders awaiting admin\n";
        if (!empty($awaiting)) {
            echo "First order: " . json_encode($awaiting[0], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";

        // Test 3: Get Ready to Approve Orders
        echo "Test 3: get_ready_to_approve_orders()\n";
        echo "-------------------------------------\n";
        $ready = $this->Order_model->get_ready_to_approve_orders($sales_rep_id);
        echo "Found " . count($ready) . " orders ready to approve\n";
        if (!empty($ready)) {
            echo "First order: " . json_encode($ready[0], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";

        // Test 4: Validate Status Transition
        echo "Test 4: validate_status_transition()\n";
        echo "-------------------------------------\n";
        $valid = $this->Order_model->validate_status_transition('Pending Review', 'Awaiting Admin', 'Sales Representative');
        echo "Pending Review → Awaiting Admin: " . ($valid['valid'] ? 'VALID' : 'INVALID') . "\n";
        echo "Message: " . $valid['message'] . "\n";
        
        $invalid = $this->Order_model->validate_status_transition('Pending Review', 'Approved', 'Sales Representative');
        echo "Pending Review → Approved: " . ($invalid['valid'] ? 'VALID' : 'INVALID') . "\n";
        echo "Message: " . $invalid['message'] . "\n";
        echo "\n";

        // Test 5: Get Order Details
        echo "Test 5: get_order_details_for_popup()\n";
        echo "-------------------------------------\n";
        if (!empty($orders)) {
            $order_id = $orders[0]->OrderID;
            $details = $this->Order_model->get_order_details_for_popup($order_id);
            if ($details) {
                echo "Order details retrieved successfully\n";
                echo "Order Number: " . $details->OrderNumber . "\n";
                echo "Status: " . $details->Status . "\n";
            } else {
                echo "Failed to get order details\n";
            }
        } else {
            echo "No orders to test with\n";
        }
        echo "\n";

        echo "</pre>";
        echo "<h3>✅ Basic Function Tests Complete</h3>";
        echo "<p>Note: These are read-only tests. To test status changes, use the manual testing guide.</p>";
    }

    /**
     * Test Database Queries
     * Access: http://localhost/Glassify-CI/TestOrderFlow/test_queries
     */
    public function test_queries()
    {
        echo "<h2>Database Query Tests</h2>";
        echo "<pre>";

        // Test query performance
        $start = microtime(true);
        $this->db->select('*');
        $this->db->from('order');
        $this->db->where('Status', 'Pending Review');
        $result = $this->db->get();
        $time = (microtime(true) - $start) * 1000;
        
        echo "Query Time: " . number_format($time, 2) . " ms\n";
        echo "Rows Returned: " . $result->num_rows() . "\n";
        
        if ($time > 500) {
            echo "⚠️ WARNING: Query is slow (>500ms)\n";
        } else {
            echo "✅ Query performance is good\n";
        }

        echo "</pre>";
    }
}
```

---

### Test Script 3: SQL Verification Queries

**File**: `test_queries.sql`

```sql
-- ============================================
-- Order Flow System - Verification Queries
-- Run these in phpMyAdmin to verify setup
-- ============================================

-- 1. Check Order Status Distribution
SELECT Status, COUNT(*) as Count 
FROM `order` 
GROUP BY Status 
ORDER BY Count DESC;

-- 2. Check for Orphaned Records
-- Orders in 'Awaiting Admin' but not in awaiting_admin_orders
SELECT o.OrderID, o.OrderNumber, o.Status
FROM `order` o
LEFT JOIN awaiting_admin_orders aao ON o.OrderID = aao.OrderID
WHERE o.Status = 'Awaiting Admin' 
AND aao.OrderID IS NULL;

-- 3. Check for Inconsistent Status
-- Orders in ready_to_approve_orders but status is not 'Ready to Approve'
SELECT o.OrderID, o.OrderNumber, o.Status, rtao.AdminStatus
FROM `order` o
JOIN ready_to_approve_orders rtao ON o.OrderID = rtao.OrderID
WHERE o.Status != 'Ready to Approve';

-- 4. Check Recent Activity Log
SELECT Action, Description, Role, UserID, Timestamp
FROM system_activity_log
ORDER BY Timestamp DESC
LIMIT 10;

-- 5. Check Payment Records
SELECT 
    p.Payment_ID,
    p.OrderID,
    o.OrderNumber,
    p.Status as PaymentStatus,
    o.Status as OrderStatus,
    p.Amount,
    o.TotalAmount
FROM payment p
JOIN `order` o ON p.OrderID = o.OrderID
ORDER BY p.Payment_ID DESC
LIMIT 10;

-- 6. Verify Foreign Key Relationships
SELECT 
    o.OrderID,
    o.Customer_ID,
    c.Customer_ID as CustomerExists,
    o.SalesRep_ID,
    u.UserID as SalesRepExists
FROM `order` o
LEFT JOIN customer c ON o.Customer_ID = c.Customer_ID
LEFT JOIN user u ON o.SalesRep_ID = u.UserID
WHERE c.Customer_ID IS NULL OR u.UserID IS NULL;

-- 7. Check for Missing Required Fields
SELECT 
    OrderID,
    OrderNumber,
    CASE WHEN OrderNumber IS NULL OR OrderNumber = '' THEN 'Missing OrderNumber' END as Issue
FROM `order`
WHERE OrderNumber IS NULL OR OrderNumber = '';

-- 8. Test Data Summary
SELECT 
    'Total Orders' as Metric,
    COUNT(*) as Value
FROM `order`
UNION ALL
SELECT 
    'Pending Review',
    COUNT(*)
FROM `order`
WHERE Status = 'Pending Review'
UNION ALL
SELECT 
    'Awaiting Admin',
    COUNT(*)
FROM `order`
WHERE Status = 'Awaiting Admin'
UNION ALL
SELECT 
    'Ready to Approve',
    COUNT(*)
FROM `order`
WHERE Status = 'Ready to Approve'
UNION ALL
SELECT 
    'Approved',
    COUNT(*)
FROM `order`
WHERE Status = 'Approved';
```

---

## 🚀 How to Run Tests

### Step 1: Database Verification
1. Copy `test_database_setup.php` to project root
2. Access: `http://localhost/Glassify-CI/test_database_setup.php`
3. Check all tables exist and structure is correct

### Step 2: Function Tests
1. Copy `TestOrderFlow.php` to `application/controllers/`
2. Access: `http://localhost/Glassify-CI/TestOrderFlow/index`
3. Review output for any errors

### Step 3: SQL Verification
1. Open phpMyAdmin
2. Select your database
3. Run queries from `test_queries.sql`
4. Check for any issues

---

## 📋 Test Checklist

After running the scripts, verify:

- [ ] All required tables exist
- [ ] Order table has all required columns
- [ ] Test users exist (Customer, Sales Rep, Admin)
- [ ] Functions return expected data
- [ ] Status transitions validate correctly
- [ ] No orphaned records in database
- [ ] Query performance is acceptable (<500ms)
- [ ] Foreign key relationships are intact

---

## 🐛 Common Issues & Fixes

### Issue: "Table doesn't exist"
**Fix**: Run the database migration script (`latest_glassifydb.sql`)

### Issue: "Function not found"
**Fix**: Check that `Order_model.php` is loaded correctly

### Issue: "Slow queries"
**Fix**: Check that indexes exist on Status, Customer_ID, SalesRep_ID columns

### Issue: "Orphaned records"
**Fix**: Run data cleanup script or manually fix inconsistencies

---

## 📝 Next Steps

1. **Run Database Verification** - Ensure setup is correct
2. **Run Function Tests** - Verify functions work
3. **Run SQL Queries** - Check data integrity
4. **Manual Testing** - Follow QUICK_TEST_CHECKLIST.md
5. **Fix Any Issues Found** - Address problems before production

---

*These scripts help verify the system is set up correctly. For full functionality testing, use the manual testing guide.*
