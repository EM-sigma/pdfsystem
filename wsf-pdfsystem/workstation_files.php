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
$workstation_number = $_SESSION['workstation_number']; // Get workstation number from session

function get_approved_files_by_workstation($workstation) {
    global $conn;
    $sql = "SELECT * FROM files WHERE status = 'approved' AND workstation = '$workstation'";
    $result = $conn->query($sql);

    $files = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
        $result->free_result();
    } else {
        echo "Error in query: " . $conn->error;
    }
    return $files;
}


$approved_files = get_approved_files_by_workstation($workstation_number); // Pass the workstation number

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
            background: #fff;  
            overflow-y: hidden;
        }

        table {
            position: relative;
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;         
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

    </style>
</head>
<body>

    <table>
        <thead>
            <tr>
                <th>Filename</th>
                <th>Workstation</th>
                <th>Status</th>
                <th>Date Uploaded</th>
                </tr>
        </thead>
        <tbody>
            <?php if (empty($pending_files)): ?>
                    <tr><td colspan="4">No approved files found on the current workstation.</td></tr>
                <?php else: ?>
                    <?php foreach ($pending_files as $file): ?>
                        <tr>
                            <td><?php echo $file['filename']; ?></td>
                            <td><?php echo $file['workstation']; ?></td>
                            <td><?php echo $file['status']; ?></td>
                            <td><?php echo $file['date_uploaded']; ?></td>
                        </tr>
                    <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            $("#workstationFilesButton").click(function() {
                window.location.href = "#"; // Replace with your actual file name
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