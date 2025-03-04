<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'db.php';

session_start(); // Ensure session is started

if (isset($_POST['file_id']) && isset($_FILES['pdf_file'])) {
    $fileId = $_POST['file_id']; // Get the research ID
    // Fetch the title name from the database using the research ID
    error_log("File ID received: " . $fileId); 
    $stmt = $conn->prepare("SELECT email, fullname FROM wsf_requests WHERE id =?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $stmt->bind_result($email, $fullname);
    $stmt->fetch();
    $stmt->close();

    if (!$email || !$fullname) { // Handle the case where the email or fullname is not found
        $em = "Request information not found.";
        $_SESSION['status'] = $em;
        $_SESSION['error'] = true;
        header("Location: files.php");
        exit;
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

        // Set email content
        $mail->isHTML(true);
        $mail->Subject = "Approval of Download Request";
        $mail->Body = "<h3>Request Results</h3>
                        <p>Hello $fullname, your download request for this file has been approved</p>
                        <p>Below is the file attachment that is only exclusive for you only.</p>
                        <p>Do NOTE that if the file gets leaked you will be sanctioned for interrogation </p>";

        $file_name = $_FILES['pdf_file']['name'];
        $file_tmp = $_FILES['pdf_file']['tmp_name'];
        $mail->addAttachment($file_tmp, $file_name);

        // Send email
        $mail->send();
        
        // Update the status to 'approved'
        $stmt = $conn->prepare("UPDATE wsf_requests SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $fileId);

        if ($stmt->execute()) {
            $_SESSION['status'] = 'Message has been sent successfully and request approved.';
            $_SESSION['error'] = false;
        } else {
            $_SESSION['status'] = "Message sent but failed to approve request. Error: " . $stmt->error;
            $_SESSION['error'] = true;
        }
        
        $stmt->close();
        header("Location: files.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        $_SESSION['error'] = true;
        header("Location: files.php");
        exit;
    }

} else {
    $_SESSION['status'] = "Error: Missing required information.";
    $_SESSION['error'] = true;
    header("Location: files.php");
    exit;
}
?>
