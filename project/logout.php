<?php
// Include the database connection file to access session configuration
require_once 'config/db_connect.php';

// Check if a session is active
if (session_status() == PHP_SESSION_ACTIVE) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();
}

// Redirect to the home page after logging out
// This must be the last step, and no output can precede this header call.
header("Location: index.php"); 
exit(); // Always exit after a header redirect
?>