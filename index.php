<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Homes | Book Your Stay</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <h2 class="logo">Happy Homes</h2>
    <div class="menu-toggle" id="mobile-menu">
        <i class="fas fa-bars"></i>
    </div>
    <ul class="nav-links">
        <li><a href="#home">Home</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#rooms">Rooms</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="my-bookings.php">My Bookings</a></li>
            <li><a href="logout.php" class="btn-login">Logout</a></li>
        <?php
else: ?>
            <li><a href="login.php" class="btn-login">Login</a></li>
        <?php
endif; ?>
    </ul>
</nav>

<section class="hero" id="home">
    <div class="hero-content">
        <h1>Find Your Perfect Room</h1>
        <p>Experience luxury and comfort at the heart of the city.</p>
        <a href="#rooms"><button>Explore Rooms</button></a>
    </div>
</section>

<section class="about" id="about">
    <div class="about-container">
        <h2>About Happy Homes</h2>
        <p>Happy Homes is a premier room booking platform dedicated to providing seamless travel experiences.</p>
        <div class="features">
            <span>⭐ 24/7 Support</span>
            <span>📍 Prime Locations</span>
            <span>🛡️ Secure Payments</span>
        </div>
    </div>
</section>

<section class="rooms" id="rooms">
    <h1>Available Rooms</h1>
    <div class="room-container">
        <!-- DELUXE ROOM -->
        <div class="room-card">
            <img src="images/deluxe.png" alt="Deluxe">
            <h3>Deluxe Room</h3>
            <p>₹2000 / night</p>
            <p class="vacancy-display" data-room-id="deluxe-room" style="font-size: 14px; font-weight: 600; margin: 5px 0; min-height: 21px;"></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="book-btn" data-room="Deluxe Room">Book Now</button>
            <?php
else: ?>
                <a href="login.php"><button class="book-btn">Login to Book</button></a>
            <?php
endif; ?>
        </div>

        <!-- STANDARD ROOM -->
        <div class="room-card">
            <img src="images/standard.png" alt="Standard">
            <h3>Standard Room</h3>
            <p>₹1200 / night</p>
            <p class="vacancy-display" data-room-id="standard-room" style="font-size: 14px; font-weight: 600; margin: 5px 0; min-height: 21px;"></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="book-btn" data-room="Standard Room">Book Now</button>
            <?php
else: ?>
                <a href="login.php"><button class="book-btn">Login to Book</button></a>
            <?php
endif; ?>
        </div>

        <!-- EXECUTIVE SUITE -->
        <div class="room-card">
            <img src="images/suite.png" alt="Suite">
            <h3>Executive Suite</h3>
            <p>₹3500 / night</p>
            <p class="vacancy-display" data-room-id="executive-suite" style="font-size: 14px; font-weight: 600; margin: 5px 0; min-height: 21px;"></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="book-btn" data-room="Executive Suite">Book Now</button>
            <?php
else: ?>
                <a href="login.php"><button class="book-btn">Login to Book</button></a>
            <?php
endif; ?>
        </div>

        <!-- FAMILY ROOM -->
        <div class="room-card">
            <img src="images/family.png" alt="Family">
            <h3>Family Room</h3>
            <p>₹2500 / night</p>
            <p class="vacancy-display" data-room-id="family-room" style="font-size: 14px; font-weight: 600; margin: 5px 0; min-height: 21px;"></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="book-btn" data-room="Family Room">Book Now</button>
            <?php
else: ?>
                <a href="login.php"><button class="book-btn">Login to Book</button></a>
            <?php
endif; ?>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Happy Homes</h3>
            <p>Your comfort, our priority.</p>
        </div>
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#rooms">Rooms</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Contact</h4>
            <p>Email: happyhomes@gmail.com</p>
            <p>Phone: +91 98765 43210</p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; 2026 Happy Homes | Designed with ❤️ | <a href="admin_login.php" style="color: #cbd5e1; text-decoration: none; margin-left:10px;">Admin Portal</a>
    </div>
</footer>

<script src="js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>