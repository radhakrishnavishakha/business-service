<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Error: Please log in first.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the client profile data
$stmt = $conn->prepare("SELECT * FROM client WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Check if profile data exists
if (!$client) {
    echo "Error: No profile found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Client Profile</title>
    <link rel="stylesheet" href="profile-style.css">
    <style>
        body {
    font-family: Arial, sans-serif;
    text-align: center;
}

.profile-container {
    width: 50%;
    margin: auto;
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 10px;
    background: #f9f9f9;
}

.profile-image {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
}

.profile-table th, .profile-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

.update-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: #4CAF50;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

    </style>
</head>
<body>

    <div class="profile-container">
        <h1>Client Profile</h1>
        <img src="<?php echo $client['client_photo']; ?>" alt="Profile Photo" class="profile-image">
        
        <table class="profile-table">
            <tr><th>First Name:</th><td><?php echo $client['first_name']; ?></td></tr>
            <tr><th>Middle Name:</th><td><?php echo $client['middle_name']; ?></td></tr>
            <tr><th>Last Name:</th><td><?php echo $client['last_name']; ?></td></tr>
            <tr><th>Contact No:</th><td><?php echo $client['contact_no']; ?></td></tr>
            <tr><th>Email:</th><td><?php echo $client['email']; ?></td></tr>
            <tr><th>State:</th><td><?php echo $client['state']; ?></td></tr>
            <tr><th>Area:</th><td><?php echo $client['area']; ?></td></tr>
            <tr><th>Pin Code:</th><td><?php echo $client['pin_code']; ?></td></tr>
            <tr><th>City:</th><td><?php echo $client['city']; ?></td></tr>
        </table>

        <a href="profile_update.php" class="update-btn">Update Profile</a>
    </div>

</body>
</html>
