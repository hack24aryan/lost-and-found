<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");  // check your actual filename (usually lowercase)
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'lostandfound');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");  // ensure proper encoding

$user_id = $_SESSION['user_id'];

// Fetch user info safely
$user_query = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_name = $user ? htmlspecialchars($user['name']) : "User";

// Count total items posted by user
$count_query = $conn->prepare("SELECT COUNT(*) AS total FROM items WHERE user_id = ?");
$count_query->bind_param("i", $user_id);
$count_query->execute();
$count_result = $count_query->get_result();
$count_data = $count_result->fetch_assoc();
$total_items = $count_data ? (int)$count_data['total'] : 0;

// Fetch recent 5 items by user
$recent_items = [];
$item_query = $conn->prepare("SELECT id, title, status, date_reported FROM items WHERE user_id = ? ORDER BY date_reported DESC LIMIT 5");
$item_query->bind_param("i", $user_id);
$item_query->execute();
$item_result = $item_query->get_result();
while ($row = $item_result->fetch_assoc()) {
    $recent_items[] = $row;
}

// Fetch recent 5 notifications (responses) on user's items with contact info and item image
$notif_query = $conn->prepare("
    SELECT 
        r.id, 
        i.title AS item_title, 
        i.image AS item_image, 
        r.found_date, 
        r.additional_details, 
        r.response_date, 
        u.name AS responder_name, 
        u.email AS responder_email, 
        u.phone AS responder_phone
    FROM responses r
    JOIN items i ON r.item_id = i.id
    JOIN users u ON r.user_id = u.id
    WHERE i.user_id = ?
    ORDER BY r.response_date DESC
    LIMIT 5
");

$notif_query->bind_param("i", $user_id);
$notif_query->execute();
$notif_result = $notif_query->get_result();

$notifications = [];
while ($row = $notif_result->fetch_assoc()) {
    $notifications[] = $row;
}


// Close statements and connection
$user_query->close();
$count_query->close();
$item_query->close();
$notif_query->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - Lost & Found</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    /* your CSS remains the same */
    :root {
      --primary-color: #0052cc;
      --secondary-color: #ff6f61;
      --bg-light: #f4f7fb;
      --text-dark: #333;
      --text-light: #fff;
      --shadow: rgba(0, 0, 0, 0.1);
      --radius: 12px;
    }
    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-light);
      color: var(--text-dark);
      margin: 0;
      padding: 0;
    }
    header {
      background-color: var(--primary-color);
      padding: 1rem 2rem;
      color: var(--text-light);
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 8px var(--shadow);
    }
    header h1 {
      font-size: 1.8rem;
      font-weight: 600;
    }
    nav a {
      color: var(--text-light);
      margin-left: 1.5rem;
      text-decoration: none;
      font-weight: 500;
    }
    nav a:hover {
      text-decoration: underline;
    }
    main {
      max-width: 1100px;
      margin: 2rem auto;
      padding: 0 1rem;
    }
    .welcome-box {
      background: white;
      padding: 2rem;
      border-radius: var(--radius);
      box-shadow: 0 4px 12px var(--shadow);
      margin-bottom: 2rem;
      text-align: center;
    }
    .welcome-box h2 {
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
    }
    .action-card {
      flex: 1 1 280px;
      background: #fff;
      padding: 1.5rem;
      border-radius: var(--radius);
      box-shadow: 0 4px 10px var(--shadow);
      transition: transform 0.3s;
      text-align: center;
    }
    .action-card:hover {
      transform: translateY(-5px);
    }
    .action-card h4 {
      font-size: 1.2rem;
      color: var(--primary-color);
      margin-top: 1rem;
    }
    .action-card p {
      color: #555;
    }
    .action-card .emoji {
      font-size: 2rem;
    }
    .section {
      background: white;
      padding: 1.5rem 2rem;
      border-radius: var(--radius);
      box-shadow: 0 4px 12px var(--shadow);
      margin-bottom: 2rem;
    }
    .section h3 {
      color: var(--primary-color);
      margin-bottom: 1rem;
      font-weight: 600;
    }
    .list-group-item {
      border: 1px solid #ddd;
      border-radius: 6px;
      margin-bottom: 0.5rem;
    }
    footer {
      text-align: center;
      padding: 1rem;
      font-size: 0.9rem;
      color: #777;
    }
  </style>
