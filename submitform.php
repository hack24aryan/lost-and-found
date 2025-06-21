<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'lostandfound');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize and validate inputs
function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$user_id = $_SESSION['user_id'];
$title = clean_input($_POST['title'] ?? '');
$type = clean_input($_POST['type'] ?? '');
$description = clean_input($_POST['description'] ?? '');
$location = clean_input($_POST['location'] ?? '');
$date_reported = $_POST['date_reported'] ?? '';

$errors = [];

if (empty($title)) $errors[] = "Title is required.";
if (empty($type) || !in_array($type, ['Lost', 'Found'])) $errors[] = "Please select a valid type.";
if (empty($description)) $errors[] = "Description is required.";
if (empty($location)) $errors[] = "Location is required.";
if (empty($date_reported)) $errors[] = "Date reported is required.";

// Handle image upload (optional)
$imagePath = NULL;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $targetDir = 'uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = basename($_FILES['image']['name']);
    $targetFilePath = $targetDir . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileType, $allowedTypes)) {
        $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    } else {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imagePath = $targetFilePath;
        } else {
            $errors[] = "Failed to upload image.";
        }
    }
}

if (!empty($errors)) {
    // If errors, show the first error and stop
    echo "<h3>Error:</h3><p>" . htmlspecialchars($errors[0]) . "</p>";
    echo "<p><a href='submitform.html'>Go back to form</a></p>";
    exit();
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO items (user_id, title, type, description, location, date_reported, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssss", $user_id, $title, $type, $description, $location, $date_reported, $imagePath);

if ($stmt->execute()) {
    // Success: redirect to dashboard with a success message (optional)
    header("Location: dashboard.php?msg=report_success");
    exit();
} else {
    echo "<h3>Error:</h3><p>Database error: " . htmlspecialchars($stmt->error) . "</p>";
    echo "<p><a href='submitform.html'>Go back to form</a></p>";
    exit();
}

$stmt->close();
$conn->close();
?>
