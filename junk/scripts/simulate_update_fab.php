<?php
// Simulate the server-side regressive check in update_fabrication_progress()
$mysqli = new mysqli('localhost','root','','latest_glassifydb');
if ($mysqli->connect_errno) {
    echo json_encode(['error' => 'DB connect failed', 'errno' => $mysqli->connect_errno]);
    exit(1);
}
$order_id = $argv[1] ?? null;
$attempt_status = $argv[2] ?? null; // e.g. 'In Progress'
if (!$order_id || !$attempt_status) {
    echo json_encode(['error'=>'Usage: php simulate_update_fab.php <order_id> "<fabrication_status>"']);
    exit(1);
}
$sql = "SELECT OrderID, FabricationStatus FROM `order` WHERE OrderID = " . intval($order_id) . " LIMIT 1";
$res = $mysqli->query($sql);
if (!$res) { echo json_encode(['error'=>'Query failed','errno'=>$mysqli->error]); exit(1); }
$row = $res->fetch_assoc();
if (!$row) { echo json_encode(['error'=>'Order not found']); exit(1); }
$existing = $row['FabricationStatus'];
$earlierStatuses = ['Queued','In Progress','Quality Check'];
if ($existing === 'Ready' && in_array($attempt_status, $earlierStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Cannot move order back from Ready status.']);
} else {
    echo json_encode(['success' => true, 'message' => 'Would allow change (no regressive violation detected).', 'existing' => $existing, 'attempt' => $attempt_status]);
}
$mysqli->close();
