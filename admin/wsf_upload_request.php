<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'db.php'; // Include your database connection file

session_start(); // Ensure session is started

// Get the POST data
$email = $_POST['email2']; 
$name = $_POST['name2'];
$file_id = $_POST['file_id']; // Assuming you're passing the file ID

if (!$email || !$name || !$file_id) { // Handle the case where the email or fullname is not found
    $em = "Request information not found.";
    $_SESSION['status'] = $em;
    $_SESSION['error'] = true;
    header("Location: upload_files.php");
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
    $mail->Subject = "Approval of Upload Request";
    $mail->Body = "<h3>Request Results</h3>
                    <p>Hello $name, your upload request for this workstation file has been approved.</p>
                    <p>The PDF has been thoroughly verified and validated.</p>
                    <p>ALL RIGHTS RESERVED</p>";

    // Send the email
    $mail->send();

    $status = 'approved'; 
    $update_stmt = $conn->prepare("UPDATE files SET status = ? WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param('si', $status, $file_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        error_log("Error preparing update statement: " . $conn->error);
    }

    // Redirect to the files page after success
    $_SESSION['status'] = "Approval email sent successfully.";
    $_SESSION['error'] = false;
    header("Location: files.php");
    exit;
} catch (Exception $e) {
    // Handle error
    $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    $_SESSION['error'] = true;
    header("Location: upload_files.php");
    exit;
}
?>
