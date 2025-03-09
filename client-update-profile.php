<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<script>alert('Error: Please log in first.'); window.location.href='login.php';</script>");
}

$user_id = $_SESSION['user_id'];

// Fetch existing user data
$stmt = $conn->prepare("SELECT * FROM client WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();
$stmt->close();

// Check if profile data exists
if (!$client) {
    die("<script>alert('Error: No profile found.'); window.location.href='index.php';</script>");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $middle_name = htmlspecialchars(trim($_POST['middle_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $contact_no = htmlspecialchars(trim($_POST['contact_no']));
    $email = htmlspecialchars(trim($_POST['email']));
    $state = htmlspecialchars(trim($_POST['state']));
    $area = htmlspecialchars(trim($_POST['area']));
    $pin_code = htmlspecialchars(trim($_POST['pin_code']));
    $city = htmlspecialchars(trim($_POST['city']));

    // Validate inputs
    if (!preg_match("/^[A-Za-z]+$/", $first_name) || !preg_match("/^[A-Za-z]+$/", $last_name)) {
        die("<script>alert('Error: First and last name should only contain letters.'); window.history.back();</script>");
    }
    if (!preg_match("/^[0-9]{10}$/", $contact_no)) {
        die("<script>alert('Error: Contact number must be a 10-digit number.'); window.history.back();</script>");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<script>alert('Error: Invalid email format.'); window.history.back();</script>");
    }
    if (!preg_match("/^[0-9]{6}$/", $pin_code)) {
        die("<script>alert('Error: Pin code must be a 6-digit number.'); window.history.back();</script>");
    }

    // Handle profile picture upload (optional)
    if (!empty($_FILES['client_photo']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($_FILES['client_photo']['type'], $allowed_types)) {
            die("<script>alert('Error: Only JPG, JPEG, and PNG files are allowed.'); window.history.back();</script>");
        }

        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES["client_photo"]["name"], PATHINFO_EXTENSION);
        $file_name = "profile_" . time() . "." . $file_ext;
        $client_photo = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES["client_photo"]["tmp_name"], $client_photo)) {
            die("<script>alert('Error: There was an issue uploading the photo.'); window.history.back();</script>");
        }

        // Delete old profile photo if a new one is uploaded
        if (!empty($client['client_photo']) && file_exists($client['client_photo'])) {
            unlink($client['client_photo']);
        }
    } else {
        $client_photo = $client['client_photo']; // Keep old photo if not updated
    }

    // Update profile data in database
    $stmt = $conn->prepare("UPDATE client SET first_name=?, middle_name=?, last_name=?, contact_no=?, email=?, state=?, area=?, pin_code=?, city=?, client_photo=? WHERE user_id=?");
    $stmt->bind_param("ssssssssssi", $first_name, $middle_name, $last_name, $contact_no, $email, $state, $area, $pin_code, $city, $client_photo, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Successfully updated the data'); window.location.href='index.php';</script>";
        exit;
    } else {
        die("<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>");
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Update Profile</title>
    <style>/* General Styles */
/* General Styles */
body {
    font-family: Arial, sans-serif;
    text-align: center;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
}

/* Profile Container */
.profile-container {
    width: 50%;
    margin: 50px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px #8a2ce8;
}

/* Profile Picture Section */
.profile-picture {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 20px;
}

.profile-picture img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ddd;
}

.add-photo-btn {
    margin-top: 10px;
    padding: 8px 12px;
    background-color: #663399;
    box-shadow: 0 2px 10px #8a2ce8;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: 0.3s;
}

.add-photo-btn:hover {
    background-color: #0056b3;
}

/* Profile Table */
.profile-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.profile-table th,
.profile-table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

.profile-table th {
    background-color: #f2f2f2;
    width: 30%;
}

/* Input Fields */
.profile-table input[type="text"],
.profile-table input[type="email"],
.profile-table input[type="tel"],
.profile-table input[type="number"],
.profile-table select {
    width: 95%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

/* Update Button */
.update-btn {
    margin-top: 20px;
    padding: 12px 20px;
    background-color:#663399;
    box-shadow: 0 2px 10px #8a2ce8;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
}

.update-btn:hover {
    background:rgb(102, 90, 235);
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .profile-container {
        width: 90%;
    }

    .profile-table th,
    .profile-table td {
        display: block;
        width: 100%;
    }

    .profile-table th {
        text-align: left;
        background: none;
        font-weight: bold;
        padding-top: 10px;
    }

    .profile-table td {
        padding-bottom: 10px;
    }
}

</style>
</head>
<body>

    <div class="profile-container">
        <h1>Update Profile</h1>

        <form method="post" enctype="multipart/form-data">
            <div class="profile-picture">
                <label for="client_photo">
                    <img id="preview" src="<?php echo $client['client_photo']; ?>" alt="Profile Picture">
                </label>
                <input type="file" id="client_photo" name="client_photo" accept="image/*" style="display: none;" onchange="previewImage(event)">
                <button type="button" class="add-photo-btn" onclick="document.getElementById('client_photo').click();">Change Photo</button>
            </div>

            <table class="profile-table">
                <tr>
                    <th>First Name:</th>
                    <td><input type="text" name="first_name" value="<?php echo $client['first_name']; ?>" required></td>
                </tr>
                <tr>
                    <th>Middle Name:</th>
                    <td><input type="text" name="middle_name" value="<?php echo $client['middle_name']; ?>"></td>
                </tr>
                <tr>
                    <th>Last Name:</th>
                    <td><input type="text" name="last_name" value="<?php echo $client['last_name']; ?>" required></td>
                </tr>
                <tr>
                    <th>Contact No:</th>
                    <td><input type="tel" name="contact_no" value="<?php echo $client['contact_no']; ?>" required></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><input type="email" name="email" value="<?php echo $client['email']; ?>" readonly required></td>
                </tr>
                <tr>
                    <th>State:</th>
                    <td><input type="text" name="state" value="<?php echo $client['state']; ?>" required></td>
                </tr>
                <tr>
                    <th>Area:</th>
                    <td><input type="text" name="area" value="<?php echo $client['area']; ?>" required></td>
                </tr>
                <tr>
                    <th>Pin Code:</th>
                    <td><input type="number" name="pin_code" value="<?php echo $client['pin_code']; ?>" required></td>
                </tr>
                <tr>
                    <th>City:</th>
                    <td><input type="text" name="city" value="<?php echo $client['city']; ?>" required></td>
                </tr>
            </table>

            <button type="submit" class="update-btn">Update Profile</button>
        </form>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('preview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>

</body>
</html>
