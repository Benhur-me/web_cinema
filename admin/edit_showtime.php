<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db.php';

// Fetch showtimes details
$showtime_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM showtimes WHERE id = $showtime_id";
$result = $conn->query($query);
$showtime = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Showtime</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #333;
            padding-top: 20px;
            color: white;
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
            background-color: #575757;
            color: #f1f1f1;
        }

        /* Header styles */
        header {
            background-color: #333;
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
        }

        .logout {
            background-color: #f44336; /* Red color */
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            display: inline-block;
            margin-right: 20px;
        }

        .logout:hover {
            background-color: #c62828; /* Darker red */
            color: #f1f1f1;
        }

        /* Main content styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 80px; /* Adjust based on header height */
        }

        /* Form styles */
        form {
            max-width: 800px;
            margin: auto;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"], input[type="date"], input[type="time"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <h1 class="header-title">Edit Showtime</h1>
        <a href="admin_logout.php" class="logout">Logout</a>
    </header>

    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_movies.php">Manage Movies</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="view_reports.php">View Reports</a>
    </div>

    <div class="main-content">
        <h2>Edit Showtime</h2>
        <form action="update_showtime.php" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($showtime['id']); ?>">
            
            <label for="movie_id">Movie ID:</label>
            <input type="text" id="movie_id" name="movie_id" value="<?php echo htmlspecialchars($showtime['movie_id']); ?>" required>
            
            <label for="showtime">Showtime:</label>
            <input type="time" id="showtime" name="showtime" value="<?php echo htmlspecialchars($showtime['showtime']); ?>" required>
            
            <label for="theater">Theater:</label>
            <input type="text" id="theater" name="theater" value="<?php echo htmlspecialchars($showtime['theater']); ?>" required>
            
            <input type="submit" value="Update Showtime">
        </form>
    </div>
</body>
</html>
