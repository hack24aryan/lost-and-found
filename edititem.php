<?php
// This is edititem.php
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

// Fetch item details
$query = "SELECT * FROM items WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $image = $item['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        }
    }

    $updateQuery = "UPDATE items SET type = ?, title = ?, description = ?, location = ?, image = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssssi", $type, $title, $description, $location, $image, $item_id);

    if ($updateStmt->execute()) {
        echo "<script>alert('Item updated successfully'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating item');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Item</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f1f3f4;
      font-family: 'Roboto', sans-serif;
    }

    .container {
      max-width: 700px;
      background-color: #fff;
      padding: 2rem;
      border-radius: 8px;
      margin-top: 50px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    h2 {
      color: #202124;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .form-label {
      font-weight: 500;
      color: #5f6368;
    }

    .form-control {
      border-radius: 4px;
      border: 1px solid #dadce0;
    }

    .form-control:focus {
      border-color: #1a73e8;
      box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.2);
    }

    .btn-primary {
      background-color: #1a73e8;
      border: none;
      width: 100%;
    }

    .btn-primary:hover {
      background-color: #1558c0;
    }

    img {
      max-height: 250px;
      object-fit: contain;
      background-color: #f8f9fa;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Lost/Found Item</h2>
    <form action="editItem.php?id=<?php echo $item_id; ?>" method="POST" enctype="multipart/form-data">
      
      <div class="mb-3">
        <label for="type" class="form-label">Type</label>
        <select name="type" class="form-select" required>
          <option value="lost" <?php echo ($item['type'] === 'lost') ? 'selected' : ''; ?>>Lost</option>
          <option value="found" <?php echo ($item['type'] === 'found') ? 'selected' : ''; ?>>Found</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($item['title']); ?>" required>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($item['description']); ?></textarea>
      </div>

      <div class="mb-3">
        <label for="location" class="form-label">Location</label>
        <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($item['location']); ?>" required>
      </div>

      <div class="mb-3">
        <label for="image" class="form-label">Upload New Image (optional)</label>
        <input type="file" name="image" class="form-control">
        <?php if ($item['image']): ?>
          <div class="mt-3">
            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Item Image" class="img-fluid">
          </div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary mt-3">Update Item</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
