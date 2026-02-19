<?php
// Simple runner to apply an SQL file to the current CodeIgniter DB config.
// Usage: php tools/run_updates.php [path/to/sqlfile.sql]

define('ENVIRONMENT', 'development');
$configPath = __DIR__ . '/../application/config/database.php';
if (!file_exists($configPath)) {
    fwrite(STDERR, "Cannot find application config at $configPath\n");
    exit(1);
}
require $configPath;
if (!isset($db) || !isset($db['default'])) {
    fwrite(STDERR, "Database config not loaded or missing \$db['default'].\n");
    exit(1);
}
$cfg = $db['default'];
$host = $cfg['hostname'] ?? 'localhost';
$user = $cfg['username'] ?? 'root';
$pass = $cfg['password'] ?? '';
$name = $cfg['database'] ?? '';

$sqlFile = $argv[1] ?? __DIR__ . '/../database_updates.sql';
if (!file_exists($sqlFile)) {
    fwrite(STDERR, "SQL file not found: $sqlFile\n");
    exit(1);
}

$mysqli = new mysqli($host, $user, $pass, $name);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "MySQL connect error ({$mysqli->connect_errno}): {$mysqli->connect_error}\n");
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "Failed to read SQL file: $sqlFile\n");
    exit(1);
}

echo "Executing SQL file: $sqlFile\n";
if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "SQL executed. Check output above for errors.\n";
} else {
    fwrite(STDERR, "Error executing SQL: {$mysqli->error}\n");
    exit(1);
}

$mysqli->close();
echo "Done.\n";
