<?php
session_start();  // Make sure this is at the top of your page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Management Office</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            height: 100vh;
            background: #eee;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            position: fixed;
            border: 1px solid #ccc;
            min-width: 50%;
            max-width: 90%;
            min-height: 50vh;
            max-height: fit-content;
            background: #fff;
            box-shadow: 0 0 10px #ddd;

            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .form-container {
            padding: 70px 15px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            width: fit-content;
            display: flex;
            flex-direction: column;
            margin: 0 auto;
            padding: 20px 50px;
            border: 1px solid #999;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        input:not(.wsf) {
            margin-bottom: 10px;
            padding: 10px 5px;
        }

        input {
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #e0e411;
            color: #333;
            border: none;
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;

            border: 1px solid transparent;
        }

        input[type="submit"]:hover {
            border-color: #333;
        }

        select {
            margin-bottom: 10px;
            padding: 5px;
            border: 1px solid #ccc;
        }

        select:hover {
            border-color: #333;
        }

        label {
            margin-bottom: 10px;
        }

        .link-container {
            width: 100%;
            display: flex;
            align-items: center;
            flex-direction: row;
            justify-content: center;
            column-gap: 20px;
        }

        .link {
            text-decoration: none;
            color: #333;
            text-align: center;
            display: block;
            padding: 10px 15px;
            border-radius: 20px;
            border: 1px solid #ccc;
        }

        .link:hover {
            border-color: #333;
        }

        .bg {
            position: relative;
            min-height: 100%;
            max-height: fit-content;
            width: 100%;
        }

        .bg img {
            position: absolute;
            top: 0;
            height: 100%;
            width: 100%;
            object-fit: cover;
            background-repeat: no-repeat;
        }

        /* Error message styling */
        .error {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: red;
            background:rgba(150, 63, 63, 0.2);
            backdrop-filter: blur(20px);
            padding: 30px;
        }

        /* Success message styling */
        .success {
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
            h2 {
                font-size: 14px;
            }
            input {
                font-size: 10px;
                padding: 3px 5px;
            }
            form {
                padding: 10px;
                margin-bottom: 10px;
            }
            label {
                margin-bottom: 5px;
                font-size: 10px;
            }
            .link-container .link {
                padding: 5px 10px;
                font-size: 15px;
            }
            .form-container {
                padding: 50px 10px;
            }
        }

    </style>
</head>
<body>
    <div class="login-container">
        <div class="bg"><img src="img/essu-bg.jpg" alt=""></div>
        <div class="form-container">
            <h2>Sign Up to Knowledge Management Office</h2>

            <?php if (isset($_SESSION['status'])): ?>
                <!-- Show the error or success message -->
                <div class="<?php echo $_SESSION['error'] ? 'error' : 'success'; ?>">
                    <?php
                    echo $_SESSION['status'];  // Display the message
                    unset($_SESSION['status']); // Clear the message from session
                    ?>
                </div>
            <?php endif; ?>


            <form action="send.php" method="post">
                <input type="text" name="full_name" placeholder="Your Full Name" required>
                <input type="hidden" name="subject" value="NEW MEMBER INQUIRY" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <input type="hidden" name="message" value="NEED YOUR APPROVAL" required>

                <label for="page">Choose site:</label>
                <select id="page" name="page">
                    <option value="Workstation Files">Workstation Files</option>
                    <option value="Student Research Title">Student Research Title</option>
                </select>

                <input type="submit" value="submit">
            </form>
            <div class="link-container">
                <a href="login.php" class="link">Login</a>
                <a class="link" href="/pdfsystem/">Home</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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
