<?php
session_start();
include 'db_connect.php'; 
if (isset($_POST['submit1'])) {
    $role = $_POST['role']; // Get the role ('hire' or 'job')

    // Determine user type
    if ($role === 'hire') {
        $_SESSION['user_type'] = 'client'; // Store in session
        echo "<script>window.location.href = 'signup.php';</script>";
    } elseif ($role === 'job') {
        $_SESSION['user_type'] = 'worker'; // Store in session
        echo "<script>window.location.href = 'signup.php';</script>";
    } else {
        die('Invalid role selected.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Role</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f8f8;
        }

        .container {
            text-align: center;
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px #8a2ce8;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #663399;
        }

        p {
            margin-bottom: 2rem;
            color: #663399;
        }

        .options {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .option {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 300px;
        }

        .option img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-right: 20px;
        }

        button {
            background-color: #663399;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Choose what you are looking for</h1>
        <p>No.1 Job platform for Bharat users</p>

        <form method="POST">
            <div class="options">
                <div class="option">
                    <img src="client.jpg" alt="I want to Hire">
                    <label for="hire">I want to Hire</label>
                    <input type="radio" id="hire" name="role" value="hire" required>
                </div>

                <div class="option">
                    <img src="worker.jpg" alt="I want a Job">
                    <label for="job">I want a Job</label>
                    <input type="radio" id="job" name="role" value="job" required>
                </div>
            </div>

            <button type="submit" name="submit1" id="submit1">Next</button>
        </form>
    </div>

</body>

</html>
