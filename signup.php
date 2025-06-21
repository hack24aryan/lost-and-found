<?php
// This is signup.php
$conn = new mysqli('localhost', 'root', '', 'lostandfound');
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$fname = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Check if email already exists
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    // Redirect with error query to trigger modal
    header("Location: signup.html?error=email_exists");
    exit();
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fname, $email, $phone, $password);

    if ($stmt->execute()) {
        // Redirect to login or success page
         include 'signup_success_modal.html'; // show modal
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$checkStmt->close();
$conn->close();
?>
