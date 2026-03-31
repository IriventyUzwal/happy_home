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
    $r_type = $conn->real_escape_string($_POST['room_type']);
    $t_rooms = (int)$_POST['total_rooms'];
    $price = (int)$_POST['price'];
    
    // Check if exists
    $check = $conn->query("SELECT id FROM rooms WHERE room_type='$r_type'");
    if($check && $check->num_rows > 0) {
        $conn->query("UPDATE rooms SET total_rooms=$t_rooms, price=$price WHERE room_type='$r_type'");
    } else {
         $conn->query("INSERT INTO rooms (room_type, total_rooms, price) VALUES ('$r_type', $t_rooms, $price)");
    }
}

// Handle Delete Room
if (isset($_POST['delete_room'])) {
    $id = (int)$_POST['room_id'];
    $conn->query("DELETE FROM rooms WHERE id=$id");
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
$bookings_sql = "SELECT b.id, b.room_name, b.check_in, b.check_out, u.fullname as user_name, u.email as user_email 
                 FROM bookings b 
                 JOIN users u ON b.user_id = u.id 
                 ORDER BY b.check_in DESC";
$bookings_result = $conn->query($bookings_sql);
$bookings = $bookings_result ? $bookings_result->fetch_all(MYSQLI_ASSOC) : [];
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
        .btn-delete { 
            background: rgba(239, 68, 68, 0.1); 
            color: #ef4444; 
            border: 1px solid rgba(239, 68, 68, 0.2); 
            padding: 8px 15px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600;
            transition: var(--transition);
        }
        .btn-delete:hover { background: #ef4444; color: white; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Admin Console</h1>
        <a href="admin_logout.php" class="logout-btn">Logout</a>
    </div>
    <div class="grid">
        <div>
            <div class="box">
                <h2>Manage Room</h2>
                <form method="POST">
                    <input type="text" name="room_type" placeholder="Room Type" required>
                    <input type="number" name="total_rooms" placeholder="Quantity" required>
                    <input type="number" name="price" placeholder="Price (₹)" required>
                    <button type="submit" name="add_room" class="btn-add">Save Room</button>
                </form>
            </div>
            <div class="box">
                <h2>Inventory</h2>
                <table>
                    <tr><th>Type</th><th>Qty</th><th>Action</th></tr>
                    <?php foreach ($rooms as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['room_type']) ?></td>
                        <td><?= $r['total_rooms'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;"><input type="hidden" name="room_id" value="<?= $r['id'] ?>"><button type="submit" name="delete_room" class="btn-delete">X</button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
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
                <h2>Recent Bookings</h2>
                <table>
                    <tr><th>Customer</th><th>Room</th><th>Check In</th><th>Check Out</th></tr>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['user_name']) ?></td>
                        <td><?= htmlspecialchars($b['room_name']) ?></td>
                        <td><?= $b['check_in'] ?></td>
                        <td><?= $b['check_out'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>