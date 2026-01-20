<?php
/**
 * Script to delete a user from the database
 * Usage: Access via browser: http://localhost/Glassify-CI/delete_user.php?userid=6
 * Or run via command line: php delete_user.php 6
 */

// Get user ID from URL parameter or command line argument
$user_id = isset($_GET['userid']) ? intval($_GET['userid']) : (isset($argv[1]) ? intval($argv[1]) : 6);

if ($user_id <= 0) {
    die("Error: Invalid UserID. Please provide a valid user ID.\n");
}

// Database configuration
$hostname = 'localhost';
$username = 'admin_glassify';
$password = 'glassifyAdmin';
$database = 'latest_glassifydb';

// Connect to database
$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "=== User Deletion Script ===\n";
echo "Attempting to delete UserID: $user_id\n\n";

// Start transaction
$conn->begin_transaction();

try {
    // 1. Check if user exists
    $check_user = $conn->query("SELECT UserID, First_Name, Last_Name, Email, Role FROM `user` WHERE UserID = $user_id");
    
    if ($check_user->num_rows === 0) {
        throw new Exception("User with UserID $user_id does not exist.");
    }
    
    $user_data = $check_user->fetch_assoc();
    echo "Found user: {$user_data['First_Name']} {$user_data['Last_Name']} ({$user_data['Email']}) - Role: {$user_data['Role']}\n\n";
    
    // 2. Check for related records that might prevent deletion
    echo "Checking related records...\n";
    
    // Check orders where user is SalesRep
    $orders_as_salesrep = $conn->query("SELECT COUNT(*) as count FROM `order` WHERE SalesRep_ID = $user_id")->fetch_assoc()['count'];
    if ($orders_as_salesrep > 0) {
        echo "  - Warning: User is assigned as SalesRep in $orders_as_salesrep order(s)\n";
        echo "    These will be set to NULL (if foreign key allows SET NULL) or deletion may fail.\n";
    }
    
    // Check orders where user approved/disapproved
    $orders_approved = $conn->query("SELECT COUNT(*) as count FROM `order` WHERE ApprovedBy_SalesRep_ID = $user_id OR ApprovedBy_Admin_ID = $user_id OR DisapprovedBy_ID = $user_id")->fetch_assoc()['count'];
    if ($orders_approved > 0) {
        echo "  - Warning: User has approved/disapproved $orders_approved order(s)\n";
        echo "    These references will be set to NULL (if foreign key allows SET NULL).\n";
    }
    
    // Check appointments
    $appointments = $conn->query("SELECT COUNT(*) as count FROM `appointment` WHERE Admin_ID = $user_id OR AssignedStaff_ID = $user_id")->fetch_assoc()['count'];
    if ($appointments > 0) {
        echo "  - Warning: User is assigned in $appointments appointment(s)\n";
        echo "    These references will be set to NULL (if foreign key allows SET NULL).\n";
    }
    
    // Check project schedules
    $schedules = $conn->query("SELECT COUNT(*) as count FROM `projectschedule` WHERE Admin_ID = $user_id")->fetch_assoc()['count'];
    if ($schedules > 0) {
        echo "  - Error: User is assigned in $schedules project schedule(s) with RESTRICT constraint.\n";
        echo "    These must be updated or deleted first before user can be deleted.\n";
        throw new Exception("Cannot delete user: User is referenced in project schedules. Please update or delete those records first.");
    }
    
    // Check inventory updates
    $inventory_updates = $conn->query("SELECT COUNT(*) as count FROM `inventory` WHERE UpdatedBy = $user_id")->fetch_assoc()['count'];
    if ($inventory_updates > 0) {
        echo "  - Warning: User has updated $inventory_updates inventory record(s)\n";
        echo "    These references may prevent deletion if foreign key is RESTRICT.\n";
    }
    
    // Check customer record (will be deleted automatically due to CASCADE)
    $customer = $conn->query("SELECT Customer_ID FROM `customer` WHERE UserID = $user_id")->fetch_assoc();
    if ($customer) {
        $customer_id = $customer['Customer_ID'];
        echo "  - Found customer record (Customer_ID: $customer_id) - will be deleted automatically (CASCADE)\n";
        
        // Check orders for this customer
        $customer_orders = $conn->query("SELECT COUNT(*) as count FROM `order` WHERE Customer_ID = $customer_id")->fetch_assoc()['count'];
        if ($customer_orders > 0) {
            echo "  - Error: Customer has $customer_orders order(s) with RESTRICT constraint.\n";
            echo "    Orders must be deleted or customer_id updated before user can be deleted.\n";
            throw new Exception("Cannot delete user: Customer has orders. Please delete or reassign orders first.");
        }
        
        // Check cart items
        $cart_items = $conn->query("SELECT COUNT(*) as count FROM `cart` WHERE Customer_ID = $customer_id")->fetch_assoc()['count'];
        if ($cart_items > 0) {
            echo "  - Found $cart_items cart item(s) - will be deleted automatically (CASCADE)\n";
        }
        
        // Check wishlist items
        $wishlist_items = $conn->query("SELECT COUNT(*) as count FROM `wishlist` WHERE Customer_ID = $customer_id")->fetch_assoc()['count'];
        if ($wishlist_items > 0) {
            echo "  - Found $wishlist_items wishlist item(s) - will be deleted automatically (CASCADE)\n";
        }
    }
    
    // Check addresses (will be deleted automatically due to CASCADE)
    $addresses = $conn->query("SELECT COUNT(*) as count FROM `user_address` WHERE UserID = $user_id")->fetch_assoc()['count'];
    if ($addresses > 0) {
        echo "  - Found $addresses address(es) - will be deleted automatically (CASCADE)\n";
    }
    
    echo "\n";
    
    // 3. Set NULL values where foreign keys allow it (before deletion)
    echo "Updating references to NULL where possible...\n";
    
    // Update orders where user is SalesRep (if foreign key allows)
    if ($orders_as_salesrep > 0) {
        $conn->query("UPDATE `order` SET SalesRep_ID = NULL WHERE SalesRep_ID = $user_id");
        echo "  - Updated order SalesRep_ID references\n";
    }
    
    // Update orders where user approved/disapproved (if foreign key allows)
    if ($orders_approved > 0) {
        $conn->query("UPDATE `order` SET ApprovedBy_SalesRep_ID = NULL WHERE ApprovedBy_SalesRep_ID = $user_id");
        $conn->query("UPDATE `order` SET ApprovedBy_Admin_ID = NULL WHERE ApprovedBy_Admin_ID = $user_id");
        $conn->query("UPDATE `order` SET DisapprovedBy_ID = NULL WHERE DisapprovedBy_ID = $user_id");
        echo "  - Updated order approval references\n";
    }
    
    // Update appointments (if foreign key allows)
    if ($appointments > 0) {
        $conn->query("UPDATE `appointment` SET Admin_ID = NULL WHERE Admin_ID = $user_id");
        $conn->query("UPDATE `appointment` SET AssignedStaff_ID = NULL WHERE AssignedStaff_ID = $user_id");
        echo "  - Updated appointment references\n";
    }
    
    // Update inventory (if foreign key allows)
    if ($inventory_updates > 0) {
        $conn->query("UPDATE `inventory` SET UpdatedBy = NULL WHERE UpdatedBy = $user_id");
        echo "  - Updated inventory references\n";
    }
    
    // Update stock_transactions (if exists and allows)
    $conn->query("UPDATE `stock_transactions` SET user_id = NULL WHERE user_id = $user_id");
    
    // Update activities (if exists and allows)
    $conn->query("UPDATE `activities` SET user_id = NULL WHERE user_id = $user_id");
    
    echo "\n";
    
    // 4. Delete the user (this will cascade to customer and user_address)
    echo "Deleting user...\n";
    $delete_user = $conn->query("DELETE FROM `user` WHERE UserID = $user_id");
    
    if (!$delete_user) {
        throw new Exception("Failed to delete user: " . $conn->error);
    }
    
    $affected_rows = $conn->affected_rows;
    
    if ($affected_rows > 0) {
        echo "  ✓ Successfully deleted user (UserID: $user_id)\n";
        echo "  ✓ Related customer record and addresses deleted automatically (CASCADE)\n";
        
        // Commit transaction
        $conn->commit();
        echo "\n=== Deletion completed successfully! ===\n";
    } else {
        throw new Exception("No rows were deleted. User may not exist or deletion was prevented.");
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "\n=== ERROR ===\n";
    echo $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes were made.\n";
    exit(1);
} finally {
    $conn->close();
}
