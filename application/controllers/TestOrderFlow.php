<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Test Controller for Order Flow Functions
 * 
 * Usage: 
 * - http://localhost/Glassify-CI/TestOrderFlow/index
 * - http://localhost/Glassify-CI/TestOrderFlow/test_queries
 * 
 * Note: This is for testing only. Remove or restrict access in production.
 */
class TestOrderFlow extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->database();
        
        // Add basic styling
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .test-section { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
            h2 { color: #333; }
            h3 { color: #666; border-bottom: 2px solid #4CAF50; padding-bottom: 5px; }
        </style>";
    }

    /**
     * Test Order Flow Functions
     * Access: http://localhost/Glassify-CI/TestOrderFlow/index
     */
    public function index()
    {
        echo "<div class='test-section'>";
        echo "<h2>🧪 Order Flow Function Tests</h2>";
        
        // Get test sales rep ID (you can change this)
        $sales_rep_id = 3; // Change to your test sales rep ID
        
        echo "<pre>";

        // Test 1: Get Sales Rep Orders
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Test 1: get_sales_rep_orders()\n";
        echo "═══════════════════════════════════════════════════════════\n";
        try {
            $orders = $this->Order_model->get_sales_rep_orders($sales_rep_id);
            echo "<span class='success'>✅ Function executed successfully</span>\n";
            echo "Found " . count($orders) . " orders for Sales Rep ID: $sales_rep_id\n";
            if (!empty($orders)) {
                echo "\nFirst order details:\n";
                echo json_encode($orders[0], JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "<span class='warning'>⚠️ No orders found for this sales rep</span>\n";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
        }
        echo "\n";

        // Test 2: Get Awaiting Admin Orders
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Test 2: get_awaiting_admin_orders()\n";
        echo "═══════════════════════════════════════════════════════════\n";
        try {
            $awaiting = $this->Order_model->get_awaiting_admin_orders();
            echo "<span class='success'>✅ Function executed successfully</span>\n";
            echo "Found " . count($awaiting) . " orders awaiting admin\n";
            if (!empty($awaiting)) {
                echo "\nFirst order details:\n";
                echo json_encode($awaiting[0], JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "<span class='warning'>⚠️ No orders awaiting admin</span>\n";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
        }
        echo "\n";

        // Test 3: Get Ready to Approve Orders
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Test 3: get_ready_to_approve_orders()\n";
        echo "═══════════════════════════════════════════════════════════\n";
        try {
            $ready = $this->Order_model->get_ready_to_approve_orders($sales_rep_id);
            echo "<span class='success'>✅ Function executed successfully</span>\n";
            echo "Found " . count($ready) . " orders ready to approve\n";
            if (!empty($ready)) {
                echo "\nFirst order details:\n";
                echo json_encode($ready[0], JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "<span class='warning'>⚠️ No orders ready to approve</span>\n";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
        }
        echo "\n";

        // Test 4: Validate Status Transition
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Test 4: validate_status_transition()\n";
        echo "═══════════════════════════════════════════════════════════\n";
        try {
            // Valid transition
            $valid = $this->Order_model->validate_status_transition('Pending Review', 'Awaiting Admin', 'Sales Representative');
            echo "Pending Review → Awaiting Admin: ";
            if ($valid['valid']) {
                echo "<span class='success'>✅ VALID</span>\n";
            } else {
                echo "<span class='error'>❌ INVALID</span>\n";
            }
            echo "Message: " . $valid['message'] . "\n\n";
            
            // Invalid transition
            $invalid = $this->Order_model->validate_status_transition('Pending Review', 'Approved', 'Sales Representative');
            echo "Pending Review → Approved: ";
            if (!$invalid['valid']) {
                echo "<span class='success'>✅ Correctly blocked (INVALID)</span>\n";
            } else {
                echo "<span class='error'>❌ Should be blocked but was allowed</span>\n";
            }
            echo "Message: " . $invalid['message'] . "\n";
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
        }
        echo "\n";

        // Test 5: Get Order Details
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Test 5: get_order_details_for_popup()\n";
        echo "═══════════════════════════════════════════════════════════\n";
        try {
            // Get first order ID from test 1
            if (!empty($orders)) {
                $order_id = $orders[0]->OrderID;
                $details = $this->Order_model->get_order_details_for_popup($order_id);
                if ($details) {
                    echo "<span class='success'>✅ Order details retrieved successfully</span>\n";
                    echo "Order Number: " . ($details->OrderNumber ?? 'N/A') . "\n";
                    echo "Status: " . ($details->Status ?? 'N/A') . "\n";
                    echo "Total Amount: " . ($details->TotalAmount ?? 'N/A') . "\n";
                } else {
                    echo "<span class='error'>❌ Failed to get order details</span>\n";
                }
            } else {
                echo "<span class='warning'>⚠️ No orders available to test with</span>\n";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
        }
        echo "\n";

        // Test 6: Count Orders by Status
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Test 6: count_sales_rep_orders_by_status()\n";
        echo "═══════════════════════════════════════════════════════════\n";
        try {
            $statuses = ['Pending Review', 'Awaiting Admin', 'Ready to Approve', 'Approved'];
            foreach ($statuses as $status) {
                $count = $this->Order_model->count_sales_rep_orders_by_status($sales_rep_id, $status);
                echo "$status: $count orders\n";
            }
            echo "<span class='success'>✅ Count function working</span>\n";
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
        }

        echo "</pre>";
        echo "</div>";
        
        echo "<div class='test-section'>";
        echo "<h3>📝 Notes</h3>";
        echo "<ul>";
        echo "<li>These are <strong>read-only</strong> tests. They don't modify data.</li>";
        echo "<li>To test status changes, use the <strong>manual testing guide</strong>.</li>";
        echo "<li>Change <code>\$sales_rep_id</code> in the code to test with different sales reps.</li>";
        echo "<li>If you see errors, check the PHP error logs: <code>application/logs/</code></li>";
        echo "</ul>";
        echo "</div>";
    }

    /**
     * Test Database Queries Performance
     * Access: http://localhost/Glassify-CI/TestOrderFlow/test_queries
     */
    public function test_queries()
    {
        echo "<div class='test-section'>";
        echo "<h2>⚡ Database Query Performance Tests</h2>";
        echo "<pre>";

        // Test 1: Query by Status
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Test 1: Query Orders by Status\n";
        echo "═══════════════════════════════════════════════════════════\n";
        
        $start = microtime(true);
        $this->db->select('*');
        $this->db->from('order');
        $this->db->where('Status', 'Pending Review');
        $result = $this->db->get();
        $time = (microtime(true) - $start) * 1000;
        
        echo "Query Time: " . number_format($time, 2) . " ms\n";
        echo "Rows Returned: " . $result->num_rows() . "\n";
        
        if ($time > 500) {
            echo "<span class='error'>⚠️ WARNING: Query is slow (>500ms). Consider adding indexes.</span>\n";
        } else {
            echo "<span class='success'>✅ Query performance is good</span>\n";
        }
        echo "\n";

        // Test 2: Query with JOIN
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Test 2: Query Orders with Customer JOIN\n";
        echo "═══════════════════════════════════════════════════════════\n";
        
        $start = microtime(true);
        $this->db->select('o.*, c.Customer_ID, u.First_Name, u.Last_Name');
        $this->db->from('order o');
        $this->db->join('customer c', 'o.Customer_ID = c.Customer_ID', 'left');
        $this->db->join('user u', 'c.UserID = u.UserID', 'left');
        $this->db->where('o.Status', 'Pending Review');
        $result = $this->db->get();
        $time = (microtime(true) - $start) * 1000;
        
        echo "Query Time: " . number_format($time, 2) . " ms\n";
        echo "Rows Returned: " . $result->num_rows() . "\n";
        
        if ($time > 500) {
            echo "<span class='error'>⚠️ WARNING: JOIN query is slow (>500ms)</span>\n";
        } else {
            echo "<span class='success'>✅ JOIN query performance is good</span>\n";
        }
        echo "\n";

        // Test 3: Check Indexes
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Test 3: Check Indexes on Order Table\n";
        echo "═══════════════════════════════════════════════════════════\n";
        
        $indexes = $this->db->query("SHOW INDEXES FROM `order`")->result();
        $important_indexes = ['Status', 'Customer_ID', 'SalesRep_ID', 'OrderDate'];
        $found_indexes = [];
        
        foreach ($indexes as $index) {
            if (in_array($index->Column_name, $important_indexes)) {
                $found_indexes[] = $index->Column_name;
            }
        }
        
        echo "Important indexes found: " . implode(', ', $found_indexes) . "\n";
        $missing = array_diff($important_indexes, $found_indexes);
        if (!empty($missing)) {
            echo "<span class='warning'>⚠️ Missing indexes: " . implode(', ', $missing) . "</span>\n";
        } else {
            echo "<span class='success'>✅ All important indexes present</span>\n";
        }

        echo "</pre>";
        echo "</div>";
    }
}
