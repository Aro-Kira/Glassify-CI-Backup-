<?php
$orderNumber = $argv[1] ?? 'GI009';
$host = '127.0.0.1'; $user='root'; $pass=''; $db='latest_glassifydb'; $port=3306;
$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) { echo "CONNECT_ERROR|{$mysqli->connect_errno}|{$mysqli->connect_error}\n"; exit(1); }
$orderNumberEsc = $mysqli->real_escape_string($orderNumber);
$sql = "SELECT OrderID, OrderNumber, Status, PaymentStatus, InstallationDate, OcularDate, FabricationDate, EstimatedDelivery, Updated_Date FROM `order` WHERE OrderNumber='".$orderNumberEsc."' LIMIT 1";
$res = $mysqli->query($sql);
if (!$res) { echo "SQL_ERROR|".$mysqli->error."\n"; exit(1); }
$row = $res->fetch_assoc();
if (!$row) { echo "NOT_FOUND|No order found for $orderNumber\n"; exit(0); }
foreach ($row as $k=>$v) { echo "$k: ".($v===null?'<NULL>':$v)."\n"; }
$mysqli->close();
