<?php
session_start();
require_once 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $plain_password = $_POST['password'];

    // Password regex: At least 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
    $pass_regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?\":{}|<>]).{8,}$/";

    if (!preg_match($pass_regex, $plain_password)) {
        $error = "Password must be 8+ chars with uppercase, lowercase, number, and special character.";
    } else {
        $password = password_hash($plain_password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $fullname, $email, $password);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed. Email might already be in use.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-container">
  <div class="form-box">
    <h2>Register</h2>

    <?php if ($error): ?>
      <p style="color: #ef4444;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <input type="text" name="fullname" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Strong Password" 
             pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*(),.?&quot;:{}|<>]).{8,}"
             title="Must contain at least 8 characters, one uppercase, one lowercase, one number, and one special character." required>
      <p style="font-size: 11px; color: #94a3b8; margin: -5px 0 10px; text-align: left;">
        * Min 8 chars, 1 uppercase, 1 number, 1 special char
      </p>
      <button type="submit">Register</button>
      <p>Already have an account? <a href="login.php">Login</a></p>
    </form>
  </div>
</div>

<script src="js/script.js"></script>
</body>
</html>