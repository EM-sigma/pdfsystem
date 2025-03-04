<?php

include "db.php";

// Unified function to get pending files and research titles
function get_pending_items($type = 'files', $search_term = '') {
    global $conn;

    $items = [];
    if ($type === 'files') {
        $sql = "SELECT * FROM files WHERE status = 'pending'";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Error preparing statement: " . $conn->error);
            return [];
        }

        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $result->free_result();
        $stmt->close();

    } elseif ($type === 'research_titles') {
        $sql = "SELECT * FROM research_titles WHERE status = 'pending'";

        if (!empty($search_term)) {
            $sql .= " AND titlename LIKE ?";
        }

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Error preparing statement: " . $conn->error);
            return [];
        }

        if (!empty($search_term)) {
            $search_term = "%" . $search_term . "%";
            $stmt->bind_param('s', $search_term);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $result->free_result();
        $stmt->close();
    }

    return $items;
}

$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Get pending files and research titles
$pending_files = get_pending_items('files');
$pending_titles = get_pending_items('research_titles', $search_term);

// Initialize status variables
$status_message = '';
$status_class = '';

if (isset($_POST['action']) && $_POST['action'] === 'reject' && isset($_POST['file_id'])) {
    $file_id = $_POST['file_id'];

    // Update the file status to 'rejected' in the database
    $update_stmt = $conn->prepare("UPDATE files SET status = 'rejected' WHERE id = ?");
    if (!$update_stmt) {
        error_log("Error preparing update statement: " . $conn->error);
        $status_message = "Error rejecting file.";
        $status_class = "rejected-message"; 
    } else {
        $update_stmt->bind_param('i', $file_id);
        if ($update_stmt->execute()) {
            $status_message = "File rejected successfully.";
            $status_class = "rejected-message"; 
            header("Location: files.php");
        } else {
            error_log("Error executing update: " . $update_stmt->error);
            $status_message = "Error rejecting file.";
            $status_class = "rejected-message"; 
        }
        $update_stmt->close();
    }
}

