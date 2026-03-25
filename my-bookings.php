<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM bookings WHERE user_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Bookings | StayEase</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body style="background-color: #f1f5f9;">

<nav class="navbar">
  <h2 class="logo">StayEase</h2>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="logout.php" class="btn-login">Logout</a></li>
  </ul>
</nav>

<section class="my-bookings-container">
  <h1>My Bookings</h1>

  <?php if ($result->num_rows === 0): ?>
    <p style="text-align: center; padding: 50px;">
      No bookings found. <a href="index.php#rooms">Book one now!</a>
    </p>
  <?php else: ?>
    <div id="bookingHistoryList">
      <?php while ($item = $result->fetch_assoc()): ?>
        <div class="booking-card-horizontal">
          <div class="booking-img">
            <img src="https://images.unsplash.com/photo-1566665797739-1674de7a421a" alt="Room">
          </div>
          <div class="booking-details">
            <div class="card-top">
              <h3><?= htmlspecialchars($item['room_name']) ?></h3>
              <span class="status-badge confirmed">Confirmed</span>
            </div>
            <div class="booking-info-grid">
              <div class="info-item">
                <small>Check‑in</small>
                <p><?= $item['check_in'] ?></p>
              </div>
              <div class="info-item">
                <small>Check‑out</small>
                <p><?= $item['check_out'] ?></p>
              </div>
              <div class="info-item">
                <small>ID</small>
                <p><?= $item['booking_id'] ?></p>
              </div>
            </div>
            <div class="card-footer">
              <a href="cancel-booking.php?id=<?= $item['id'] ?>">
                <button class="btn-cancel">Cancel Booking</button>
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>

<script src="js/script.js"></script>
</body>
</html>