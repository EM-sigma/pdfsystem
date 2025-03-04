<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();  // Ensure session is started

if (isset($_POST['full_name']) && isset($_POST['email']) && isset($_POST['subject']) && isset($_POST['message']) && isset($_POST['page'])) {
    
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $text = $_POST['message'];
    $page = $_POST['page'];  // Capture the selected page

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $em = "Invalid email format";
        $_SESSION['status'] = $em;
        $_SESSION['error'] = true;
        header("Location: sign-up.php");
        exit;
    }

    if (empty($name) || empty($subject) || empty($text) || empty($page)) {
        $em = "Fill out all required entry fields";
        $_SESSION['status'] = $em;
        $_SESSION['error'] = true;
        header("Location: sign-up.php");
        exit;
    }

    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 2;
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jenniferbaguls@gmail.com';
        $mail->Password = 'jzaf abrh stdm spmv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
    
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom($email, $name);
        $mail->addAddress('edmardelmonte12@gmail.com');
    
        // Include the page selection in the email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "<h3>New Sign Up Information</h3>
                       <p><strong>Name</strong>: $name</p>
                       <p><strong>Email</strong>: $email</p>
                       <p><strong>Subject</strong>: $subject</p>
                       <p><strong>Message</strong>: $text</p>
                       <p>I am requesting to register for sign up for the </strong>$page</strong> files</p>";  // Include selected page
    
        $mail->send();
        $_SESSION['status'] = 'Message has been sent successfully!';
        $_SESSION['error'] = false;
        header("Location: sign-up.php");
    } catch (Exception $e) {
        $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        $_SESSION['error'] = true;
        header("Location: sign-up.php");
    }
}

?>
