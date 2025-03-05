<?php

include "db.php";

session_start(); 


// Get the search term from the query string (if available)
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

function get_files($search_term = '') {
    global $conn;
    $current_date = date('Y-m-d');
    $five_years_ago = date('Y-m-d', strtotime('-5 years'));

    // Start building the SQL query
    $sql = "SELECT * FROM files WHERE status = 'approved'";

    // Apply search filter if the term is not empty
    if (!empty($search_term)) {
        $sql .= " AND filename LIKE ?";
    }

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the search term parameter, if applicable
    if (!empty($search_term)) {
        $search_term = "%" . $search_term . "%"; // Add wildcards for LIKE
        $stmt->bind_param('s', $search_term);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all approved files
    $all_approved_files = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $all_approved_files[] = $row;
        }
        $result->free_result();
    } else {
        echo "Error in query: " . $stmt->error;
    }
    $stmt->close();

    // 2. Archive files that are older than 5 years (separate operation)
    foreach ($all_approved_files as $file) {
        $upload_date = $file['date_uploaded'];
        $file_id = $file['id'];

        if ($upload_date < $five_years_ago) {
            $archive_workstation = "archive"; // Archive workstation name

            // Update file status to archived
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

    // 3. Filter out archived files and only return files that are still approved
    $filtered_files = array_filter($all_approved_files, function($file) use ($five_years_ago) {
        return $file['date_uploaded'] >= $five_years_ago;
    });

    return $filtered_files;
}

$files = get_files($search_term); // Get files based on search term

$search_term = isset($_GET['search']) ? $_GET['search'] : ''; // Get the search term if available

// Function to get research titles from the database
function get_research_titles($search_term = '') {
    global $conn;
    $current_date = date('Y-m-d');
    $five_years_ago = date('Y-m-d', strtotime('-5 years'));

    // Start building the SQL query
    $sql = "SELECT * FROM research_titles WHERE status = 'approved'";

    // Apply search filter if the term is not empty
    if (!empty($search_term)) {
        $sql .= " AND titlename LIKE ?";
    }

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the search term parameter, if applicable
    if (!empty($search_term)) {
        $search_term = "%" . $search_term . "%"; // Add wildcards for LIKE
        $stmt->bind_param('s', $search_term);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all approved research titles
    $all_approved_titles = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $all_approved_titles[] = $row;
        }
        $result->free_result();
    } else {
        echo "Error in query: " . $stmt->error;
    }
    $stmt->close();

    return $all_approved_titles;
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

$research_titles = get_research_titles($search_term); // Get research titles based on search term

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f8f8f8;
        }

        aside {
            border: 1px solid #fff;
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
            background-color: #f5f5f5;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        .download-view {
            color: #000;
            text-decoration: none;
            text-align: center;
            border: 1px solid #999;
            display: block;
            padding: 5px;
        }

        .download-view:hover {
            background:rgba(67, 150, 63, 0.2);
            color: green;
            border-color: green;
        }

        .wsf, .srt {
            display: flex;
            flex-direction: row;
            height: fit-content;
            margin-bottom: 15px;
        }

        h3 {
            margin-right: 10px;
            padding-top: 7px;
            text-align: center;
            width: fit-content;
        }

        .wsf a {
            height: fit-content;
        }

        .wsf .wsf-fc {
            margin-right: 20px;
        }

        .wsf input {
            margin: 0 auto;
        }

        .srt button, .wsf a {
            color: #000;
            text-decoration: none;
            padding: 5px 10px;
            border: none;
            background: none;
            border: 1px solid #ccc;
            font-size: 1.2rem;
            background: #fff;
            box-shadow: 3px 3px 0 #999;
        }

        .srt button:hover, .wsf a:hover {
            color: #fff;
            background: #000;
            border-color: #000;
        }

        #search {
            padding: 10px;  
            border: none;
            border: 1px solid #ccc;
            margin-right: auto;
            width: 250px;
        }

        .research_titles_form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            border: none;
            height: 65vh;
            width: 410px;
            background: none;
            margin: 0;
        }

        .research_titles_form_iframe {
            position: fixed;
            height: 100%;
            width: 100%;
            overflow: hidden;
        }

        /* Hide the scrollbar but still allow scrolling */
        .research_titles_form {
            -ms-overflow-style: none;  
            scrollbar-width: none;    
        }

        .research_titles_form::-webkit-scrollbar {
            display: none; 
        }

        @media only screen and (max-width: 600px) {
            body {
                padding: 5px;
            }
            #search {
                padding: 5px;
                width: 80px;
                padding: 3px 5px;
                font-size: 10px;
            }
            th, td {
                font-size: 7px;
                padding: 5px;
            }
            .srt button, .wsf a {
                font-size: 7px;
                padding: 3px 5px;
                box-shadow: 2px 2px 0 #999;
            }
            .wsf a {
                width: 70px;
            }
            .wsf .wsf-fc {
                margin-right: 5px;
            }
            .wsf input {
                margin: 0;
                margin-left: 3px;
            }
            h3 {
                font-size: 5px;
            }
            h3 {
                margin-right: 5px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="wsf">
    <h3>WSF FILES</h3>
        <input type="text" name="search" id="search" placeholder="Search title files..." value="<?php echo htmlspecialchars($search_term); ?>">
        <a class="wsf-fc" href="upload_files.php">Upload Pending Files</a>
        <a href="download_files.php">Download Pending Files</a>
    </div>

    <aside style="margin-bottom: 20px;">
        <table id="files-table">
            <thead>
                <tr>
                    <th>FILENAME</th>
                    <th>WORKSTATION</th>
                    <th>DATE UPLOADED</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($files)): ?>
                    <?php foreach ($files as $file): ?>
                        <tr>
                            <td class="file-name"><?php echo htmlspecialchars($file['filename']); ?></td>
                            <td>
                            <?php
                                $workstation_name = get_workstation_name($file['workstation']);
                                echo htmlspecialchars($workstation_name);
                            ?>
                            </td>
                            <td><?php echo htmlspecialchars($file['date_uploaded']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No files found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </aside>

    <div class="srt">
        <h3 style="margin-right: auto;">SRT FILES</h3>
        <button popovertarget="research_titles_form">Add New SRT File</button>
    </div>

    <div id="research_titles_form" class="research_titles_form" popover>
        <iframe class="research_titles_form_iframe" src="research_titles_form.php" frameborder="0"></iframe>
    </div>

    <!-- Research Titles Table -->
    <aside>
        <table id="research-titles-table">
            <thead>
                <tr>
                    <th>TITLE NAME</th>
                    <th>COLLEGE</th>
                    <th>PROGRAM</th>
                    <th>Date Uploaded</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($research_titles)): ?>
                    <?php foreach ($research_titles as $title): ?>
                        <tr>
                            <td class="title-name"><?php echo htmlspecialchars($title['titlename']); ?></td>
                            <td><?php echo htmlspecialchars($title['college']); ?></td>
                            <td><?php echo htmlspecialchars($title['program']); ?></td>
                            <td><?php echo htmlspecialchars($title['date_uploaded']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No research titles found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </aside>

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

</body>
</html>