<?php
// Include db.php to establish database connection
include 'db.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch movie details for editing
if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    $query = "SELECT * FROM movies WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: manage_movies.php");
    exit();
}

// Handle form submission for updating a movie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_movie'])) {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $show_time = $_POST['show_time'];
    $theater_name = $_POST['theater_name'];
    $poster_path = $movie['poster']; // Keep existing poster if not updated

    // Update poster if a new one is uploaded
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['poster']['tmp_name'];
        $fileName = $_FILES['poster']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Sanitize the file name to avoid any risk
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        // Directory where the file is uploaded
        $uploadFileDir = './uploads/posters/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }
        $dest_path = $uploadFileDir . $newFileName;

        // Allow certain file formats
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $poster_path = $dest_path; // Update the poster path
            } else {
                $message = 'There was some error moving the file to upload directory.';
            }
        } else {
            $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
        }
    }

    // Convert show time from local time (Africa/Kampala) to UTC for storing in the database
    if (!empty($show_time)) {
        $date = new DateTime($show_time, new DateTimeZone('Africa/Kampala'));
        $date->setTimezone(new DateTimeZone('UTC'));
        $utc_time = $date->format('Y-m-d H:i:s');
    } else {
        $utc_time = null;
    }

    // Update movie in the database
    $query = "UPDATE movies SET title = ?, genre = ?, description = ?, duration = ?, poster = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $title, $genre, $description, $duration, $poster_path, $movie_id);
    $stmt->execute();
    $stmt->close();

    // Update showtime in the database
    if (!empty($utc_time) && !empty($theater_name)) {
        $query = "UPDATE showtimes SET show_time = ?, theater_name = ? WHERE movie_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $utc_time, $theater_name, $movie_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to the manage movies page
    header("Location: manage_movies.php");
    exit();
}

// Convert show time from UTC to Africa/Kampala time for display
$show_time_display = '';
if (isset($movie['show_time']) && !empty($movie['show_time'])) {
    $date = new DateTime($movie['show_time'], new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone('Africa/Kampala'));
    $show_time_display = $date->format('Y-m-d\TH:i');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Movie</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
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
            z-index: 100;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 60px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group button {
            padding: 10px 15px;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>

    <header>
        <h1>Edit Movie</h1>

    </header>

    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_movies.php" class="active">Manage Movies</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="manage_admins.php">Manage Admins</a>
        <a href="view_reports.php">View Reports</a>
        <a href="admin_logout.php" class="logout">Logout</a>

        <!-- Add more sidebar links here -->
    </div>

    <div class="main-content">
        <div class="container">
            <!-- Edit Movie Form -->
            <h2>Edit Movie</h2>
            <form action="edit_movie.php?id=<?php echo htmlspecialchars($movie_id); ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($movie['title'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="genre">Genre</label>
                    <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($movie['genre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($movie['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="duration">Duration (in minutes)</label>
                    <input type="number" id="duration" name="duration" value="<?php echo htmlspecialchars($movie['duration'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="show_time">Show Time (in Africa/Kampala time)</label>
                    <input type="datetime-local" id="show_time" name="show_time" value="<?php echo htmlspecialchars($show_time_display ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="theater_name">Theater Name</label>
                    <input type="text" id="theater_name" name="theater_name" value="<?php echo htmlspecialchars($movie['theater_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="poster">Poster (leave empty to keep current poster)</label>
                    <input type="file" id="poster" name="poster">
                </div>
                <div class="form-group">
                    <button type="submit" name="update_movie">Update Movie</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
