<?php
require_once '../config/db_connect.php';

// SECURITY CHECK: Ensure only logged-in Admins can view this page
if (!is_admin()) {
    // If not admin, redirect to login page
    header("Location: ../login.php");
    exit();
}

$status_message = '';
$status_class = '';

// Check for success/error messages passed via GET parameters after an action (e.g., delete)
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $status_message = "Action successful!";
        $status_class = 'status-success';
    } elseif ($_GET['status'] === 'deleted') {
        $status_message = "Event deleted successfully.";
        $status_class = 'status-success';
    } elseif ($_GET['status'] === 'error') {
        $status_message = "An error occurred during the action.";
        $status_class = 'status-error';
    }
}

// --- ANALYTICS AND DATA FETCHING ---

$analytics = [
    'total_events' => 0,
    'total_registrations' => 0,
    'total_users' => 0,
    'upcoming_events' => 0,
];

// CRITICAL FIX: The global $pdo object is already available here because of db_connect.php

try {
    // Total Events
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    $analytics['total_events'] = $stmt->fetchColumn();

    // Total Registrations
    $stmt = $pdo->query("SELECT COUNT(*) FROM registrations");
    $analytics['total_registrations'] = $stmt->fetchColumn();

    // Total Users (Excluding Admin, assuming admin count is minimal)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $analytics['total_users'] = $stmt->fetchColumn();

    // Upcoming Events (Events dated today or in the future)
    $stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE date >= CURDATE()");
    $analytics['upcoming_events'] = $stmt->fetchColumn();

    // Fetch all events for the table listing
    $stmt = $pdo->prepare("SELECT event_id, title, date, venue, organizer FROM events ORDER BY date DESC");
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $status_message = "Database Error: Could not load data.";
    $status_class = 'status-error';
    $events = [];
}

// <<< REMOVED REDUNDANT get_participants() FUNCTION DEFINITION HERE >>>
// The function is now correctly defined only in config/db_connect.php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Pathing Check: ../assets/style.css is correct for files inside the 'admin' folder -->
    <link rel="stylesheet" href="../assets/style.css">
    <!-- Font Awesome Link: Ensure this external link is not being blocked -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        /* Specific Admin Styles */
        /* Note: Most styles are imported from style.css. This section contains dashboard specifics */
        .admin-dashboard {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .action-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .action-bar a {
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
        }
        .create-btn {
            background-color: var(--success-color);
            color: var(--text-light);
        }
        .create-btn:hover { background-color: #27ae60; }

        .events-table, .participants-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 0.9em;
        }
        .events-table th, .events-table td, .participants-table th, .participants-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .events-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        .events-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .action-links a {
            margin-right: 10px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .action-links .delete-link {
            color: var(--error-color);
        }
        .participant-section {
            margin-top: 20px;
            padding: 15px;
            background: #f0f8ff;
            border-radius: 8px;
            border-left: 5px solid var(--secondary-color); /* Changed color variable for consistency */
        }
        
        /* Analytics Dashboard Styles */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: background 0.3s;
        }
        .stat-card h4 {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        .stat-card .stat-value {
            font-size: 2.2em;
            font-weight: 700;
            color: var(--primary-color);
        }
        .stat-card i {
            font-size: 1.2em;
            margin-right: 5px;
        }
    </style>
    <script>
        // Client-side confirmation for deletion
        function confirmDelete(title) {
             const confirmMsg = "Type 'DELETE' to confirm you want to permanently delete the event: " + title;
             const input = prompt(confirmMsg);
             return input === 'DELETE';
        }
    </script>
</head>
<body>

    <header>
        <div class="container">
            <h1>Admin Dashboard</h1>
            <nav>
                <ul>
                    <li><a href="../index.php">View Events</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="admin-dashboard">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Admin)</h2>

            <?php if ($status_message): ?>
                <div class="status-message <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars($status_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Analytics Dashboard Section -->
            <h3>System Overview Analytics</h3>
            <div class="analytics-grid">
                <div class="stat-card">
                    <h4><i class="fas fa-calendar-alt"></i> Total Events</h4>
                    <div class="stat-value"><?php echo $analytics['total_events']; ?></div>
                </div>
                <div class="stat-card">
                    <h4><i class="fas fa-clock"></i> Upcoming Events</h4>
                    <div class="stat-value"><?php echo $analytics['upcoming_events']; ?></div>
                </div>
                <div class="stat-card">
                    <h4><i class="fas fa-users"></i> Total Registrations</h4>
                    <div class="stat-value"><?php echo $analytics['total_registrations']; ?></div>
                </div>
                <div class="stat-card">
                    <h4><i class="fas fa-user-graduate"></i> Registered Students</h4>
                    <div class="stat-value"><?php echo $analytics['total_users']; ?></div>
                </div>
            </div>
            <hr style="margin-bottom: 20px;">
            <!-- End Analytics Dashboard Section -->

            <div class="action-bar">
                <h3>Event Management</h3>
                <a href="event_form.php" class="create-btn"><i class="fas fa-plus-circle"></i> Create New Event</a>
            </div>

            <table class="events-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Organizer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr><td colspan="6" style="text-align: center;">No events created yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['event_id']); ?></td>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($event['date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                <td><?php echo htmlspecialchars($event['organizer']); ?></td>
                                <td class="action-links">
                                    <a href="event_form.php?id=<?php echo $event['event_id']; ?>">Edit</a>
                                    <a href="#participants-<?php echo $event['event_id']; ?>" onclick="document.getElementById('p-<?php echo $event['event_id']; ?>').style.display='block';">Participants</a>
                                    <!-- Delete action link with confirmation -->
                                    <a href="delete_event.php?id=<?php echo $event['event_id']; ?>" 
                                       onclick="return confirmDelete('<?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?>')"
                                       class="delete-link">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Participants Details Section -->
            <?php foreach ($events as $event): ?>
                <?php 
                // CRITICAL FIX: The function is now globally available via db_connect.php
                $participants = get_participants($event['event_id']); 
                ?>
                <div id="p-<?php echo $event['event_id']; ?>" class="participant-section" style="display: none;">
                    <h4>Participants for: <?php echo htmlspecialchars($event['title']); ?> (<?php echo count($participants); ?> registered)</h4>
                    <?php if (empty($participants)): ?>
                        <p>No students have registered for this event yet.</p>
                    <?php else: ?>
                        <table class="participants-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Student ID</th>
                                    <th>Email</th>
                                    <th>Registered At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td><?php echo htmlspecialchars($p['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($p['email']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($p['timestamp'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

</body>
</html>