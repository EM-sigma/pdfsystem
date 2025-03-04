<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['srt_user'])) {
    // If the user is not logged in, return an empty array
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['srt_user'];

// Prepare the SQL query to fetch notifications
$sql = "SELECT id, message_text, is_read FROM messages WHERE recipient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];

// Fetch notifications and store them in an array
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Return the notifications as a JSON response
echo json_encode($notifications);
?>