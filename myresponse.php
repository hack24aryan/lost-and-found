<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'lostandfound');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT r.*, i.title AS item_title, i.image AS item_image, u.name AS responder_name, u.phone AS responder_phone 
        FROM responses r
        JOIN items i ON r.item_id = i.id
        JOIN users u ON r.user_id = u.id
        WHERE i.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>📬 My Item Responses | Lost & Found</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
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
    .response-box {
      background: white;
      padding: 1.5rem;
      border-radius: var(--radius);
      box-shadow: 0 4px 12px var(--shadow);
      margin-bottom: 2rem;
      display: flex;
      gap: 1.5rem;
      align-items: flex-start;
    }
    .response-image img {
      width: 200px;
      height: auto;
      border-radius: var(--radius);
      object-fit: cover;
      box-shadow: 0 2px 8px var(--shadow);
    }
    .response-content {
      flex: 1;
    }
    .response-content h4 {
      color: var(--primary-color);
      font-weight: 600;
    }
    .emoji {
      font-size: 1.3rem;
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
    <a href="myfeed.php">Community Feed</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main>
  <h2 class="mb-4">📬 Responses to Your Items</h2>

  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="response-box">
        <div class="response-image">
          <?php if (!empty($row['item_image'])): ?>
            <img src="<?php echo htmlspecialchars($row['item_image']); ?>" alt="Item Image">
          <?php else: ?>
            <img src="default-image.jpg" alt="No Image">
          <?php endif; ?>
        </div>
        <div class="response-content">
          <h4 class="emoji">📦 <?php echo htmlspecialchars($row['item_title']); ?></h4>
          <p><span class="emoji">🧍‍♂️</span> <strong>Responder:</strong> <?php echo htmlspecialchars($row['responder_name']); ?></p>
          <p><span class="emoji">📅</span> <strong>Found On:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($row['found_date']))); ?></p>
          <p><span class="emoji">📝</span> <strong>Details:</strong> <?php echo htmlspecialchars($row['additional_details']); ?></p>
          <p><span class="emoji">📞</span> <strong>Contact:</strong>
            <?php 
              if (!empty($row['responder_phone'])) {
                echo '<a href="tel:' . htmlspecialchars($row['responder_phone']) . '">' . htmlspecialchars($row['responder_phone']) . '</a>';
              } else {
                echo 'No contact provided';
              }
            ?>
          </p>
          <p><small class="text-muted">⏱ Responded on: <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['response_date']))); ?></small></p>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-muted">😔 You haven't received any responses yet.</p>
  <?php endif; ?>
</main>

<footer>
  <p>❤️ Trisha Tarwey © 2025 — Lost & Found Community 🧭💼
</p>
</footer>

</body>
</html>
