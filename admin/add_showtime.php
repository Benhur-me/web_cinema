<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db.php';

// Fetch movie IDs for the dropdown
$movies_query = "SELECT id, title FROM movies";
$movies_result = $conn->query($movies_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $movie_id = $_POST['movie_id'];
    $show_time = $_POST['show_time'];
    $theater_name = $_POST['theater_name'];

    // Insert showtime into the database
    $query = "INSERT INTO showtimes (movie_id, show_time, theater_name) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $movie_id, $show_time, $theater_name);
    $stmt->execute();

    // Redirect to manage showtimes page
    header("Location: manage_showtimes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Showtime</title>
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
        }

        main {
            padding: 2em;
            max-width: 800px;
            margin: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        form {
            display: grid;
            gap: 1em;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 0.75em;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 0.75em;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .back-icon {
            display: inline-block;
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }

        .back-icon:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>Add Showtime</h1>
        <a href="admin_dashboard.php" class="back-icon">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </header>
    <main>
        <h2>Add Showtime</h2>
        <form action="add_showtime.php" method="POST">
            <label for="movie_id">Movie:</label>
            <select id="movie_id" name="movie_id" required>
                <option value="">Select a movie</option>
                <?php while ($movie = $movies_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($movie['id']); ?>">
                        <?php echo htmlspecialchars($movie['title']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <label for="show_time">Show Time:</label>
            <input type="datetime-local" id="show_time" name="show_time" required>
            <label for="theater_name">Theater Name:</label>
            <input type="text" id="theater_name" name="theater_name" required>
            <input type="submit" value="Add Showtime">
        </form>
    </main>
</body>
</html>
