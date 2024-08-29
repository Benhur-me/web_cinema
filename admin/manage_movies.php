<?php
// Database credentials
$hostname = 'localhost';
$username = 'root';
$password = ''; // Update this if your password is different
$database = 'cinema_booking'; // Use your actual database name

// Create a new connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding or deleting movies
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_movie'])) {
        // Add movie code here (for example, to insert a new movie into the database)
    } elseif (isset($_POST['delete_movie'])) {
        $movie_id = intval($_POST['movie_id']);
        $sql = "DELETE FROM movies WHERE id = $movie_id";
        if ($conn->query($sql) === TRUE) {
            echo "Movie deleted successfully.";
        } else {
            echo "Error deleting movie: " . $conn->error;
        }
    }
}

// Fetch movies from the database
$sql = "SELECT * FROM movies";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("Error fetching movies: " . $conn->error);
}

$movies = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}

$conn->close();
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden; /* Prevent horizontal scrolling */
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
            overflow-y: auto;
            transition: transform 0.3s ease;
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

        /* Hamburger Icon */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            margin-left: 10px;
        }

        .hamburger div {
            width: 25px;
            height: 3px;
            background-color: white;
            margin: 4px 0;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 80px;
            flex: 1;
        }

        /* Footer Styles */
        footer {
            background-color: #2c3e50;
            color: white;
            padding: 10px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: calc(100% - 250px);
            left: 250px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            header {
                width: calc(100% - 200px);
                left: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            footer {
                width: calc(100% - 200px);
                left: 200px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar a {
                padding: 15px 20px;
                text-decoration: none;
                font-size: 18px;
                color: white;
                display: block;
                border-radius: 4px;
                margin-top: 60px;
            
        }

            .sidebar.open {
                transform: translateX(0);
            }

            header {
                width: 100%;
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            footer {
                width: 100%;
                left: 0;
            }

            .hamburger {
                display: flex;
            }
        }

        /* Form and Table Styles */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .form-group button {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #2980b9;
        }

        /* Table Styles */
        .table-container {
            overflow-x: hidden; /* Prevent horizontal scrolling */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Ensure the table does not exceed container width */
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
            overflow: hidden; /* Prevent content overflow */
            text-overflow: ellipsis; /* Add ellipsis for overflow text */
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        .btn-edit, .btn-danger {
            text-decoration: none;
            padding: 5px 10px;
            color: white;
            border-radius: 4px;
        }

        .btn-edit {
            background-color: #3498db;
        }

        .btn-edit:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <header>
        <div class="hamburger" onclick="toggleSidebar()">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <h1 class="header-title">Manage Movies</h1>
        <a href="admin_logout.php" class="logout">Logout</a>
    </header>
    
    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_movies.php" class="active">Manage Movies</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="manage_admins.php">Manage Admins</a>
        <a href="view_reports.php">View Reports</a>
        <a href="admin_logout.php" class="logout">Logout</a>
    </div>
    
    <div class="main-content">
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
                <label for="release_date">Release Date (dd-mm-yyyy)</label>
                <input type="text" id="release_date" name="release_date" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="poster">Poster</label>
                <input type="file" id="poster" name="poster">
            </div>
            <div class="form-group">
                <button type="submit" name="add_movie">Add Movie</button>
            </div>
        </form>
        
        <h2>Current Movies</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Genre</th>
                        <th>Release Date</th>
                        <th>Description</th>
                        <th>Poster</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movies)): ?>
                        <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($movie['id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($movie['title'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($movie['genre'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($movie['release_date'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($movie['description'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($movie['poster'])): ?>
                                    <img src="<?php echo htmlspecialchars($movie['poster'] ?? ''); ?>" alt="Poster">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_movie.php?id=<?php echo $movie['id']; ?>" class="btn-edit">Edit</a>
                                <form action="manage_movies.php" method="post" style="display:inline-block;">
                                    <input type="hidden" name="movie_id" value="<?php echo $movie['id']; ?>">
                                    <button type="submit" name="delete_movie" class="btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No movies found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <footer>
        &copy; 2024 Cinema. All Rights Reserved.
    </footer>

    <script>
        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }
    </script>
</body>
</html>
