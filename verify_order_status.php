<?php
$pdo = new PDO('mysql:host=localhost;dbname=glassify-test', 'root', '');
$stmt = $pdo->query('SELECT OrderID, AdminStatus FROM ready_to_approve_orders WHERE OrderID = "GI017"');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo 'Order GI017 AdminStatus: ' . ($result['AdminStatus'] ?? 'NULL') . PHP_EOL;

