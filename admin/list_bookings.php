<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db.php';

$query = "SELECT bookings.*, users.username, movies.title FROM bookings
          JOIN users ON bookings.user_id = users.id
          JOIN movies ON bookings.movie_id = movies.id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: white;
            padding: 1em 0;
            text-align: center;
        }

        main {
            padding: 2em;
            max-width: 1000px;
            margin: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1em;
        }

        th, td {
            padding: 0.75em;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .actions a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Manage Bookings</h1>
    </header>
    <main>
        <h2>Manage Bookings</h2>
        <table>
            <tr>
                <th>Booking ID</th>
                <th>Username</th>
                <th>Movie Title</th>
                <th>Booking Date</th>
                <th>Show Time</th>
                <th>Theater Name</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                <td><?php echo htmlspecialchars($row['show_time']); ?></td>
                <td><?php echo htmlspecialchars($row['theater_name']); ?></td>
                <td class="actions">
                    <!-- Add any additional actions if needed -->
                    <a href="delete_booking.php?id=<?php echo $row['id']; ?>">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </main>
</body>
</html>
