<?php
/**
 * Fix user_address table - Add missing columns
 * 
 * Access this file directly in your browser:
 * http://localhost/Glassify-CI/fix_address_table.php
 * 
 * Or run via command line: php fix_address_table.php
 */

// Bootstrap CodeIgniter
define('ENVIRONMENT', 'development');
require_once('index.php');

$CI =& get_instance();
$CI->load->database();

echo "<h2>Fixing user_address table...</h2>";
echo "<pre>";

$table = 'user_address';
$columns_to_add = [
    'UnitHouseNumber' => [
        'type' => 'varchar(100)',
        'default' => 'NULL',
        'after' => 'AddressLine'
    ],
    'Street' => [
        'type' => 'varchar(255)',
        'default' => 'NULL',
        'after' => 'UnitHouseNumber'
    ],
    'Subdivision' => [
        'type' => 'varchar(255)',
        'default' => 'NULL',
        'after' => 'Street'
    ],
    'Barangay' => [
        'type' => 'varchar(100)',
        'default' => 'NULL',
        'after' => 'Subdivision'
    ],
    'Region' => [
        'type' => 'varchar(100)',
        'default' => 'NULL',
        'after' => 'Province'
    ]
];

foreach ($columns_to_add as $column => $def) {
    // Check if column exists by trying to describe the table
    $query = $CI->db->query("DESCRIBE `$table`");
    $columns = array();
    foreach ($query->result() as $row) {
        $columns[] = $row->Field;
    }
    
    if (in_array($column, $columns)) {
        echo "✓ Column '$column' already exists\n";
    } else {
        echo "Adding column '$column'...\n";
        
        $sql = "ALTER TABLE `$table` 
                ADD COLUMN `$column` {$def['type']} DEFAULT {$def['default']} 
                AFTER `{$def['after']}`";
        
        try {
            $CI->db->query($sql);
            echo "✓ Successfully added column '$column'\n";
        } catch (Exception $e) {
            echo "✗ Error adding column '$column': " . $e->getMessage() . "\n";
        }
    }
}

echo "\n<h3>Done! Table structure updated.</h3>";
echo "<p><a href='" . base_url('usercon/profile') . "'>Go to Profile Page</a></p>";
echo "</pre>";

