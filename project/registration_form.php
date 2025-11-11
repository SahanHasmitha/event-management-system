<?php


// TEMPORARY DEBUGGING LINES START
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// TEMPORARY DEBUGGING LINES END

// Include the database connection file
require_once 'config/db_connect.php';

// Initialize variables for event details and status messages
// ... (rest of the code)


// Include the database connection file
require_once 'config/db_connect.php';

// Initialize variables for event details and status messages
$event_id = null;
$event = null;
$status_message = '';
$status_class = '';

// --- 1. HANDLE FORM SUBMISSION (PHP Server-side logic) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the required POST fields are present
    if (isset($_POST['event_id'], $_POST['student_id'], $_POST['student_name'], $_POST['email'], $_POST['contact_number'])) {
        
        $event_id = htmlspecialchars($_POST['event_id']);
        $student_id = htmlspecialchars($_POST['student_id']);
        $name = htmlspecialchars($_POST['student_name']);
        $email = htmlspecialchars($_POST['email']);
        $contact = htmlspecialchars($_POST['contact_number']);

        // BASIC SERVER-SIDE VALIDATION (In addition to client-side JS)
        if (empty($event_id) || empty($student_id) || empty($name) || empty($email) || empty($contact)) {
            $status_message = "Error: All fields are mandatory for registration.";
            $status_class = 'status-error';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $status_message = "Error: Invalid email format.";
            $status_class = 'status-error';
        } else {
            // Validation passed, proceed with database operations
            try {
                // --- Transaction Start ---
                $pdo->beginTransaction();

                // 1. Check if the user already exists in the 'users' table based on student_id/email.
                // NOTE: For simplicity, we are handling registration and event sign-up in one form.
                // In the future, this should link to a proper user login/session system.
                
                // Try to find the user by student_id or email
                $stmt_check_user = $pdo->prepare("SELECT user_id FROM users WHERE student_id = ? OR email = ?");
                $stmt_check_user->execute([$student_id, $email]);
                $user = $stmt_check_user->fetch();
                
                $user_id = null;
                
                if ($user) {
                    $user_id = $user['user_id'];
                    // User found, use existing user_id
                } else {
                    // User not found, create a new user account (with a dummy password hash)
                    // This is a temporary user creation logic. A real sign-up page should handle passwords.
                    $dummy_password_hash = password_hash(uniqid(), PASSWORD_DEFAULT);
                    $stmt_insert_user = $pdo->prepare("INSERT INTO users (student_id, name, email, contact_number, password_hash) VALUES (?, ?, ?, ?, ?)");
                    $stmt_insert_user->execute([$student_id, $name, $email, $contact, $dummy_password_hash]);
                    $user_id = $pdo->lastInsertId();
                }

                // 2. Check if the user is already registered for this event
                $stmt_check_reg = $pdo->prepare("SELECT reg_id FROM registrations WHERE user_id = ? AND event_id = ?");
                $stmt_check_reg->execute([$user_id, $event_id]);
                if ($stmt_check_reg->rowCount() > 0) {
                    throw new Exception("You are already registered for this event.");
                }

                // 3. Insert the registration record (Using PREPARED STATEMENT)
                $stmt_insert_reg = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
                
                if ($stmt_insert_reg->execute([$user_id, $event_id])) {
                    // Commit the transaction if successful
                    $pdo->commit();
                    $status_message = "Success! You have been registered for the event.";
                    $status_class = 'status-success';
                } else {
                    throw new Exception("Database failed to record registration.");
                }
                
            } catch (Exception $e) {
                // Catch any exception (including custom ones and PDO errors)
                if ($pdo->inTransaction()) {
                    $pdo->rollBack(); // Roll back the transaction on error
                }
                // Handle the error (e.g., duplicate registration)
                $status_message = "Registration Failed: " . $e->getMessage();
                $status_class = 'status-error';
            }
        }
    } else {
        $status_message = "Error: Invalid form submission data.";
        $status_class = 'status-error';
    }
} 
// --- END FORM SUBMISSION HANDLING ---


// --- 2. FETCH EVENT DETAILS (For display on the form) ---
// Use the event_id from POST if available, otherwise from GET
$display_event_id = isset($_POST['event_id']) ? $_POST['event_id'] : (isset($_GET['event_id']) ? $_GET['event_id'] : null);

if ($display_event_id) {
    try {
        // Use a prepared statement to prevent SQL injection when fetching the event details
        $stmt = $pdo->prepare("SELECT title, date, venue, organizer FROM events WHERE event_id = ?");
        $stmt->execute([$display_event_id]);
        $event = $stmt->fetch();

        if (!$event) {
            $status_message = "Error: Event not found or invalid ID.";
            $status_class = 'status-error';
        }

    } catch (PDOException $e) {
        $status_message = "Database Error: Could not load event details.";
        $status_class = 'status-error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

    <header>
        <div class="container">
            <h1>UoM Student Event Hub</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Events</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register_user.php">Sign Up</a></li>
                    <li><a href="admin/dashboard.php">Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="form-container">

            <?php if ($status_message): ?>
                <div class="status-message <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars($status_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($event): ?>
                <h2>Register for: <?php echo htmlspecialchars($event['title']); ?></h2>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['date'])); ?></p>
                <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                <hr style="margin: 20px 0;">

                <form id="registrationForm" method="POST" action="registration_form.php">
                    <!-- Hidden field to pass the event ID to the server -->
                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($display_event_id); ?>">

                    <div class="form-group">
                        <label for="student_name">Full Name (as per Student ID)</label>
                        <input type="text" id="student_name" name="student_name" required>
                    </div>

                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="tel" id="contact_number" name="contact_number" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Complete Registration</button>
                    </div>
                </form>
            <?php else: ?>
                <!-- If event ID is missing or event data could not be fetched -->
                <p class="status-message status-error">Please select a valid event from the <a href="index.php">event listing page</a>.</p>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Link the JavaScript file for client-side validation -->
    <script src="assets/script.js"></script>

</body>
</html>