<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("Access denied.");
}

$booking_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get booking details + User details
$sql = "SELECT b.*, u.fullname, u.email, r.price as nightly_price, r.image
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        LEFT JOIN rooms r ON b.room_name = r.room_type
        WHERE b.id = ? AND b.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die("Booking not found.");
}

// Calculate total nights and price
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$nights = $check_in->diff($check_out)->days;
if($nights <= 0) $nights = 1;
$total_price = $nights * (float)$booking['nightly_price'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Receipt - <?= $booking['booking_id'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea5e9;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }
        body { font-family: 'Inter', sans-serif; color: var(--text-dark); margin: 0; padding: 40px; background: #fff; }
        .receipt-container { max-width: 800px; margin: 0 auto; border: 1px solid var(--border); padding: 50px; border-radius: 8px; }
        .header { display: flex; justify-content: space-between; align-items: top; border-bottom: 2px solid var(--primary); padding-bottom: 30px; margin-bottom: 40px; }
        .logo h1 { margin: 0; color: var(--primary); font-size: 28px; letter-spacing: -1px; }
        .logo p { margin: 5px 0 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; }
        .receipt-title { text-align: right; }
        .receipt-title h2 { margin: 0; font-size: 24px; color: var(--text-dark); }
        .receipt-title p { margin: 5px 0 0; color: var(--text-muted); font-size: 14px; }

        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px; }
        .details-box h3 { font-size: 12px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 10px; letter-spacing: 1px; }
        .details-box p { margin: 0; font-size: 16px; font-weight: 600; line-height: 1.6; }

        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 50px; }
        .summary-table th { text-align: left; background: #f8fafc; padding: 15px; border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 12px; text-transform: uppercase; }
        .summary-table td { padding: 20px 15px; border-bottom: 1px solid var(--border); font-size: 15px; }
        .summary-table .price-col { text-align: right; font-weight: 600; }

        .total-box { text-align: right; margin-top: 30px; }
        .total-row { display: flex; justify-content: flex-end; gap: 40px; margin-bottom: 10px; }
        .total-label { color: var(--text-muted); font-size: 14px; }
        .total-value { font-size: 16px; font-weight: 600; min-width: 120px; }
        .grand-total { margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--text-dark); }
        .grand-total .total-label { color: var(--text-dark); font-weight: 700; font-size: 18px; }
        .grand-total .total-value { color: var(--primary); font-size: 24px; font-weight: 800; }

        .footer-note { margin-top: 80px; text-align: center; color: var(--text-muted); font-size: 13px; border-top: 1px solid var(--border); padding-top: 30px; }

        .no-print { position: fixed; top: 20px; right: 20px; }
        .btn-print { background: var(--primary); color: white; padding: 12px 25px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: inherit; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .receipt-container { border: none; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Print Receipt</button>
    <a href="my-bookings.php" style="margin-left: 10px; color: var(--text-muted); text-decoration: none; font-size: 14px;">Back to Bookings</a>
</div>

<div class="receipt-container">
    <div class="header">
        <div class="logo">
            <h1>HAPPY HOMES</h1>
            <p>Luxury Stays & Accommodations</p>
        </div>
        <div class="receipt-title">
            <h2>Payment Receipt</h2>
            <p>Booking ID: #<?= htmlspecialchars($booking['booking_id']) ?></p>
            <p>Date: <?= date('d M Y') ?></p>
        </div>
    </div>

    <div class="details-grid">
        <div class="details-box">
            <h3>Customer Info</h3>
            <p><?= htmlspecialchars($booking['fullname']) ?></p>
            <p><?= htmlspecialchars($booking['email']) ?></p>
            <p>+91 <?= htmlspecialchars($booking['mobile']) ?></p>
        </div>
        <div class="details-box">
            <h3>Accommodation Details</h3>
            <p><?= htmlspecialchars($booking['room_name']) ?></p>
            <p><?= $booking['guests'] ?> Guests</p>
            <p><?= date('d M Y', strtotime($booking['check_in'])) ?> to <?= date('d M Y', strtotime($booking['check_out'])) ?></p>
        </div>
    </div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th style="text-align: right;">Nightly Rate</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($booking['room_name']) ?> Stay</td>
                <td><?= $nights ?> Nights</td>
                <td style="text-align: right;">₹<?= number_format($booking['nightly_price'], 2) ?></td>
                <td class="price-col">₹<?= number_format($total_price, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-row">
            <span class="total-label">Subtotal</span>
            <span class="total-value">₹<?= number_format($total_price, 2) ?></span>
        </div>
        <div class="total-row">
            <span class="total-label">Taxes & Fees</span>
            <span class="total-value">₹0.00</span>
        </div>
        <div class="total-row grand-total">
            <span class="total-label">Grand Total</span>
            <span class="total-value">₹<?= number_format($total_price, 2) ?></span>
        </div>
        <div style="margin-top: 15px; color: var(--text-muted); font-size: 13px;">
            Payment Method: **<?= htmlspecialchars($booking['payment_type']) ?>**
        </div>
    </div>

    <div class="footer-note">
        <p>Thank you for choosing Happy Homes. We hope you have a pleasant stay!</p>
        <p>This is a computer-generated receipt and does not require a physical signature.</p>
        <p style="margin-top: 10px; font-weight: 600; color: var(--primary);">www.happyhomes.com</p>
    </div>
</div>

</body>
</html>
