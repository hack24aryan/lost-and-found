<?php
// This is item_details.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'lostandfound');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$item_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch item
$item_query = $conn->prepare("SELECT * FROM items WHERE id = ?");
$item_query->bind_param("i", $item_id);
$item_query->execute();
$item_result = $item_query->get_result();
$item = $item_result->fetch_assoc();

// Fetch responses if posted by current user
$responses = [];
if ($item['user_id'] == $user_id) {
    $response_query = $conn->prepare("SELECT r.found_date, r.contact, r.response_date, u.name AS responder_name
                                      FROM responses r
                                      JOIN users u ON r.user_id = u.id
                                      WHERE r.item_id = ?
                                      ORDER BY r.response_date DESC");
    $response_query->bind_param("i", $item_id);
    $response_query->execute();
    $response_result = $response_query->get_result();
    while ($row = $response_result->fetch_assoc()) {
        $responses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Item Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f1f3f4;
      font-family: 'Roboto', sans-serif;
    }

    .navbar {
      background-color: #1a73e8;
    }

    .navbar-brand {
      font-weight: 500;
      font-size: 1.25rem;
    }

    .card {
      border: none;
      border-radius: 8px;
    }

    .card-img-top {
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;
      max-height: 400px;
      object-fit: contain;
      background-color: #f8f9fa;
    }

    .card-body h4 {
      color: #202124;
      font-weight: 500;
    }

    .card-body p {
      color: #5f6368;
      margin-bottom: 0.5rem;
    }

    .card-header {
      background-color: #e8f0fe;
      border-bottom: 1px solid #d2e3fc;
    }

    .list-group-item {
      background-color: #ffffff;
      border: 1px solid #e0e0e0;
    }

    footer {
      background-color: #1a73e8;
      color: white;
    }

    @media (max-width: 768px) {
      .card-img-top {
        max-height: 250px;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php">Lost & Found</a>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="card shadow-sm mb-4">
      <img src="<?php echo htmlspecialchars($item['image']); ?>" class="card-img-top" alt="Item Image">
      <div class="card-body">
        <h4 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h4>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($item['status']); ?></p>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($item['type']); ?></p>
        <p><strong>Date Reported:</strong> <?php echo htmlspecialchars($item['date_reported']); ?></p>
      </div>
    </div>

    <?php if ($item['user_id'] == $user_id): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header">
          <h5 class="mb-0 text-primary">Responses Received</h5>
        </div>
        <div class="card-body">
          <?php if (count($responses) > 0): ?>
            <ul class="list-group">
              <?php foreach ($responses as $res): ?>
                <li class="list-group-item">
                  <strong><?php echo htmlspecialchars($res['responder_name']); ?></strong> found it on <?php echo htmlspecialchars($res['found_date']); ?><br>
                  <strong>Contact:</strong> <?php echo htmlspecialchars($res['contact']); ?><br>
                  <strong>Responded at:</strong> <?php echo htmlspecialchars($res['response_date']); ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">No responses yet.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <footer class="text-center py-3 mt-4">
    <p class="mb-0">❤️ Trisha Tarwey © 2025 — Lost & Found Community 🧭💼
</p>
  </footer>
</body>
</html>
