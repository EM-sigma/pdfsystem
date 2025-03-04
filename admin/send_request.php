<?php
// Include the database connection
include 'db.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and collect form data
    $titlename = isset($_POST['titlename']) ? trim($_POST['titlename']) : '';
    $college = isset($_POST['college']) ? trim($_POST['college']) : '';
    $program = isset($_POST['program']) ? trim($_POST['program']) : '';
    $status = 'approved'; // Default status

    // Handle the uploaded file (approval sheet)
    if (isset($_FILES['approvalsheet']) && $_FILES['approvalsheet']['error'] === UPLOAD_ERR_OK) {
        // File details
        $fileTmpPath = $_FILES['approvalsheet']['tmp_name'];
        $fileName = $_FILES['approvalsheet']['name'];
        $fileSize = $_FILES['approvalsheet']['size'];
        $fileType = $_FILES['approvalsheet']['type'];

        // Open the file and read it as binary data
        $fileData = file_get_contents($fileTmpPath);
        
        // Prepare the SQL query to insert the request data into the table
        $sql = "INSERT INTO research_titles (titlename, college, program, approvalsheet, status ) 
                VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind the parameters
            $stmt->bind_param("sssbs", $titlename, $college, $program, $fileData, $status);

            // Execute the query
            if ($stmt->execute()) {
                // Redirect to index.php with a success message
                header("Location: research_titles_form.php?message=success");
                exit();
            } else {
                // Redirect to index.php with an error message
                header("Location: research_titles_form.php?message=error");
                exit();
            }

            // Close the statement
            $stmt->close();
        } else {
            // Redirect to index.php with an error message
            header("Location: research_titles_form.php?message=error_preparing_query");
            exit();
        }
    } else {
        // Redirect to index.php with an error message for the file upload
        header("Location: research_titles_form.php?message=error_file_upload");
        exit();
    }

    // Close the database connection
    $conn->close();
}
?>
