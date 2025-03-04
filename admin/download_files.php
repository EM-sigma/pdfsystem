<?php
include "db.php";

// Get the search term from the query string (if available)
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Unified function to get pending items from either wsf_requests or srt_requests
function get_pending_items($type = 'files', $search_term = '') {
    global $conn;

    if ($type === 'files') {
        // SQL to get pending files from wsf_requests
        $sql = "SELECT * FROM wsf_requests WHERE status = 'pending'";

        if (!empty($search_term)) {
            $sql .= " AND filename LIKE ?";
        }

        // Prepare and execute the query for files
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Error preparing statement: " . $conn->error);  // Log error
            return [];
        }

        if (!empty($search_term)) {
            $search_term = "%" . $search_term . "%";
            $stmt->bind_param('s', $search_term);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $pending_files = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pending_files[] = $row;
            }
            $result->free_result();
        } else {
            error_log("Error in query: " . $stmt->error);  // Log error
        }
        $stmt->close();
        return $pending_files;

    } elseif ($type === 'research_titles') {
        // SQL to get pending research titles from srt_requests
        $sql = "SELECT * FROM srt_requests WHERE status = 'pending'";

        if (!empty($search_term)) {
            $sql .= " AND rtitle LIKE ?";
        }

        // Prepare and execute the query for research titles
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Error preparing statement: " . $conn->error);  // Log error
            return [];
        }

        if (!empty($search_term)) {
            $search_term = "%" . $search_term . "%";
            $stmt->bind_param('s', $search_term);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $pending_titles = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pending_titles[] = $row;
            }
            $result->free_result();
        } else {
            error_log("Error in query: " . $stmt->error);  // Log error
        }
        $stmt->close();
        return $pending_titles;
    }

    return [];  // Default case, should not happen
}


// Get pending files and research titles
$pending_files = get_pending_items('files', $search_term); // Get pending files from wsf_requests
$pending_titles = get_pending_items('research_titles', $search_term); // Get pending research titles from srt_requests

// Initialize status variables
$status_message = '';
$status_class = '';

