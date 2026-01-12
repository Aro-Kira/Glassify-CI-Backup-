<?php
/**
 * Script to check and add missing columns to user_address table
 * Run this file directly in your browser: http://localhost/Glassify-CI/check_and_fix_address_table.php
 */

// Load CodeIgniter
require_once('index.php');

// Get database connection
$CI =& get_instance();
$CI->load->database();

echo "<h2>Checking user_address table structure...</h2>";

// Check which columns exist
$columns_to_check = [
    'UnitHouseNumber' => "varchar(100) DEFAULT NULL",
    'Street' => "varchar(255) DEFAULT NULL",
    'Subdivision' => "varchar(255) DEFAULT NULL",
    'Barangay' => "varchar(100) DEFAULT NULL",
    'Region' => "varchar(100) DEFAULT NULL"
];

$dbname = $CI->db->database;
$table = 'user_address';

foreach ($columns_to_check as $column => $definition) {
    $query = $CI->db->query("
        SELECT COUNT(*) as count 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = ? 
        AND TABLE_NAME = ? 
        AND COLUMN_NAME = ?
    ", [$dbname, $table, $column]);
    
    $result = $query->row();
    
    if ($result->count == 0) {
        echo "<p style='color: orange;'>Column '$column' is MISSING. Adding it...</p>";
        
        // Determine position
        $position = '';
        if ($column == 'UnitHouseNumber') {
            $position = 'AFTER AddressLine';
        } elseif ($column == 'Street') {
            $position = 'AFTER UnitHouseNumber';
        } elseif ($column == 'Subdivision') {
            $position = 'AFTER Street';
        } elseif ($column == 'Barangay') {
            $position = 'AFTER Subdivision';
        } elseif ($column == 'Region') {
            $position = 'AFTER Province';
        }
        
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition $position";
        
        try {
            $CI->db->query($sql);
            echo "<p style='color: green;'>✓ Successfully added column '$column'</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Failed to add column '$column': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Column '$column' exists</p>";
    }
}

echo "<h3>Done! Try adding an address again.</h3>";
echo "<p><a href='" . base_url('usercon/profile') . "'>Go to Profile Page</a></p>";

