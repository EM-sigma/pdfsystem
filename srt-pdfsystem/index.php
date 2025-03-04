<?php

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'db.php';

if (!isset($_SESSION['srt_user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['srt_user'];
$username = $_SESSION['username'];
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the SQL query to fetch research titles
$sql = "SELECT id, titlename, college, program FROM research_titles WHERE status='approved'";

// Check if the search term is provided and modify the query accordingly
if (!empty($search_term)) {
    $sql .= " WHERE LOWER(titlename) LIKE ? OR LOWER(college) LIKE ? OR LOWER(program) LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term_like = "%" . strtolower($search_term) . "%";
    $stmt->bind_param("sss", $search_term_like, $search_term_like, $search_term_like);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$research_titles = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $research_titles[] = $row;
    }
} else {
    $research_titles = [];
}

$stmt = $conn->prepare("SELECT image FROM srt_users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($image_data);
$stmt->fetch();
$stmt->close();

$base64_image = '';
if ($image_data) {
    $base64_image = base64_encode($image_data);
    $image_src = 'data:image/jpeg;base64,' . $base64_image;
} else {
    $image_src = "images/bg.jpg";
}

$user_id = $_SESSION['srt_user'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Research Archives</title>
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
        }

        .header {
            position: fixed;
            top: 0;
            display: flex;
            align-items: center;
            width: 100%;
            padding: 30px;
            height: 90px;
            width: 100%;
            background: #fff;   
            z-index: 100;

            border-bottom: 1px solid #ccc;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-right: auto;
        }

        .profile img {
            width: 40px; /* Slightly smaller image */
            height: 40px;
            background-size: cover;
            border-radius: 50%;
            margin-right: 10px;

            border: 1px solid #333;
        }

        .profile h3 {
            font-size: 16px; /* Slightly smaller font */
            margin: 0; /* Remove default margin */
        }

        .workstation-details {
            text-align: right;
            font-size: 14px; /* Smaller font for details */
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .workstation-details p, .workstation-details .logout {
            color: #000;
            text-decoration: none;
            font-size: 1rem;
        }

        .workstation-details .logout {
            background: green;
            color: #fff;
            border-radius: 20px;
            padding: 10px 15px; 

            border: 1px solid transparent;
        }

        .workstation-details .logout:hover {
            border-color: #000;
        }

        .workstation-details p {
            border-radius: 20px;
            padding: 10px 15px; 
            border: 1px solid #333;
        }

        .get {
            position: fixed;
            top: 90px;
            height: 100px;
            width: 100%;
            padding: 20px;
            display: flex;
            align-items: center;
            background:rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(5px);
            z-index: 100;
            border-bottom: 1px solid #f8f8f8;       
        }

        .get input[type='text'] {
            margin-right: 10px;
            padding: 10px 5px;
        }

        .get #search {
            margin-right: auto;
            border: none;
            border: 1px solid #999;
            width: 200px;
        }

        .get button {
            color: #000;
            border: none;
            padding: 10px 15px;
            font-size: 1rem;
            border: 1px solid #333;
            background: none; 
            background: #fff;  
            box-shadow: 3px 3px 0 #999;  
        }

        .get button:hover {
            color: #fff;
            border-color: #000;
            background: #000;
        }

        aside {
            position: absolute;
            top: 190px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            width: 100%;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            border: 1px solid #ccc;
            background: #fff;
        }

        th, td {
            border: 1px solid #999;
            padding: 5px;
            text-align: center;
            font-size: 1rem;
        }

        th {
            background-color: #f5f5f5;
            padding: 10px;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        .send-download {
            border: 1px solid #999;
            width: 100%;
            margin: 0 auto;
            display: block;
            background: none;
            padding: 5px 15px;
            background: #fff;
            font-size: 1rem;
        }

        .send-download:hover {
            background: green;
            color: #fff;
            border-color: green;
        }

        .upload {
            position: relative;
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-right: 10px;
        }

        .upload input {
            background: none;
            border: none;
            border: 1px solid #ccc;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 1rem;
        }

        .upload input[type='file'] {
            position: relative;
            width: 100%;
            padding-right: 50px;
        }

        .upload input[type='submit'] {
            border: 1px solid #333;
            background: #fff;
            position: absolute;
            right: 3px;
            top: 3px;
            border-radius: 18px;
        }

        .upload input[type='submit']:hover {
            background: #000;
            color: #fff;
        }

        .upload .message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: fit-content;
            height: fit-content;
            padding: 50px;
            background: #eee;
            border: 1px solid #ccc;
        }

        .pending-files {
            width: 80%;
            height: 70vh;
            border: 1px solid #333;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            overflow: hidden;
        }

        .pending-files iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .profile button {
            background: none;
            border: none;
            width: fit-content;
        }

        .profile button img:hover {
            padding: 3px;
        }

        .user-profile {
            position: absolute;
            top: 70px;
            left: 30px;
            width: 150px;
            height: fit-content;
            border: none;
            padding: 3px;

            border: 1px solid #999;
        } 

        .user-profile img {
            height: 100px;
            width: 100%;
            background-size: 100% 100%;
            background-repeat: no-repeat;
        }

        .user-profile p {
            text-align: center;
            padding: 5px;
        }

        .user-profile button {
            background: none;
            border: none;
            border: 1px solid #ccc;
            width: 100%;
            padding: 5px;
        }

        .user-profile button:hover {
            background: #000;
            color: #fff;
        }

        .form-container {
            position: relative;
            height: fit-content;
            width: 400px;
            padding: 20px;
            border: none;
            background: green;
            border: 1px solid #fff;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 5px 5px 1px #999;  
        }

        .form-container h2 {
            font-size: 1rem;
            color: #fff;
        }

        .request-form {
            position: relative;
            padding: 20px;
            height: fit-content;
            width: fit-content;
            background: #fff;
            border: 1px solid #333;
        }

        .request-form input:not(input[type="submit"]), .request-form select {
            padding: 10px;
            border: 1px solid #ccc;
            width: 100%;
        }

        .request-form select {
            margin-right: 10px;
        }

        .request-form input[type='submit'] {
            border: none;
            border: 1px solid #ccc;
            background: none;
            font-size: 1rem;
            padding: 8px;
            width: 100%;
        }

        .request-form input[type='submit']:hover {
            background:rgba(67, 150, 63, 0.2);
            color: green;
            border-color: green;
        }

        .request-form input[type="file"]:hover {
            border-color: #999;
        }

        .request-form label {
            margin-bottom: 10px;
        }

        /* Error message styling */
        .error {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: red;
            background:rgba(150, 63, 63, 0.2);
            padding: 30px;
            z-index: 100;
        }

        /* Success message styling */
        .success {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: green;
            background:rgba(67, 150, 63, 0.2);
            padding: 30px;  
            z-index: 100;
        }

        .pending-files {
            width: 80%;
            height: 70vh;
            border: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            overflow: hidden;
            background: none;
            border: 1px solid #333;
        }

        .pending-files iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: none;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 1rem;
        }

    </style>
</head>
<body>
        <div class="header">
            <div class="profile">
                <button id="user-profile-btn"><img src="<?php echo $image_src; ?>" alt="Profile Picture"></button>
                <h3><?php echo $username; ?></h3>
            </div>
            <div class="workstation-details">
                    <div class="notification-bell">
                        <span class="badge" id="notification-badge"></span>
                    </div>
                 <a href="logout.php" class="logout">Logout</a>
            </div>
        </div>

        <div class="get">
            <input type="text" name="search" id="search" placeholder="Search title..." value="<?php echo htmlspecialchars($search_term); ?>">
            <div class="upload" id="upload">  
                <button popovertarget="form-container">Send File Request</button>
            </div>
            <button popovertarget="pending-files">Pending Request</button>
        </div>

        <div class="pending-files" id="pending-files" popover>
            <iframe src="pending_files.php" frameborder="0"></iframe>
        </div>

        <div class="form-container" id="form-container" popover>
            <h2>Send request through gmail</h2>
            <form class="request-form" action="send_request.php" method="POST">

                <!-- Hidden Email Field -->
                 <label for="email">Your Email Account</label>
                <input type="email" id="email" name="email" value="your_email_account@gmail.com" required><br><br>

                <!-- Title Field -->
                <label for="title">PDF Title:</label>
                <input type="text" id="title" name="title" required><br><br>

                <!-- Name Field -->
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required><br><br>

                <!-- Program Dropdown -->
                <label for="program">Program:</label>
                <select id="program" name="program" required>
                    <option value="BSCE - Bachelor of Science in Civil Engineering">BSCE - Bachelor of Science in Civil Engineering</option>
                    <option value="BSEE - Bachelor of Science in Electrical Engineering">BSEE - Bachelor of Science in Electrical Engineering</option>
                    <option value="BSCE - Bachelor of Science in Computer Engineering">BSCE - Bachelor of Science in Computer Engineering</option>
                    <option value="BSCS - Bachelor of Science in Computer Science">BSCS - Bachelor of Science in Computer Science</option>
                    <option value="BSIT - Bachelor of Science in Information Technology">BSIT - Bachelor of Science in Information Technology</option>
                    <option value="BSBA - Bachelor of Science in Business Administration Major">BSBA - Bachelor of Science in Business Administration Major</option>
                    <option value="BSA - Bachelor of Science in Accountancy">BSA - Bachelor of Science in Accountancy</option>
                    <option value="BSTM - Bachelor of Science in Tourism Management">BSTM - Bachelor of Science in Tourism Management</option>
                    <option value="BSHM - Bachelor of Science in Hospitality Management">BSHM - Bachelor of Science in Hospitality Management</option>
                    <option value="BSE - Bachelor of Science in Entrepreneurship">BSE - Bachelor of Science in Entrepreneurship</option>
                    <option value="BEEd - Bachelor in Elementary Education">BEEd - Bachelor in Elementary Education</option>
                    <option value="BSEd - Bachelor in Secondary Education">BSEd - Bachelor in Secondary Education</option>
                    <option value="BSAIS - Bachelor of Science in Accounting and Information System">BSAIS - Bachelor of Science in Accounting and Information System</option>
                    <option value="BSES - Bachelor of Science in Environmental Science">BSES - Bachelor of Science in Environmental Science</option>
                    <option value="BAT - Bachelor in Agricultural Technology">BAT - Bachelor in Agricultural Technology</option>
                    <option value="BSA - Bachelor of Science in Agriculture">BSA - Bachelor of Science in Agriculture</option>
                    <option value="BSF - Bachelor of Science in Fisheries">BSF - Bachelor of Science in Fisheries</option>
                    <option value="BAC - Bachelor of Arts in Communication">BAC - Bachelor of Arts in Communication</option>
                    <option value="BAPS - Bachelor of Arts in Political Science">BAPS - Bachelor of Arts in Political Science</option>
                    <option value="BSB - Bachelor of Science in Biology">BSB - Bachelor of Science in Biology</option>
                    <option value="BSSW - Bachelor of Science in Social Work">BSSW - Bachelor of Science in Social Work</option>
                    <option value="BSN - Bachelor of Science in Nursing">BSN - Bachelor of Science in Nursing</option>
                    <option value="BSCrim - Bachelor of Science in Criminology">BSCrim - Bachelor of Science in Criminology</option>
                    <option value="BSMid - Bachelor of Science in Midwifery">BSMid - Bachelor of Science in Midwifery</option>
                </select><br><br>

                <!-- College Dropdown -->
                <label for="college">College:</label>
                <select id="college" name="college" required>
                    <option value="CCS">CCS</option>
                    <option value="CAS">CAS</option>
                    <option value="CS">CS</option>
                    <option value="CE">CE</option>
                    <option value="CCJE">CCJE</option>
                    <option value="CANS">CANS</option>
                    <option value="COT">COT</option> <!-- Duplicate removed -->
                </select><br><br>

                <input type="file" name="title_id" id="title_id" value=""><br><br>

                <!-- Submit Button -->
                <input type="submit" value="Send">

            </form>
        </div>
        <div class="form-container" id="send_download_request" popover>
            <h2>Send request through gmail</h2>
            <form class="request-form" action="send_download_request.php" method="POST">
                <input type="hidden" id="research_id" name="research_id" value="">
                <!-- Hidden Email Field -->
                <label for="email">Your Email Account</label>
                <input type="email" id="email2" name="email2" value="your_email_account@gmail.com" required><br><br>

                <!-- Name Field -->
                <label for="name">Full Name:</label>
                <input type="text" id="name2" name="name2" required><br><br>

                <input type="submit" value="Send">
            </form>

        </div>
        <?php if (isset($_SESSION['status'])): ?>
            <!-- Show the error or success message -->
            <div class="<?php echo $_SESSION['error'] ? 'error' : 'success'; ?>">
                <?php
                echo $_SESSION['status'];  // Display the message
                unset($_SESSION['status']); // Clear the message from session
                ?>
            </div>
        <?php endif; ?>


        <aside>          
            <table id="research-titles-table">
                <thead>
                    <tr>
                        <th>TITLE</th>
                        <th>COLLEGE</th>
                        <th>PROGRAM</th>
                        <th>REQUEST FOR DOWNLOAD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($research_titles)): ?>
                        <?php foreach ($research_titles as $title): ?>
                            <tr>
                                <td class="title-name"><?php echo htmlspecialchars($title['titlename']); ?></td>
                                <td><?php echo htmlspecialchars($title['college']); ?></td>
                                <td><?php echo htmlspecialchars($title['program']); ?></td>
                                <td> <button class="send-download" popovertarget="send_download_request" 
                                        data-research-id="<?php echo $title['id'];?>">  
                                    Send request to download
                                </button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No research titles found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
         </aside>

    <script>
        $(document).ready(function() {
            $("#user-profile-btn").click(function() {
                window.location.href = "profile.php"; // Replace with your actual file name
            });
        });
        const uploadButtons = document.querySelectorAll('[popovertarget="form-container"]');
        uploadButtons.forEach(button => {
            button.addEventListener('click', () => {
                const titleId = button.dataset.titleId;
                document.getElementById('title_id').value = titleId; 
            });
        });
        const requestButtons = document.querySelectorAll('[popovertarget="send_download_request"]');
        requestButtons.forEach(button => {
            button.addEventListener('click', () => {
                const researchId = button.dataset.researchId;
                document.getElementById('research_id').value = researchId; 
            });
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
            }, 5000); // 3000 ms = 3 seconds
        }
    </script>

    <script>
        $(document).ready(function() {
            // Listen for changes in the search input
            $('#search').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase(); // Get the search term and convert it to lowercase

                // Target the rows in the research titles table
                $('#research-titles-table tbody tr').each(function() {
                    var titleName = $(this).find('.title-name').text().toLowerCase(); // Get the title name in lowercase
                    
                    if (titleName.indexOf(searchTerm) > -1) {
                        // If the title contains the search term, show the row
                        $(this).show();
                    } else {
                        // Otherwise, hide the row
                        $(this).hide();
                    }
                });
            });
        });
    </script>
    
</body>
</html>