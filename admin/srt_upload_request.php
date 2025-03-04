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
$title_id = $_POST['title_id']; // Get the title ID

var_dump($title_id); 

if (!$email || !$name || !$title_id) { // Handle the case where the email or fullname is not found
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
                        <p>Hello $name, your upload request for this research title has been approved</p>
                        <p>The pdf has been thoroughly verified and validated</p>
                        <p>ALL RIGHTS RESERVED</p>";

        $mail->send();

        $status = 'approved'; 
        $update_stmt = $conn->prepare("UPDATE research_titles SET status = ? WHERE id = ?");
    
        if (!$update_stmt) {
            // Log the error for debugging
            error_log("Error preparing update statement: " . $conn->error); 
            throw new Exception("Error updating research title status."); 
        }
    
        $update_stmt->bind_param('si', $status, $title_id);
    
        if (!$update_stmt->execute()) {
            // Log the error for debugging
            error_log("Error executing update: " . $update_stmt->error); 
            throw new Exception("Error updating research title status.");
        }
        $update_stmt->close();
        header("Location: files.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        $_SESSION['error'] = true;
        header("Location: files.php");
        exit;
} 
?>
