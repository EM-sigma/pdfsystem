<?php

require 'db.php'; // Include your database connection file

// Handle form submissions
if (isset($_POST['add_workstation'])) {
    $workstationName = $_POST['workstationName'];
    $workstationName = $conn->real_escape_string($workstationName); // Sanitize input

    $sql = "INSERT INTO workstations (name) VALUES ('$workstationName')";

    if ($conn->query($sql) === TRUE) {
        // Success message or redirect
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

if (isset($_POST['delete_workstation'])) {
    $id = $_POST['id'];
    $id = intval($id); // Ensure ID is an integer

    $sql = "DELETE FROM workstations WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        // Success message or redirect
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch workstations from the database
$sql = "SELECT id, name AS workstation FROM workstations";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workstation Management</title>
    <style>
                * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f8f8f8;
        }

        aside {
            border: 1px solid #fff;
        }

        table {
            position: relative;
            margin: 0 auto;
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        th, td {
            border: 1px solid #999;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #f5f5f5;
            padding: 10px;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        form button, input[type="text"] {
            width: 100%;
            background: none;
            border: 1px solid #ccc;
            display: block;
            padding: 5px;
            text-align: center;
        }

        .popover button {
            box-shadow: 3px 3px 0 #999;
        }

        .popover button:hover {
            border-color: green;
            color: #fff;
            background: green;
        }

        .delete-btn:hover {
            background: red;
            border-color: red;
            color: #fff;    
        }

        .btn {
            display: flex;
            align-items: center;
            margin-bottom: 20px
        }

        #form-btn {
            background: none;
            border: 1px solid #ccc;
            box-shadow: 3px 3px 0 #999;
            padding: 5px 10px;
            font-size: 1.2rem;
            margin-left: auto;
        }

        #form-btn:hover {
            border-color: #000;
            color: #fff;
            background: #000;
        }
        
        .popover {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border: 1px solid #fff;
            box-shadow: 3px 3px 0 #999;
            z-index: 1000;
        }

        .popover h3 {
            margin-bottom: 20px;
        }

        .popover input, .popover button {
            padding: 8px;
        }


        @media only screen and (max-width: 600px) {
            body {
                padding: 5px;
            }
            .btn {
                margin: 10px 0;
            }
            h3 {
                font-size: 1rem;
                text-align: center;
            }
            .popover label {
                font-size: 10px;
            }
            h2 {
                font-size: 10px;
            }
            #search {
                padding: 5px;
                font-size: 10px;
                width: 100px;
            }
            th, td {
                font-size: 7px;
                padding: 3px;
            }
            th {
                padding: 5px;
            }
            .back a {
                font-size: 8px;
                margin-bottom: 10px;
                padding: 5px;
                box-shadow: 2px 2px 0 #999;
            }
            .delete-btn {
                padding: 5px;
                font-size: 10px;
            }
            #form-btn {
                font-size: 10px;
                padding: 3px 5px;
                box-shadow: 2px 2px 0 #999;
            }
            .approve {
                margin-bottom: 3px;
            }
            .approve, .reject {
                padding: 3px 5px;
                font-size: 10px;
                display: block;
                width: 100%;
            }
            .popover button, input[type="text"] {
                padding: 3px 5px;
            }
            .popover {
                padding: 10px;
            }
        }

    </style>
</head>
<body>
    <div class="btn">
        <h2>WORKSTATIONS</h2>
        <button id="form-btn" popovertarget="form">Add Workstation</button>
    </div>
    

    <div id="form" class="popover" popover>
        <h3>Add New Workstation</h3>
        <form id="workstationForm" action="" method="POST">
            <label for="workstationName">Workstation Name:</label><br>
            <input type="text" id="workstationName" name="workstationName"><br>
            <button type="submit" name="add_workstation">Add</button>
        </form>
    </div>

    <table>
        <tr>
            <th>INDEX</th>
            <th>WORKSTATION NAME</th>
            <th>ACTION</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['workstation']}</td>
                        <td>
                            <form action='' method='POST'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <button type='submit' name='delete_workstation' class='delete-btn'>Delete</button>
                            </form>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No workstations found</td></tr>";
        }
        ?>
    </table>

</body>
</html>
<?php $conn->close(); ?>