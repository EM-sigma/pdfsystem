<?php
// Check if a message is passed in the query string
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    
    // Display the corresponding message with CSS classes
    if ($message == 'success') {
        echo '<div class="message success">The file has been submitted successfully!</div>';
    } elseif ($message == 'error') {
        echo '<div class="message error">Error submitting the files. Please try again.</div>';
    } elseif ($message == 'error_file_upload') {
        echo '<div class="message error">Error with the file upload. Please try again.</div>';
    } elseif ($message == 'error_preparing_query') {
        echo '<div class="message error">Error preparing the query. Please try again later.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            background: none;
            font-family: sans-serif;
        }

        .message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border: 1px solid #333;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 1rem;
        }

        .success {
            background-color: #fff; /* Green background */
            color: green;
            border: 1px solid green;
        }

        .error {
            background-color: #fff; /* Red background */
            color: red;
            border: 1px solid red;
        }

        .warning {
            background-color: #fff; /* Orange background */
            color: red;
            border: 1px solid red;
        }

        .form-container {
            height: fit-content;
            width: 400px;
            padding: 20px;
            padding-top: 15px;
            border: none;
            background: green;
            border: 1px solid #eee;
            
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            border: 1px solid #fff; 
            box-shadow: 3px 3px 0 #999;
        }

        h3 {
            text-align: center;
            margin-bottom: 10px;
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
            margin-bottom: 0;
        }

        .request-form select {
            margin-right: 10px;
        }

        .request-form input[type='submit'] {
            border: none;
            border: 1px solid #ccc;
            background: none;
            font-size: 1rem;
            padding: 5px 10px;
            width: 100%;
        }

        .request-form input[type='submit']:hover {
            background:rgba(67, 150, 63, 0.2);
            color: green;
            border-color: green;
        }

        label {
            font-size: 1rem;
        }

    </style>
</head>
<body>
<div class="form-container" id="form-container">
    <h3>Add File for Student Research Title</h3>
    <form class="request-form" action="send_request.php" method="POST" enctype="multipart/form-data">

        <!-- Title Field -->
        <label for="titlename">PDF Title:</label>
        <input type="text" id="titlename" name="titlename" required><br><br>

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
            <option value="COT">COT</option>
        </select><br><br>

        <!-- Upload Approval Sheet -->
        <label for="approvalsheet">Upload File (PDF):</label>
        <input type="file" id="approvalsheet" name="approvalsheet" accept="application/pdf" required><br><br>

        <!-- Submit Button -->
        <input type="submit" value="Insert">
        
    </form>
</div>


</body>
</html>