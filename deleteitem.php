<?php
// This is deleteitem.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'lostandfound');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$item_id = $_GET['id'] ?? null;

if (!$item_id) {
    echo "Invalid item ID.";
    exit();
}

$query = "SELECT * FROM items WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo "Item not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Delete Confirmation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f1f3f4;
      font-family: 'Roboto', sans-serif;
    }
    .container {
      max-width: 600px;
      margin: 80px auto;
    }
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      padding: 24px;
    }
    .btn-danger {
      background-color: #d93025;
      border-radius: 24px;
      padding: 10px 24px;
      font-weight: 500;
    }
    .btn-secondary {
      border-radius: 24px;
      padding: 10px 24px;
    }
    h3 {
      font-weight: 500;
    }
    .item-title {
      font-weight: 600;
      color: #202124;
    }
    .card-img-top {
      max-height: 300px;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="card text-center">
    <?php if (!empty($item['image'])): ?>
      <img src="<?php echo htmlspecialchars($item['image']); ?>" class="card-img-top mb-3" alt="Item Image">
    <?php endif; ?>

    <h3>Are you sure you want to delete this item?</h3>
    <p class="item-title mt-2 mb-4"><?php echo htmlspecialchars($item['title']); ?></p>

    <form action="deleteItemAction.php" method="POST">
      <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
      <button type="submit" class="btn btn-danger me-3">Yes, Delete</button>
      <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>

</body>
</html>
