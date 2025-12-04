<?php
/**
 * Script to create enduser table and populate it with customer data
 */

// Load CodeIgniter
define('ENVIRONMENT', 'development');
require_once('index.php');

$ci =& get_instance();
$ci->load->database();

echo "Creating enduser table...\n\n";

// Create enduser table
$create_table_sql = "
CREATE TABLE IF NOT EXISTS `enduser` (
  `EndUser_ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `PhoneNum` varchar(13) NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `Date_Created` timestamp NOT NULL DEFAULT current_timestamp(),
  `Date_Updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Last_Active` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`EndUser_ID`),
  UNIQUE KEY `UserID` (`UserID`),
  UNIQUE KEY `Email` (`Email`),
  CONSTRAINT `enduser_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

$ci->db->query($create_table_sql);
echo "✓ Enduser table created\n\n";

// Populate enduser table with existing customers
echo "Populating enduser table with customers from user table...\n\n";

$customers = $ci->db->where('Role', 'Customer')->get('user')->result();

$inserted = 0;
$updated = 0;

foreach ($customers as $customer) {
    // Check if record already exists
    $ci->db->where('UserID', $customer->UserID);
    $existing = $ci->db->get('enduser')->row();
    
    if ($existing) {
        // Update existing record
        $ci->db->where('UserID', $customer->UserID);
        $ci->db->update('enduser', [
            'First_Name' => $customer->First_Name,
            'Last_Name' => $customer->Last_Name,
            'Middle_Name' => $customer->Middle_Name,
            'Email' => $customer->Email,
            'PhoneNum' => $customer->PhoneNum,
            'Status' => $customer->Status,
            'Date_Updated' => date('Y-m-d H:i:s')
        ]);
        $updated++;
        echo "  - Updated: {$customer->First_Name} {$customer->Last_Name} ({$customer->Email})\n";
    } else {
        // Insert new record
        $ci->db->insert('enduser', [
            'UserID' => $customer->UserID,
            'First_Name' => $customer->First_Name,
            'Last_Name' => $customer->Last_Name,
            'Middle_Name' => $customer->Middle_Name,
            'Email' => $customer->Email,
            'PhoneNum' => $customer->PhoneNum,
            'Status' => $customer->Status,
            'Date_Created' => $customer->Date_Created,
            'Date_Updated' => $customer->Date_Updated
        ]);
        $inserted++;
        echo "  - Inserted: {$customer->First_Name} {$customer->Last_Name} ({$customer->Email})\n";
    }
}

echo "\n=== Summary ===\n";
echo "Inserted: {$inserted}\n";
echo "Updated: {$updated}\n";
echo "Total customers: " . count($customers) . "\n";
echo "\nDone!\n";

