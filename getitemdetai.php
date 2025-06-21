<?php
// this is getitemdetai.php
$id = $_GET['id'];
$conn = new mysqli('localhost', 'root', '', 'lostandfound');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM items WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

echo json_encode($item);
?>
