<?php
// Database connection
$hostname = 'localhost';
$username = 'root';
$password = ''; // Update if necessary
$database = 'cinema_booking';

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $num_tickets = intval($_POST['num_tickets']);
    $booking_date = date('Y-m-d H:i:s');

    // Prepare and execute the booking query
    $stmt = $conn->prepare("INSERT INTO bookings (movie_id, user_name, user_email, booking_date, num_tickets) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $movie_id, $user_name, $user_email, $booking_date, $num_tickets);

    if ($stmt->execute()) {
        $success_message = "Your booking has been confirmed!";
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

        h1 {
            text-align: center;
            font-size: 36px;
            color: #2c3e50;
            margin-bottom: 40px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap; /* Allows wrapping */
            justify-content: center; /* Centers movie cards */
            gap: 30px; /* Adjusted spacing */
        }

        .movie-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 300px; /* Fixed width for consistency */
            transition: transform 0.3s ease;
        }

        .movie-card:hover {
            transform: translateY(-5px);
        }

        .movie-card img {
            width: 100%; /* Full width of the card */
            height: 400px; /* Fixed height for consistency */
            border-radius: 8px;
            object-fit: cover; /* Keeps image aspect ratio */
            margin-bottom: 20px;
        }

        .movie-details {
            text-align: center;
        }

        .movie-title {
            font-size: 24px;
            color: #34495e;
            margin-bottom: 10px;
        }

        .movie-genre, .movie-showtime, .movie-description {
            margin: 5px 0;
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
            max-height: 500px; /* Adjust accordingly */
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

        .success-message, .error-message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            font-size: 16px;
            display: none;
        }

        .success-message {
            background-color: #2ecc71;
            color: #fff;
        }

        .error-message {
            background-color: #e74c3c;
            color: #fff;
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
        }

        <?php if (isset($success_message)): ?>
        window.onload = function() {
            showMessage('success', "<?php echo $success_message; ?>");
        };
        <?php elseif (isset($error_message)): ?>
        window.onload = function() {
            showMessage('error', "<?php echo $error_message; ?>");
        };
        <?php endif; ?>
    </script>
</head>
<body>
    <h1>Available Movies</h1>

    <div class="container">
        <?php if (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <div class="movie-card">
                    <!-- Movie Poster -->
                    <?php if (!empty($movie['poster'])): ?>
                        <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="Movie Poster">
                    <?php else: ?>
                        <img src="uploads/default-poster.jpg" alt="Default Poster">
                    <?php endif; ?>
                    
                    <div class="movie-details">
                        <h2 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h2>
                        <p class="movie-genre"><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
                        <p class="movie-showtime"><strong>Show Time:</strong> <?php echo htmlspecialchars($movie['show_time']); ?></p>
                        <p class="movie-description"><?php echo htmlspecialchars($movie['description']); ?></p>
                        <p class="movie-price"><strong>Price:</strong> $<?php echo htmlspecialchars($movie['price']); ?></p>
                        
                        <!-- Book Now Button -->
                        <button class="book-now" onclick="showBookingForm(<?php echo $movie['id']; ?>)">Book Now</button>

                        <!-- Booking Form -->
                        <div id="booking-form-<?php echo $movie['id']; ?>" class="booking-form">
                            <h3>Book Movie: <?php echo htmlspecialchars($movie['title']); ?></h3>
                            <form action="user_interface.php" method="post">
                                <input type="hidden" name="movie_id" value="<?php echo $movie['id']; ?>">
                                <div class="form-group">
                                    <label for="user_name">Your Name</label>
                                    <input type="text" name="user_name" id="user_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="user_email">Your Email</label>
                                    <input type="email" name="user_email" id="user_email" required>
                                </div>
                                <div class="form-group">
                                    <label for="num_tickets">Number of Tickets</label>
                                    <input type="number" name="num_tickets" id="num_tickets" required min="1">
                                </div>
                                <button type="submit" name="book_movie" class="book-now">Confirm Booking</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No movies available.</p>
        <?php endif; ?>

        <div class="success-message"></div>
        <div class="error-message"></div>
    </div>
</body>
</html>
