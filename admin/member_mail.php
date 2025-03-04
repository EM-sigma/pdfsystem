<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'db.php';

session_start(); // Ensure session is started

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST["fullname"];
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $workstation = $_POST["workstation"];
    $form_type = $_POST["form_type"];
    

    if (!$fullname || !$email || !$username || !$password || !$workstation || !$form_type) {
        $_SESSION['status'] = "Missing required information.";
        $_SESSION['error'] = true;
        header("Location: files.php"); // Or wherever your form is
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if ($form_type === "wsf") {
        // WSF User Insertion
        if ($_FILES['image']['error'] == 0) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $stmt = $conn->prepare("INSERT INTO wsf_user (username, password, image, workstation) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashedPassword, $imageData, $workstation);

            if (!$stmt->execute()) {
                $_SESSION['status'] = "Error inserting wsf_user: " . $stmt->error;
                $_SESSION['error'] = true;
                header("Location: files.php?form_reset=false");
                exit;
            }
        }
    } elseif ($form_type === "srt") {
        // SRT User Insertion
        if ($_FILES['image']['error'] == 0) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $stmt = $conn->prepare("INSERT INTO srt_users (username, password, image) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $imageData);

            if (!$stmt->execute()) {
                $_SESSION['status'] = "Error inserting srt_users: " . $stmt->error;
                $_SESSION['error'] = true;
                header("Location: files.php?form_reset=false");
                exit;
            }
            $conn->query("ALTER TABLE srt_users AUTO_INCREMENT = 1");
        }
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // Disable verbose debug output
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jenniferbaguls@gmail.com'; // Your Gmail address
        $mail->Password = 'jzaf abrh stdm spmv'; // Use the generated App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Disable SSL certificate verification temporarily (for testing only)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Set recipient email
        $mail->setFrom('jenniferbaguls@gmail.com', 'Jennifer Bagulaya Abogaa'); // Use a valid sender address
        $mail->addAddress($email); // Recipient email

        // Set email content based on form type
        $mail->isHTML(true);

        if ($form_type === "wsf") {
            $mail->Subject = "Workstation Files User Account Created";
            $mail->Body = "<h3>Workstation Files Account Created</h3>
                           <p>Hello $fullname,</p>
                           <p>Your Workstation Files account has been created with the following details:</p>
                           <p>Username: $username</p>
                           <p>Password: $password</p>
                           <p>Workstation: $workstation</p>
                           <p>ALL RIGHTS RESERVED</p>";
        } elseif ($form_type === "srt") {
            $mail->Subject = "Student Research Title User Account Created";
            $mail->Body = "<h3>Student Research Title Account Created</h3>
                           <p>Hello $fullname,</p>
                           <p>Your Student Research Title account has been created with the following details:</p>
                           <p>Username: $username</p>
                           <p>Password: $password</p>
                           <p>Workstation: $workstation</p>
                           <p>ALL RIGHTS RESERVED</p>";
        } else {
            $_SESSION['status'] = "Invalid form type.";
            $_SESSION['error'] = true;
            header("Location: files.php"); // Or your form page
            exit;
        }

        // Send email
        $mail->send();
        $_SESSION['status'] = "Email sent successfully!";
        $_SESSION['error'] = false;
        header("Location: files.php"); // Or your form page
        exit;

    } catch (Exception $e) {
        $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        $_SESSION['error'] = true;
        header("Location: files.php"); // Or your form page
        exit;
    }
} else {
    $_SESSION['status'] = "Invalid request method.";
    $_SESSION['error'] = true;
    header("Location: files.php"); // Or your form page
    exit;
}
?>