<?php
require_once 'config/db_connect.php';

// Redirect logged-in users away from the login page
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$status_message = '';
$status_class = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $id_or_email = trim(filter_input(INPUT_POST, 'id_or_email', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $password = $_POST['password']; // Password is handled raw, as hashing functions protect it later

    // Server-side Validation
    if (empty($id_or_email) || empty($password)) {
        $status_message = "Please enter both your ID/Email and password.";
        $status_class = 'status-error';
    } else {
        try {
            // Check if the input is likely an email or a student ID
            $is_email = filter_var($id_or_email, FILTER_VALIDATE_EMAIL);
            
            // Use prepared statement to find the user by student_id OR email
            if ($is_email) {
                $stmt = $pdo->prepare("SELECT user_id, name, password_hash, role FROM users WHERE email = ?");
            } else {
                $stmt = $pdo->prepare("SELECT user_id, name, password_hash, role FROM users WHERE student_id = ?");
            }
            
            $stmt->execute([$id_or_email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful: Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to the home page or admin dashboard if admin
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                // Invalid credentials
                $status_message = "Invalid Student ID/Email or password.";
                $status_class = 'status-error';
            }
        } catch (PDOException $e) {
            $status_message = "A database error occurred during login.";
            $status_class = 'status-error';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <!-- CSS link: Ensure this path is correct relative to the file's location -->
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
                    <li><a href="login.php" class="active">Login</a></li>
                    <li><a href="register_user.php">Sign Up</a></li>
                    <li><a href="admin/dashboard.php">Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- The form-container class is crucial for CSS styling -->
        <div class="form-container">
            <h2>Log In to Your Account</h2>

            <?php if ($status_message): ?>
                <div class="status-message <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars($status_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="id_or_email">Student ID or Email</label>
                    <input type="text" id="id_or_email" name="id_or_email" required 
                           value="<?php echo htmlspecialchars($_POST['id_or_email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Login</button>
                </div>
                <p style="text-align: center; margin-top: 15px;">
                    Don't have an account? <a href="register_user.php" style="color: var(--primary-color);">Sign Up here</a>
                </p>
            </form>
        </div>
    </main>

</body>
</html>