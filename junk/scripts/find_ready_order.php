<?php
$mysqli = new mysqli('localhost','root','','latest_glassifydb');
if ($mysqli->connect_errno) {
    echo json_encode(['error' => 'DB connect failed', 'errno' => $mysqli->connect_errno]);
    exit(1);
}
$sql = "SELECT OrderID, OrderNumber, FabricationStatus FROM `order` WHERE FabricationStatus = 'Ready' LIMIT 1";
$res = $mysqli->query($sql);
if (!$res) {
    echo json_encode(['error' => 'Query failed', 'errno' => $mysqli->error]);
    exit(1);
}
$row = $res->fetch_assoc();
if (!$row) {
    echo json_encode(['found' => false]);
} else {
    echo json_encode(['found' => true, 'order' => $row]);
}
$mysqli->close();
