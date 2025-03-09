<?php
// Start the session to store login status
session_start();

include 'db_connect.php'; 
if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Validation checks
    if(strlen($username) < 5) {
        echo "<script>alert('Username must be at least 5 characters long.');</script>";
    } 
    else if(strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.');</script>";
    } 
    else if(!preg_match('/\d/', $password)) {
        echo "<script>alert('Password must contain at least one number.');</script>";
    }
    else if(!preg_match('/[a-z]/', $password)) {
        echo "<script>alert('Password must contain at least one lowercase letter.');</script>";
    }
    else if(!preg_match('/[A-Z]/', $password)) {
        echo "<script>alert('Password must contain at least one uppercase letter.');</script>";
    }

    $stmt = $conn->prepare("SELECT * FROM `signup-b` WHERE `username` = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type1'] = $user['usertype'];
            $_SESSION['logged_in'] = true;

            // **Check if the profile exists**
            $user_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type1'];

            if ($user_type === 'worker') {
                $stmt2 = $conn->prepare("SELECT * FROM worker WHERE user_id = ?");
            } elseif ($user_type === 'client') {
                $stmt2 = $conn->prepare("SELECT * FROM client WHERE user_id = ?");
            }

            if (isset($stmt2)) {
                $stmt2->bind_param("i", $user_id);
                $stmt2->execute();
                $result2 = $stmt2->get_result();

                if ($result2->num_rows > 0) {
                    $_SESSION['profile_created'] = true; // Profile exists
                } else {
                    $_SESSION['profile_created'] = false; // No profile found
                }

                $stmt2->close();
            }

            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('No user found with that username'); window.location.href='login.php';</script>";
    }
}
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="logsign.css">
</head>

<body style="background-color: #fff;">
    <div class="container">
        <div class="left-section">
            <h1>Sign in to</h1>
            <h2>BUSINESS SERVICE<br> SYSTEM</h2>
            <p>if you don't have an account, <br> you can <a href="asking.php">Register here!</a></p>
        </div>

        <div class="image-section">
            <img src="image1.jpeg" alt="Character Illustration">
        </div>

        <div class="right-section">
            <form action="login.php" method="post" id="loginForm">
                <h2>Login</h2>
                <input type="text" id="username" name="username" placeholder="Enter user name" required>
                <div class="password">
                    <input type="password" id="password" name="password" placeholder="Password" required><br>
                    <img src="hide-password2.png" onclick="pass()" class="pass-icon" id="pass-icon">
                </div>
                <a href="email.php" class="forgot-password">Forget password?</a>
                <button class="btn" type="submit" id="submit" name="submit">Login</button>
                
                </div>
            </form>
        </div>
    </div>

    <script>
        var a;
        function pass() {
            if (a == 1) {
                document.getElementById('password').type = 'password';
                document.getElementById('pass-icon').src = 'hide-password2.png';
                a = 0;
            } else {
                document.getElementById('password').type = 'text';
                document.getElementById('pass-icon').src = 'hide-password.png';
                a = 1;
            }
        }

        
    </script>

</body>

</html>
