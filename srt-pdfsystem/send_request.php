<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'db.php';

session_start(); 

if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['title']) && isset($_POST['program']) && isset($_POST['college']) && isset($_POST['title_id'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $title = $_POST['title'];
    $program = $_POST['program'];
    $college = $_POST['college'];
    $titleId = $_POST['title_id'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $em = "Invalid email format";
        $_SESSION['status'] = $em;
        $_SESSION['error'] = true;
        header("Location: index.php");
        exit;
    }

    // Check for empty fields
    if (empty($name) || empty($title) || empty($program) || empty($college)) {
        $em = "Fill out all required fields";
        $_SESSION['status'] = $em;
        $_SESSION['error'] = true;
        header("Location: index.php");
        exit;
    }
    
        // Send email using PHPMailer
    $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jenniferbaguls@gmail.com';  // Your Gmail address
            $mail->Password = 'jzaf abrh stdm spmv';  // Use the generated App Password
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
            $mail->setFrom($email, $name);
            $mail->addAddress('edmardelmonte12@gmail.com');  // Recipient email

            // Set email content
            $mail->isHTML(true);
            $mail->Subject = "Request for $title";
            $mail->Body = "<h3>Contact Form</h3>
                            <p><strong>Name</strong>: $name</p>
                            <p><strong>Email</strong>: $email</p>
                            <p><strong>Title</strong>: $title</p>
                            <p><strong>Program</strong>: $program</p>
                            <p><strong>College</strong>: $college</p>";

            // Send email
            $mail->send();

            $sql = "INSERT INTO research_titles (titlename, college, program, status, date_uploaded, approvalsheet) VALUES (?, ?, ?, 'pending', NOW(), ?)";
            $stmt = $conn->prepare($sql);
    
            if ($stmt === false) {
                $_SESSION['status'] = "Error saving request: " . $conn->error;
                $_SESSION['error'] = true;
                header("Location: index.php");
                exit;
            }
    
            // Assuming you are uploading the PDF file separately and have the file data in a variable $pdfData
            $stmt->bind_param('ssss', $title, $college, $program, $titleId); 
    
            if ($stmt->execute()) {
                $_SESSION['status'] = 'Message has been sent successfully and wait for admin response for approval!';
                $_SESSION['error'] = false;
                header("Location: index.php");
            } else {
                $_SESSION['status'] = "Error saving request: " . $stmt->error;
                $_SESSION['error'] = true;
                header("Location: index.php");
            }
    
            $stmt->close();
    
        } catch (Exception $e) {
            $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $_SESSION['error'] = true;
            header("Location: index.php");
        }
    } else {
        $_SESSION['status'] = "Error saving request: ". $stmt->error;
        $_SESSION['error'] = true;
        header("Location: index.php");
    }

    $stmt->close();

?>
