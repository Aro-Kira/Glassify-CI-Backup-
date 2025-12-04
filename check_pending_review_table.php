<?php
$pdo = new PDO("mysql:host=localhost;dbname=glassify-test", 'root', '');
echo "pending_review_orders table columns:\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM pending_review_orders");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

