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

// Validate form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_id = $_POST['item_id'] ?? null;
    $user_id = $_SESSION['user_id'];
    $response_date = trim($_POST['response_date'] ?? '');
    $details = trim($_POST['details'] ?? '');

    // Basic validation
    if (!$item_id || !$response_date) {
        $message = "❗ All fields are required.";
        $success = false;
    } else {
        $stmt = $conn->prepare("INSERT INTO responses (item_id, user_id, found_date, additional_details) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $item_id, $user_id, $response_date, $details);

        if ($stmt->execute()) {
            $message = "✅ Your response has been submitted successfully!";
            $success = true;
        } else {
            $message = "❌ Error: " . $stmt->error;
            $success = false;
        }

        $stmt->close();
    }

    $conn->close();
} else {
    $message = "❌ Invalid request method.";
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Response Submission</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f1f3f4;
      font-family: 'Roboto', sans-serif;
    }

    .container {
      max-width: 600px;
      margin-top: 100px;
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .message {
      font-size: 1.3rem;
      color: <?php echo $success ? '#34a853' : '#d93025'; ?>;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .btn {
      border-radius: 24px;
      padding: 10px 20px;
      font-weight: 500;
    }

    .btn-primary {
      background-color: #1a73e8;
      border-color: #1a73e8;
    }
  </style>
</head>
<body>

  <div class="container">
    <h3 class="message">
      <?= $success ? "🎉 " : "⚠️ "; ?>
      <?= htmlspecialchars($message) ?>
    </h3>

    <a href="dashboard.php" class="btn btn-primary mt-3">← Back to Dashboard</a>
  </div>

</body>
</html>
