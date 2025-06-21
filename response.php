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

$item_id = $_GET['id'];
$action = $_GET['action'];

$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    echo "Item not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Respond to Item</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f8fb;
            color: #333;
        }

        .container {
            margin-top: 40px;
            max-width: 800px;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
            color: #007bff;
        }

        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .card-img-top {
            max-height: 400px;
            object-fit: cover;
        }

        .card-body {
            padding: 25px;
        }

        .card-title {
            font-size: 24px;
            font-weight: bold;
        }

        .form-label {
            font-weight: 600;
        }

        input[type="date"],
        textarea {
            border-radius: 8px;
            border: 1px solid #ccc;
            padding: 10px;
        }

        button.btn-primary {
            background-color: #007bff;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        button.btn-primary:hover {
            background-color: #0056b3;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            margin-bottom: 30px;
            font-weight: 600;
            color: #007bff;
            background-color: #e9f2ff;
            border: none;
            padding: 10px 18px;
            border-radius: 30px;
            text-decoration: none;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .back-btn:hover {
            background-color: #d0e7ff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            color: #0056b3;
        }

        .back-btn svg {
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .back-btn:hover svg {
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="myfeed.php" class="back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H3.707l4.147 4.146a.5.5 0 0 1-.708.708l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 1 1 .708.708L3.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
            </svg>
            Back to Dashboard
        </a>

        <h2>🔎 Respond to: <?php echo htmlspecialchars($item['title']); ?></h2>

        <div class="card">
            <?php if (!empty($item['image'])): ?>
                <img src="<?php echo htmlspecialchars($item['image']); ?>" class="card-img-top" alt="Item Image">
            <?php else: ?>
                <img src="default-placeholder.png" class="card-img-top" alt="No Image Available">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title">📌 <?php echo htmlspecialchars($item['title']); ?></h5>
                <p class="card-text">📝 <?php echo htmlspecialchars($item['description']); ?></p>
                <p class="card-text">📍 <strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                <p class="card-text">🔖 <strong>Type:</strong> <?php echo htmlspecialchars($item['type']); ?></p>
                <p class="card-text">📌 <strong>Status:</strong> <?php echo htmlspecialchars($item['status']); ?></p>
            </div>
        </div>

        <form action="submitResponse.php" method="POST">
            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <input type="hidden" name="response_action" value="<?php echo htmlspecialchars($action); ?>">

            <div class="mb-3">
                <label for="response_date" class="form-label">📅 Date you <?php echo $action === 'found' ? 'found' : 'lost'; ?> it</label>
                <input type="date" class="form-control" id="response_date" name="response_date" required>
            </div>

            <div class="mb-3">
                <label for="details" class="form-label">🗒️ Additional Details (optional)</label>
                <textarea class="form-control" id="details" name="details" rows="4" placeholder="Add any helpful information..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">✅ Submit Response</button>
        </form>
    </div>
</body>
</html>
