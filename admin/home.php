<?php
// Database Connection
include "db.php";

// Fetch total counts
$wsf_user_count = $conn->query("SELECT COUNT(*) as total FROM wsf_user")->fetch_assoc()['total'];
$srt_users_count = $conn->query("SELECT COUNT(*) as total FROM srt_users")->fetch_assoc()['total'];

$files_count = $conn->query("SELECT COUNT(*) as total FROM files WHERE status = 'approved' OR status = 'pending'")->fetch_assoc()['total'];
$research_titles_count = $conn->query("SELECT COUNT(*) as total FROM research_titles WHERE status = 'approved' OR status = 'pending'")->fetch_assoc()['total'];

// Fetch approved and pending files count from 'files' table
$approved_files = $conn->query("SELECT COUNT(*) as total FROM files WHERE status = 'approved'")->fetch_assoc()['total'];
$pending_files = $conn->query("SELECT COUNT(*) as total FROM files WHERE status = 'pending'")->fetch_assoc()['total'];

// Fetch approved and pending research titles count from 'research_titles' table
$approved_research = $conn->query("SELECT COUNT(*) as total FROM research_titles WHERE status = 'approved'")->fetch_assoc()['total'];
$pending_research = $conn->query("SELECT COUNT(*) as total FROM research_titles WHERE status = 'pending'")->fetch_assoc()['total'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }

        body {
            background-color: #f8f8f8;
        }

        h1 {
            margin-bottom: 20px;
        }

        .dashboard {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 3 Columns */
            gap: 50px;
        }

        .box {
            width: 300px;
            background: white;
            padding: 50px 15px;
            box-shadow: 7px 7px 0 #999;
            border: 1px solid #eee;
        }

        .box p {
            font-size: 18px;
            margin-bottom: 10px;
            text-align: center;
        }

        .box h1 {
            font-weight: bold;
            font-size: 5rem;
            text-align: center;
        }

        .box:hover {
            background: green;
            border-color: green;
            color: #fff;
        }

        .box span {
            text-align: left;
            padding-left: 20px;
        }

        /* Responsive Design */
        @media only screen and (max-width: 600px) { /* 1 Column for small screens  900 for medium screen*/
            .dashboard {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                width: 100%;
                padding: 20px;
                top: 25%;
            }
            .box {
                width: auto;
                padding: 10px 5px;
                box-shadow: 3px 3px 0 #999;
            }
            .box p {
                font-size: 10px;
            }
            .box h1 {
                font-size: 2rem;
            }
            .box span {
                font-size: 10px;
            }
        }

    </style>
</head>
<body>    
    <div class="dashboard">
        <div class="box">
            <p>Total Workstation Files Users</p>
            <h1><?php echo $wsf_user_count; ?></h1>
        </div>
        <div class="box">
            <p>Total Student Research Titles Users</p>
            <h1><?php echo $srt_users_count; ?></h1>
        </div>
        <div class="box">
            <p>Total Workstation Files</p>
            <h1><?php echo $files_count; ?></h1>
            <span>Pending Files: <?php echo $pending_files; ?></span><br>
            <span>Approved Files: <?php echo $approved_files; ?></span>
        </div>
        <div class="box">
            <p>Total Student Research Title Files</p>
            <h1><?php echo $research_titles_count; ?></h1>
            <span>Approved Files: <?php echo $approved_research; ?></span><br>
            <span>Pending Files: <?php echo $pending_research; ?></span>
        </div>
    </div>

</body>
</html>
