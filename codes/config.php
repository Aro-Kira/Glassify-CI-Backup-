<?php
/**
 * GlassWorth BUILDERS - Database Configuration
 * 
 * Update these values according to your MySQL server settings
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'glassworth_builders');

// Create database connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4 for proper character encoding
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Alternative PDO connection (if you prefer PDO)
function getPDOConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Helper function to execute queries safely
function executeQuery($query, $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Default to string type
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
    
    return $result;
}

// Helper function to fetch all results
function fetchAll($query, $params = []) {
    $result = executeQuery($query, $params);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Helper function to fetch single row
function fetchOne($query, $params = []) {
    $result = executeQuery($query, $params);
    return $result->fetch_assoc();
}

?>

