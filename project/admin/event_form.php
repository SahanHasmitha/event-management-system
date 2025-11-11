<?php
require_once '../config/db_connect.php';

// SECURITY CHECK: Must be an admin to access this page
if (!is_admin()) {
    header("Location: ../login.php");
    exit();
}

global $pdo; // <<< FIX: Ensure $pdo is accessible

$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$is_editing = $event_id !== null && $event_id !== false;

$title = $date = $time = $venue = $description = $organizer = '';
$max_participants = 0;
$status_message = '';
$status_class = '';

// --- Handle Form Submission (INSERT or UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id_post = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $venue = trim(filter_input(INPUT_POST, 'venue', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $organizer = trim(filter_input(INPUT_POST, 'organizer', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $max_participants = filter_input(INPUT_POST, 'max_participants', FILTER_VALIDATE_INT) ?? 0;

    if (empty($title) || empty($date) || empty($venue) || empty($organizer)) {
        $status_message = "Title, Date, Venue, and Organizer are mandatory fields.";
        $status_class = 'status-error';
    } else {
        try {
            if ($event_id_post) { // UPDATE existing event
                $sql = "UPDATE events SET title=?, date=?, time=?, venue=?, description=?, organizer=?, max_participants=? WHERE event_id=? AND created_by_user_id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $date, $time, $venue, $description, $organizer, $max_participants, $event_id_post, $_SESSION['user_id']]);
                
                $status_message = "Event updated successfully!";
                $status_class = 'status-success';
                // Update the local $event_id for correct page rendering
                $event_id = $event_id_post;

            } else { // INSERT new event
                $sql = "INSERT INTO events (title, date, time, venue, description, organizer, max_participants, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $date, $time, $venue, $description, $organizer, $max_participants, $_SESSION['user_id']]);

                $status_message = "Event created successfully!";
                $status_class = 'status-success';
                // Redirect to the dashboard after creation
                header("Location: dashboard.php?status=success");
                exit();
            }
        } catch (PDOException $e) {
            $status_message = "Database Error: Could not save event. " . $e->getMessage();
            $status_class = 'status-error';
            error_log("Event CRUD Error: " . $e->getMessage());
        }
    }
}

// --- Handle Initial Load for EDITING ---
if ($is_editing && !$_POST) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();

        if ($event) {
            $title = $event['title'];
            $date = $event['date'];
            $time = $event['time'];
            $venue = $event['venue'];
            $description = $event['description'];
            $organizer = $event['organizer'];
            $max_participants = $event['max_participants'];
        } else {
            $status_message = "Event not found.";
            $status_class = 'status-error';
            $is_editing = false;
        }
    } catch (PDOException $e) {
        $status_message = "Database error while loading event details.";
        $status_class = 'status-error';
        $is_editing = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_editing ? 'Edit Event' : 'Create New Event'; ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

    <header>
        <div class="container">
            <h1><?php echo $is_editing ? 'Edit Event' : 'Create Event'; ?></h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Events</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="form-container">
            <h2><?php echo $is_editing ? 'Editing: ' . htmlspecialchars($title) : 'Create New Event'; ?></h2>

            <?php if ($status_message): ?>
                <div class="status-message <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars($status_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="event_form.php">
                <input type="hidden" name="event_id" value="<?php echo $is_editing ? htmlspecialchars($event_id) : ''; ?>">

                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($title); ?>">
                </div>

                <div class="form-group">
                    <label for="organizer">Organizer (Club/Department)</label>
                    <input type="text" id="organizer" name="organizer" required value="<?php echo htmlspecialchars($organizer); ?>">
                </div>

                <div class="form-group">
                    <label for="venue">Venue</label>
                    <input type="text" id="venue" name="venue" required value="<?php echo htmlspecialchars($venue); ?>">
                </div>
                
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" required value="<?php echo htmlspecialchars($date); ?>">
                </div>

                <div class="form-group">
                    <label for="time">Time</label>
                    <input type="time" id="time" name="time" value="<?php echo htmlspecialchars($time); ?>">
                </div>

                <div class="form-group">
                    <label for="max_participants">Max Participants (0 for unlimited)</label>
                    <input type="number" id="max_participants" name="max_participants" min="0" value="<?php echo htmlspecialchars($max_participants); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn"><?php echo $is_editing ? 'Save Changes' : 'Create Event'; ?></button>
                </div>
                <p style="text-align: center; margin-top: 15px;">
                    <a href="dashboard.php">Cancel and go back to Dashboard</a>
                </p>
            </form>
        </div>
    </main>
</body>
</html>