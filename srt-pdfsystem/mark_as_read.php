<?php
session_start();
include 'db.php';

if (!isset($_SESSION['srt_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

if (isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];
    $user_id = $_SESSION['srt_user'];

    // Update the notification status to "read"
    $sql = "UPDATE messages SET is_read = 1 WHERE id = ? AND recipient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notification_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to mark as read']);
    }
}
?>
