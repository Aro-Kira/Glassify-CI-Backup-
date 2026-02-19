<?php
$orderId = $argv[1] ?? '9';
$host = '127.0.0.1'; $user='root'; $pass=''; $db='latest_glassifydb'; $port=3306;
$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) { echo "CONNECT_ERROR|{$mysqli->connect_errno}|{$mysqli->connect_error}\n"; exit(1); }
$orderIdEsc = (int)$orderId;
$sql = "SELECT OrderItemID, Product_ID, Quantity, Dimensions, GlassShape, GlassType, GlassThickness, EdgeWork, FrameType, DesignRef, Customization FROM order_items WHERE OrderID={$orderIdEsc}";
$res = $mysqli->query($sql);
if (!$res) { echo "SQL_ERROR|".$mysqli->error."\n"; exit(1); }
if ($res->num_rows === 0) { echo "NO_ITEMS|No order items for OrderID {$orderId}\n"; exit(0); }
while ($row = $res->fetch_assoc()) {
    echo "---\n";
    foreach ($row as $k=>$v) { echo "$k: ".($v===null?'<NULL>':$v)."\n"; }
}
$mysqli->close();
