<?php
// Configuration file for secure database connection using PDO

// Start the session immediately for state management
if (session_status() == PHP_SESSION_NONE) {
    // Set secure session cookies
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Database credentials
define('DB_HOST', 'localhost'); // Host name (often localhost)
define('DB_USER', 'root');      // Database user (change this in production)
define('DB_PASS', '');          // Database password (change this in production)
define('DB_NAME', 'event_management_db'); // Name of the database you create

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
$options = [
    // Recommended options for robust and secure connections
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for better performance and security
];

try {
    // Create a new PDO instance (the database connection)
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // If connection fails, display a user-friendly error message (or a generic one in a production environment)
    exit('Database connection failed: ' . $e->getMessage());
}

// Helper function to check if a user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Helper function to check if the logged-in user is an admin
function is_admin() {
    return is_logged_in() && $_SESSION['user_role'] === 'admin';
}

// Function to fetch registered participants for a specific event (Used in dashboard)
// This function needs access to the $pdo object which is outside its scope.
function get_participants($event_id) {
    global $pdo; // <<< FIX: Access the global $pdo connection
    
    $stmt = $pdo->prepare("
        SELECT 
            u.student_id, u.name, u.email, r.timestamp 
        FROM registrations r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.event_id = ?
        ORDER BY r.timestamp ASC
    ");
    $stmt->execute([$event_id]);
    return $stmt->fetchAll();
}

// $pdo is now your reusable database connection object.
?>