</head>
<body>
  <header>
    <h1>🎒 Lost & Found</h1>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="submitform.html">Report Lost</a>
      <a href="myfeed.php">Community Feed</a>
      <a href="myresponse.php">Responses</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main>
    <div class="welcome-box">
      <h2>👋 Welcome Back, <?php echo $user_name; ?>!</h2>
      <p>Your recent activity and updates will appear below.</p>
    </div>

    <div class="actions">
      <div class="action-card">
        <div class="emoji">➕</div>
        <h4>Report Lost/Found</h4>
        <p>Post an item you've lost or found to help others identify it.</p>
        <a href="submitform.html" class="btn btn-primary mt-2">Report Now</a>
      </div>

      <div class="action-card">
        <div class="emoji">📬</div>
        <h4>View Your Items</h4>
        <p>Track the items you've listed and their responses. You have <strong><?php echo $total_items; ?></strong> items.</p>
        <a href="dashboard_items.php" class="btn btn-secondary mt-2">View Items</a>
      </div>

      <div class="action-card">
        <div class="emoji">🔔</div>
        <h4>Notifications</h4>
        <p>Stay updated with relevant reports and replies.</p>
        <a href="myresponse.php" class="btn btn-warning mt-2">Check Alerts</a>
      </div>
    </div>

<!-- Recent Items Section -->
<div class="section">
  <h3>📝 Your Recent Reports</h3>
  <?php if (count($recent_items) > 0): ?>
    <ul class="list-group">
      <?php foreach ($recent_items as $item): ?>
        <li class="list-group-item">
          <strong><?php echo htmlspecialchars($item['title']); ?></strong> — 
          Status: <em><?php echo htmlspecialchars($item['status']); ?></em> — 
          Reported on: <?php echo htmlspecialchars(date('d M Y', strtotime($item['date_reported']))); ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>You have not reported any items yet.</p>
  <?php endif; ?>
</div>


<div class="section">
  <h3>🔔 Latest Responses to Your Items</h3>
  <?php if (count($notifications) > 0): ?>
    <ul class="list-group">
      <?php foreach ($notifications as $notif): ?>
        <li class="list-group-item d-flex flex-column flex-md-row align-items-start gap-3 p-3">
          <?php if (!empty($notif['item_image'])): ?>
            <img src="<?php echo htmlspecialchars($notif['item_image']); ?>" alt="Item Image" style="width: 120px; height: auto; border-radius: 10px; object-fit: cover; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
          <?php else: ?>
            <img src="default-image.jpg" alt="No Image" style="width: 120px; height: auto; border-radius: 10px; object-fit: cover; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
          <?php endif; ?>
          
          <div class="flex-grow-1">
            <p class="mb-1">
              <strong><?php echo htmlspecialchars($notif['responder_name']); ?></strong> found your item: 
              <em><?php echo htmlspecialchars($notif['item_title']); ?></em> on 
              <?php echo htmlspecialchars(date('d M Y', strtotime($notif['found_date']))); ?>.
            </p>
            <p class="mb-1">
              <strong>📝 Additional Details:</strong> <?php echo htmlspecialchars($notif['additional_details']); ?>
            </p>
            <p class="mb-1">
              <strong>📞 Contact Number:</strong> 
              <?php 
                if (!empty($notif['responder_phone'])) {
                  echo '<a href="tel:' . htmlspecialchars($notif['responder_phone']) . '">' . htmlspecialchars($notif['responder_phone']) . '</a>';
                } else {
                  echo 'No contact number available';
                }
              ?>
            </p>
            <small class="text-muted">⏱ Responded on: <?php echo htmlspecialchars(date('d M Y H:i', strtotime($notif['response_date']))); ?></small>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No new responses yet.</p>
  <?php endif; ?>
</div>
<footer>
     ❤️ Trisha Tarwey © 2025 — Lost & Found Community 🧭💼
  </footer>
