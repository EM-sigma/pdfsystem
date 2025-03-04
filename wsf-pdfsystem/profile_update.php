<?php
include "db.php"; // Include your database connection

session_start();
if (!isset($_SESSION['wsf_user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['wsf_user'];

// Fetch user data from the database
$stmt = $conn->prepare("SELECT username, password, image FROM wsf_user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_username, $db_password, $db_image);
$stmt->fetch();
$stmt->close();

// Initialize the message
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    
    // Flag to track if anything changed
    $changed = false;
    
    // Check if username has changed
    if ($new_username !== $db_username) {
        $changed = true;
    }
    
    // Check if password has changed
    if (!empty($new_password)) {
        $new_password = password_hash($new_password, PASSWORD_DEFAULT);
        $changed = true;
    } else {
        // If no password provided, keep the old password
        $new_password = $db_password;
    }

    // Image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // If a new image is uploaded, read the image file as binary data
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
        $changed = true;
    } else {
        // Retain existing image if no new image is uploaded
        $image_data = $db_image;
    }

    // Update the database if any changes have been made
    if ($changed) {
        $stmt = $conn->prepare("UPDATE wsf_user SET username = ?, password = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssbi", $new_username, $new_password, $image_data, $user_id);
        $stmt->send_long_data(2, $image_data); // Send the long data for the image
        $stmt->execute();
        $stmt->close();

        // Update session values
        $_SESSION['username'] = $new_username;

        // Prepare a success message
        $message = "Profile updated successfully!";
    } else {
        $message = "No changes were made.";
    }

    // Redirect after successful update (with success message)
    header("Location: profile.php?message=" . urlencode($message)); // Pass message via URL parameter
    exit();
}
?>