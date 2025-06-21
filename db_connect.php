<?php
// This is db_connect.php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "lost_and_found";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
