<?php
session_start(); 
// Database connection (replace these with your actual credentials)
include "db.php";

// Sanitize and validate form data
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];
$loginFor = $_POST['loginFor'];

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
        echo "Invalid login type!";
        exit();
}

$query = "SELECT * FROM $table WHERE username = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo "Error preparing statement.";
    exit(); 
}

$stmt->bind_param('s', $username); 
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
    
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_for'] = $loginFor;
        $_SESSION['user_id'] = $user['id']; 

        switch ($loginFor) {
            case 'Admin':
                $_SESSION['admin'] = 'Admin';
                header("Location: admin/"); 
                exit();

            case 'Student Research Title':
                $_SESSION['srt_user'] = $_SESSION['user_id']; 
                $_SESSION['username'] = $_SESSION['username'];
                header("Location: srt-pdfsystem/"); 
                exit();

            case 'Workstation Files':
                $_SESSION['wsf_user'] = $user['id'];  
                $workstation_id = $user['workstation']; 

                if ($workstation_id !== null && $workstation_id !== "") {
                    $workstation_id = (int)$workstation_id;
                    $_SESSION['workstation_number'] = $workstation_id;
                } else {
                    $_SESSION['workstation_number'] = null; 
                    echo "Workstation NOT assigned. <br>"; 
                }
                header("Location: wsf-pdfsystem/"); 
                exit();
            default:
                echo "Error: Invalid login type during session.";
                exit();
        }

    } else {
        $_SESSION['login_error'] = "Invalid password!";
        header("Location: login.php");
        exit();
    }
} else {
    $_SESSION['login_error'] = "Invalid username!";
    header("Location: login.php"); 
    exit();
}

$stmt->close();
$conn->close();
?>
