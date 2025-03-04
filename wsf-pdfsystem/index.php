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
$workstation_number = $_SESSION['workstation_number'];

$search_term = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT name FROM workstations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $workstation_number); // Use the workstation_number from the session

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $workstation_name = $row['name'];
} else {
    $workstation_name = "Unknown Workstation"; // Or handle the case where the ID is not found
}

function get_files($search_term = '') {
    global $conn;
    $current_date = date('Y-m-d');
    $five_years_ago = date('Y-m-d', strtotime('-5 years'));

    // 1. Get ALL approved files (before archiving)
    $sql = "SELECT * FROM files WHERE status = 'approved'";

    if (!empty($search_term)) {
        $sql .= " AND filename LIKE ?";
    }

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    if (!empty($search_term)) {
        $search_term = "%" . $search_term . "%";
        $stmt->bind_param('s', $search_term);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $all_approved_files = []; // Store ALL approved files here
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $all_approved_files[] = $row;
        }
        $result->free_result();
    } else {
        echo "Error in query: " . $stmt->error;
    }
    $stmt->close();

    // 2. NOW, archive the old files (separate operation)
    foreach ($all_approved_files as $file) {
        $upload_date = $file['date_uploaded'];
        $file_id = $file['id'];

        if ($upload_date < $five_years_ago) {
            $archive_workstation = "archive"; // Or whatever you want

            $update_stmt = $conn->prepare("UPDATE files SET workstation = ?, status = 'archived' WHERE id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param('si', $archive_workstation, $file_id);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                echo "Error preparing update statement: " . $conn->error;
            }
        }
    }

    // 3. Filter for the files that are still approved (after archiving)
    $filtered_files = [];
    foreach ($all_approved_files as $file) {
      if ($file['date_uploaded'] >= $five_years_ago) {
        $filtered_files[] = $file;
      }
    }

    return $filtered_files;
}

$files = get_files($search_term); // Get the files

