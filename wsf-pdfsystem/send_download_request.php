<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'db.php';

session_start();  // Ensure session is started

if (isset($_POST['name2']) && isset($_POST['email2']) && isset($_POST['research_id'])) {

    // Capture form fields
    $name = $_POST['name2'];
    $email = $_POST['email2'];
    $researchId = $_POST['research_id']; // Get the research ID

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $em = "Invalid email format";
        $_SESSION['status'] = $em;
        $_SESSION['error'] = true;
        header("Location: index.php");
        exit;
    }

    // Check for empty fields
    if (empty($name)) {
        $em = "Fill out all required fields";
        $_SESSION['status'] = $em;
        $_SESSION['error'] = true;
        header("Location: index.php");
        exit;
    }

    // Fetch the title name from the database using the research ID
    $stmt = $conn->prepare("SELECT filename FROM files WHERE id =?");
    $stmt->bind_param("i", $researchId);
    $stmt->execute();
    $stmt->bind_result($title); // Bind the result to $title
    $stmt->fetch();
    $stmt->close();

    if (!$title) { // Handle the case where the title is not found
        $em = "Research title not found.";
        $_SESSION['status'] = $em;
        $_SESSION['error'] = true;
        header("Location: index.php");
        exit;
    }

    // Store the request in the srt_requests table
    $status = 'pending'; // Default status
    $stmt = $conn->prepare("INSERT INTO wsf_requests (filename, email, fullname, status) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $title, $email, $name, $status);

    if ($stmt->execute()) {
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
            $mail->Subject = "Workstation User Download Request for $title"; // Use the fetched $title
            $mail->Body = "<h3>Contact Form</h3>
                            <p><strong>Name</strong>: $name</p>
                            <p><strong>Email</strong>: $email</p>
                            <p><strong>Workstation File</strong>: $title</p>"; // Include the title in the email body

            // Send email
            $mail->send();

            $_SESSION['status'] = 'Message has been sent successfully and wait for admin response for approval!';
            $_SESSION['error'] = false;
            header("Location: index.php");
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
} else {
    $_SESSION['status'] = "Error: Missing required information.";
    $_SESSION['error'] = true;
    header("Location: index.php");
}
?>