<?php
require_once 'config/db_connect.php';

$status_message = '';
$status_class = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $student_id = trim(filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $contact = trim(filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Server-side Validation
    if (empty($student_id) || empty($name) || empty($email) || empty($contact) || empty($password) || empty($confirm_password)) {
        $status_message = "All fields are mandatory.";
        $status_class = 'status-error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $status_message = "Invalid email format.";
        $status_class = 'status-error';
    } elseif ($password !== $confirm_password) {
        $status_message = "Passwords do not match.";
        $status_class = 'status-error';
    } elseif (strlen($password) < 8) {
        $status_message = "Password must be at least 8 characters long.";
        $status_class = 'status-error';
    } else {
        try {
            // Check for existing user (ID or Email)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE student_id = ? OR email = ?");
            $stmt->execute([$student_id, $email]);
            if ($stmt->fetchColumn() > 0) {
                $status_message = "User with this Student ID or Email already exists.";
                $status_class = 'status-error';
            } else {
                // Hash the password securely
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user using PREPARED STATEMENT
                $stmt_insert = $pdo->prepare("INSERT INTO users (student_id, name, email, contact_number, password_hash, role) VALUES (?, ?, ?, ?, ?, 'student')");
                
                if ($stmt_insert->execute([$student_id, $name, $email, $contact, $password_hash])) {
                    $status_message = "Registration successful! You can now log in.";
                    $status_class = 'status-success';
                    // Clear POST data to prevent re-submission
                    $_POST = array();
                } else {
                    $status_message = "Registration failed due to a server error.";
                    $status_class = 'status-error';
                }
            }
        } catch (PDOException $e) {
            $status_message = "Database Error: Could not complete registration.";
            $status_class = 'status-error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Sign Up</title>
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
            <h2>Student Account Sign Up</h2>

            <?php if ($status_message): ?>
                <div class="status-message <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars($status_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register_user.php">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" required value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="tel" id="contact_number" name="contact_number" required value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password (Min 8 characters)</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Register Account</button>
                </div>
                <p style="text-align: center; margin-top: 15px;">
                    Already have an account? <a href="login.php" style="color: var(--primary-color);">Log In here</a>
                </p>
            </form>
        </div>
    </main>
</body>
</html>