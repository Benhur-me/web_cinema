<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: manage_showtimes.php"); // Redirect if no ID is provided
    exit();
}

$showtime_id = $_GET['id'];

$query = "SELECT showtimes.*, movies.title FROM showtimes JOIN movies ON showtimes.movie_id = movies.id WHERE showtimes.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_showtimes.php"); // Redirect if no showtime found
    exit();
}

$showtime = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Showtime Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Showtime Details</h1>
        <a href="manage_showtimes.php" class="back-icon"><i class="fas fa-arrow-left"></i> Back</a>
    </header>
    <main>
        <h2>Showtime Details</h2>
        <p><strong>Movie Title:</strong> <?php echo htmlspecialchars($showtime['title']); ?></p>
        <p><strong>Show Time:</strong> <?php echo htmlspecialchars($showtime['show_time']); ?></p>
        <p><strong>Theater Name:</strong> <?php echo htmlspecialchars($showtime['theater_name']); ?></p>
        <a href="manage_showtimes.php">Back to Showtimes List</a>
    </main>
</body>
</html>
