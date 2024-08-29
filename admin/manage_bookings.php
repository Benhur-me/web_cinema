<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Connect to the database
include 'db.php';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
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
            flex: 1; /* Allows main content to grow */
        }

        .main-content h2 {
            color: #34495e;
            font-size: 28px;
            margin-bottom: 20px;
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

        footer p {
            margin: 0;
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
    </style>
</head>
<body>
    <header>
        <div class="hamburger" onclick="toggleSidebar()">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <h1 class="header-title">Manage Bookings</h1>
        <a href="admin_logout.php" class="logout">Logout</a>
    </header>

    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_movies.php">Manage Movies</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_bookings.php" class="active">Manage Bookings</a>
        <?php if ($is_superadmin): ?>
            <a href="manage_admins.php">Manage Admins</a>
        <?php endif; ?>
        <a href="view_reports.php">View Reports</a>
        <a href="admin_logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h2>Manage Bookings</h2>
        <!-- Page content goes here -->
    </div>

    <footer>
        <p>&copy; 2024 Cinema Admin Panel</p>
    </footer>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }
    </script>
</body>
</html>
