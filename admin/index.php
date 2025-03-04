<?php
session_start();
include "db.php";

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION["admin"])) {
    header("Location: /pdfsystem/"); // Assuming you have a login page
    exit();
}

// Fetch user image from the database
$adminUsername = $_SESSION["username"];
$query = "SELECT id, username, password, email, image FROM admin WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $adminUsername);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($adminId, $currentUsername, $currentPassword, $currentEmail, $image);
$stmt->fetch();

// Check if the image is a path or binary data
$imagePath = $image ? $image : "img/default-avatar.png"; // Fallback to a default image

$base64_image = '';
if ($image && file_exists($image)) {  // If the image is a path, check if the file exists
    // If the image is a path, convert the image file to base64
    $imageData = file_get_contents($image);
    $base64_image = base64_encode($imageData);  // Encode the image to base64
    $image_src = 'data:image/jpeg;base64,' . $base64_image; // Image source in base64 format
} elseif ($image) { // If image is binary data (stored directly in the DB)
    $base64_image = base64_encode($image);  // Encode the binary data to base64
    $image_src = 'data:image/jpeg;base64,' . $base64_image; // Image source in base64 format
} else {
    $image_src = "images/bg.jpg"; // Default image if no profile picture
}

// Handle form submission for updating user info
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the updated values from the form
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password']; // Optionally hash the password
    $newEmail = $_POST['email'];
    $newImage = $_FILES['image']['tmp_name'] ? file_get_contents($_FILES['image']['tmp_name']) : $image; // If a new image is uploaded

    // Optionally hash the password if it's being changed
    if (!empty($newPassword)) {
        $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    } else {
        $newPassword = $currentPassword; // Keep the old password if no new one is provided
    }

    // Update the admin data in the database
    $updateQuery = "UPDATE admin SET username = ?, password = ?, email = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssi", $newUsername, $newPassword, $newEmail, $newImage, $adminId);
    $stmt->execute();

    // Redirect after update
    header("Location: index.php"); // Redirect to the admin dashboard (or the same page)
    exit();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;    
        }

        .container {
            height: 100vh;
            width: 100%;
        }

        .top-nav {
            position: fixed;
            top: 0;
            right: 0;
            width: calc(100% - 100px);
            height: 100px;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
        }

        .top-nav .logout {
            padding: 5px 15px;
            border-radius: 20px;
            margin-left: auto;
            background: green;
            color: #fff;
            font-size: 1.3rem;
            border: 1px solid transparent;
            text-decoration: none;
        }

        .top-nav .logout:hover {
            border-color: #333;
        }

        .nav-container {
            position: fixed;
            height: 100%;
            width: 100px;
            padding: 25px;
            border-right: 1px solid #eee;
            background: #fff;
        }

        .nav-container button {
            background: none;
            border-color: transparent;
            height: fit-content;
            display: flex;
            justify-content: center;
            align-items: center;
            
        }

        ul {
            list-style-type: none;
            width: fit-content;
        }

        ul li:first-child {
            margin-bottom: 50px;
            border-radius: 50%;
            border: 1px solid #ccc;
            width: fit-content;
            padding: 3px;
        }

        ul li:first-child img {
            border-radius: 50%;
            height: 40px;
            width: 40px;
            object-fit: cover;
        }

        ul li:first-child:hover {
            border-color: #333;
        }

        ul li:not(:first-child) {
            margin-bottom: 15px;
        }

        ul li {
            padding: 10px;
            display: block;
            width: fit-content;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            border: 1px solid #eee;
        }

        ul li img {
            height: 35px;
            width: 35px;
            background-size: cover;
        }

        ul li:not(:first-child):hover {
            background: #eee;
        }

        iframe {
            position: fixed;
            right: 0;
            top: 100px;
            width: calc(100% - 100px);
            height: calc(100vh - 100px);
        }

        .admin {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 15px;
            border: 1px solid #ccc;
        }

        .admin h3 {
            text-align: center;
        }

        .admin form {
            margin-top: 20px;
        }

        .admin label {
            display: block;
            margin-bottom: 5px;
        }

        .admin input {
            margin-bottom: 15px;
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
        }

        .admin button {
            padding: 10px 15px;
            border: none;
            width: 100%;
            border: 1px solid #ccc;
            background: none;
        }

        .admin button:hover {
            border-color: green;
            background: green;
            color: #fff;
        }

        .admin input {
            border: none;
            border: 1px solid #ccc;
        }

        .admin input:hover {
            border-color: #999;
        }

        .admin form {
            margin-bottom: 20px;
        }

        .admin img {
            height: 120px;
            width: 100%;
            object-fit: cover;
            padding: 5px;
            border: 1px solid #ccc;
        }

        @media only screen and (max-width: 700px) {
            .top-nav {
                height: 50px;
                width: calc(100% - 50px);
                padding: 10px;
            }
            .top-nav label {
                font-size: 14px;
            }
            .top-nav input[type='search'] {
                padding: 3px 5px;
                width: 100px;
            }
            .top-nav img {
                height: 20px;
                width: 20px;
            }
            .top-nav .logout {
                font-size: 14px;
                padding: 3px 10px;
            }
            .nav-container {
                width: 50px;
                padding: 10px;
            }
            .nav-container li:first-child {
                font-size: 14px;
            }
            .nav-container li:not(:first-child) {
                padding: 3px;
            }
            .nav-container img {
                height: 20px;
                width: 20px;
            }
            ul li:first-child {
                padding: 1px;
            }
            ul li:first-child img {
                height: 25px;
                width: 25px;
            }
            iframe {
                height: calc(100vh - 50px);
                width: calc(100% - 50px);
                top: 50px;
            }


            .admin {
                width: 200px;
                padding: 10px;
                font-size: 10px;
            }
            .admin h3 {
                font-size: 1rem;
            }
            .admin input {
                width: 100%;
                padding: 5px;
                font-size: 10px;
            }
            .admin label {
                font-size: 1rem;
            }
            .admin button {
                padding: 5px;
            }
            .admin strong {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="top-nav">
            <a class="logout" href="/pdfsystem/">Logout</a>
        </div>
        <div class="nav-container">
            <ul>
                <li><button popovertarget="admin"><img src="<?php echo htmlspecialchars($image_src); ?>" alt="Admin Image"></button></li>

                <li><a href="home.php" target="frame">
                    <img src="img/home.png" alt=""></a>
                </li>
                <li><a href="files.php" target="frame">
                    <img src="img/files.png" alt=""></a>
                </li>
                <li><a href="members.php" target="frame">
                    <img src="img/members.png" alt=""></a>
                </li>
                <li><a href="workstation.php" target="frame">
                    <img src="img/workstation.png" alt=""></a>
                </li>
            </ul>
        </div>

        <iframe src="home.php" frameborder="0" name="frame"></iframe>
    </div>

    <div class="admin" id="admin" popover>
        <h3>Edit information</h3>

        <!-- Edit Form to Update Username, Password, and Email -->
        <form method="POST" enctype="multipart/form-data">
            <label for="username">Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($currentUsername); ?>">

            <label for="password">Password:</label>
            <input type="password" name="password" placeholder="Enter new password"><br>

            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($currentEmail); ?>">

            <label for="image">Profile Image:</label>
            <input type="file" name="image"><br>

            <button type="submit">Update</button>
        </form>

        <!-- Display current admin info -->
        <strong>USERNAME: </strong> <?php echo htmlspecialchars($currentUsername); ?><br>
        <strong>EMAIL: </strong> <?php echo htmlspecialchars($currentEmail); ?><br>
        <strong>IMAGE: </strong><br><br><img src="<?php echo htmlspecialchars($image_src); ?>" alt="Admin Image" width="50" height="50">  
    </div>
</body>
</html>
