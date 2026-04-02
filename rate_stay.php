<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$room_name = isset($_GET['room']) ? htmlspecialchars($_GET['room']) : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = $conn->real_escape_string($_POST['comment']);
    $r_name = $_POST['room_name'];

    $sql = "INSERT INTO reviews (user_id, room_name, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isis", $user_id, $r_name, $rating, $comment);

    if ($stmt->execute()) {
        $success = "Thank you for your feedback! Redirecting...";
        echo "<script>setTimeout(() => { window.location.href = 'my-bookings.php'; }, 2000);</script>";
    } else {
        $error = "Error submitting review: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rate Your Stay | Happy Homes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .rate-box { max-width: 600px; margin: 100px auto; background: var(--card-bg); padding: 40px; border-radius: 24px; border: 1px solid var(--glass-border); color: white; }
        .rating-stars { display: flex; gap: 10px; font-size: 24px; margin-bottom: 20px; color: #fbbf24; cursor: pointer; }
        label { display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 14px; }
        textarea { width: 100%; padding: 15px; border-radius: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); color: white; margin-bottom: 20px; font-family: inherit; height: 120px; resize: none; box-sizing: border-box; }
        .btn-submit { width: 100%; background: var(--primary-gradient); color: white; padding: 15px; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3); }
    </style>
</head>
<body style="background: var(--background);">
    <div class="rate-box">
        <h2>Rate Your Stay</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px;">How was your experience in the **<?= $room_name ?>**?</p>
        
        <?php if(isset($success)): ?> <p style="color: #10b981; font-weight: 600;"><?= $success ?></p> <?php endif; ?>
        <?php if(isset($error)): ?> <p style="color: #ef4444; font-weight: 600;"><?= $error ?></p> <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="room_name" value="<?= $room_name ?>">
            <label>Your Rating</label>
            <div class="rating-stars" id="starContainer">
                <i class="far fa-star" data-val="1"></i>
                <i class="far fa-star" data-val="2"></i>
                <i class="far fa-star" data-val="3"></i>
                <i class="far fa-star" data-val="4"></i>
                <i class="far fa-star" data-val="5"></i>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="5">

            <label>Review Comment</label>
            <textarea name="comment" placeholder="Tell us about your stay..." required></textarea>

            <button type="submit" class="btn-submit">Submit Review</button>
        </form>
    </div>

    <script>
        const stars = document.querySelectorAll('.rating-stars i');
        const input = document.getElementById('ratingInput');

        stars.forEach(star => {
            star.onclick = function() {
                let val = this.dataset.val;
                input.value = val;
                stars.forEach(s => {
                    if(s.dataset.val <= val) {
                        s.classList.replace('far', 'fas');
                    } else {
                        s.classList.replace('fas', 'far');
                    }
                });
            }
        });
    </script>
</body>
</html>
