<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$room = htmlspecialchars($_GET['room'] ?? 'Deluxe Room');
$error = ""; $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id     = $_SESSION['user_id'];
    $room_name   = trim($_POST['room_name']);
    $check_in    = $_POST['check_in'];
    $check_out   = $_POST['check_out'];
    $guests      = (int)$_POST['guests'];
    $mobile      = $_POST['mobile'];
    $age         = (int)$_POST['age'];
    $pay_type    = $_POST['payment_type'];
    $booking_id  = "SE-" . rand(1000, 9999);

    // 1. Validation: Max 4 members
    if ($guests > 4) {
        $error = "Sorry, a maximum of only 4 members are allowed per room.";
    } else {
        $sql = "INSERT INTO bookings (user_id, room_name, check_in, check_out, guests, mobile, age, payment_type, booking_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssisiss", $user_id, $room_name, $check_in, $check_out, $guests, $mobile, $age, $pay_type, $booking_id);
        
        if ($stmt->execute()) {
            $success = "Booking confirmed! Redirecting...";
            echo "<script>setTimeout(() => { window.location.href = 'my-bookings.php'; }, 1500);</script>";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Room | StayEase</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<section class="booking">
    <div class="booking-box">
        <h2>Confirm Your Stay</h2>
        <?php if ($error): ?> <div style="color:red; margin-bottom:10px;"><?= $error ?></div> <?php endif; ?>
        <?php if ($success): ?> <div style="color:green; margin-bottom:10px;"><?= $success ?></div> <?php endif; ?>

        <form method="POST">
            <label>Selected Room</label>
            <input type="text" name="room_name" value="<?= $room ?>" readonly>

            <div style="display:flex; gap:10px;">
                <div style="flex:1;">
                    <label>Mobile Number</label>
                    <input type="text" name="mobile" placeholder="10-digit mobile" required pattern="[0-9]{10}">
                </div>
                <div style="flex:1;">
                    <label>Your Age</label>
                    <input type="number" name="age" min="18" required>
                </div>
            </div>

            <label>Check-in & Check-out</label>
            <div style="display:flex; gap:10px;">
                <input type="date" name="check_in" required min="<?= date('Y-m-d') ?>">
                <input type="date" name="check_out" required>
            </div>

            <label>Number of Guests (Max 4)</label>
            <input type="number" min="1" max="4" name="guests" required>

            <label>Payment Method</label>
            <select name="payment_type" style="width:100%; padding:12px; border-radius:5px; border:1px solid #e2e8f0; margin-bottom:15px;">
                <option value="Online">Online Payment</option>
                <option value="Offline">Pay at Hotel (Offline)</option>
            </select>

            <button type="submit">Confirm Booking</button>
        </form>
    </div>
</section>
</body>
</html>