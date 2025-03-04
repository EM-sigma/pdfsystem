<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'db.php';

if (!isset($_SESSION['wsf_user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['wsf_user'];
$username = $_SESSION['username'];
$workstation_id = $_SESSION['workstation_number']; // Retrieve workstation ID from session

$upload_message = ""; // Initialize message variable



if (isset($_POST["submit"])) {
    $target_dir = "upload/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a actual PDF file or fake file
    $check = filesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $upload_message = "File is not a PDF.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 16000000) {
        $upload_message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($fileType != "pdf") {
        $upload_message = "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $upload_message =  "Sorry, your file was not uploaded. " . $upload_message; // Concatenate error message

    // if everything is ok, try to upload file
    } else {
        $filename =  basename($_FILES["fileToUpload"]["name"]);
        $pdf_content = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);

        $stmt = $conn->prepare("INSERT INTO files (filename, pdf, status, workstation, date_uploaded) VALUES (?, ?, 'pending', ?, NOW())");
        if ($stmt) {
            if (isset($_SESSION['workstation_number'])) { // Check if it's set in the session
                $workstation_id = $_SESSION['workstation_number'];
                $stmt->bind_param("sbi", $filename, $pdf_content, $workstation_id); // Removed extra arguments and workstation_id is now bound
            }
            if ($stmt->execute()) {
                $upload_message = "The file " . $filename . " has been uploaded.";
            } else {
                $upload_message = "Sorry, there was an error uploading your file. Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $upload_message = "Error preparing statement: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Files</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
            
        body {
            font-family: sans-serif;
        }

        aside {
            margin-top: 100px;
            width: 100%;
            padding: 20px;
            display: flex;
            flex-direction: row; 
            gap: 10px;
        }

        aside button {
            width: fit-content;
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 20px;
            color: #000;
            border: none;
            cursor: pointer;
            background: #fff;

            border: 1px solid #000;
        }

        aside button:last-child {
            background: #ecda30;
            border: 1px solid transparent;
        }

        aside button:last-child:hover {
            border-color: #333;
        }

        aside button:not(:last-child):hover{
            background: #000;
            color: #fff;
        }

        main {
            width: 100%;
            padding: 20px;

            border: 1px solid #333;
        }

        .frame-upload {
            border: 
        }
    </style>
</head>
<body>

    <aside>
        <button id="workstationFilesButton">Workstation Files</button>
        <button id="pendingFilesButton">Pending Files</button>
        <button id="dashboardButton">Back</button>
    </aside>

    <main>
    <div class="frame-upload" id="upload">  
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                Select PDF file to upload:
                <br><input type="file" name="fileToUpload" id="fileToUpload"><br>
                <input type="submit" value="Upload File" name="submit"><br>
                <p><?php echo $upload_message; ?></p>
            </form>
     </div>
    </main>

    <script>
        $(document).ready(function() {
            $("#workstationFilesButton").click(function() {
                window.location.href = "workstation_files.php"; // Replace with your actual file name
            });

            $("#pendingFilesButton").click(function() {
                window.location.href = "pending_files.php"; // Replace with your actual file name
            });

            $("#dashboardButton").click(function() {
                window.location.href = "index.php"; // Replace with your actual file name
            });
        });
    </script>

</body>
</html>