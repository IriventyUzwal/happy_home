<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = (int)($_GET['id'] ?? 0);

$sql = "DELETE FROM bookings WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();

header("Location: my-bookings.php");
exit();
?>