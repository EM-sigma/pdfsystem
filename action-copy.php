<?php
session_start(); // Start the session at the very beginning

include "db.php";

// Check if the user is already logged in
if (isset($_SESSION['login_for'])) {
    // If the user is logged in, redirect to their respective page
    switch ($_SESSION['login_for']) {
        case 'Admin':
            header("Location: admin/");
            exit();
        case 'Student Research Title':
            header("Location: srt-pdfsystem/");
            exit();
        case 'Workstation Files':
            header("Location: wsf-pdfsystem/");
            exit();
    }
}

// Database connection (replace these with your actual credentials)
include "db.php";

// Sanitize and validate form data
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];
$loginFor = $_POST['loginFor'];

// Set table based on login type
switch ($loginFor) {
    case 'Admin':
        $table = 'admin';
        break;
    case 'Student Research Title':
        $table = 'srt_users';
        break;
    case 'Workstation Files':
        $table = 'wsf_user';
        break;
    default:
        $_SESSION['login_error'] = "Invalid login type!";
        header("Location: login.php");
        exit();
}

// Prepare and execute the query
$query = "SELECT * FROM $table WHERE username = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    $_SESSION['login_error'] = "Error preparing statement.";
    header("Location: login.php");
    exit();
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Check password
    if (password_verify($password, $user['password'])) {
    
        // Set session variables based on user type
        $_SESSION['login_for'] = $loginFor;

        switch ($loginFor) {
            case 'Admin':
                $_SESSION['admin_id'] = $user['admin_id'];
                $_SESSION['admin_username'] = $user['admin'];
                header("Location: admin/");
                exit();

            case 'Student Research Title':
                $_SESSION['srt_id'] = $user['srt_id'];
                $_SESSION['srt_username'] = $user['srt_username'];
                header("Location: srt-pdfsystem/");
                exit();

            case 'Workstation Files':
                $_SESSION['wsf_id'] = $user['wsf_id'];
                $_SESSION['wsf_username'] = $user['wsf_username'];
                $workstation_id = $user['workstation']; 

                if ($workstation_id !== null && $workstation_id !== "") {
                    $_SESSION['workstation_number'] = (int)$workstation_id;
                } else {
                    $_SESSION['workstation_number'] = null;
                    echo "Workstation NOT assigned. <br>";
                }
                header("Location: wsf-pdfsystem/");
                exit();

            default:
                $_SESSION['login_error'] = "Invalid login type during session.";
                header("Location: login.php");
                exit();
        }

    } else {
        // Invalid password
        $_SESSION['login_error'] = "Invalid password!";
        header("Location: login.php");
        exit();
    }
} else {
    // Invalid username
    $_SESSION['login_error'] = "Invalid username!";
    header("Location: login.php");
    exit();
}

// Clean up
$stmt->close();
$conn->close();
?>
