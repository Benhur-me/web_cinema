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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Showtime</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #2c3e50;
            color: white;
            padding-top: 20px;
            overflow-x: hidden;
        }

        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            border-radius: 4px;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #34495e;
            color: #f1f1f1;
        }

        /* Header Styles */
        header {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
            position: fixed;
            width: calc(100% - 250px);
            left: 250px;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .logout {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 20px;
        }

        .logout:hover {
            background-color: #c0392b;
            color: #f1f1f1;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 80px;
        }

        .main-content h2 {
            color: #34495e;
            font-size: 28px;
            margin-bottom: 20px;
        }

        /* Form Styles */
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .form-container label {
            font-weight: bold;
            margin-bottom: 0.5em;
            display: block;
        }

        .form-container input[type="text"],
        .form-container input[type="datetime-local"],
        .form-container select {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-container input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 0.75em;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }

        .form-container input[type="submit"]:hover {
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
        <h1 class="header-title">Add Showtime</h1>
        <a href="admin_logout.php" class="logout">Logout</a>
    </header>

    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_movies.php">Manage Movies</a>
        <a href="manage_showtimes.php" class="active">Manage Showtimes</a>
        <a href="add_showtime.php">Add Showtime</a>
        <a href="view_reports.php">View Reports</a>
    </div>

    <div class="main-content">
        <h2>Add Showtime</h2>

        <div class="form-container">
            <h2>Add New Showtime</h2>
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
        </div>
    </div>
</body>
</html>
