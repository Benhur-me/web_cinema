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

// Fetch admin details including username and superadmin status
$username = "Admin"; // Default value
$is_superadmin = 0; // Default value
$query = "SELECT username, is_superadmin FROM admins WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $logged_in_admin_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row) {
    $username = $row['username'];
    $is_superadmin = $row['is_superadmin'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        /* Dashboard Cards */
        .cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1;
            min-width: 250px;
            text-align: center;
        }

        .card h3 {
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 18px;
            color: #7f8c8d;
        }

        .superadmin-symbol {
            color: #f39c12; /* Color for superadmin symbol */
            font-size: 24px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1 class="header-title">Admin Dashboard</h1>
        <a href="admin_logout.php" class="logout">Logout</a>
    </header>

    <div class="sidebar">
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="manage_movies.php">Manage Movies</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <?php if ($is_superadmin): ?>
            <a href="manage_admins.php">Manage Admins</a>
        <?php endif; ?>
        <a href="view_reports.php">View Reports</a>
        
    </div>

    <div class="main-content">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>
            <?php if ($is_superadmin): ?>
                <span class="superadmin-symbol">👑</span>
            <?php endif; ?>
        </h2>
        <div class="cards">
            <div class="card">
                <h3>Total Movies</h3>
                <p>120</p>
            </div>
            <div class="card">
                <h3>Total Users</h3>
                <p>450</p>
            </div>
            <div class="card">
                <h3>Total Bookings</h3>
                <p>300</p>
            </div>
            <div class="card">
                <h3>Reports Generated</h3>
                <p>50</p>
            </div>
        </div>
    </div>
</body>
</html>