// Handle research title rejection (similar to file rejection)
if (isset($_POST['action']) && $_POST['action'] === 'reject' && isset($_POST['title_id'])) {
    $title_id = $_POST['title_id'];

    // Update the research title status to 'rejected' in the database
    $update_stmt = $conn->prepare("UPDATE research_titles SET status = 'rejected' WHERE id = ?");
    if (!$update_stmt) {
        error_log("Error preparing update statement: " . $conn->error);
        $status_message = "Error rejecting research title.";
        $status_class = "rejected-message"; 
    } else {
        $update_stmt->bind_param('i', $title_id);
        if ($update_stmt->execute()) {
            $status_message = "Research title rejected successfully.";
            $status_class = "rejected-message"; 
            header("Location: files.php");
        } else {
            error_log("Error executing update: " . $update_stmt->error);
            $status_message = "Error rejecting research title.";
            $status_class = "rejected-message"; 
        }
        $update_stmt->close();
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Files and Research Titles</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            padding: 20px;
            background: #f8f8f8;
            font-family: sans-serif;
        }

        .back {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .back form {
            margin-right: auto;
        }

        #search {
            padding: 10px;  
            border: none;
            border: 1px solid #ccc;
        }

        .back a:first-child {
            margin-right: 10px;
        }

        .back a {
            border: 1px solid #ccc;
            padding: 5px 15px;
            text-decoration: none;
            font-size: 1.2rem;
            color: #000;
            background: #fff;
            box-shadow: 3px 3px 1px #999;
        }

        .back a:hover {
            background: #000;
            color: #fff;
            border-color: #000;
        }

        .wsf button, .srt button {
            font-size: 1.3rem;
            margin-top: 5px;
        }

        .wsf, .srt {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 0;
        }

        .wsf h3, .srt h3 {
            margin-right: 10px;
            padding: 0;
            padding-top: 7px;
        }

        table {
            position: relative;
            margin: 0 auto;
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        th, td {
            border: 1px solid #999;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #eee;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        button {
            border: none;
            background: none;
            border: 1px solid #ccc;
            padding: 5px 10px;
        }

        .approve, .reject {
            width: 45%;
            display: inline-block;
        }

        .approve:hover {
            color: white;
            background: green;
            border-color: green;
        }

        .reject:hover {
            border-color: red;
            color: white;
            background: red;
        }

        .approved-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: green;
            background:rgba(67, 150, 63, 0.2);
            padding: 30px;
            z-index: 100;
        }

        .rejected-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: red;
            background:rgba(150, 63, 63, 0.2);
            padding: 30px;
            z-index: 100;
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
            transform: translate(-50%, -50%);
            box-shadow: 5px 5px 1px #999;  
        }

        .form-container h3 {
            color: #fff;
            text-align: center;
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
            padding: 8px;
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

        @media only screen and (max-width: 600px) {
            body {
                padding: 5px;
            }
            h3 {
                font-size: 10px;
            }
            #search {
                padding: 5px;
                font-size: 10px;
                width: 100px;
            }
            th, td {
                font-size: 7px;
                padding: 3px;
            }
            .back a {
                font-size: 8px;
                margin-bottom: 10px;
                padding: 5px;
                box-shadow: 2px 2px 0 #999;
            }
            .approve {
                margin-bottom: 3px;
            }
            .approve, .reject {
                padding: 3px 5px;
                font-size: 10px;
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Display status message if exists -->
    <?php if ($status_message): ?>
        <div class="<?php echo $status_class; ?>">
            <?php echo $status_message; ?>
        </div>
    <?php endif; ?>

    <!-- Pending Files Section -->
    <div class="back">
        <form method="get" action="">
           <input type="text" name="search" id="search" placeholder="Search title both files..." value="<?php echo htmlspecialchars($search_term); ?>">
        </form>
        <a href="files.php">Approved Files</a>
        <a href="download_files.php">Download Pending Files</a>
    </div>

    <div class="form-container" id="wsf_upload_request" popover>
        <h3>Approve Files</h3>
        <form class="request-form" action="wsf_upload_request.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="file"  id="file_id" value="">

            <label for="email">Recepient Email Account</label>
            <input type="email" id="email2" name="email2" value="recepient_email_account@gmail.com" required><br><br>

            <label for="name">Full Name</label>
            <input type="text" id="name2" name="name2" required><br><br>

            <input type="submit" value="Approve">
        </form>
    </div>


    <div class="form-container" id="srt_upload_request" popover>
        <form class="request-form" action="srt_upload_request.php" method="POST" enctype="multipart/form-data">
            <label for="email">Your Email Account</label>

            <input type="hidden" name="title_id" id="title_id" value="">
            <input type="email" id="email2" name="email2" value="recepient_email_account@gmail.com" required><br><br>

            <label for="name">Full Name</label>
            <input type="text" id="name2" name="name2" required><br><br>
            <input type="submit" value="Upload">
        </form>
    </div>

    <div class="wsf">
        <h3>UPLOAD PENDING FILES</h3>
    </div>

    <aside style="margin-bottom: 20px;">
        <table id="files-table">
            <thead>
                <tr>
                    <th>FILENAME</th>
                    <th>WORKSTATION</th>
                    <th>DATE UPLOADED</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pending_files)): ?>
                    <?php foreach ($pending_files as $file): ?>
                        <tr>
                            <td class="file-name"><?php echo htmlspecialchars($file['filename']); ?></td>
                            <td><?php echo htmlspecialchars($file['workstation']); ?></td>
                            <td><?php echo htmlspecialchars($file['date_uploaded']); ?></td>
                            <td>
                                <button class="approve" data-file-id="<?php echo $file['id']; ?>" popovertarget="wsf_upload_request">Approve</button>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <button class="reject" type="submit" name="action" value="reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No files found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </aside>

    <!-- Pending Research Titles Section -->
    <div class="srt">
        <h3>STUDENT RESEARCH TITLE PENDING FILES</h3>
    </div>

    <aside>
        <table id="research-titles-table">
            <thead>
                <tr>
                    <th>TITLE NAME</th>
                    <th>COLLEGE</th>
                    <th>PROGRAM</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pending_titles)): ?>
                    <?php foreach ($pending_titles as $title): ?>
                        <tr>
                            <td class="title-name"><?php echo htmlspecialchars($title['titlename']); ?></td>
                            <td><?php echo htmlspecialchars($title['college']); ?></td>
                            <td><?php echo htmlspecialchars($title['program']); ?></td>
                            <td>
                                <button class="approve" data-title-id="<?php echo $title['id']; ?>" popovertarget="srt_upload_request">Approve</button>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="title_id" value="<?php echo $title['id']; ?>">
                                    <button class="reject" type="submit" name="action" value="reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No files found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </aside>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wsfApproveButtons = document.querySelectorAll('[popovertarget="wsf_upload_request"]');
            wsfApproveButtons.forEach(button => {
            button.addEventListener('click', () => {
                const fileId = button.dataset.fileId;
                document.getElementById('file_id').value = fileId;
            });
        });
        const srtApproveButtons = document.querySelectorAll('[popovertarget="srt_upload_request"]');
            srtApproveButtons.forEach(button => {
            button.addEventListener('click', () => {
                const titleId = button.dataset.titleId;
                document.getElementById('title_id').value = titleId;
                });
            });
    });
    </script>

    <script>
        // Client-side filtering logic for the search input
        document.getElementById('search').addEventListener('keyup', function() {
            var searchTerm = this.value.toLowerCase(); // Get the search term
            var rows = document.querySelectorAll('#files-table tbody tr'); // Get all rows of the table

            rows.forEach(function(row) {
                var filename = row.querySelector('.file-name').textContent.toLowerCase(); // Get the filename
                if (filename.indexOf(searchTerm) > -1) {
                    // If the filename contains the search term, show the row
                    row.style.display = '';
                } else {
                    // Otherwise, hide the row
                    row.style.display = 'none';
                }
            });
        });
    </script>

    <script>
        // Client-side filtering logic for the search input
        document.getElementById('search').addEventListener('keyup', function() {
            var searchTerm = this.value.toLowerCase(); // Get the search term
            var rows = document.querySelectorAll('#research-titles-table tbody tr'); // Get all rows of the table

            rows.forEach(function(row) {
                var titlename = row.querySelector('.title-name').textContent.toLowerCase(); // Get the title name
                if (titlename.indexOf(searchTerm) > -1) {
                    // If the titlename contains the search term, show the row
                    row.style.display = '';
                } else {
                    // Otherwise, hide the row
                    row.style.display = 'none';
                }
            });
        });
    </script>

    <script>
        window.onload = function() {
            var success = document.querySelector('.approved-message'); // Assuming you have a class 'success' for success messages
            var error = document.querySelector('.rejected-message'); // Assuming you have a class 'error' for error messages
            
            if (success) {
                success.style.display = 'block';
            }
            
            if (error) {
                error.style.display = 'block';
            }
            
            setTimeout(function() {
                if (success) {
                    success.style.display = 'none';
                }
                if (error) {
                    error.style.display = 'none';
                }
            }, 3000); // 3000 ms = 3 seconds
        }
    </script>

</body>
</html>
