<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db.php';

$query = "SELECT showtimes.*, movies.title FROM showtimes JOIN movies ON showtimes.movie_id = movies.id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Showtimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            position: relative;
        }

        header .back-icon {
            position: absolute;
            left: 1em;
            top: 1em;
            color: #007BFF; /* Blue color */
            font-size: 1.2em;
            text-decoration: none;
        }

        header .back-icon:hover {
            color: #0056b3; /* Darker blue for hover effect */
        }

        main {
            padding: 2em;
            max-width: 1200px;
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
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1em;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 0.75em;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .actions a {
            margin-right: 1em;
        }
    </style>
</head>
<body>
    <header>
        <a href="admin_dashboard.php" class="back-icon">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <h1>Manage Showtimes</h1>
    </header>
    <main>
        <h2>Manage Showtimes</h2>
        <a href="add_showtime.php" class="button">Add New Showtime</a>
        <table>
            <tr>
                <th>ID</th>
                <th>Movie Title</th>
                <th>Show Time</th>
                <th>Theater Name</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['show_time']); ?></td>
                <td><?php echo htmlspecialchars($row['theater_name']); ?></td>
                <td class="actions">
                    <a href="edit_showtime.php?id=<?php echo urlencode($row['id']); ?>">Edit</a>
                    <a href="delete_showtime.php?id=<?php echo urlencode($row['id']); ?>">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </main>
</body>
</html>
