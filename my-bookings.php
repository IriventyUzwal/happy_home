<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's bookings with room details
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Happy Homes</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background-color: var(--background);">

<nav class="navbar">
    <h2 class="logo">HAPPY HOMES</h2>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="logout.php" class="btn-login">Logout</a></li>
    </ul>
</nav>

<section class="my-bookings-container">
    <div class="booking-header-main">
        <h1>My Bookings</h1>
        <p style="color: var(--text-muted);">Manage your upcoming stays and history</p>
    </div>

    <?php if ($result->num_rows === 0): ?>
        <div style="text-align: center; padding: 80px 20px; background: var(--card-bg); border-radius: 24px; border: 1px solid var(--glass-border); margin-top: 30px;">
            <i class="fas fa-calendar-times" style="font-size: 50px; color: var(--primary-light); margin-bottom: 20px; display: block;"></i>
            <h3 style="color: white; margin-bottom: 10px;">No bookings found</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">You haven't booked any rooms yet.</p>
            <a href="index.php#rooms" class="btn-login" style="text-decoration: none;">Book a Room Now</a>
        </div>
    <?php else: ?>
        <div id="bookingHistoryList" style="margin-top: 40px;">
            <?php while ($item = $result->fetch_assoc()): ?>
                <div class="booking-card-horizontal">
                    <div class="booking-img">
                        <img src="https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&q=80&w=1000" alt="Room">
                    </div>
                    <div class="booking-details">
                        <div class="card-top">
                            <h3><?= htmlspecialchars($item['room_name']) ?></h3>
                            <span class="status-badge <?= htmlspecialchars($item['status']) ?>">
                                <?= ucfirst(htmlspecialchars($item['status'])) ?>
                            </span>
                        </div>
                        
                        <div class="booking-info-grid">
                            <div class="info-item">
                                <small>CHECK-IN</small>
                                <p><i class="far fa-calendar-check" style="margin-right: 8px; color: var(--primary-light);"></i> <?= date('d M Y', strtotime($item['check_in'])) ?></p>
                            </div>
                            <div class="info-item">
                                <small>CHECK-OUT</small>
                                <p><i class="far fa-calendar-times" style="margin-right: 8px; color: var(--accent);"></i> <?= date('d M Y', strtotime($item['check_out'])) ?></p>
                            </div>
                            <div class="info-item">
                                <small>BOOKING ID</small>
                                <p><?= htmlspecialchars($item['booking_id']) ?></p>
                            </div>
                        </div>

                        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--glass-border); padding-top: 20px;">
                            <div class="guest-info">
                                <span style="color: var(--text-muted); font-size: 13px;">
                                    <i class="fas fa-users" style="margin-right: 5px;"></i> <?= $item['guests'] ?> Guests
                                </span>
                            </div>
                            <a href="cancel-booking.php?id=<?= $item['id'] ?>" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                <button class="btn-cancel"><i class="fas fa-times-circle"></i> Cancel Booking</button>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<footer class="footer" style="margin-top: 100px;">
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Happy Homes Luxury Stays. All rights reserved.</p>
    </div>
</footer>

<script src="js/script.js"></script>
</body>
</html>