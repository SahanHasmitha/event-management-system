<?php
require_once '../config/db_connect.php';

// SECURITY CHECK: Must be an admin to delete an event
if (!is_admin()) {
    header("Location: ../login.php");
    exit();
}

global $pdo; // <<< FIX: Ensure $pdo is accessible

$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$event_id) {
    // If ID is missing or invalid, redirect with an error message
    header("Location: dashboard.php?status=error&message=Invalid event ID.");
    exit();
}

try {
    // Start transaction to ensure atomicity
    $pdo->beginTransaction();

    // Delete the event itself:
    $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);

    $pdo->commit();

    // Redirect back to the dashboard with a success message
    header("Location: dashboard.php?status=deleted");
    exit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Redirect back to the dashboard with an error message
    error_log("Delete Error: " . $e->getMessage());
    header("Location: dashboard.php?status=error&message=Database error on deletion.");
    exit();
}
?>