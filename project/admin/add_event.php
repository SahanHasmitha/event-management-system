<?php
require_once '../config/db_connect.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $location = $_POST['location'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO events (title, description, date, location) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $date, $location]);
    header("Location: dashboard.php?status=created");
    exit();
}
?>
<form method="post">
    <input name="title" placeholder="Title" required><br>
    <textarea name="description" placeholder="Description"></textarea><br>
    <input name="date" type="date" required><br>
    <input name="location" placeholder="Location" required><br>
    <button type="submit">Add Event</button>
</form>