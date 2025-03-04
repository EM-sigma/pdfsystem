<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if there's an error with the login
if (isset($_SESSION['login_error'])) {
    $loginError = $_SESSION['login_error'];
    unset($_SESSION['login_error']);  // Clear the error after it's shown
} else {
    $loginError = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Knowledge Management</title>
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
            padding: 25px 15px;
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

        input:first-child, input:nth-child(2) {
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
            min-height: 50vh;
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

        .error {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: red;
            background-color: #f8d7da;
            border: 1px solid red;
            padding: 10px;
            border-radius: 5px;
        }

        .input-error {
            border-color: red;
        }

        @media only screen and (max-width: 700px) {
            h2 {
                font-size: 14px;
                margin-bottom: 10px
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
                padding: 20px 10px;
            }
        }
    </style>
</head>
<body>
    <a class="home" href="index.php">Home</a>
    <div class="login-container">
        <div class="bg"><img src="img/essu-bg.jpg" alt=""></div>
            <div class="form-container">
                <h2>Login to Knowledge Management Office</h2>
                <form action="action.php" method="post">
                    <input type="text" name="username" id="username" placeholder="Username" 
                        class="<?= $loginError ? 'input-error' : '' ?>" required>
                    <input type="password" name="password" id="password" placeholder="Password" 
                        class="<?= $loginError ? 'input-error' : '' ?>" required>

                    <label for="loginFor">Login for:</label>
                    <input type="hidden" name="loginFor" id="loginFor">
                    <input type="submit" value="Admin" onclick="setLoginFor('Admin')">
                    <input type="submit" value="Student Research Title" onclick="setLoginFor('Student Research Title')">
                    <input class="wsf" type="submit" value="Workstation Files" onclick="setLoginFor('Workstation Files')">
                </form>

                <!-- Error message display -->
                <?php if ($loginError): ?>
                    <div class="error"><?= $loginError ?></div>
                <?php endif; ?>

                <div class="link-container">
                    <a href="sign-up.php" class="link">Sign Up</a>
                    <a class="link" href="index.php">Home</a>
                </div>
            </div>
    </div>

    <script>
        function setLoginFor(loginType) {
            document.getElementById("loginFor").value = loginType;
        }
    </script>

    <script>
        window.onload = function() {
            var error = document.querySelector('.error'); // Assuming you have a class 'error' for error messages

            // Check if the error message element exists and is visible
            if (error) {
                // Make the error message visible
                error.style.display = 'block';

                // Hide the error message after 3 seconds (3000 ms)
                setTimeout(function() {
                    error.style.display = 'none';
                }, 3000);
            }
        }
    </script>

</body>
</html>
