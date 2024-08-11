<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db.php';

$showtime_id = $_GET['id'];

$query = "DELETE FROM showtimes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
header("Location: list_showtimes.php");
?>
