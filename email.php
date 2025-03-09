<?php
session_start();
include 'db_connect.php'; 



// Handling AJAX request to check email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkEmail'])) {
    $email = trim($_POST['checkEmail']);

    // Query to check if email exists
    $query = "SELECT email FROM `signup-b` WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "exists";  // Email exists
    } else {
        echo "not_found";  // Email not found
    }
    $stmt->close();
    exit();
}

// Handling AJAX request to update password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatePassword']) && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['updatePassword']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Password Validation
    if (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.');</script>";
        exit();
    } 
    else if (!preg_match('/\d/', $password)) {
        echo "<script>alert('Password must contain at least one number.');</script>";
        exit();
    }
    else if (!preg_match('/[a-z]/', $password)) {
        echo "<script>alert('Password must contain at least one lowercase letter.');</script>";
        exit();
    }
    else if (!preg_match('/[A-Z]/', $password)) {
        echo "<script>alert('Password must contain at least one uppercase letter.');</script>";
        exit();
    }
    else if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.');</script>";
        exit();
    }

    // Hash password and update database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE `signup-b` SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $hashedPassword, $email);

    if ($stmt->execute()) {
        echo "success";  // Password updated
    } else {
        echo "error";  // Error in updating password
    }
    $stmt->close();
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
    <link rel="stylesheet" href="logsign.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("submit").addEventListener("click", function (event) {
                event.preventDefault(); // Prevent page reload

                var email = document.getElementById("email").value.trim();
                if (email === '') {
                    alert('Email cannot be empty.');
                    return;
                }

                // AJAX request to check if email exists
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "email.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        if (xhr.responseText.trim() === "exists") {
                            document.getElementById("forgotPasswordForm").style.display = "none";
                            document.getElementById("resetBox").style.display = "block";
                        } else {
                            alert("Email not found!");
                        }
                    }
                };
                xhr.send("checkEmail=" + encodeURIComponent(email));
            });

            document.getElementById("updatePassword").addEventListener("click", function (event) {
                event.preventDefault();

                var email = document.getElementById("email").value.trim();
                var newPassword = document.getElementById("newPassword").value.trim();
                var confirmPassword = document.getElementById("confirmPassword").value.trim();

                if (newPassword === '' || confirmPassword === '') {                                  
                    alert("Passwords cannot be empty.");
                    return;
                }

                if (newPassword !== confirmPassword) {
                    alert("Passwords do not match!");
                    return;
                }

                // AJAX request to update password
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "email.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        if (xhr.responseText.trim() === "success") {
                            alert("Password updated successfully!");
                            window.location.href = "login.php"; // Redirect to login page
                        } else {
                            alert("Error updating password.");
                        }
                    }
                };
                xhr.send("updatePassword=" + encodeURIComponent(newPassword) + "&email=" + encodeURIComponent(email));
            });
        });
    </script>
</head>
<body style="background-color: #fff;">
    <div class="container">
        <div class="left-section">
            <h1>Sign in to</h1>
            <h2>BUSINESS SERVICE<br> SYSTEM</h2>
            <p>If you don't have an account, <br> you can <a href="signup.php">Register here!</a></p>
        </div>

        <div class="image-section">
            <img src="image1.jpeg" alt="Character Illustration">
        </div>

        <div class="right-section">
            <!-- Email Form -->
            <form id="forgotPasswordForm">
                <h2>Forget Password?</h2>
                <input type="email" id="email" name="email" placeholder="Enter email" required>
                <button class="btn" type="button" id="submit">Reset Password</button>
            </form>

            <!-- Password Reset Box (Hidden initially) -->
            <div id="resetBox" style="display: none;">
                <h2>Reset Your Password</h2>
                <input type="password" id="newPassword" placeholder="Enter new password" required>
                <input type="password" id="confirmPassword" placeholder="Confirm new password" required>
                <button class="btn" id="updatePassword">Update Password</button>
            </div>
        </div>
    </div>
</body>
</html>
