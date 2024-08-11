<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Connect to the database
include 'db.php';

$message = "";

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
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        /* Header Styles */
        header {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
            position: fixed;
            width: 100%;
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

        .main-content .error {
            color: #e74c3c;
            font-weight: bold;
        }

        /* Form Styles */
        form {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        form input[type="text"], 
        form input[type="email"], 
        form input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        form button {
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #34495e;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
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

        /* Button Styles */
        .button {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }

        .button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <header>
        <h1 class="header-title">Manage Admins</h1>
        <a href="admin_logout.php" class="logout">Logout</a>
    </header>

    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_movies.php">Manage Movies</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="manage_admins.php" class="active">Manage Admins</a>
        <a href="view_reports.php">View Reports</a>
    </div>

    <div class="main-content">
        <h2>Manage Admins</h2>
        
        <?php if ($message): ?>
            <p class="error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($is_superadmin): ?>
            <form method="post" action="">
                <h3>Add New Admin</h3>
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <label>
                    <input type="checkbox" name="is_superadmin"> Superadmin
                </label>
                <button type="submit" name="add_admin">Add Admin</button>
            </form>
        <?php endif; ?>

        <h3>Existing Admins</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Superadmin</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo $row['is_superadmin'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <?php if ($is_superadmin && $row['id'] != $logged_in_admin_id): ?>
                            <a href="?delete_id=<?php echo htmlspecialchars($row['id']); ?>" class="button">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
