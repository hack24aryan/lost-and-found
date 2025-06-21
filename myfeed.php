<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: Login.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'lostandfound');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$stmt = $conn->prepare("
    SELECT items.*, users.name 
    FROM items 
    JOIN users ON items.user_id = users.id 
    WHERE items.user_id != ? 
    ORDER BY items.date_reported DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>📢 Community Feed - Lost & Found</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --primary: #0052cc;
      --secondary: #ff6f61;
      --bg: #f4f7fb;
      --light: #ffffff;
      --text: #333;
      --shadow: rgba(0, 0, 0, 0.1);
      --radius: 12px;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: var(--text);
      margin: 0;
    }

    header {
      background-color: var(--primary);
      padding: 1rem 2rem;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 8px var(--shadow);
    }

    header h1 {
      font-size: 1.6rem;
      font-weight: 600;
    }

    nav a {
      color: white;
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

    .feed-box {
      background: var(--light);
      padding: 2rem;
      border-radius: var(--radius);
      box-shadow: 0 4px 12px var(--shadow);
    }

    .feed-box h2 {
      color: var(--primary);
      font-weight: 600;
      margin-bottom: 1.5rem;
    }

    .card {
      border: none;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: 0 2px 10px var(--shadow);
      margin-bottom: 1.5rem;
    }

    .card img {
      height: 200px;
      object-fit: contain;
      background-color: #f0f0f0;
      width: 100%;
    }

    .card-body h5 {
      font-size: 1.2rem;
      color: var(--secondary);
      font-weight: 600;
    }

    .card-body p {
      font-size: 0.95rem;
      margin: 0.3rem 0;
    }

    .btn {
      border-radius: 20px;
      padding: 6px 16px;
      font-weight: 500;
      font-size: 0.9rem;
    }

    .btn-success {
      background-color: #34a853;
      border-color: #34a853;
      color: white;
    }

    .btn-warning {
      background-color: #fbbc04;
      border-color: #fbbc04;
      color: black;
    }

    .badge.resolved {
      background-color: #cccccc;
      color: #333;
      font-size: 0.85rem;
      padding: 6px 12px;
      border-radius: 20px;
      display: inline-block;
      margin-top: 0.5rem;
    }

    footer {
      text-align: center;
      padding: 1rem;
      font-size: 0.9rem;
      color: #777;
      margin-top: 2rem;
    }
  </style>
</head>
<body>

  <header>
    <h1>🌐 Community Feed</h1>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="submitform.html">Report Lost</a>
      <a href="myresponse.php">Responses</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main>
    <div class="feed-box">
      <h2>👀 Hello <?= htmlspecialchars($user_name) ?>! See What Others Have Reported</h2>

      <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-4">
            <div class="card">
              <?php if (!empty($row['image'])): ?>
                <img src="<?= htmlspecialchars($row['image']) ?>" alt="Item Image" class="card-img-top" />
              <?php else: ?>
                <img src="default-placeholder.png" alt="No Image Available" class="card-img-top" />
              <?php endif; ?>

              <div class="card-body">
                <h5><?= htmlspecialchars($row['title']) ?></h5>
                <p><strong>👤 Posted by:</strong> <?= htmlspecialchars($row['name']) ?></p>
                <p><strong>📅 Posted on:</strong> <?= date('d M Y', strtotime($row['date_reported'])) ?> • <?= htmlspecialchars($row['location']) ?></p>
                <p><strong>📌 Type:</strong> <?= htmlspecialchars($row['type']) ?></p>
                <p><strong>📅 Status:</strong> <?= htmlspecialchars($row['status']) ?></p>

                <?php
                  $type = strtolower(trim($row['type']));
                  $status = strtolower(trim($row['status']));
                  if ($status !== 'resolved'):
                      if ($type === 'lost'):
                ?>
                        <a href="response.php?id=<?= (int)$row['id'] ?>&action=found" class="btn btn-success mt-2">✅ Report Found Item</a>
                      <?php else: ?>
                        <a href="response.php?id=<?= (int)$row['id'] ?>&action=lost" class="btn btn-warning mt-2">❗ Claim Lost Item</a>
                      <?php endif; ?>
                <?php else: ?>
                      <span class="badge resolved">✅ Already Resolved</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </main>

  <footer>
     ❤️ Trisha Tarwey © 2025 — Lost & Found Community 🧭💼
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
