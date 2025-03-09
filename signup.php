<?php
session_start();
if (!isset($_SESSION['user_type'])) {
    echo "<script>alert('No role selected. Please go back and select a role.');</script>";
    exit;
}

$user_type = $_SESSION['user_type']; // Access the user type from session

include 'db_connect.php'; 

if (isset($_POST["submit"])) {
    // Sanitize inputs
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = $_POST["password"];
    $confirmpassword = $_POST["confirmPassword"];

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
    else if ($password !== $confirmpassword) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM `signup-b` WHERE `username` = ? OR `email` = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('Username or Email Has Already Taken');</script>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new data into the database
            $stmt_insert = $conn->prepare("INSERT INTO `signup-b` (`username`, `email`, `password`, `usertype`) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $user_type);

            if ($stmt_insert->execute()) {
                // Redirect to login page after successful registration
                echo "<script>
                        alert('Data submitted successfully!');
                        window.location.href = 'login.php'; 
                    </script>";
            } else {
                echo "Query failed...";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <link rel="stylesheet" href="logsign.css">
    

</head>

<body style="background-color: #fff;">
    <div class="container">
        <div class="left-section">
            <h1>Log in to</h1>
            <h2>BUSINESS SERVICE<br> SYSTEM</h2>
            <p>if you have an account, <br> you can <a href="login.php">Log in </a></p>
        </div>

        <div class="image-section">
            <img src="image1.jpeg" alt="Character Illustration">
        </div>

        <div class="right-section">
            <form method="post">
            <h2>Signup</h2>
            <input type="text" id="username" name="username" placeholder="Enter user name" require><br>
            <input type="email" id="email" name="email" placeholder="Enter email" require><br>
           <div class="password">
            
            <input type="password" id="password" name="password" placeholder="Password" require><br>
           <img src="hide-password2.png" onclick="pass()" class="pass-icon" id="pass-icon" >
            </div>
            <div class="password1">
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" require>
            <img src="hide-password4.png" onclick="pass1()" class="pass-icon1" id="pass-icon1">
            </div>

            <button type="submit" class="btn" id="signupButton" name="submit">Signup</button>
          </form>
        </div>
    </div>

    <script>
        var a;
function pass(){
if(a==1)
{
 document.getElementById('password').type='password'; 
 document.getElementById('pass-icon').src='hide-password2.png';
 a=0;
}
else{
 document.getElementById('password').type='text';
document.getElementById('pass-icon').src='hide-password.png';
a=1;
}
}
       var b;
function pass1(){
if(b==1)
{
 document.getElementById('confirmPassword').type='password'; 
 document.getElementById('pass-icon1').src='hide-password4.png';
 b=0;
}
else {
 document.getElementById('confirmPassword').type='text';
document.getElementById('pass-icon1').src='hide-password3.png';
b=1;
}
}
</script>

</body>

</html>