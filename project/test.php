<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Login Diagnostic</h2>";

// Check DB connection
$dbPath = __DIR__ . '/config/db_connect.php';
if (!file_exists($dbPath)) {
    echo "<p style='color:red'>ERROR: config/db_connect.php not found!</p>";
    exit;
}
require_once $dbPath;

if (!isset($pdo) || !$pdo) {
    echo "<p style='color:red'>ERROR: \$pdo not defined in db_connect.php!</p>";
    exit;
}

try {
    // Show users table columns
    echo "<h3>users Table Columns</h3><pre>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo htmlspecialchars($col['Field']) . " (" . htmlspecialchars($col['Type']) . ")\n";
    }
    echo "</pre>";

    // Show all admin users
    echo "<h3>Admin Users</h3>";
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$admins) {
        echo "<p style='color:orange'>No admin users found.</p>";
    } else {
        echo "<pre>";
        print_r($admins);
        echo "</pre>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>DB error: " . htmlspecialchars($e->getMessage()) . "</p>";
}