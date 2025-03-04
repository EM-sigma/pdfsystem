<?php
include('db.php'); // Include the database connection file

// Get the user ID from the URL (for editing)
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the current user data based on the ID from the wsf_user table
    $sql = "SELECT * FROM wsf_user WHERE id = $id";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
    
    // If not found in wsf_user, try fetching from srt_users table
    if (!$user) {
        $sql = "SELECT * FROM srt_users WHERE id = $id";
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();
    }
}

if (isset($_POST['update_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];  // Only update if a new password is provided
    $workstation = $_POST['workstation'];

    // Prepare the SQL update query using a prepared statement
    if (isset($user['workstation'])) {
        // Update wsf_user (image won't be processed or updated)
        $stmt = $conn->prepare("UPDATE wsf_user SET username=?, password=?, workstation=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $password, $workstation, $id);
    } else {
        // Update srt_users (image won't be processed or updated)
        $stmt = $conn->prepare("UPDATE srt_users SET username=?, password=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $password, $id);
    }

    // Execute the statement
    if ($stmt->execute()) {
        echo "Record updated successfully";
        // Optionally, redirect after success to prevent form resubmission
        // header("Location: " . $_SERVER['PHP_SELF']);
        // exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        /* Simple CSS to format the form */
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: fit-content;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            border: 1px solid #ccc;
        }
        .form:hover {
            border-color: #333;
        }
        form input {
            margin: 5px 0;
            padding: 10px;
            width: 200px;
            border: 1px solid #ccc;
        }
        form button {
            width: 100%;
            background: #EEEF58;
            padding: 10px;
            border: none;
            border-radius: 20px;
        }
        form button:hover {
            border-color: #333;
        }
        .back {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 5px 10px;
            text-decoration: none;
            color: #000;
            border: 1px solid transparent;
            background: #fff;
            border: 1px solid #ccc;
        }
        .back:hover {
            border-color: #333;
            background: #000;
            color: #fff;
        }

        @media only screen and (max-width: 700px) {
            h3 {
                font-size: 14px;
            }
            .back {
                padding: 3px 10px; 
                font-size: 14px;
            }
            .form {
                padding: 10px;
            }
            form input {
                margin: 3px 0;
                width: 100%;
                padding: 5px;
                font-size: 10px;
            }
            form button {
                font-size: 1rem;
                padding: 5px;
            }
        }
    </style>
</head>
<body>

<a class="back" href="members.php">Back</a>

<!-- HTML form to edit user -->
<div class="form">
    <h3>EDIT USER</h3>
    <form method="POST" enctype="multipart/form-data">
        Username <br><input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required><br><br>
        Password <br><input type="password" name="password" value="" placeholder="New Password"><br><br>
        Workstation <br><input type="text" name="workstation" value="<?php echo htmlspecialchars($user['workstation'] ?? ''); ?>" placeholder="Workstation"><br>
        <button type="submit" name="update_user">Update User</button>
    </form>
</div>

<script>
    // Reset form after submission or page load
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.reset();
        }
    });
</script>

</body>
</html>
