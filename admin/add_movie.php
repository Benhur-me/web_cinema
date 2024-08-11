<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $description = $_POST['description'];
    $release_date = $_POST['release_date'];
    $duration = $_POST['duration'];
    $poster = $_POST['poster'];

    $query = "INSERT INTO movies (title, genre, description, release_date, duration, poster) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $title, $genre, $description, $release_date, $duration, $poster);
    $stmt->execute();
    header("Location: manage_movies.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Movie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: white;
            padding: 1em 0;
            text-align: center;
        }

        main {
            padding: 2em;
            max-width: 800px;
            margin: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        form {
            display: grid;
            gap: 1em;
        }

        label {
            font-weight: bold;
            margin-bottom: 0.5em;
            display: block;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 0.75em;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .back-icon {
            position: absolute;
            top: 20px;
            left: 20px;
            display: inline-flex;
            align-items: center;
            color: #007bff; /* Blue color */
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .back-icon:hover {
            color: #0056b3; /* Darker blue on hover */
        }

        .back-icon i {
            margin-right: 8px; /* Space between icon and text */
        }
    </style>
</head>
<body>
    <header>
    
        <a href="admin_dashboard.php" class="back-icon">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        
   
        <h1>Add Movie</h1>
    </header>
    <main>
        <h2>Add New Movie</h2>
        <form action="add_movie.php" method="POST">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="genre">Genre:</label>
            <input type="text" id="genre" name="genre">

            <label for="description">Description:</label>
            <textarea id="description" name="description"></textarea>

            <label for="release_date">Release Date:</label>
            <input type="date" id="release_date" name="release_date">

            <label for="duration">Duration (min):</label>
            <input type="number" id="duration" name="duration">

            <label for="poster">Poster URL:</label>
            <input type="text" id="poster" name="poster">

            <input type="submit" value="Add Movie">
        </form>
    </main>
</body>
</html>
