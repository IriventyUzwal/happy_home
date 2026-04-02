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

    // 2. ID Proof Upload
    $id_proof_name = "";
    if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === 0) {
        $target_dir = "proofs/";
        $file_ext = pathinfo($_FILES["id_proof"]["name"], PATHINFO_EXTENSION);
        $new_name = $booking_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_name;
        
        if (move_uploaded_file($_FILES["id_proof"]["tmp_name"], $target_file)) {
            $id_proof_name = $new_name;
        }
    }

    // 3. Price Calculation
    $room_res = $conn->query("SELECT price FROM rooms WHERE room_type = '$room_name'");
    $room_data = $room_res->fetch_assoc();
    $nightly_price = (float)$room_data['price'];
    $check_in_dt = new DateTime($check_in);
    $check_out_dt = new DateTime($check_out);
    $nights = $check_in_dt->diff($check_out_dt)->days;
    if($nights <= 0) $nights = 1;
    $total_amount = $nights * $nightly_price;

    if ($id_proof_name === "") {
        $error = "Identification proof is mandatory. Please upload your ID.";
    } elseif ($guests > 4) {
        $error = "Sorry, a maximum of only 4 members are allowed per room.";
    } else {
        $sql = "INSERT INTO bookings (user_id, room_name, check_in, check_out, guests, mobile, age, payment_type, booking_id, status, id_proof, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssisissssd", $user_id, $room_name, $check_in, $check_out, $guests, $mobile, $age, $pay_type, $booking_id, $id_proof_name, $total_amount);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            $success = "Booking confirmed! <a href='print-receipt.php?id=$new_id' target='_blank' style='color:#059669; font-weight:700; text-decoration:underline;'>Print Receipt Now</a>. Redirecting...";
            echo "<script>setTimeout(() => { window.location.href = 'my-bookings.php'; }, 3000);</script>";
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

        <form method="POST" enctype="multipart/form-data">
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

            <label>Identification Proof (Aadhar/Passport/ID)</label>
            <input type="file" name="id_proof" id="id_proof_input" accept="image/*,.pdf" required style="padding: 10px; border: 1px solid #e2e8f0; border-radius: 5px; background: white; margin-bottom: 5px;">
            <p id="file_name_display" style="font-size: 11px; color: #0f172a; font-weight: 600; margin-bottom: 15px;"></p>

            <script>
                document.getElementById('id_proof_input').onchange = function() {
                    let fileName = this.files[0] ? this.files[0].name : "";
                    document.getElementById('file_name_display').innerHTML = fileName ? "Selected: " + fileName : "";
                };
            </script>

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