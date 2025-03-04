<?php
include "db.php"; // Include your database connection

session_start();
if (!isset($_SESSION['srt_user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['srt_user'];
$username = $_SESSION['username'];

// Fetch user data from the database
$stmt = $conn->prepare("SELECT username, password, image FROM srt_users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_username, $db_password, $db_image);
$stmt->fetch();
$stmt->close();

// Convert BLOB to base64 for display
$base64_image = '';
if ($db_image) {  // Check if the image data exists
    $base64_image = base64_encode($db_image);  // Encode the image to base64
    $image_src = 'data:image/jpeg;base64,' . $base64_image; // Image source in base64 format
} else {
    $image_src = "images/bg.jpg"; // Default image if no profile picture
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>User Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #eee;

            font-family: sans-serif;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 40%;
            position: relative;
            min-width: 50%;
            max-width: 90%;
            height: 60vh;
            border: 1px solid #aaa;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 0 5px #ccc;
        }

        .container h1 {
            text-align: center;
            display: block;
            width: 100%;
            margin-bottom: 20px;
        }

        .container img {
            width: 100%;
            height: 60vh;
            object-fit: cover;
            margin: auto;
            display: flex;
            align-self: center;
            padding: 10px;
            border-right: 1px solid #ccc;
        }

        form {
            padding: 20px;  
        }

        button {
            font-size: 1.3rem;
        }

        .back {
            position: absolute;
            right: 20px;
            bottom: 20px;
            border: none;
            background: none;
            border: 1px solid #999;
            padding: 5px 15px;
            width: fit-content;
            box-shadow: 3px 3px 0 #999;
        }

        .submit {
            border: none;
            background: none;
            border: 1px solid #999;
            padding: 5px 15px;
            width: fit-content;
            box-shadow: 3px 3px 0 #999;
        }

        button:hover {
            background: green;;
            color: #fff;
            border-color: green;
        }

        .form-group label {
            font-size: 1.5rem;
        }

        #ws {
            font-size: 1.5rem;
        }

        input[type='text'], input[type='password'] {
            margin-bottom: 10px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
        }

        input[type='file'] {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px;
        }

        input[type='file']:hover {
            border-color: #333;
        }

        .submit {
            margin-right: 10px;
        }

        .form-group:last-child {
            position: absolute;
            bottom: 20px;
            display: inline-block;
        }

        .alert {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: green;
            background:rgba(67, 150, 63, 0.2);
            backdrop-filter: blur(20px);
            padding: 30px;
        }

        @media only screen and (max-width: 700px) {
            .form-group label {
                font-size: 12px;
            }
            h1 {
                font-size: 1rem;
            }
            .submit {
                width: 100%;
                display: block;
            }
        }

        @media only screen and (max-width: 700px) {
            .form-group label {
                font-size: 12px;
            }
            h1 {
                font-size: 1rem;
            }
            .container {
                grid-template-columns: 1fr 1fr;
            }
            .submit {
                width: 100%;
                display: block;
                font-size: 10px;
                padding: 5px;
            }
            input[type='file'], input[type='text'], input[type='password'] {
                padding: 5px;
            }
            .back {
                font-size: 10px;
                padding: 5px;
            }
            form {
                padding: 10px;
            }
            #ws {
                font-size: 1rem;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="img"><img id="profileImagePreview" src="<?php echo $image_src; ?>" alt="Profile Picture"></div>
        <form method="POST" action="profile_update.php" enctype="multipart/form-data">
            <!-- Display the profile image -->
            
            <h1><?php echo htmlspecialchars($username); ?></h1>

            <input type="file" id="image" name="image" accept="image/*">

            <div class="form-group">
                <label for="username">Username: </label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password: </label>
                <input type="password" id="password" name="password" placeholder="Leave empty if unchanged">
            </div>

            <div class="form-group">
                <button class="submit" type="submit">Update Profile</button>
            </div>    
            
        </form>
        <button display="inline-block;" class="back" id="wsf-dashboard-btn">Back</button>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="alert">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>


    <script>
        // JavaScript to preview the image before uploading
        document.getElementById("image").addEventListener("change", function(e) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById("profileImagePreview").src = event.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        });

        // Back button functionality
        $(document).ready(function() {
            $("#wsf-dashboard-btn").click(function() {
                window.location.href = "index.php"; // Replace with your actual file name
            });
        });
    </script>

    <script>
        window.onload = function() {
        var messageBox = document.querySelector('.alert');
        
        // Display the message box
        messageBox.style.display = 'block';
        
        // Hide the message box after 5 seconds
        setTimeout(function() {
            messageBox.style.display = 'none';
        }, 5000); // 5000 ms = 5 seconds
    }
    </script>
</body>
</html>

