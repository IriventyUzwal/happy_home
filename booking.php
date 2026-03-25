<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Fix: Safe room name handling
$room = htmlspecialchars($_GET['room'] ?? 'Deluxe Room');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $room_name  = trim($_POST['room_name']);
    $check_in   = $_POST['check_in'];
    $check_out  = $_POST['check_out'];
    $guests     = (int)$_POST['guests'];
    $booking_id = "SE-" . rand(1000, 9999);

    // FIXED: 6 values = 6 types: i s s s i s
    $sql = "INSERT INTO bookings 
            (user_id, room_name, check_in, check_out, guests, booking_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'confirmed')";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("isssis", $user_id, $room_name, $check_in, $check_out, $guests, $booking_id);
        
        if ($stmt->execute()) {
            $success = "Booking confirmed successfully! Redirecting...";
            echo "<script>setTimeout(() => { window.location.href = 'my-bookings.php'; }, 1500);</script>";
        } else {
            $error = "Booking failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Room | StayEase</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <h2 class="logo">StayEase</h2>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="my-bookings.php">My Bookings</a></li>
        <li><a href="logout.php" class="btn-login">Logout</a></li>
    </ul>
</nav>

<section class="booking">
    <div class="booking-box">
        <h2>Confirm Your Stay</h2>

        <?php if ($error): ?>
            <div style="color: #ef4444; padding: 15px; background: #fee2e2; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ef4444;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="color: #10b981; padding: 15px; background: #dcfce7; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #10b981;">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="booking.php" id="bookingForm">
            <label>Selected Room</label>
            <input type="text" id="displayRoomName" name="room_name" value="<?= $room ?>" readonly style="background: #f1f5f9;">

            <label>Check-in Date</label>
            <input type="date" name="check_in" id="checkin" required min="<?= date('Y-m-d') ?>">

            <label>Check-out Date</label>
            <input type="date" name="check_out" id="checkout" required>

            <label>Guests</label>
            <input type="number" min="1" max="5" value="1" name="guests" required>

            <button type="submit">Confirm Booking</button>
        </form>

        <p style="text-align: center; margin-top: 20px; color: #64748b; font-size: 14px;">
            <a href="index.php#rooms">&larr; Back to Rooms</a>
        </p>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Date validation
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    
    if (checkinInput) {
        checkinInput.addEventListener('change', function() {
            const checkin = new Date(this.value);
            const tomorrow = new Date(checkin);
            tomorrow.setDate(checkin.getDate() + 1);
            checkoutInput.min = tomorrow.toISOString().split('T')[0];
        });
    }

    // Form submission feedback
    const form = document.getElementById('bookingForm');
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.textContent = 'Confirming...';
        submitBtn.disabled = true;
    });
});
</script>

<script src="js/script.js"></script>
</body>
</html>