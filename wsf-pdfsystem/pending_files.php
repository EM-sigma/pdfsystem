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

    function get_pending_files() {
        global $conn;
        $sql = "SELECT * FROM files WHERE status = 'pending' OR status = 'rejected' "; // Select only pending files
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

    $pending_files = get_pending_files(); // Get pending files

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
                background: #f8f8f8;
                padding: 20px;
            }

            table {
                position: relative;
                margin: 0 auto;
                width: 100%;
                border-collapse: collapse;
                background: #fff;

                overflow-x: scroll
            }

            th, td {
                border: 1px solid #ccc;
                padding: 10px;
                text-align: center;
            }

            td {
                font-size: 1rem;
            }

            th {
                background-color: #f5f5f5;
            }

            tr:hover {
                background-color: #f2f2f2;
            }
        </style>
    </head>
    <body>

        <main>
        <table>
            <thead>
                <tr>
                    <th>FILENAME</th>
                    <th>WORKSTATION</th>
                    <th>STATUS</th>
                    <th>DATE UPLOADED</th>
                    </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_files)): ?>
                        <tr><td colspan="4">No pending files found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pending_files as $file): ?>
                            <tr>
                                <td><?php echo $file['filename']; ?></td>
                                <td>
                                <?php
                                    $workstation_name = get_workstation_name($file['workstation']);
                                    echo htmlspecialchars($workstation_name);
                                ?>
                                </td>
                                <td><?php echo $file['status']; ?></td>
                                <td><?php echo $file['date_uploaded']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                <?php endif; ?>

            </tbody>
        </table>

        <script>
            $(document).ready(function() {
                // Refresh the page every 30 seconds (30000 milliseconds)
                setInterval(function() {
                    location.reload(); // This will reload the page
                }, 30000); // Set this to whatever time interval (in milliseconds) you need, e.g., 30 seconds
            });
        </script>

    </body>
    </html>