<?php

// Include db.php to establish database connection
include 'db.php';

// New Block: Fix Poster Image Uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['poster'])) {
    // Check if the file was uploaded without errors
    if ($_FILES['poster']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['poster']['tmp_name'];
        $fileName = $_FILES['poster']['name'];
        $fileSize = $_FILES['poster']['size'];
        $fileType = $_FILES['poster']['type'];
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
                $message = 'File is successfully uploaded.';
                $poster_path = $dest_path; // Store the path for saving in the database
            } else {
                $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by the web server.';
                $poster_path = null;
            }
        } else {
            $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
            $poster_path = null;
        }
    } elseif ($_FILES['poster']['error'] == UPLOAD_ERR_NO_FILE) {
        $message = 'No file was uploaded.';
        $poster_path = null;
    } else {
        $message = 'Error during file upload.';
        $poster_path = null;
    }
    // Store the $message to display in the UI or log it as needed
}

// Original Code Starts Here

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Ensure uploads directory exists
if (!file_exists('uploads/posters')) {
    mkdir('uploads/posters', 0777, true);
}

// Handle form submission for adding a movie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_movie'])) {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $show_time = $_POST['show_time'];
    $theater_name = $_POST['theater_name'];

    // Use the poster path from the new block
    // Convert user input show time from local time (Africa/Kampala) to UTC
    $date = new DateTime($show_time, new DateTimeZone('Africa/Kampala'));
    $date->setTimezone(new DateTimeZone('UTC'));
    $utc_time = $date->format('Y-m-d H:i:s');

    // Debugging
    echo "Original Show Time: $show_time<br>";
    echo "UTC Time: $utc_time<br>";

    // Insert movie into the database
    $query = "INSERT INTO movies (title, genre, description, duration, poster) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $title, $genre, $description, $duration, $poster_path);
    $stmt->execute();
    $movie_id = $stmt->insert_id;
    $stmt->close();

    // Insert showtime into the database
    if (!empty($utc_time) && !empty($theater_name)) {
        $query = "INSERT INTO showtimes (movie_id, show_time, theater_name) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $movie_id, $utc_time, $theater_name);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to the same page to prevent form resubmission
    header("Location: manage_movies.php");
    exit();
}

// Handle movie deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_movie_id'])) {
    $delete_movie_id = $_POST['delete_movie_id'];
    $query = "DELETE FROM movies WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $delete_movie_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch the logged-in admin ID
$logged_in_admin_id = $_SESSION['admin_id'];

// Fetch superadmin status for the logged-in admin
$is_superadmin = 0; // Default value
$query = "SELECT is_superadmin FROM admins WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $logged_in_admin_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row) {
    $is_superadmin = $row['is_superadmin'];
}
$stmt->close();

// Fetch movies and showtimes
$query = "SELECT m.id, m.title, m.genre, m.description, m.duration, m.poster, s.show_time, s.theater_name 
          FROM movies m 
          LEFT JOIN showtimes s ON m.id = s.movie_id
          ORDER BY m.id ASC, s.show_time ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            padding-bottom: 40px; /* Space for footer */
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
            padding: -6px;
            text-align: center;
            position: fixed;
            width: calc(100% - 250px);
            left: 250px;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-left: 10px;
            /* border: 1px solid green; */
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
            margin-top: 40px;
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

        .movie-list {
            margin-top: 20px;
        }

        .movie-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .movie-list th, .movie-list td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .movie-list th {
            background-color: #2c3e50;
            color: white;
        }

        .movie-list td img {
            max-width: 100px;
        }

        .btn-edit {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 10px;
        }

        .btn-edit:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: calc(100% - 250px);
            left: 250px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <a href="logout.php" class="logout">Logout</a>
    </header>
    
    <div class="sidebar">
    <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_movies.php" class="active">Manage Movies</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <?php if ($is_superadmin): ?>
            <a href="manage_admins.php">Manage Admins</a>
        <?php endif; ?>
        <a href="view_reports.php">View Reports</a>
        <a href="admin_logout.php" class="logout">Logout</a>
    </div>
    
    <div class="main-content">
        <div class="container">
            <h2>Add New Movie</h2>
            <form action="manage_movies.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="genre">Genre</label>
                    <input type="text" id="genre" name="genre" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="duration">Duration</label>
                    <input type="text" id="duration" name="duration" required>
                </div>
                <div class="form-group">
                    <label for="show_time">Show Time (Africa/Kampala)</label>
                    <input type="datetime-local" id="show_time" name="show_time" required>
                </div>
                <div class="form-group">
                    <label for="theater_name">Theater Name</label>
                    <input type="text" id="theater_name" name="theater_name" required>
                </div>
                <div class="form-group">
                    <label for="poster">Poster</label>
                    <input type="file" id="poster" name="poster" accept="image/*" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="add_movie">Add Movie</button>
                </div>
            </form>
            
            <div class="movie-list">
                <h2>Existing Movies</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Title</th>
                            <th>Genre</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Show Time</th>
                            <th>Theater Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><img src="<?php echo $row['poster']; ?>" alt="Poster"></td>
                                <td><?php echo $row['title']; ?></td>
                                <td><?php echo $row['genre']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td><?php echo $row['duration']; ?></td>
                                <td><?php echo $row['show_time']; ?></td>
                                <td><?php echo $row['theater_name']; ?></td>
                                <td>
                                    <a href="edit_movie.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                    <form action="manage_movies.php" method="post" style="display:inline;">
                                        <input type="hidden" name="delete_movie_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <footer>
        &copy; 2024 Cinema. All Rights Reserved.
    </footer>
</body>
</html>
