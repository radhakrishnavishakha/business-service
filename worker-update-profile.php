<?php  
session_start();
include 'db_connect.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Error: Please log in first.'); window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
// Fetch existing worker data
$stmt = $conn->prepare("SELECT * FROM worker WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$worker = $result->fetch_assoc();

if (!$worker) {
    echo "<script>alert('Error: Worker profile not found.'); window.location.href = 'index.php';</script>";
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $middle_name = htmlspecialchars(trim($_POST['middle_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $dob = htmlspecialchars(trim($_POST['dob']));
    $contact_no = htmlspecialchars(trim($_POST['contact_no']));
    $email = htmlspecialchars(trim($_POST['email']));
    $state = htmlspecialchars(trim($_POST['state']));
    $area = htmlspecialchars(trim($_POST['area']));
    $pin_code = htmlspecialchars(trim($_POST['pin_code']));
    $city = htmlspecialchars(trim($_POST['city']));
    $work_type =htmlspecialchars(trim($_POST['work_type']));
    $skills = htmlspecialchars(trim($_POST['skills']));
    $experience = htmlspecialchars(trim($_POST['experience']));
    
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

    // Handle new profile picture upload (optional)
    if (!empty($_FILES['worker_photo']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($_FILES['worker_photo']['type'], $allowed_types)) {
            echo "Error: Only JPG, JPEG, and PNG files are allowed.";
            exit;
        }

        $upload_dir = 'worker/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES["worker_photo"]["name"], PATHINFO_EXTENSION);
        $file_name = "profile_" . time() . "." . $file_ext;
        $worker_photo = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES["worker_photo"]["tmp_name"],$worker_photo)) {
            echo "Error: There was an issue uploading the photo.";
            exit;
        }

        // Delete old profile photo if a new one is uploaded
        if (!empty($worker['worker_photo']) && file_exists($worker['worker_photo'])) {
            unlink($worker['worker_photo']);
        }
    } else {
        $worker_photo = $worker['worker_photo']; // Keep old photo if not updated
    }

    // Update work photos if new ones are uploaded
    $work_photos = [];
    if (!empty($_FILES['work_photos']['name'][0])) {
        $total_photos = count($_FILES['work_photos']['name']);
        $upload_dir = 'worker/';
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        for ($i = 0; $i < $total_photos; $i++) {
            if ($_FILES["work_photos"]["error"][$i] == 0) {
                $work_ext = strtolower(pathinfo($_FILES["work_photos"]["name"][$i], PATHINFO_EXTENSION));

                if (!in_array($work_ext, $allowed_ext)) {
                    echo "<script>alert('Error: Invalid work photo format.');</script>";
                    exit;
                }

                if ($_FILES["work_photos"]["size"][$i] > 5000000) { // 5MB max
                    echo "<script>alert('Error: Work photo file too large. Max size 5MB.');</script>";
                    exit;
                }

                $work_name = "work_" . time() . "_$i." . $work_ext;
                $work_path = $upload_dir . $work_name;

                if (move_uploaded_file($_FILES["work_photos"]["tmp_name"][$i], $work_path)) {
                    $work_photos[] = $work_path;
                }
            }
        }
    }

    // If there are less than 5 work photos uploaded, fill in the empty ones with current ones (if available)
    $work_photo1 = $work_photos[0] ?? $_POST['current_work_photo1'];
    $work_photo2 = $work_photos[1] ?? $_POST['current_work_photo2'];
    $work_photo3 = $work_photos[2] ?? $_POST['current_work_photo3'];
    $work_photo4 = $work_photos[3] ?? $_POST['current_work_photo4'];
    $work_photo5 = $work_photos[4] ?? $_POST['current_work_photo5'];

    // Update worker profile in database
    $stmt = $conn->prepare("UPDATE worker SET 
        first_name = ?, middle_name = ?, last_name = ?, dob = ?, contact_no = ?, 
        state = ?, area = ?, pin_code = ?, city = ?, work_type = ?, skills = ?, experience = ?, 
        worker_photo = ?, work_photo1 = ?, work_photo2 = ?, work_photo3 = ?, work_photo4 = ?, work_photo5 = ? 
        WHERE user_id = ?");

    $stmt->bind_param("ssssssssssssssssssi", 
        $first_name, $middle_name, $last_name, $dob, $contact_no, 
        $state, $area, $pin_code, $city, $work_type, $skills, $experience, 
        $worker_photo, $work_photo1, $work_photo2, $work_photo3, $work_photo4, $work_photo5, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('Error: Could not update profile.');</script>";
    }

    $stmt->close();
    $conn->close();
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Update Worker Profile</title>
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
.profile-table input[type="date"],
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
    <h1>Update Worker Profile</h1>

    <form method="post" enctype="multipart/form-data">
    <div class="profile-picture">
                <label for="client_photo">
                    <img id="preview" src="<?php echo $worker['worker_photo']; ?>" alt="Profile Picture">
                </label>
                <input type="file" id="worker_photo" name="worker_photo" accept="image/*" style="display: none;" onchange="previewImage(event)">
                <button type="button" class="add-photo-btn" onclick="document.getElementById('worker_photo').click();">Change Photo</button>
            </div>
        <input type="hidden" name="current_work_photo1" value="<?php echo $worker['work_photo1']; ?>">
        <input type="hidden" name="current_work_photo2" value="<?php echo $worker['work_photo2']; ?>">
        <input type="hidden" name="current_work_photo3" value="<?php echo $worker['work_photo3']; ?>">
        <input type="hidden" name="current_work_photo4" value="<?php echo $worker['work_photo4']; ?>">
        <input type="hidden" name="current_work_photo5" value="<?php echo $worker['work_photo5']; ?>">

        <table class="profile-table">
        <tr>
                    <th>First Name:</th>
                    <td><input type="text" name="first_name" value="<?php echo $worker['first_name']; ?>" required></td>
                </tr>
                <tr>
                    <th>Middle Name:</th>
                    <td><input type="text" name="middle_name" value="<?php echo $worker['middle_name']; ?>"></td>
                </tr>
                <tr>
                    <th>Last Name:</th>
                    <td><input type="text" name="last_name" value="<?php echo $worker['last_name']; ?>" required></td>
                </tr>
                <tr>
                    <th>DOB:</th>
                    <td><input type="date" name="dob" value="<?php echo $worker['dob']; ?>" required></td>
                </tr>
                <tr>
                    <th>Contact No:</th>
                    <td><input type="tel" name="contact_no" value="<?php echo $worker['contact_no']; ?>" required></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><input type="email" name="email" value="<?php echo $worker['email']; ?>" readonly required></td>
                </tr>
                <tr>
                    <th>State:</th>
                    <td><input type="text" name="state" value="<?php echo $worker['state']; ?>" required></td>
                </tr>
                <tr>
                    <th>Area:</th>
                    <td><input type="text" name="area" value="<?php echo $worker['area']; ?>" required></td>
                </tr>
                <tr>
                    <th>Pin Code:</th>
                    <td><input type="number" name="pin_code" value="<?php echo $worker['pin_code']; ?>" required></td>
                </tr>
                <tr>
                    <th>City:</th>
                    <td><input type="text" name="city" value="<?php echo $worker['city']; ?>" required></td>
                </tr>
                <tr>
                    <th>Work_Type:</th>
                    <td><select id="work_type" name="work_type" required>
                        <option value="Painting" <?php echo ($worker['work_type'] == 'Painting') ? 'selected' : ''; ?>>Painting</option>
                        <option value="Plumbing" <?php echo ($worker['work_type'] == 'Plumbing') ? 'selected' : ''; ?>>Plumbing</option>
                        <option value="Electrical" <?php echo ($worker['work_type'] == 'Electrical') ? 'selected' : ''; ?>>Electrical</option>
                        <option value="Gardening" <?php echo ($worker['work_type'] == 'Gardening') ? 'selected' : ''; ?>>Gardening</option>
                        <option value="Carpentry" <?php echo ($worker['work_type'] == 'Carpentry') ? 'selected' : ''; ?>>Carpentry</option>
                        <option value="Cleaning" <?php echo ($worker['work_type'] == 'Cleaning') ? 'selected' : ''; ?>>Cleaningn</option>
                        <option value="Mason" <?php echo ($worker['work_type'] == 'Mason') ? 'selected' : ''; ?>>Mason</option>
                        
                    </select></td>
                </tr>
                <tr>
                    <th>Skills:</th>
                    <td><input type="text" id="skills" name="skills" value="<?php echo $worker['skills']; ?>" required></td>
                </tr>
                <tr>
                    <th>Experience:</th>
                    <td><input type="text" id="experience" name="experience" value="<?php echo $worker['experience']; ?>" required></td>
                </tr>
            <tr>
                <td>
                    <label for="work_photos">Work Photos:</label>
                    <input type="file" id="work_photos" name="work_photos[]" multiple accept="image/*">
                </td>
                <td>
                    <div>
                        <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                if ($worker["work_photo$i"]) {
                                    echo "<img src='" . $worker["work_photo$i"] . "' alt='Work Photo $i' width='100'>";
                                }
                            }
                        ?>
                    </div>
                </td>
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