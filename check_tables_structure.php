<?php
$pdo = new PDO("mysql:host=localhost;dbname=glassify-test", 'root', '');
echo "User table columns:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM user");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "  - {$col['Field']}\n";
}
echo "\nCustomer table columns:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM customer");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "  - {$col['Field']}\n";
}