function reject_wsf_request($file_id) {
    global $conn;
    $sql = "UPDATE wsf_requests SET status = 'rejected' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error);
        return false;
    }
    $stmt->bind_param('i', $file_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Function to reject a srt_request
function reject_srt_request($title_id) {
    global $conn;
    $sql = "UPDATE srt_requests SET status = 'rejected' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error);
        return false;
    }
    $stmt->bind_param('i', $title_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Handle reject actions
if (isset($_POST['action']) && $_POST['action'] == 'reject') {
    if (isset($_POST['file_id'])) {
        $file_id = $_POST['file_id'];
        if (reject_wsf_request($file_id)) {
            $status_message = "File request rejected successfully.";
            $status_class = "rejected-message";
        } else {
            $status_message = "Failed to reject file request.";
            $status_class = "rejected-message";
        }
    } elseif (isset($_POST['title_id'])) {
        $title_id = $_POST['title_id'];
        if (reject_srt_request($title_id)) {
            $status_message = "Research title request rejected successfully.";
            $status_class = "rejected-message";
        } else {
            $status_message = "Failed to reject research title request.";
            $status_class = "rejected-message";
        }
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

        #files-table {
            margin-bottom: 20px;
        }

        h3 {
            padding-bottom: 10px;
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

        .approved, .rejected {
            width: 45%;
            display: inline-block;
        }

        .approved:hover {
            color: white;
            background: green;
            border-color: green;
        }

        .rejected:hover {
            border-color: red;
            color: white;
            background: red;
        }

        .form-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            border: none;
            padding: 10px;
            border: 1px solid #fff;
            box-shadow: 3px 3px 0 #999;
        }

        .form-container input {
            border: 1px solid #ccc;
            width: 100%;
            padding: 5px;
        }

        .form-container input:hover {
            border-color: #999;
        }

        .form-container h2 {
            text-align: center;
            font-size: 1rem;
            padding: 5px;
            margin-bottom: 10px;
        }

        .form-container input[type="submit"] {
            background: #fff;
        }

        .form-container input[type="submit"]:hover {
            background: green;
            border-color: green;
            color: #fff;
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
    <?php if ($status_message): ?>
        <div class="<?php echo $status_class; ?>">
            <?php echo $status_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container" id="wsf_download_request" popover>
        <h2>     PDF</h2>
            <form class="request-form" action="wsf_download_request.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="pdf_file" accept=".pdf" required><br><br>
                <input type="hidden" name="file_id" id="wsf_file_id_input" value=""> 
                <input type="submit" value="Upload">
            </form>
    </div>

    <div class="form-container" id="srt_download_request" popover>
        <h2>Upload PDF</h2>
            <form class="request-form" action="srt_download_request.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="pdf_file" accept=".pdf" required><br><br>
                <input type="hidden" name="title_id" id="srt_title_id_input" value=""> 
                <input type="submit" value="Upload">
            </form>
    </div>

    <div class="back">
        <form method="get" action="">
           <input type="text" name="search" id="search" placeholder="Search title both files..." value="<?php echo htmlspecialchars($search_term); ?>">
        </form>
        <a href="files.php">Approved Files</a>
        <a href="upload_files.php">Upload Pending Files</a>
    </div>

    <h3>PENDING WORKSTATION DOWNLOAD FILES</h3>
    <table id="files-table">
        <thead>
            <tr>
                <th>FILENAME</th>
                <th>EMAIL</th>
                <th>FULL NAME</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pending_files)): ?>
                <?php foreach ($pending_files as $file): ?>
                    <tr>
                        <td class="file-name"><?php echo htmlspecialchars($file['filename']); ?></td>
                        <td><?php echo htmlspecialchars($file['email']); ?></td>
                        <td><?php echo htmlspecialchars($file['fullname']); ?></td>
                        <td>
                            <button class="approved" data-file-id="<?php echo $file['id']; ?>" popovertarget="wsf_download_request">Approve</button>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                <button class="rejected" type="submit" name="action" value="reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No pending files found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h3>PENDING RESEARCH TITLES DOWNLOAD REQUEST</h3>
    <table id="research-titles-table">
        <thead>
            <tr>
                <th>TITLE NAME</th>
                <th>EMAIL</th>
                <th>FULL NAME</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pending_titles)): ?>
                <?php foreach ($pending_titles as $title): ?>
                    <tr>
                        <td class="title-name"><?php echo htmlspecialchars($title['rtitle']); ?></td>
                        <td><?php echo htmlspecialchars($title['email']); ?></td>
                        <td><?php echo htmlspecialchars($title['fullname']); ?></td>
                        <td>
                            <button class="approved" data-title-id="<?php echo $title['id']; ?>" popovertarget="srt_download_request">Approve</button>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="title_id" value="<?php echo $title['id']; ?>">
                                <button class="rejected" type="submit" name="action" value="reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No pending research titles found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wsfApproveButtons = document.querySelectorAll('[popovertarget="wsf_download_request"]');
            wsfApproveButtons.forEach(button => {
            button.addEventListener('click', () => {
                const fileId = button.dataset.fileId;
                document.getElementById('wsf_file_id_input').value = fileId;
            });
        });

            const srtApproveButtons = document.querySelectorAll('[popovertarget="srt_download_request"]');
            srtApproveButtons.forEach(button => {
            button.addEventListener('click', () => {
                const titleId = button.dataset.titleId;
                document.getElementById('srt_title_id_input').value = titleId;
                });
            });
        });
        // Client-side filtering logic for the search input
        document.getElementById('search').addEventListener('keyup', function() {
            var searchTerm = this.value.toLowerCase(); // Get the search term

            // Handle files table filtering
            var fileRows = document.querySelectorAll('#files-table tbody tr'); // Get all rows of the files table
            fileRows.forEach(function(row) {
                var filename = row.querySelector('.file-name').textContent.toLowerCase(); // Get the filename
                if (filename.indexOf(searchTerm) > -1) {
                    row.style.display = ''; // Show the row if the filename matches
                } else {
                    row.style.display = 'none'; // Hide the row if the filename does not match
                }
            });

            // Handle research titles table filtering
            var titleRows = document.querySelectorAll('#research-titles-table tbody tr'); // Get all rows of the research titles table
            titleRows.forEach(function(row) {
                var titlename = row.querySelector('.title-name').textContent.toLowerCase(); // Get the title name
                if (titlename.indexOf(searchTerm) > -1) {
                    row.style.display = ''; // Show the row if the title name matches
                } else {
                    row.style.display = 'none'; // Hide the row if the title name does not match
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