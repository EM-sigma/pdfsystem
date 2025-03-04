<?php
// download.php

include 'db.php'; // Your database connection

if (isset($_GET['id'])) {
    $file_id = $_GET['id'];

    // 1. Get file information (including BLOB and filename)
    $stmt = $conn->prepare("SELECT filename, pdf FROM files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->bind_result($filename, $pdf_data);
    $stmt->fetch();
    $stmt->close();

    if ($pdf_data !== null) {  // Check if BLOB data exists

        // 2. Set headers for file download
        header('Content-Type: application/pdf'); // Force PDF download
        header('Content-Disposition: attachment; filename="' . $filename . '"'); // Set filename
        header('Content-Length: ' . strlen($pdf_data)); // Set content length (important for large files)
        header('Cache-Control: private, max-age=0'); // Disable caching
        header('Pragma: public');

        // 3. Output the BLOB data
        echo $pdf_data;

    } else {
        // 4. Handle file not found
        echo "File not found."; // Or redirect to an error page
        // You can also log this error for debugging.
        error_log("File not found: ID = " . $file_id);
    }

} else {
    // 5. Handle invalid request (no ID provided)
    echo "Invalid request. File ID missing.";
}

$conn->close(); // Close the database connection
?>