<?php
$pdo = new PDO("mysql:host=localhost;dbname=glassify-test", 'root', '');
$pdo->exec("UPDATE inventory_items SET InStock = 0 WHERE ItemID = 'HD-007'");
echo "Updated HD-007 (Handle Set) to 0 stock\n";

