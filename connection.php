<?php
// Database configuration
$host = 'localhost'; // Your database host
$db = 'restaurantpos'; // Your database name
$user = 'root'; // Your database username
$pass = '2000'; // Your database password

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>