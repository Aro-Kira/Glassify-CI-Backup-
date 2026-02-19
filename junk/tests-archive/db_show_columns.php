<?php
$host='127.0.0.1'; $user='root'; $pass=''; $db='latest_glassifydb'; $port=3306;
$mysqli = new mysqli($host,$user,$pass,$db,$port);
if ($mysqli->connect_errno) { echo $mysqli->connect_error; exit(1); }
$res = $mysqli->query("SHOW COLUMNS FROM order_items");
while ($row = $res->fetch_assoc()) { echo $row['Field']."\n"; }
$mysqli->close();
