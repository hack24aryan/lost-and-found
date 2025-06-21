<?php
// login.php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "lostandfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare and execute query to find user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Update last login timestamp
            $updateLogin = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateLogin->bind_param("i", $user['id']);
            $updateLogin->execute();
            $updateLogin->close();

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Wrong password
            header("Location: wrong.html");
            exit();
        }
    } else {
        // User not found
        header("Location: usernotfound.html");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <style>
    body {
      background: linear-gradient(to right, #ffe4ec, #d0e8ff);
      font-family: 'Comic Sans MS', cursive;
      color: #444;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .card {
      background: white;
      padding: 40px;
      border-radius: 25px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      border: 4px dashed #ff69b4;
      text-align: center;
      max-width: 350px;
    }

    h1 {
      color: #ff69b4;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 2px solid #ffb6c1;
      border-radius: 12px;
      outline: none;
    }

    button {
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #87cefa;
      border: none;
      color: white;
      border-radius: 20px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #6495ed;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Login</h1>
    <form method="POST" action="login.php" novalidate>
      <label for="email">Email:</label>
      <input type="email" name="email" id="email" required autocomplete="email" />

      <label for="password">Password:</label>
      <input type="password" name="password" id="password" required autocomplete="current-password" />

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