$stmt = $conn->prepare("SELECT image FROM wsf_user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($image_data); // Bind the BLOB data
$stmt->fetch();
$stmt->close();

// Convert BLOB to base64 for display
$base64_image = '';
if ($image_data) {
    $base64_image = base64_encode($image_data);
    $image_src = 'data:image/jpeg;base64,' . $base64_image; // Or data:image/png, etc.
} else {
    $image_src = "images/bg.jpg"; // Default image if no profile picture
}

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
    if ($_FILES["fileToUpload"]["size"] > 1000000000) {
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
                $stmt->bind_param("sbi", $filename, $pdf_content, $workstation_id); // Bind parameters
                $stmt->send_long_data(1, $pdf_content); // Send the long data for the PDF content
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

function get_workstation_name($workstation_id) {
    global $conn;
    $sql = "SELECT name FROM workstations WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        return "Unknown Workstation"; // Return a default value on error
    }

    $stmt->bind_param("i", $workstation_id); // "i" for integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['name'];
    } else {
        $stmt->close();
        return "Unknown Workstation"; // Return a default value if not found
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Workstation Files</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            background: #f8f8f8;
        }

        .message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: fit-content;
            height: fit-content;
            padding: 30px;
            background:rgba(67, 150, 63, 0.2);
            backdrop-filter: blur(20px);
            color: green;
            z-index: 100;
        }

        .error {
            color: red;
        }

        .header {
            position: fixed;
            top: 0;
            display: flex;
            align-items: center;
            width: 100%;
            padding: 30px;
            height: 90px;
            width: 100%;
            background: #fff;
            border-bottom: 1px solid #ccc;
            z-index: 100;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-right: auto;
        }

        .profile img {
            width: 40px; 
            height: 40px;
            background-size: cover;
            border-radius: 50%;
            margin-right: 10px;

            border: 1px solid #333;
        }

        .profile h3 {
            font-size: 16px; 
            margin: 0;
        }

        .workstation-details {
            text-align: right;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .workstation-details p, .workstation-details .logout {
            color: #000;
            text-decoration: none;
            font-size: 1rem;
        }

        .workstation-details .logout {
            background: green;
            color: #fff;
            border-radius: 20px;
            padding: 10px 15px; 

            border: 1px solid transparent;
        }

        .workstation-details .logout:hover {
            border-color: #000;
        }

        .workstation-details p {
            border-radius: 20px;
            padding: 10px 15px; 
            border: 1px solid #333;
        }

        .get {
            position: fixed;
            top: 90px;
            height: 100px;
            width: 100%;
            padding: 20px;
            background:rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);

            display: flex;
            align-items: center;
            z-index: 100;
        }

        .get input[type='text'] {
            margin-right: 10px;
            padding: 10px 5px;
            background: #fff;
        }

        .get #search {
            margin-right: auto;
            border: 1px solid #999;
            width: 200px;
        }

        .get button {
            color: #000;
            border: none;
            padding: 10px 15px;
            font-size: 1rem;
            border: 1px solid #333;
            background: #fff;   
            box-shadow: 3px 3px 0 #999;
        }

        .get button:hover {
            color: #fff;
            background: #000;
            border-color: #000;
        }

        aside {
            position: absolute;
            top: 190px;
            padding: 20px;
            display: flex;
            flex-direction: column; 
            width: 100%;
            padding: 0 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            background: #fff;

            border: 1px solid #ccc;
        }

        th, td {
            border: 1px solid #999;
            padding: 5px;
            text-align: center;
            font-size: 1rem;
        }

        th {
            background-color: #f5f5f5;
            padding: 10px;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .download-btn {
            width: 100%;
            background: #fff;
            border: 1px solid #999;
            padding: 5px;
            font-size: 1rem;
        }

        .download-btn:hover {
            color: #fff;
            border-color: green;
            background: green;
        }

        .upload {
            position: relative;
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-right: 10px;
        }

        .upload input {
            background: none;
            border: none;
            border: 1px solid #ccc;
            padding: 10px 15px;
            font-size: 1rem;
        }

        .upload input[type='file'] {
            position: relative;
            width: 100%;
            padding-right: 50px;
            background: #fff;
        }

        .upload input[type='file']:hover {
            border-color: #999;
        }

        .upload input[type='submit'] {
            border: 1px solid #333;
            background: #fff;
            position: absolute;
            right: 3px;
            top: 3px;
            box-shadow: 3px 3px 0 #999;
        }

        .upload input[type='submit']:hover {
            color: #fff;
            background: #000;
        }

        .pending-files {
            width: 80%;
            height: 70vh;
            border: 1px solid #333;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            overflow: hidden;
        }

        .pending-files iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .profile button {
            background: none;
            border: none;
            width: fit-content;
        }

        .profile button img:hover {
            padding: 3px;
        }

        .user-profile {
            position: absolute;
            top: 70px;
            left: 30px;
            width: 150px;
            height: fit-content;
            border: none;
            padding: 3px;

            border: 1px solid #999;
        } 

        .user-profile img {
            height: 100px;
            width: 100%;
            background-size: 100% 100%;
            background-repeat: no-repeat;
        }

        .user-profile p {
            text-align: center;
            padding: 5px;
        }

        .user-profile button {
            background: none;
            border: none;
            border: 1px solid #ccc;
            width: 100%;
            padding: 5px;
        }

        .user-profile button:hover {
            background: #000;
            color: #fff;
        }

        .form-container {
            position: relative;
            height: fit-content;
            width: 400px;
            padding: 20px;
            border: none;
            background: green;
            border: 1px solid #fff;
            position: fixed;
            top: 50%;
            left: 50%;
            box-shadow: 3px 3px 1px #999;
            transform: translate(-50%, -50%);
        }

        .form-container h2 {
            font-size: 1rem;
            text-align: center;
            color: #fff;
            padding-bottom: 10px;
        }

        .request-form {
            position: relative;
            padding: 20px;
            height: fit-content;
            width: fit-content;
            background: #fff;
            border: 1px solid #333;
        }

        .request-form input:not(input[type="submit"]), .request-form select {
            padding: 10px;
            border: 1px solid #ccc;
            width: 100%;
        }

        .request-form select {
            margin-right: 10px;
        }

        .request-form input[type='submit'] {
            border: none;
            border: 1px solid #ccc;
            background: none;
            font-size: 1rem;
            padding: 5px 10px;
            width: 100%;
        }

        .request-form input[type='submit']:hover {
            background:rgba(67, 150, 63, 0.2);
            color: green;
            border-color: green;

        }

        .request-form label {
            margin-bottom: 10px;
        }

    </style>
</head>
<body>
<p class="message"><?php echo $upload_message; ?></p>
        <div class="header">
            <div class="profile">
                <button id="user-profile-btn"><img src="<?php echo $image_src; ?>" alt="Profile Picture"></button>
                <h3><?php echo $username; ?></h3>
            </div>
            <div class="workstation-details">
                <p><?php echo htmlspecialchars($workstation_name); ?></p> <a href="logout.php" class="logout">Logout</a>
            </div>
        </div>

        <div class="get">
            <input type="text" name="search" id="search" placeholder="Search files..." value="<?php echo htmlspecialchars($search_term); ?>">
            <div class="upload" id="upload">  
                <button popovertarget="send_upload_request" data-user-id="<?php echo $_SESSION['user_id']; ?>" >Upload Files</button>
            </div>
            <button popovertarget="pending-files">Pending Files</button>
        </div>
        <div class="form-container" id="send_upload_request" popover>
            <h2>Upload File Request</h2>
                <form class="request-form" action="send_upload_request.php" method="POST" enctype="multipart/form-data">
                    <label for="email">Your Email Account</label>
                    <input type="email" id="email2" name="email2" value="your_email_account@gmail.com" required><br><br>
                    <label for="name">Full Name:</label>
                    <input type="text" id="name2" name="name2" required><br><br>
                    <input type="file" name="pdf_file" accept=".pdf" required><br><br>
                    <input type="hidden" name="title_id" id="srt_title_id_input" value=""> 
                    <input type="submit" value="Upload">
                </form>
        </div>
        <div class="pending-files" id="pending-files" popover>
            <iframe src="pending_files.php" frameborder="0"></iframe>
        </div>

        <div class="form-container" id="send_download_request" popover>
            <h2>Send request through gmail</h2>

            <form class="request-form" action="send_download_request.php" method="POST">
                <input type="hidden" id="research_id" name="research_id" value="">
                 <label for="email">Your Email Account</label>
                <input type="email" id="email2" name="email2" value="your_email_account@gmail.com" required><br><br>
                <label for="name">Full Name:</label>
                <input type="text" id="name2" name="name2" required><br><br>
                <input type="submit" value="Send">
            </form>
        </div>

        <aside>          
            <table id="files-table">
                <thead>
                    <tr>
                        <th>FILENAME</th>
                        <th>WORKSTATION</th>
                        <th>DATE UPLOADED</th>
                        <th>REQUEST DOWNLOAD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($files)): ?>  
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td class="file-name"><?php echo $file['filename']; ?></td>
                                <td>
                                <?php
                                    $workstation_name = get_workstation_name($file['workstation']);
                                    echo htmlspecialchars($workstation_name);
                                ?>
                                </td>
                                <td><?php echo $file['date_uploaded']; ?></td>
                                <td><button class="download-btn" popovertarget="send_download_request" 
                                        data-research-id="<?php echo $file['id'];?>">  
                                    Send request to download
                                </button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>  
                        <tr><td colspan="4">No files found.</td></tr>  
                    <?php endif; ?>
                </tbody>
            </table>
        </aside>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const srtApproveButtons = document.querySelectorAll('[popovertarget="send_upload_request"]');
            srtApproveButtons.forEach(button => {
            button.addEventListener('click', () => {
                const titleId = button.dataset.titleId;
                document.getElementById('srt_title_id_input').value = titleId;
                });
            });
        });

    </script>
    <script>
        window.onload = function() {
        var messageBox = document.querySelector('.message');
        messageBox.style.display = 'block'; 
        setTimeout(function() {
            messageBox.style.display = 'none';
        }, 5000); // 5000 ms = 5 seconds
    }
    </script>

    <script>
        $(document).ready(function() {
            $("#user-profile-btn").click(function() {
                window.location.href = "profile.php"; // Replace with your actual file name
            });
            const requestButtons = document.querySelectorAll('[popovertarget="send_download_request"]');
                requestButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const researchId = button.dataset.researchId;
                    const userId = button.dataset.userId; // Get user ID from data attribute
                document.getElementById('research_id').value = researchId;
                document.getElementById('user_id_input').value = userId; // Set user ID in hidden input
            });
        });
        });
    </script>

    <script>
        $(document).ready(function() {
    // Listen for changes in the search input
            $('#search').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase(); // Get the search term and convert it to lowercase
                $('#files-table tbody tr').each(function() {
                    var filename = $(this).find('.file-name').text().toLowerCase(); // Get the filename in lowercase
                    if (filename.indexOf(searchTerm) > -1) {
                        // If the filename contains the search term, show the row
                        $(this).show();
                    } else {
                        // Otherwise, hide the row
                        $(this).hide();
                    }
                });
            });
        });
    </script>
    
</body>
</html>