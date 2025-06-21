<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'lostandfound');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$item_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if item belongs to current user
$check = $conn->prepare("SELECT id FROM items WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $item_id, $user_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 1) {
    // Update status to 'resolved'
    $update = $conn->prepare("UPDATE items SET status = 'resolved' WHERE id = ?");
    $update->bind_param("i", $item_id);
    $update->execute();
}

$conn->close();
header("Location: dashboard.php");
exit();
?>
