<?php
require_once 'config.php';
header('Content-Type: application/json');

// Fetch all rooms
$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);
$db_rooms = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rtype = $row['room_type'];
        if (!isset($db_rooms[$rtype])) {
            $db_rooms[$rtype] = ['total_inventory' => 0];
        }
        $db_rooms[$rtype]['total_inventory'] += $row['total_rooms'];
    }
}

// Fetch active bookings
$book_sql = "SELECT room_name, COUNT(*) as bcount FROM bookings WHERE check_out >= CURDATE() GROUP BY room_name";
$book_res = $conn->query($book_sql);
$bookings = [];
if ($book_res) {
    while ($row = $book_res->fetch_assoc()) {
        $bookings[$row['room_name']] = (int)$row['bcount'];
    }
}

// Calculate final availability
$response = [];
foreach ($db_rooms as $rtype => $rdata) {
    $booked = isset($bookings[$rtype]) ? $bookings[$rtype] : 0;
    $available = $rdata['total_inventory'] - $booked;
    // creating a safe CSS class name id by replacing spaces with hyphens
    $id = strtolower(str_replace(' ', '-', $rtype));

    $response[$id] = [
        'available' => $available,
        'sold_out' => ($available <= 0)
    ];
}

echo json_encode($response);
?>
