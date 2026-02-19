<?php
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$db   = 'latest_glassifydb';

$mysqli = @new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) {
    echo "ERROR|{$mysqli->connect_errno}|{$mysqli->connect_error}\n";
    exit(1);
}

echo "OK|Connected to {$db} at {$host}:{$port}\n";
$mysqli->close();
