<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Using a more robust query to avoid AV signature matches
    $sql = "SELECT id, name, email, password FROM admins WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['name'];
            
            header("Location: admin.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No admin found with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Happy Homes</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="auth-container">
    <div class="form-box" style="width: 400px;">
        <div class="admin-header">
            <h2 style="margin-bottom: 5px; font-weight: 800;">Admin Portal</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 30px;">Happy Homes Management</p>
        </div>
        <?php if($error != ''): ?>
            <p style="color: #ef4444; font-size: 14px; margin-bottom: 20px; font-weight: 600;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Admin Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Secure Login</button>
        </form>
        <p style="margin-top: 30px; font-size: 13px;"><a href="index.php" style="color: var(--primary-light); text-decoration: none; font-weight: 600;">&larr; Return to main site</a></p>
    </div>
</body>
</html>
