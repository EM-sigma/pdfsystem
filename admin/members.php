<?php
include('db.php'); // Include the database connection file

// DELETE operation for wsf_user
// DELETE operation for wsf_user
if (isset($_POST['delete_wsf_user'])) {
    $id = $_POST['id'];
    
    // First, check if the workstation is being referenced by any other users
    $checkWorkstation = "SELECT COUNT(*) AS count FROM wsf_user WHERE workstation = (SELECT workstation FROM wsf_user WHERE id = $id)";
    $resultCheck = $conn->query($checkWorkstation);
    $rowCheck = $resultCheck->fetch_assoc();
    
    if ($rowCheck['count'] == 1) {
        // If this is the only record referencing that workstation, delete the reference in workstations table
        $deleteWorkstation = "DELETE FROM workstations WHERE id = (SELECT workstation FROM wsf_user WHERE id = $id)";
        $conn->query($deleteWorkstation);
    }

    // Now delete from wsf_user
    $sql = "DELETE FROM wsf_user WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo '<div class="success">Record deleted successfully</div>';
    } else {
        echo '<div class="error">Error: ' . $sql . '<br>' . $conn->error . '</div>';
    }
}


// DELETE operation for srt_user
if (isset($_POST['delete_srt_user'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM srt_users WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo '<div class="success">Record deleted successfully</div>';
        // Reset AUTO_INCREMENT if the table is empty
        $checkEmpty = "SELECT COUNT(*) as count FROM srt_users";
        $resultCheck = $conn->query($checkEmpty);
        $rowCheck = $resultCheck->fetch_assoc();
        if ($rowCheck['count'] == 0) {
            $resetAutoIncrement = "ALTER TABLE srt_users AUTO_INCREMENT = 1";
            $conn->query($resetAutoIncrement);
        }
    } else {
        echo '<div class="error">Error: ' . $sql . '<br>' . $conn->error . '</div>';
    }
}


// CREATE operation for wsf_user
if (isset($_POST['create_wsf_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $workstation = $_POST['workstation'];

    // Check if the workstation ID exists
    $workstationCheck = "SELECT COUNT(*) AS count FROM workstations WHERE id = ?";
    $stmtCheck = $conn->prepare($workstationCheck);
    $stmtCheck->bind_param("i", $workstation);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    if ($rowCheck['count'] > 0) {
        // The workstation exists, proceed with inserting the user
        if ($_FILES['image']['error'] == 0) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $stmt = $conn->prepare("INSERT INTO wsf_user (username, password, image, workstation) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $password, $imageData, $workstation);

            if ($stmt->execute()) {
                echo '<div class="success">User created successfully</div>';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo '<div class="error">Error: ' . $stmt->error . '</div>';
            }
        }
    } else {
        echo '<div class="error">Error: The specified workstation does not exist.</div>';
    }
}


// CREATE operation for srt_user
if (isset($_POST['create_srt_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Handle the image upload
    if ($_FILES['image']['error'] == 0) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        // The image will be stored as a binary LONGBLOB
        $stmt = $conn->prepare("INSERT INTO srt_users (username, password, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $imageData);

        if ($stmt->execute()) {
            // Reset AUTO_INCREMENT if the table is empty
            $resetAutoIncrement = "ALTER TABLE srt_users AUTO_INCREMENT = 1";
            $conn->query($resetAutoIncrement);
            // Success message
            echo '<div class="success">User created successfully</div>';
            // Redirect to avoid form resubmission on page refresh
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // Error message
            echo '<div class="error">Error: ' . $stmt->error . '</div>';
        }
    }
}

// Fetch wsf_user members
$sql = "SELECT wsf_user.id, wsf_user.username, workstations.name AS workstation_name, wsf_user.image 
        FROM wsf_user 
        LEFT JOIN workstations ON wsf_user.workstation = workstations.id";
$result = $conn->query($sql);

// Fetch srt_user members
$sql_srt_user = "SELECT * FROM srt_users";
$result_srt_user = $conn->query($sql_srt_user);

// Close the connection when done with queries
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View and Manage Users</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            padding: 10px;
            font-family: sans-serif;
            background: #f5f5f5;
        }

        a {
            border: none;
            text-decoration: none;
        }

        table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
        }

        table img {
            border: 1px solid #ccc;
            height: 50px;
            width: 50px;
            object-position: center;
        }

        h2 {
            padding: 10px 0 0 0;
            margin: 20px 0;
            font-size: 1.5rem;
        }

        th {
            background: #eee;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        .delete-btn {
            width: 100%;
            padding: 5px 10px;
            background-color: #fff;
            font-size: 1rem;
            border: none;
            border: 1px solid #ccc;
            height: 100%;
            display: block;
            border-radius: none;
        }

        .delete-btn:hover {
            border-color: red;
            color: #fff;
            background: red;
        }

        .wsf-btn, .srt-btn {
            background: none;
            border: 1px solid #ccc;
            padding: 5px 10px;  
            font-size: 1.2rem;
            box-shadow: 3px 3px 0 #999;
            background: #fff;
        }

        .wsf-btn {
            margin-right: 10px; 
        }

        button:hover {
            background: #000;
            color: #fff;
            border-color: #000;
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form {
            width: 15%;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background: #fff;
            border: 1px solid #ccc;
            box-shadow: 5px 5px 0 #999;
        }

        .form input {
            margin: 5px 0;
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
        }

        .form input[type='file'] {
            margin-bottom: 30px
        }

        .form input[type='file']:hover {
            border-color: #333;
        }

        .form button {
            width: 100%;
            background: none;
            padding: 10px;
            border: none;
            border: 1px solid #999;
            box-shadow: 3px 3px 0 #999;
        }

        .form button:hover {
            border-color: green;
            background: green;
            color: #fff;
        }

        /* Success message style */
        .success {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: green;
            background:rgba(67, 150, 63, 0.2);
            backdrop-filter: blur(20px);
            padding: 30px;
            z-index: 100;
        }

        .error {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: red;
            background:rgba(150, 63, 63, 0.2);
            backdrop-filter: blur(20px);
            padding: 30px;
            z-index: 100;
        }

        @media only screen and (max-width: 600px) {
            body {
                padding: 5px;
            }
            .wsf-btn, .srt-btn {
                font-size: 10px;
                box-shadow: 2px 2px 0 #999;
            }
            .form {
                padding: 7px;
            }
            h3 {
                font-size: 10px;
                margin-bottom: 5px;
            }
            .form input {
                width: 100px;
                width: 100%;
                font-size: 10px;
                padding: 5px;
                margin: 3px 0;
            }
            .form:last-child button {
                bottom: 7px;
            }
            .form:first-child button {
                width: 100%;
            }
            .form button {
                padding: 5px;
                font-size: 10px;
                width: calc(100% - 14px);
            }
            h2 {
                font-size: 10px;
                margin: 5px;
            }
            table img {
                height: 30px;
                width: 30px;
            }
            th, td {
                font-size: 8px;
                padding: 5px;
            }
            .delete-btn {
                display: block;
                width: 100%;
                font-size: 5px;
                padding: 3px 5px;
                border-color: #ccc;
            }
            .edit-btn {
                margin-bottom: 3px;
            }
        }
    </style>
</head>
<body>
    <button popovertarget="wsf" class="wsf-btn">Add Workstation Files</button>
    <button popovertarget="srt" class="srt-btn">Add Student Research Title Files</button>

    <div class="form-container">
        <div class="form" id="wsf" popover>
            <h3>Workstation Files</h3>
            <form name="create_wsf_user" id="wsfForm" method="POST" enctype="multipart/form-data" action="member_mail.php">
                <input type="text" name="fullname" placeholder="Full Name" required><br>
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <input type="text" name="workstation" placeholder="Workstation" required><br>
                <input class="file" type="file" name="image" required>
                <input type="hidden" name="form_type" value="wsf">
                <button type="submit" name="create_wsf_user">Create User</button>
            </form>
        </div>

        <div class="form" id="srt" popover>
            <h3>Student Research Title</h3>
            <form name="create_srt_user" id="srtForm" method="POST" enctype="multipart/form-data" action="member_mail.php">
                <input type="text" name="fullname" placeholder="Full Name" required><br>
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <input type="hidden" name="workstation" value="Student Research Title"><br>
                <input class="file" type="file" name="image" required>
                <input type="hidden" name="form_type" value="srt">
                <button type="submit" name="create_srt_user">Create User</button>
            </form>
        </div>
    </div>



        <h2>MEMBERS OF WORKSTATION FILES</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>USERNAME</th>
                <th>WORKSTATION</th>
                <th>IMAGE</th>
                <th>ACTION</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $imageData = $row['image']; // Get binary image data from database
                    if ($imageData) {
                        $imageSrc = 'data:image/jpeg;base64,' . base64_encode($imageData);
                    } else {
                        $imageSrc = 'path/to/default/image.jpg'; // Fallback if no image
                    }

                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['username']}</td>
                            <td>{$row['workstation_name']}</td>
                            <td><img src='$imageSrc' width='50'></td>
                            <td>
                                <form action='' method='POST'>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                    <button type='submit' name='delete_wsf_user' class='delete-btn'>Delete</button>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No members found</td></tr>";
            }
            ?>
        </table>

        <h2>MEMBERS OF STUDENT RESEARCH TITLE</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>USERNAME</th>
                <th>IMAGE</th>
                <th>ACTION</th>
            </tr>

            <?php
            if ($result_srt_user->num_rows > 0) {
                while ($row = $result_srt_user->fetch_assoc()) {
                    $imageData = $row['image']; // Get binary image data from database
                    if ($imageData) {
                        $imageSrc = 'data:image/jpeg;base64,' . base64_encode($imageData);
                    } else {
                        $imageSrc = 'path/to/default/image.jpg'; // Fallback if no image
                    }

                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['username']}</td>
                            <td><img src='$imageSrc' width='50'></td>
                            <td>
                                <form action='' method='POST'>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                    <button type='submit' name='delete_srt_user' class='delete-btn'>Delete</button>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No members found</td></tr>";
            }
            ?>
        </table>



    <script>
        // Reset forms after submission (now after page reload from member_mail.php)
            document.addEventListener('DOMContentLoaded', function() {
            // You might need to add logic to check if the submission to member_mail.php was successful
            // before resetting the forms. For example, you could pass a parameter in the URL
            // from member_mail.php to indicate success.
            const urlParams = new URLSearchParams(window.location.search);
            const formReset = urlParams.get('form_reset');

            if (formReset === 'true') {
                const createWsfUserForm = document.getElementById('wsfForm');
                const createSrtUserForm = document.getElementById('srtForm');

                if (createWsfUserForm) {
                    createWsfUserForm.reset();
                }

                if (createSrtUserForm) {
                    createSrtUserForm.reset();
                }
            }
        });
    </script>

    <script>
        window.onload = function() {
            var success = document.querySelector('.success'); // Assuming you have a class 'success' for success messages
            var error = document.querySelector('.error'); // Assuming you have a class 'error' for error messages
            
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
