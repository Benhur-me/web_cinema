<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$hostname = 'localhost';
$username = 'root';
$password = ''; // Update if necessary
$database = 'cinema_booking';

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$username = $user['name'] ?? 'User';
$user_email = $user['email'] ?? 'user@example.com';
$user_query->close();

// Fetch movies
$sql = "SELECT * FROM movies";
$result = $conn->query($sql);
$movies = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_movie'])) {
    $movie_id = intval($_POST['movie_id']);
    $num_tickets = intval($_POST['num_tickets']);
    $booking_date = date('Y-m-d H:i:s');

    // Fetch movie details for movie name
    $movie_query = $conn->prepare("SELECT title FROM movies WHERE id = ?");
    $movie_query->bind_param("i", $movie_id);
    $movie_query->execute();
    $movie_result = $movie_query->get_result();
    $movie = $movie_result->fetch_assoc();
    $movie_name = $movie['title'] ?? 'Unknown Movie';
    $movie_query->close();

    // Insert booking into the database
    $stmt = $conn->prepare("INSERT INTO bookings (movie_id, user_id, user_name, user_email, movie_name, booking_date, num_tickets) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssi", $movie_id, $user_id, $username, $user_email, $movie_name, $booking_date, $num_tickets);

    if ($stmt->execute()) {
        $success_message = "Congratulations! Your booking for '$movie_name' has been confirmed!";
    } else {
        $error_message = "Error booking movie: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Movie</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 40px;
        }

        .alert-message {
            margin-bottom: 20px;
            font-size: 20px;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            display: none; /* Start as hidden */
        }

        .alert-success {
            background-color: #2ecc71;
            color: #fff;
        }

        .alert-error {
            background-color: #e74c3c;
            color: #fff;
        }

        .movies-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .movie-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease;
            width: 300px;
        }

        .movie-card:hover {
            transform: translateY(-5px);
        }

        .movie-card img {
            width: 200px;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .movie-details {
            text-align: center;
        }

        .movie-title {
            font-size: 24px;
            color: #34495e;
        }

        .movie-genre, .movie-showtime, .movie-description {
            margin: 10px 0;
            font-size: 16px;
            color: #7f8c8d;
        }

        .movie-price {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            color: #2ecc71;
        }

        .book-now {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .book-now:hover {
            background-color: #c0392b;
        }

        .booking-form {
            display: none;
            background-color: #f9f9f9;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: max-height 0.5s ease;
            overflow: hidden;
            max-height: 0;
        }

        .booking-form.show {
            display: block;
            max-height: 500px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #34495e;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        .logout-btn {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
            float: right;
        }

        .logout-btn:hover {
            background-color: #2980b9;
        }
    </style>
    <script>
        function showBookingForm(movieId) {
            var form = document.getElementById('booking-form-' + movieId);
            form.classList.toggle('show');
        }

        function showMessage(type, message) {
            var messageDiv = document.querySelector(`.${type}-message`);
            messageDiv.textContent = message;
            messageDiv.style.display = 'block';

            // Automatically hide the message after 5 seconds
            setTimeout(function() {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        // Show welcome message temporarily
        function showWelcomeMessage(username) {
            var welcomeDiv = document.createElement('div');
            welcomeDiv.className = 'alert-message alert-success';
            welcomeDiv.textContent = "Welcome, " + username + "!";
            document.querySelector('.container').insertBefore(welcomeDiv, document.querySelector('.movies-container'));

            // Show the message
            welcomeDiv.style.display = 'block';

            // Automatically hide the message after 5 seconds
            setTimeout(function() {
                welcomeDiv.style.display = 'none';
            }, 5000);
        }

        // Call function to show welcome message on page load
        window.onload = function() {
            <?php if (isset($success_message)): ?>
                showMessage('alert-success', "<?php echo $success_message; ?>");
            <?php elseif (isset($error_message)): ?>
                showMessage('alert-error', "<?php echo $error_message; ?>");
            <?php endif; ?>
            showWelcomeMessage("<?php echo htmlspecialchars($username); ?>");
        };
    </script>
</head>
<body>
    <div class="container">
        <!-- Success and Error messages -->
        <div class="alert-message alert-success"></div>
        <div class="alert-message alert-error"></div>
        
        <a href="logout.php" class="logout-btn">Logout</a>
        <h1>Available Movies</h1>

        <div class="movies-container">
            <?php if (!empty($movies)): ?>
                <?php foreach ($movies as $movie): ?>
                    <div class="movie-card">
                        <!-- Movie Poster -->
                        <?php if (!empty($movie['poster'])): ?>
                            <img src="../admin/<?php echo htmlspecialchars($movie['poster']); ?>" alt="Movie Poster">
                        <?php else: ?>
                            <img src="../admin/default_poster.jpg" alt="Default Poster"> <!-- Default poster image -->
                        <?php endif; ?>

                        <div class="movie-details">
                            <h2 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h2>
                            <p class="movie-genre"><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
                            <p class="movie-description"><?php echo htmlspecialchars($movie['description']); ?></p>
                            <p class="movie-showtime"><strong>Showtime:</strong> <?php echo htmlspecialchars($movie['showtime'] ?? 'N/A'); ?></p>
                            <p class="movie-price">Price: $<?php echo htmlspecialchars(number_format($movie['price'], 2)); ?></p>
                            <button class="book-now" onclick="showBookingForm(<?php echo $movie['id']; ?>)">Book Now</button>
                        </div>

                        <div class="booking-form" id="booking-form-<?php echo $movie['id']; ?>">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="num_tickets">Number of Tickets:</label>
                                    <input type="number" name="num_tickets" id="num_tickets" min="1" required>
                                </div>
                                <input type="hidden" name="movie_id" value="<?php echo $movie['id']; ?>">
                                <button type="submit" name="book_movie">Confirm Booking</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No movies available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
