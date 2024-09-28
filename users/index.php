<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Booking</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
        }

        /* Centered Container for Buttons */
        .button-container {
            text-align: center;
            padding: 20px;
            background-color: #333;
        }

        .button-container a {
            display: inline-block;
            color: #fff;
            background-color: #3498db;
            padding: 10px 20px;
            margin: 0 10px; /* Space between buttons */
            border-radius: 5px;
            text-decoration: none;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }

        .button-container a:hover {
            background-color: #2980b9;
        }

        /* Welcome Section */
        .welcome-section {
            text-align: center;
            padding: 80px 20px;
            background-color: #fff;
        }

        .welcome-section h1 {
            font-size: 48px;
            color: #3498db;
            margin-bottom: 20px;
            animation: fade-in 1.2s ease;
        }

        .welcome-section p {
            font-size: 22px;
            color: #7f8c8d;
            max-width: 700px;
            margin: 0 auto;
            animation: fade-in 1.5s ease;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: white;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>

<!-- Buttons Container -->
<div class="button-container">
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
</div>

<!-- Welcome Section -->
<div class="welcome-section">
    <h1>Welcome to Cinema Booking</h1>
    <p>Book your favorite movies, manage your account, and enjoy a seamless cinema experience. Login or register to get started!</p>
</div>

<!-- Footer -->
<footer>
    &copy; 2024 Cinema Booking. All rights reserved.
</footer>

</body>
</html>
