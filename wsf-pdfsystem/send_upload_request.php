<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'db.php';

session_start(); // Ensure session is started

if (isset($_POST['title_id']) && isset($_FILES['pdf_file']) && isset($_POST['email2']) && isset($_POST['name2'])) {
    $titleId = $_POST['title_id']; // Get the research ID
    $email = $_POST['email2']; // Get email from the form
    $fullname = $_POST['name2']; // Get fullname from the form

    // Get the workstation ID using the user's ID (assuming you have the user's ID in the session)
    $userId = $_SESSION['user_id']; // Replace with how you access the user's ID
    $stmt = $conn->prepare("SELECT workstation FROM wsf_user WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($workstationId);
    $stmt->fetch();
    $stmt->close();

    if (!$workstationId) {
        $_SESSION['status'] = "Error: User's workstation not found.";
        $_SESSION['error'] = true;
        header("Location: files.php");
        exit;
    }

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
        $mail->setFrom($email, $fullname); // Use a valid sender address
        $mail->addAddress('edmardelmonte12@gmail.com'); // Recipient email

        // Set email content
        $mail->isHTML(true);
        $mail->Subject = "New File Upload Request"; // Changed subject
        $mail->Body = "<h3>New File Upload Request</h3>
                        <p>User $fullname ($email) has uploaded a file for research title </p>
                        <p>Approve or Reject it on the Admin Dashboard =) ";
                        

        $file_name = $_FILES['pdf_file']['name'];
        $file_tmp = $_FILES['pdf_file']['tmp_name'];
        $mail->addAttachment($file_tmp, $file_name);

        // Send email
        $mail->send();

        // Insert the file request into the database
        $stmt = $conn->prepare("INSERT INTO files (filename, pdf, status, workstation, date_uploaded) VALUES (?, ?, 'pending', ?, NOW())");
        $stmt->bind_param("ssi", $file_name, $file_tmp, $workstationId); // Assuming 'pdf' column stores the file path

        if ($stmt->execute()) {
            $_SESSION['status'] = 'File upload request sent to admin successfully.';
            $_SESSION['error'] = false;
        } else {
            $_SESSION['status'] = "File upload request sent but failed to save to database. Error: " . $stmt->error;
            $_SESSION['error'] = true;
        }

        $stmt->close();
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        $_SESSION['error'] = true;
        header("Location: index.php");
        exit;
    }

} else {
    $_SESSION['status'] = "Error: Missing required information.";
    $_SESSION['error'] = true;
    header("Location: index.php");
    exit;
}
?>
