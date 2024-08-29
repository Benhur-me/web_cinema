<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Connect to the database
include 'db.php';

// Check if $conn is defined
if (!isset($conn)) {
    die("Database connection failed.");
}

// Fetch the logged-in admin ID
$logged_in_admin_id = $_SESSION['admin_id'];

// Fetch admin details including username and superadmin status
$username = "Admin"; // Default value
$is_superadmin = 0; // Default value
$query = "SELECT username, is_superadmin FROM admins WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param("i", $logged_in_admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
        $username = $row['username'];
        $is_superadmin = $row['is_superadmin'];
    }
    $stmt->close();
} else {
    echo "<p>Error preparing statement: " . htmlspecialchars($conn->error) . "</p>";
}

// Handle form submissions (e.g., adding admins) only if the user is a superadmin
if ($is_superadmin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $is_superadmin_post = isset($_POST['is_superadmin']) ? 1 : 0;
    $is_approved = 1;

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $message = "Username, email, and password are required.";
    } else {
        // Check if username or email already exists
        $query = "SELECT * FROM admins WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = "An admin with this username or email already exists.";
        } else {
            // Insert new admin
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO admins (username, email, password, is_superadmin, is_approved) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssss", $username, $email, $password_hashed, $is_superadmin_post, $is_approved);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $message = "Admin added successfully.";
            } else {
                $message = "Failed to add admin.";
            }
            $stmt->close();
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $admin_id_to_delete = $_GET['delete_id'];

    // Ensure the admin to delete is not the logged-in superadmin
    if ($is_superadmin) {
        if ($admin_id_to_delete != $logged_in_admin_id) {
            $query = "DELETE FROM admins WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $admin_id_to_delete);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $message = "Admin deleted successfully.";
            } else {
                $message = "Failed to delete admin.";
            }
            $stmt->close();
        } else {
            $message = "You cannot delete your own account.";
        }
    } else {
        $message = "You do not have permission to delete admins.";
    }
}

// Fetch existing admins
$query = "SELECT id, username, email, is_superadmin FROM admins";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
    <style>
        /* General Styles */
        * {
            box-sizing: border-box; /* Ensures padding and borders are included in the width and height */
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        .main-content h2 {
            color: #34495e;
            font-size: 28px;
            margin-bottom: 20px;
        }

        /* Form Styles */
        form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="checkbox"],
        button[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        button[type="submit"] {
            background-color: #2c3e50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #34495e;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            overflow-x: auto;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Responsive Table Styles */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }

            table, thead, tbody, th, td, tr {
                display: block;
                width: 100%;
            }

            thead tr {
                display: none;
            }

            tr {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            td {
                display: flex;
                justify-content: space-between;
                padding: 8px;
                position: relative;
                text-align: left;
                font-size: 14px;
                border: none;
            }

            td:before {
                content: attr(data-label);
                flex-basis: 50%;
                font-weight: bold;
                padding-right: 10px;
                color: #34495e;
            }

            .hamburger {
                display: flex;
            }

            .sidebar {
                transform: translateX(-100%);
                width: 200px;
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
        }

        /* Footer Styles */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 15px;
            position: fixed;
            width: calc(100% - 250px);
            left: 250px;
            bottom: 0;
        }

        @media (max-width: 768px) {
            footer {
                width: 100%;
                left: 0;
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
        <h1 class="header-title">Manage Admins</h1>
        <a href="admin_logout.php" class="logout">Logout</a>
    </header>

    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_movies.php">Manage Movies</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <?php if ($is_superadmin): ?>
            <a href="manage_admins.php" class="active">Manage Admins</a>
        <?php endif; ?>
        <a href="view_reports.php">View Reports</a>
        <a href="admin_logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <?php if (!empty($message)) : ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <h2>Add New Admin</h2>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="is_superadmin">Superadmin:</label>
            <input type="checkbox" id="is_superadmin" name="is_superadmin">

            <button type="submit" name="add_admin">Add Admin</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Superadmin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td data-label="ID"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td data-label="Username"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td data-label="Email"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td data-label="Superadmin"><?php echo $row['is_superadmin'] ? 'Yes' : 'No'; ?></td>
                        <td data-label="Actions">
                            <?php if ($row['id'] != $logged_in_admin_id) : ?>
                                <a href="?delete_id=<?php echo htmlspecialchars($row['id']); ?>" class="button">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Admin Management System</p>
    </footer>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }
    </script>
</body>
</html>
