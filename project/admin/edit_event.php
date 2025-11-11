<?php
<?php
require_once '../config/db_connect.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$event_id = $_GET['id'] ?? '';
if (!$event_id) {
    echo "No event selected.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $location = $_POST['location'] ?? '';

    $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, date=?, location=? WHERE event_id=?");
    $stmt->execute([$title, $description, $date, $location, $event_id]);
    header("Location: dashboard.php?status=updated");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id=?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form method="post">
    <input name="title" value="<?= htmlspecialchars($event['title']) ?>" required><br>
    <textarea name="description"><?= htmlspecialchars($event['description']) ?></textarea><br>
    <input name="date" type="date" value="<?= htmlspecialchars($event['date']) ?>" required><br>
    <input name="location" value="<?= htmlspecialchars($event['location']) ?>" required><br>
    <button type="submit">Update Event</button>
</form>