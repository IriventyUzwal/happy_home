<?php
session_start();
require_once 'config.php';

// Check if admin is logged in securely
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle Add/Update Room
if (isset($_POST['add_room'])) {
    $r_type   = $conn->real_escape_string($_POST['room_type']);
    $t_rooms  = (int)$_POST['total_rooms'];
    $price    = (int)$_POST['price'];
    $desc     = $conn->real_escape_string($_POST['description']);
    $edit_id  = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // Handle Image Upload
    $image_name = $_POST['existing_image'] ?? 'default_room.png';
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === 0) {
        $target_dir = "images/";
        $file_ext = pathinfo($_FILES["room_image"]["name"], PATHINFO_EXTENSION);
        $new_name = strtolower(str_replace(' ', '_', $r_type)) . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_name;
        
        if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file)) {
            $image_name = $new_name;
        }
    }

    // Validation
    if ($t_rooms <= 0 || $price <= 0) {
        $error_msg = "Error: Total rooms and price must be greater than zero.";
    } else {
        if ($edit_id > 0) {
            $sql = "UPDATE rooms SET room_type=?, total_rooms=?, price=?, description=?, image=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siissi", $r_type, $t_rooms, $price, $desc, $image_name, $edit_id);
            $stmt->execute();
            $room_id = $edit_id;
            $success_msg = "Room updated successfully!";
        } else {
            $sql = "INSERT INTO rooms (room_type, total_rooms, price, description, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siiss", $r_type, $t_rooms, $price, $desc, $image_name);
            $stmt->execute();
            $room_id = $conn->insert_id;
            $success_msg = "Room added successfully!";
        }

        // Handle Gallery Uploads (Limited to 2)
        if (isset($_FILES['room_gallery']) && count($_FILES['room_gallery']['name']) > 0) {
            $target_dir = "images/";
            $count = 0;
            foreach ($_FILES['room_gallery']['tmp_name'] as $key => $tmp_name) {
                if ($count >= 2) break; // Limit to 2 images
                if ($_FILES['room_gallery']['error'][$key] === 0) {
                    $file_name = $_FILES['room_gallery']['name'][$key];
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_gallery_name = "gallery_" . $room_id . "_" . $key . "_" . time() . "." . $file_ext;
                    if (move_uploaded_file($tmp_name, $target_dir . $new_gallery_name)) {
                        $conn->query("INSERT INTO room_images (room_id, image_path) VALUES ($room_id, '$new_gallery_name')");
                        $count++;
                    }
                }
            }
        }
    }
}

// Handle Toggle Availability
if (isset($_POST['toggle_availability'])) {
    $id = (int)$_POST['room_id'];
    $conn->query("UPDATE rooms SET is_available = 1 - is_available WHERE id=$id");
}

// Handle Delete Room
if (isset($_POST['delete_room'])) {
    $id = (int)$_POST['room_id'];
    $conn->query("DELETE FROM rooms WHERE id=$id");
}

// Fetch Room for Editing
$edit_room = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_room = $conn->query("SELECT * FROM rooms WHERE id=$id")->fetch_assoc();
}

// Fetch all rooms
$rooms = $conn->query("SELECT * FROM rooms")->fetch_all(MYSQLI_ASSOC);

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $new_email = $conn->real_escape_string($_POST['admin_email']);
    $admin_id = $_SESSION['admin_id'];
    
    // Update email
    $conn->query("UPDATE admins SET email='$new_email' WHERE id=$admin_id");
    
    // Update password if provided
    if (!empty($_POST['admin_password'])) {
        $pass_regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?\":{}|<>]).{8,}$/";
        if (!preg_match($pass_regex, $_POST['admin_password'])) {
            $error_msg = "Weak password! Must be 8+ chars with uppercase, number, and special char.";
        } else {
            $new_pass = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);
            $conn->query("UPDATE admins SET password='$new_pass' WHERE id=$admin_id");
            $success_msg = "Profile updated successfully!";
        }
    } else {
        $success_msg = "Profile updated successfully!";
    }
}

// Fetch current admin info
$admin_data = $conn->query("SELECT email FROM admins WHERE id=" . $_SESSION['admin_id'])->fetch_assoc();

// Fetch all bookings
$bookings_sql = "SELECT b.id, b.room_name, b.check_in, b.check_out, b.status, b.id_proof, b.total_amount, u.fullname as user_name, u.email as user_email 
                 FROM bookings b 
                 JOIN users u ON b.user_id = u.id 
                 ORDER BY b.check_in DESC";
$bookings_result = $conn->query($bookings_sql);
$bookings = $bookings_result ? $bookings_result->fetch_all(MYSQLI_ASSOC) : [];

