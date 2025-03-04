<?php
session_start();
include "db.php";

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION["admin"])) {
    header("Location: /pdfsystem/"); // Assuming you have a login page
    exit();
}

// Handle form submission
function getUsers($conn, $search = null) {
    $wsfUsers = [];
    $srtUsers = [];

    $wsfQuery = "SELECT id, username, image FROM wsf_user";
    $srtQuery = "SELECT id, username, image FROM srt_users";

    if ($search) {
        $search = "%" . $search . "%";
        $wsfQuery .= " WHERE username LIKE ?";
        $srtQuery .= " WHERE username LIKE ?";
    }

    $wsfStmt = $conn->prepare($wsfQuery);
    $srtStmt = $conn->prepare($srtQuery);

    if ($search) {
        $wsfStmt->bind_param("s", $search);
        $srtStmt->bind_param("s", $search);
    }

    // Process wsf users
    $wsfStmt->execute();
    $wsfResult = $wsfStmt->get_result();

    while ($row = $wsfResult->fetch_assoc()) {
        $wsfUsers[] = $row;
    }

    $wsfResult->free_result();
    $wsfStmt->close();

    // Process srt users
    $srtStmt->execute();
    $srtResult = $srtStmt->get_result();

    while ($row = $srtResult->fetch_assoc()) {
        $srtUsers[] = $row;
    }

    $srtResult->free_result();
    $srtStmt->close();

    return ['wsfUsers' => $wsfUsers, 'srtUsers' => $srtUsers];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"])) {
    $message = $_POST["message"];
    $recipient = $_POST["recipient"];

    if ($recipient == "all") {
        // Send to all users
        $users = getUsers($conn);
        foreach ($users['wsfUsers'] as $user) {
            $userId = $user['id'];
            $userTable = "wsf_user";
            $messageQuery = "INSERT INTO messages (sender, recipient_id, recipient_table, message_text) VALUES (?, ?, ?, ?)";
            $messageStmt = $conn->prepare($messageQuery);
            $messageStmt->bind_param("siss", $_SESSION['username'], $userId, $userTable, $message);
            $messageStmt->execute();
            $messageStmt->close();
        }
        foreach ($users['srtUsers'] as $user) {
            $userId = $user['id'];
            $userTable = "srt_users";
            $messageQuery = "INSERT INTO messages (sender, recipient_id, recipient_table, message_text) VALUES (?, ?, ?, ?)";
            $messageStmt = $conn->prepare($messageQuery);
            $messageStmt->bind_param("siss", $_SESSION['username'], $userId, $userTable, $message);
            $messageStmt->execute();
            $messageStmt->close();
        }
    } elseif ($recipient == "allwsf") {
        // Send to all wsf users
        $users = getUsers($conn);
        foreach ($users['wsfUsers'] as $user) {
            $userId = $user['id'];
            $userTable = "wsf_user";
            $messageQuery = "INSERT INTO messages (sender, recipient_id, recipient_table, message_text) VALUES (?, ?, ?, ?)";
            $messageStmt = $conn->prepare($messageQuery);
            $messageStmt->bind_param("siss", $_SESSION['username'], $userId, $userTable, $message);
            $messageStmt->execute();
            $messageStmt->close();
        }
    } elseif ($recipient == "allsrt") {
        // send to all srt users
        $users = getUsers($conn);
        foreach ($users['srtUsers'] as $user) {
            $userId = $user['id'];
            $userTable = "srt_users";
            $messageQuery = "INSERT INTO messages (sender, recipient_id, recipient_table, message_text) VALUES (?, ?, ?, ?)";
            $messageStmt = $conn->prepare($messageQuery);
            $messageStmt->bind_param("siss", $_SESSION['username'], $userId, $userTable, $message);
            $messageStmt->execute();
            $messageStmt->close();
        }
    }
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : null;
$users = getUsers($conn, $search);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <script src="./assets/js/jquery.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
    <style>
        /* Add your styles here */
        .user-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .user-item {
            display: flex;
            align-items: center;
            padding: 5px;
            border-bottom: 1px solid #ccc;
        }
        .user-item img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }
        body {
             margin:0 !important;
             padding:0 !important;
             box-sizing: border-box;
             font-family: 'Roboto', sans-serif;
        }
        .round{
          width:20px;
          height:20px;
          border-radius:50%;
          position:relative;
          background:red;
          display:inline-block;
          padding:0.3rem 0.2rem !important;
          margin:0.3rem 0.2rem !important;
          left:-18px;
          top:10px;
          z-index: 99 !important;
        }
        .round > span {
          color:white;
          display:block;
          text-align:center;
          font-size:1rem !important;
          padding:0 !important;
        }
        #list{
         
          display: none;
          top: 33px;
          position: absolute;
          right: 2%;
          background:#ffffff;
  z-index:100 !important;
    width: 25vw;
    margin-left: -37px;
   
    padding:0 !important;
    margin:0 auto !important;
    
          
        }
        .message > span {
           width:100%;
           display:block;
           color:red;
           text-align:justify;
           margin:0.2rem 0.3rem !important;
           padding:0.3rem !important;
           line-height:1rem !important;
           font-weight:bold;
           border-bottom:1px solid white;
           font-size:1.8rem !important;

        }
        .message{
          /* background:#ff7f50;
          margin:0.3rem 0.2rem !important;
          padding:0.2rem 0 !important;
          width:100%;
          display:block; */
          
        }
        .message > .msg {
           width:90%;
           margin:0.2rem 0.3rem !important;
           padding:0.2rem 0.2rem !important;
           text-align:justify;
           font-weight:bold;
           display:block;
           word-wrap: break-word;
         
          
        }
    </style>
</head>
<body>
    <h2>Send Announcement</h2>
    <div class="container">


    <h2>User List</h2>
    <div class="user-list">
        <?php foreach ($users['wsfUsers'] as $user): ?>
            <div class="user-item">
                <?php if ($user['image']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($user['image']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>'s Profile Picture">
                <?php else: ?>
                    <img src="img/default-avatar.png" alt="Default Profile Picture">
                <?php endif; ?>
                <?php echo htmlspecialchars($user['username']); ?> (WSF)
            </div>
        <?php endforeach; ?>
        <?php foreach ($users['srtUsers'] as $user): ?>
            <div class="user-item">
                <?php if ($user['image']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($user['image']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>'s Profile Picture">
                <?php else: ?>
                    <img src="img/default-avatar.png" alt="Default Profile Picture">
                <?php endif; ?>
                <?php echo htmlspecialchars($user['username']); ?> (SRT)
            </div>
        <?php endforeach; ?>
    </div>
</body>


</html>