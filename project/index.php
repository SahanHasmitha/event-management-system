<?php
// Include the database connection file (which now handles session_start())
require_once 'config/db_connect.php';

// Initialize search/filter variables
$search_term = filter_input(INPUT_GET, 'search_term', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$sort_by = filter_input(INPUT_GET, 'sort_by', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'date_asc';
$events = [];

// --- PHP Logic for Search and Filter ---
$sql_query = "SELECT event_id, title, date, time, venue, description, organizer FROM events";
$params = [];
$where_clauses = [];

// 1. Handle Search Term
if (!empty($search_term)) {
    // Search across Title, Venue, and Organizer
    $where_clauses[] = "(title LIKE ? OR venue LIKE ? OR organizer LIKE ? OR description LIKE ?)";
    $search_param = '%' . $search_term . '%';
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

// Construct WHERE clause
if (!empty($where_clauses)) {
    $sql_query .= " WHERE " . implode(' AND ', $where_clauses);
}

// 2. Handle Sorting
$order_clause = '';
switch ($sort_by) {
    case 'title_asc':
        $order_clause = " ORDER BY title ASC";
        break;
    case 'date_desc':
        $order_clause = " ORDER BY date DESC, time DESC";
        break;
    case 'date_asc':
    default:
        $order_clause = " ORDER BY date ASC, time ASC";
        $sort_by = 'date_asc';
        break;
}

$sql_query .= $order_clause;

// 3. Execute the Query
try {
    $stmt = $pdo->prepare($sql_query);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Error fetching events: " . $e->getMessage();
    error_log($error_message); // Log the error securely
    // We display a generic message to the user
    $display_error = "<p class='status-message status-error'>A database error occurred while trying to load events.</p>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Event Management - Event Listing</title>
    <link rel="stylesheet" href="assets/style.css">
    <!-- Font Awesome for Icons (Optional, but adds visual appeal) -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

    <header>
        <div class="container">
            <h1>UoM Student Event Hub</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Events</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="#"><i class="fas fa-user-circle"></i> Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a></li>
                        <?php if (is_admin()): ?>
                             <!-- Admin link only visible to admin users -->
                            <li><a href="admin/dashboard.php" style="background-color: #f39c12; border-radius: 5px;">Admin Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register_user.php">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <h2>Available Events</h2>

        <?php if (isset($display_error)) echo $display_error; ?>

        <!-- Search and Filter Form -->
        <div class="search-filter-container">
            <form method="GET" action="index.php">
                <div class="search-bar">
                    <input type="text" name="search_term" placeholder="Search by title, venue, or organizer..." 
                           value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="sort_by">Sort By:</label>
                    <select id="sort_by" name="sort_by">
                        <option value="date_asc" <?php if ($sort_by == 'date_asc') echo 'selected'; ?>>Date (Upcoming)</option>
                        <option value="date_desc" <?php if ($sort_by == 'date_desc') echo 'selected'; ?>>Date (Recent)</option>
                        <option value="title_asc" <?php if ($sort_by == 'title_asc') echo 'selected'; ?>>Title (A-Z)</option>
                    </select>
                </div>
                
                <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Search / Filter</button>
            </form>
        </div>
        <!-- End Search and Filter Form -->

        <?php if (empty($events)): ?>
            <p style="text-align: center; margin-top: 50px; font-size: 1.1em;">
                No events match your current filter or search term '<?php echo htmlspecialchars($search_term); ?>'.
            </p>
        <?php else: ?>
            <div class="event-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <h2><?php echo htmlspecialchars($event['title']); ?></h2>

                        <div class="event-info">
                            <p><i class="fas fa-calendar-alt"></i> Date: <?php echo date('F j, Y', strtotime($event['date'])); ?></p>
                            <p><i class="fas fa-clock"></i> Time: <?php echo date('h:i A', strtotime($event['time'])); ?></p>
                            <p><i class="fas fa-map-marker-alt"></i> Venue: <?php echo htmlspecialchars($event['venue']); ?></p>
                            <p><i class="fas fa-user-tie"></i> Organizer: <?php echo htmlspecialchars($event['organizer']); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                        
                        <!-- Link to the registration form, passing the event ID -->
                        <a href="registration_form.php?event_id=<?php echo htmlspecialchars($event['event_id']); ?>" class="register-btn">
                            Register Now
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- You will link your main JS file here later for client-side functionality -->
    <!-- <script src="assets/script.js"></script> -->

</body>
</html>