<?php
$pdo = new PDO("mysql:host=localhost;dbname=glassify-test", 'root', '');
echo "Order table columns:\n";
$stmt = $pdo->query("SHOW COLUMNS FROM `order`");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}