// Calculate Earnings
$this_month_res = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$this_month_amt = (float)($this_month_res->fetch_assoc()['total'] ?? 0);

$last_month_res = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))");
$last_month_amt = (float)($last_month_res->fetch_assoc()['total'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Happy Homes</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background: var(--background); color: var(--text-main); padding: 40px; }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            background: var(--card-bg); 
            padding: 40px; 
            border-radius: 32px; 
            backdrop-filter: blur(20px); 
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
        }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid var(--glass-border); 
            padding-bottom: 30px; 
            margin-bottom: 40px; 
        }
        .header h1 {
            font-size: 32px;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .logout-btn { 
            background: var(--accent); 
            color: white; 
            padding: 12px 25px; 
            text-decoration: none; 
            border-radius: 12px; 
            font-weight: 700; 
            transition: var(--transition);
        }
        .logout-btn:hover { transform: scale(1.05); filter: brightness(1.2); }
        .grid { display: grid; grid-template-columns: 1fr 2.5fr; gap: 40px; }
        .box { 
            background: rgba(15, 23, 42, 0.4); 
            padding: 25px; 
            border-radius: 20px; 
            margin-bottom: 30px; 
            border: 1px solid var(--glass-border);
        }
        .box h2 { font-size: 20px; margin-bottom: 20px; color: var(--primary-light); }
        input { 
            width: 100%; 
            padding: 12px 15px; 
            margin-bottom: 15px; 
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border); 
            border-radius: 10px; 
            color: white;
            box-sizing: border-box; 
            transition: var(--transition);
        }
        input:focus { border-color: var(--primary); outline: none; }
        .btn-add { 
            width: 100%; 
            background: var(--primary-gradient); 
            color: white; 
            padding: 15px; 
            border: none; 
            border-radius: 10px; 
            cursor: pointer; 
            font-weight: 700; 
            transition: var(--transition);
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 18px; text-align: left; border-bottom: 1px solid var(--glass-border); font-size: 14px; }
        th { color: var(--primary-light); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        td { color: var(--text-muted); }
        .btn-delete:hover { background: #ef4444; color: white; }
        .btn-edit { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); padding: 8px 15px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; }
        .btn-edit:hover { background: #38bdf8; color: white; }
        .btn-toggle { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; }
        .btn-toggle.unavailable { color: #f43f5e; border-color: rgba(244, 63, 94, 0.2); background: rgba(244, 63, 94, 0.1); }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Admin Console</h1>
        <a href="admin_logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Analytics Dashboard -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 40px;">
        <div class="box" style="background: linear-gradient(135deg, #0ea5e9, #2563eb); border: none; padding: 30px;">
            <p style="color: rgba(255,255,255,0.8); font-size: 14px; font-weight: 600; margin-bottom: 10px;">EARNINGS THIS MONTH</p>
            <h2 style="color: white; font-size: 32px; margin: 0;">₹<?= number_format($this_month_amt, 2) ?></h2>
        </div>
        <div class="box" style="background: rgba(15, 23, 42, 0.4); padding: 30px;">
            <p style="color: var(--text-muted); font-size: 14px; font-weight: 600; margin-bottom: 10px;">EARNINGS LAST MONTH</p>
            <h2 style="color: var(--primary-light); font-size: 32px; margin: 0;">₹<?= number_format($last_month_amt, 2) ?></h2>
        </div>
    </div>

    <div class="grid">
        <div>
            <div class="box">
                <h2>Manage Room</h2>
                <?php if(isset($success_msg)): ?>
                    <p style="color: #059669; font-size: 13px; font-weight: 600; margin-bottom: 15px;"><?= $success_msg ?></p>
                <?php endif; ?>
                <?php if(isset($error_msg)): ?>
                    <p style="color: #ef4444; font-size: 13px; font-weight: 600; margin-bottom: 15px;"><?= $error_msg ?></p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <?php if($edit_room): ?>
                        <input type="hidden" name="edit_id" value="<?= $edit_room['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= $edit_room['image'] ?>">
                        <p style="font-size: 12px; color: var(--primary-light); margin-bottom: 10px;">Editing: <?= htmlspecialchars($edit_room['room_type']) ?></p>
                    <?php endif; ?>
                    
                    <input type="text" name="room_type" placeholder="Room Type" value="<?= $edit_room ? htmlspecialchars($edit_room['room_type']) : '' ?>" required>
                    <input type="number" name="total_rooms" placeholder="Quantity" min="1" value="<?= $edit_room ? $edit_room['total_rooms'] : '' ?>" required>
                    <input type="number" name="price" placeholder="Price (₹)" min="1" value="<?= $edit_room ? (int)$edit_room['price'] : '' ?>" required>
                    <textarea name="description" placeholder="Room Description" style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); color: white; margin-bottom: 15px; height: 100px;"><?= $edit_room ? htmlspecialchars($edit_room['description']) : '' ?></textarea>
                    
                    <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 5px;">MAIN ROOM IMAGE</label>
                    <input type="file" name="room_image" accept="image/*" style="padding-top: 10px;">
                    
                    <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 5px;">GALLERY IMAGES (MAX 2 PHOTOS)</label>
                    <input type="file" name="room_gallery[]" id="galleryInput" accept="image/*" multiple style="padding-top: 10px;" onchange="checkFileLimit(this)">
                    <p style="font-size: 10px; color: var(--primary-light); margin-top: 5px; font-weight: 600;"><i class="fas fa-info-circle"></i> Only the first 2 photos will be saved.</p>
                    
                    <script>
                        function checkFileLimit(input) {
                            if (input.files.length > 2) {
                                alert("Luxury Rooms are limited to 2 gallery photos. Please select only your best 2!");
                                input.value = ""; // Clear the selection
                            }
                        }
                    </script>
                    
                    <button type="submit" name="add_room" class="btn-add"><?= $edit_room ? 'Update Room' : 'Add Room' ?></button>
                    <?php if($edit_room): ?>
                        <a href="admin.php" style="display: block; margin-top: 10px; color: var(--text-muted); font-size: 13px; text-decoration: none;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="box">
                <h2>Inventory</h2>
                <div style="overflow-x: auto;">
                    <table>
                        <tr><th>Type</th><th>Qty</th><th>Price</th><th>Availability</th><th>Actions</th></tr>
                        <?php foreach ($rooms as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['room_type']) ?></td>
                            <td><?= $r['total_rooms'] ?></td>
                            <td>₹<?= (int)$r['price'] ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="toggle_availability" class="btn-toggle <?= !$r['is_available'] ? 'unavailable' : '' ?>">
                                        <?= $r['is_available'] ? 'Available' : 'Unavailable' ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="admin.php?edit=<?= $r['id'] ?>" class="btn-edit">Edit</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <div class="box" style="background: #e0f2fe; border: 1px solid #bae6fd;">
                <h2>Admin Settings</h2>
                <?php if(isset($success_msg)): ?>
                    <p style="color: #059669; font-size: 13px; font-weight: 600;"><?= $success_msg ?></p>
                <?php endif; ?>
                <?php if(isset($error_msg)): ?>
                    <p style="color: #ef4444; font-size: 13px; font-weight: 600;"><?= $error_msg ?></p>
                <?php endif; ?>
                <form method="POST">
                    <label style="font-size: 11px; color: #64748b; font-weight:600;">ADMIN LOGIN EMAIL</label>
                    <input type="email" name="admin_email" value="<?= htmlspecialchars($admin_data['email']) ?>" required>
                    <label style="font-size: 11px; color: #64748b; font-weight:600;">NEW PASSWORD (MIN 8 CHARS, UPPER, SPECIAL)</label>
                    <input type="password" name="admin_password" placeholder="••••••••"
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*(),.?&quot;:{}|<>]).{8,}"
                           title="Must contain at least 8 characters, one uppercase, one lowercase, one number, and one special character.">
                    <button type="submit" name="update_profile" class="btn-add" style="background: #38bdf8;">Update Credentials</button>
                </form>
            </div>
        </div>
        <div>
            <div class="box" style="background: white;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Recent Bookings</h2>
                    <input type="text" id="bookingSearch" placeholder="Search customer, room or ID..." style="width: 250px; margin-bottom: 0; background: #f8fafc; border: 1px solid #e2e8f0; color: #0f172a;">
                </div>
                <table id="bookingsTable">
                    <tr><th>Customer</th><th>Room</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Proof</th></tr>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['user_name']) ?></td>
                        <td><?= htmlspecialchars($b['room_name']) ?></td>
                        <td><?= $b['check_in'] ?></td>
                        <td><?= $b['check_out'] ?></td>
                        <td>
                            <span class="status-badge <?= $b['status'] ?>" style="font-size: 10px; padding: 4px 10px;">
                                <?= ucfirst($b['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($b['id_proof']): ?>
                                <a href="proofs/<?= $b['id_proof'] ?>" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 600;">View ID</a>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">No ID</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    // Real-time Booking Search
    document.getElementById('bookingSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#bookingsTable tr:not(:first-child)');
        
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>
</body>
</html>