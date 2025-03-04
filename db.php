<?php

// Database connection details
$server_name = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "pdfsystem";

// Create connection
$conn = new mysqli($server_name, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>