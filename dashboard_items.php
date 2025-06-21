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
$conn->set_charset("utf8mb4");

$user_id = $_SESSION['user_id'];

// Fetch user name
$user_query = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_name = $user ? htmlspecialchars($user['name']) : "User";

// Count total items
$count_query = $conn->prepare("SELECT COUNT(*) AS total FROM items WHERE user_id = ?");
$count_query->bind_param("i", $user_id);
$count_query->execute();
$count_result = $count_query->get_result();
$total_items = $count_result->fetch_assoc()['total'] ?? 0;

// Get 5 recent items
$recent_items = [];
$item_query = $conn->prepare("SELECT id, title, status, date_reported, image FROM items WHERE user_id = ? ORDER BY date_reported DESC LIMIT 5");
$item_query->bind_param("i", $user_id);
$item_query->execute();
$item_result = $item_query->get_result();
while ($row = $item_result->fetch_assoc()) {
    $recent_items[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>📦 Your Reported Items | Lost & Found</title>
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
    h2 {
      margin-bottom: 1rem;
      font-weight: 600;
      color: var(--primary-color);
    }
    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill,minmax(280px,1fr));
      gap: 1.5rem;
    }
    .item-card {
      background: white;
      border-radius: var(--radius);
      box-shadow: 0 4px 12px var(--shadow);
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .item-image {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-bottom: 1px solid #eee;
    }
    .item-content {
      padding: 1rem;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .item-title {
      font-weight: 600;
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
      color: var(--secondary-color);
    }
    .item-status {
      font-weight: 500;
      color: var(--primary-color);
      margin-bottom: 0.5rem;
    }
    .item-date {
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 1rem;
    }
    .btn-edit {
      align-self: flex-start;
      background-color: var(--primary-color);
      color: var(--text-light);
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    .btn-edit:hover {
      background-color: #003d99;
      color: #fff;
    }
    .empty-message {
      text-align: center;
      font-style: italic;
      color: #777;
      margin-top: 3rem;
    }
    .item-action {
  margin: 1rem;
  display: flex;
  justify-content: center;
}

.btn-mark {
  background-color: var(--secondary-color);
  color: #fff;
  font-weight: 600;
  padding: 0.5rem 1.2rem;
  border-radius: 8px;
  text-decoration: none;
  border: none;
  transition: background-color 0.3s ease, transform 0.2s ease;
  box-shadow: 0 4px 10px var(--shadow);
}

.btn-mark:hover {
  background-color: #e05549;
  transform: scale(1.03);
}

.resolved-badge {
  background-color: #28a745;
  color: #fff;
  font-weight: 600;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.85rem;
  display: inline-block;
  box-shadow: 0 3px 8px var(--shadow);
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
  <h2>📦 Your Reported Items</h2>
  <p>Hi <strong><?php echo $user_name; ?></strong>, you have <strong><?php echo $total_items; ?></strong> item<?php echo $total_items !== 1 ? 's' : ''; ?> reported.</p>

  <?php if (count($recent_items) > 0): ?>
    <div class="items-grid">
      <?php foreach ($recent_items as $item): ?>
        <?php
          $img = $item['image'];
          if (!empty($img) && substr($img, 0, 8) !== 'uploads/') {
              $img = 'uploads/' . $img;
          }
        ?>
        <div class="item-card">
           <div class="item-action">
  <?php if (strtolower($item['status']) !== 'resolved'): ?>
    <a href="close_item.php?id=<?php echo $item['id']; ?>" class="btn-mark">✅ Mark as Found</a>
  <?php else: ?>
    <span class="resolved-badge">✅ Resolved</span>
  <?php endif; ?>
</div>



          <img class="item-image" src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
          <div class="item-content">
            <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
            <div class="item-status">Status: <?php echo htmlspecialchars(ucfirst($item['status'])); ?></div>
            <div class="item-date">Reported on: <?php echo date('d M Y', strtotime($item['date_reported'])); ?></div>
            <a href="edititem.php?id=<?php echo $item['id']; ?>" class="btn-edit">✏️ Edit</a>
          </div>
        </div>
      <?php endforeach; ?>
      
    </div>
  <?php else: ?>
    <p class="empty-message">You have not reported any items yet.</p>
  <?php endif; ?>
  
</main>

</body>

</html>

