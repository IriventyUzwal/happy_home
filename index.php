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
        <div
            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 40px; gap: 20px;">
            <h1>Available Rooms</h1>
            <div class="filter-bar" style="display: flex; gap: 20px; align-items: center; background: white; padding: 15px 25px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                <input type="text" id="roomSearch" placeholder="Search room type..." style="padding: 10px 10px 10px 40px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; color: #0f172a; margin: 0; width: 200px; outline: none;">
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <label style="color: #0f172a; font-size: 14px; font-weight: 600;">Max Price: ₹<span id="priceVal">5000</span></label>
                <input type="range" id="priceFilter" min="500" max="5000" value="5000" style="width: 150px; cursor: pointer; margin: 0;">
            </div>
        </div>
        </div>
        <div class="room-container">
            <?php
            require_once 'config.php';
            $rooms_query = $conn->query("SELECT * FROM rooms ORDER BY id DESC");
            if ($rooms_query && $rooms_query->num_rows > 0):
                while ($room = $rooms_query->fetch_assoc()):
                    $room_slug = strtolower(str_replace(' ', '-', $room['room_type']));
                    $room_id = $room['id'];
                    $gallery_res = $conn->query("SELECT image_path FROM room_images WHERE room_id = $room_id LIMIT 2");
                    $gallery = [];
                    while ($g = $gallery_res->fetch_assoc())
                        $gallery[] = $g['image_path'];
                    $rating_res = $conn->query("SELECT AVG(rating) as avg_r, COUNT(*) as count_r FROM reviews WHERE room_name = '" . $room['room_type'] . "'");
                    $r_data = $rating_res->fetch_assoc();
                    $avg_rating = round((float) $r_data['avg_r'], 1);
                    $review_count = $r_data['count_r'];
                        ?>
                    <div class="room-card <?= !$room['is_available'] ? 'unavailable-card' : '' ?>"
                        id="room-card-<?= $room_id ?>">
                        <div class="room-slider-container"
                            style="position: relative; height: 300px; overflow: hidden; border-radius: 20px 20px 0 0; background: #0f172a;">
                            <div class="room-slides"
                                style="display: flex; height: 100%; transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1); width: 100%;">
                                <!-- Main Image -->
                                <img src="images/<?= htmlspecialchars($room['image']) ?>"
                                    alt="<?= htmlspecialchars($room['room_type']) ?>"
                                    style="min-width: 100%; width: 100%; height: 100%; object-fit: cover; flex-shrink: 0; display: block;">

                                <!-- Gallery Images -->
                                <?php foreach ($gallery as $img): ?>
                                    <img src="images/<?= htmlspecialchars($img) ?>" alt="Gallery"
                                        style="min-width: 100%; width: 100%; height: 100%; object-fit: cover; flex-shrink: 0; display: block;">
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($gallery) > 0): ?>
                                <button class="slider-btn prev" onclick="moveSlider(<?= $room_id ?>, -1)"
                                    style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); background: rgba(15, 23, 42, 0.7); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; backdrop-filter: blur(8px); z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: 0.3s;"><i
                                        class="fas fa-chevron-left" style="font-size: 14px;"></i></button>
                                <button class="slider-btn next" onclick="moveSlider(<?= $room_id ?>, 1)"
                                    style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: rgba(15, 23, 42, 0.7); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; backdrop-filter: blur(8px); z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: 0.3s;"><i
                                        class="fas fa-chevron-right" style="font-size: 14px;"></i></button>

                                <div class="slider-dots"
                                    style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 10;">
                                    <span class="dot active"
                                        style="width: 25px; height: 4px; background: white; border-radius: 10px; opacity: 0.9; transition: all 0.3s ease;"></span>
                                    <?php for ($i = 0; $i < count($gallery); $i++): ?>
                                        <span class="dot"
                                            style="width: 8px; height: 4px; background: white; border-radius: 10px; opacity: 0.4; transition: all 0.3s ease;"></span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" id="currentSlide-<?= $room_id ?>" value="0">
                        <input type="hidden" id="totalSlides-<?= $room_id ?>" value="<?= count($gallery) + 1 ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <h3 style="margin: 0;"><?= htmlspecialchars($room['room_type']) ?></h3>
                            <?php if ($review_count > 0): ?>
                                <span style="color: #fbbf24; font-size: 12px; font-weight: 600;"><i class="fas fa-star"></i>
                                    <?= $avg_rating ?> (<?= $review_count ?>)</span>
                            <?php endif; ?>
                        </div>
                        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 10px;">
                            <?= htmlspecialchars($room['description']) ?></p>
                        <p>₹<?= (int) $room['price'] ?> / night</p>
                        <p class="vacancy-display" data-room-id="<?= $room_slug ?>"
                            style="font-size: 14px; font-weight: 600; margin: 5px 0; min-height: 21px;"></p>

                        <?php if (!$room['is_available']): ?>
                            <button class="book-btn" disabled
                                style="background: #475569; cursor: not-allowed; opacity: 0.6;">Currently Unavailable</button>
                        <?php elseif (isset($_SESSION['user_id'])): ?>
                            <button class="book-btn" data-room="<?= htmlspecialchars($room['room_type']) ?>">Book Now</button>
                        <?php else: ?>
                            <a href="login.php"><button class="book-btn">Login to Book</button></a>
                        <?php endif; ?>
                    </div>
                <?php
                endwhile;
            else:
                echo "<p style='grid-column: 1/-1; color: var(--text-muted);'>No rooms available at the moment.</p>";
            endif;
            ?>
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
            &copy; 2026 Happy Homes | Designed with ❤️ | <a href="admin_login.php"
                style="color: #cbd5e1; text-decoration: none; margin-left:10px;">Admin Portal</a>
        </div>
    </footer>

    <script src="js/script.js?v=<?php echo time(); ?>"></script>
</body>

</html>