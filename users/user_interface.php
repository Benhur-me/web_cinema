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
$success_message = '';
$error_message = '';
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
            display: none;
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
            overflow: hidden;
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

        /* Flex container for user initial and logout button */
        .user-info {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        /* Circle style for user initial */
        .user-initial-circle {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #3498db;
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            margin-right: 15px; /* Space between circle and logout button */
        }

        .logout-btn {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
        }

        .logout-btn:hover {
            background-color: #2980b9;
        }
    </style>
    <script>
        // Show the booking form for the selected movie
        function showBookingForm(movieId) {
            var bookingForm = document.getElementById('booking-form-' + movieId);
            bookingForm.style.display = bookingForm.style.display === "block" ? "none" : "block";
        }

        // Calculate and show the total price in a pop-up before submitting
        function calculateAndShowPrice(movieId, moviePrice) {
    var numTickets = document.querySelector(`#booking-form-${movieId} #num_tickets`).value;
    if (numTickets <= 0) {
        alert("Please enter a valid number of tickets.");
        return false;
    }
    
    var totalPrice = numTickets * moviePrice;
    
    // Show pop-up with OK and Cancel options
    var confirmBooking = confirm("The total amount to pay is: $" + totalPrice.toFixed(2) + " at the cinema cashier.\n\nDo you want to proceed?");
    
    // If user clicks Cancel, stop the form submission
    if (!confirmBooking) {
        return false;
    }

    return true; // User clicked OK, proceed with booking
}


        // Show success or error message on page load if they exist
        window.onload = function() {
            var successMessage = document.querySelector('.alert-success');
            var errorMessage = document.querySelector('.alert-error');

            <?php if (!empty($success_message)): ?>
                successMessage.textContent = "<?php echo addslashes($success_message); ?>";
                successMessage.style.display = 'block';
                setTimeout(() => { successMessage.style.display = 'none'; }, 5000);
            <?php elseif (!empty($error_message)): ?>
                errorMessage.textContent = "<?php echo addslashes($error_message); ?>";
                errorMessage.style.display = 'block';
                setTimeout(() => { errorMessage.style.display = 'none'; }, 5000);
            <?php endif; ?>
        };
    </script>
</head>
<body>
    <div class="container">
        <!-- Success and Error Messages -->
        <div class="alert-message alert-success"></div>
        <div class="alert-message alert-error"></div>

        <!-- User Info -->
        <div class="user-info">
            <div class="user-initial-circle"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
            <form action="logout.php" method="POST">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>

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

                        <div class="booking-form" id="booking-form-<?php echo $movie['id']; ?>" style="display: none;">
                            <form method="POST" onsubmit="return calculateAndShowPrice(<?php echo $movie['id']; ?>, <?php echo $movie['price']; ?>)">
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
