<?php
// Assume you have a connection to your database
include "db.php";

// Check if 'id' is passed via GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);  // Ensure the ID is an integer to prevent SQL injection

    // Fetch the PDF content by ID from the database
    $sql = "SELECT pdf FROM files WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    // Check if the file exists in the database
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($pdf);
        $stmt->fetch();

        // Clean the output buffer to avoid issues with headers
        ob_clean();
        flush();

        // Set appropriate headers for PDF
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=document.pdf");
        echo $pdf;  // Output the raw PDF content to the browser
    } else {
        echo "File not found.";
    }
    $stmt->close();
} else {
    echo "No ID provided.";
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Viewer</title>
    <!-- Include Syncfusion's stylesheets and scripts -->
    <link href="https://cdn.syncfusion.com/ej2/20.3.56/material.css" rel="stylesheet">
    <script src="https://cdn.syncfusion.com/ej2/20.3.56/dist/ej2.min.js"></script>
</head>
<body>
    <!-- Div for PDF Viewer -->
    <div id="pdfviewer" style="height: 600px;"></div>

    <script>
        window.onload = function () {
            var pdfId = 1;  // Example ID, replace dynamically if necessary

            // Fetch the PDF file from the PHP endpoint
            fetch('/path-to-your-php-endpoint.php?id=' + pdfId)
                .then(response => response.blob())  // Convert response to blob (PDF file)
                .then(pdfBlob => {
                    // Create a temporary URL for the blob
                    var pdfUrl = URL.createObjectURL(pdfBlob);

                    // Initialize the PdfViewer
                    var pdfViewer = new ej.pdfviewer.PdfViewer({
                        documentPath: pdfUrl,  // Provide the URL pointing to the PDF Blob
                        toolbarSettings: { 
                            showToolbar: false  // Hide the toolbar completely
                        }
                    });

                    // Append PDF viewer to the div
                    pdfViewer.appendTo('#pdfviewer');
                })
                .catch(error => {
                    console.error("Error fetching the PDF:", error);
                });
        };
    </script>

</body>
</html>
