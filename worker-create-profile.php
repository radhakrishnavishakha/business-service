<?php 
session_start();
include 'db_connect.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    $error_message = "Error: Please log in first.";
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

$errors = []; // Initialize an array for errors

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
    $work_type = htmlspecialchars(trim($_POST['work_type']));
    $skills = htmlspecialchars(trim($_POST['skills']));
    $experience = htmlspecialchars(trim($_POST['experience']));

    // Validate inputs
    if (!preg_match("/^[A-Za-z]+$/", $first_name) || !preg_match("/^[A-Za-z]+$/", $last_name)) {
        $errors['name'] = 'First and last name should only contain letters.';
    }
    if (!preg_match("/^[0-9]{10}$/", $contact_no)) {
        $errors['contact_no'] = 'Contact number must be a 10-digit number.';
    }
    
    if (!preg_match("/^[0-9]{6}$/", $pin_code)) {
        $errors['pin_code'] = 'Pin code must be a 6-digit number.';
    }

    // ✅ Check if email already exists in the "worker" table
    $stmt_check = $conn->prepare("SELECT * FROM worker WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $errors['email'] = 'Email already exists. Please use a different email.';
    }
    $stmt_check->close();

    // Profile Photo Upload
    if (!isset($_FILES['worker_photo']) || $_FILES['worker_photo']['error'] != 0) {
        $errors['worker_photo'] = 'Please upload a profile photo.';
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($_FILES['worker_photo']['type'], $allowed_types)) {
        $errors['worker_photo'] = 'Only JPG, JPEG, and PNG files are allowed.';
    }

    $upload_dir = 'worker/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_ext = pathinfo($_FILES["worker_photo"]["name"], PATHINFO_EXTENSION);
    $file_name = "profile_" . time() . "." . $file_ext;
    $worker_photo = $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES["worker_photo"]["tmp_name"], $worker_photo)) {
        $errors['worker_photo'] = 'There was an issue uploading the photo.';
    }

    // Work Photos Upload - Maximum 5
    $work_photos = [];
    if (!empty($_FILES['work_photos']['name'][0])) {
        $total_photos = count($_FILES['work_photos']['name']);
        if ($total_photos > 5) {
            $errors['work_photos'] = 'You can upload a maximum of 5 work photos.';
        }

        foreach ($_FILES['work_photos']['tmp_name'] as $index => $tmp_name) {
            if ($_FILES['work_photos']['error'][$index] == 0) {
                $work_ext = strtolower(pathinfo($_FILES["work_photos"]["name"][$index], PATHINFO_EXTENSION));

                if (!in_array($work_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $errors['work_photos'] = 'Invalid work photo format.';
                }

                if ($_FILES["work_photos"]["size"][$index] > 5000000) {
                    $errors['work_photos'] = 'Work photo file too large. Max size 5MB.';
                }

                $work_name = "work_" . time() . "_$index." . $work_ext;
                $work_path = $upload_dir . $work_name;

                if (move_uploaded_file($tmp_name, $work_path)) {
                    $work_photos[] = $work_path;
                }
            }
        }
    }
    // Ensure empty slots are filled (if less than 5)
while (count($work_photos) < 5) {
    $work_photos[] = "";
}

// Assigning values
$work_photo1 = $work_photos[0];
$work_photo2 = $work_photos[1];
$work_photo3 = $work_photos[2];
$work_photo4 = $work_photos[3];
$work_photo5 = $work_photos[4];



    // If no errors, proceed to insert
    if (empty($errors)) {
        // Insert into database using prepared statement
        $stmt = $conn->prepare("INSERT INTO worker 
            (user_id, first_name, middle_name, last_name, dob, contact_no, email, state, area, pin_code, city, work_type, skills, experience, worker_photo, work_photo1, work_photo2, work_photo3, work_photo4, work_photo5) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("isssssssssssssssssss", 
            $user_id, $first_name, $middle_name, $last_name, $dob, $contact_no, $email, $state, $area, $pin_code, $city, 
            $work_type, $skills, $experience, $worker_photo, $work_photo1, $work_photo2, $work_photo3, $work_photo4, $work_photo5);

        if ($stmt->execute()) {
            echo "<script>alert('Profile created successfully!'); window.location.href = 'index.php';</script>";
            $_SESSION['profile_created'] = true;
            exit;
        } else {
            $errors['general'] = 'Could not save profile.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Client Profile Creation</title>
    <link rel="stylesheet" href="profile-create.css">
</head>

<body>
    <div class="container">
        <div class="background-image"></div>

        <div class="content">
            <h1>Worker Profile Creation</h1>

            <div class="profile-section">
                <div class="profile-picture">
                    <label for="worker_photo">
                        <img id="preview" src="th.jpg" alt="Profile Picture">
                    </label>
                </div>
                <button class="add-photo-btn">Add Photo</button>
            </div>

            <div class="form-section">
                <form id="clientForm" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                    <input type="file" id="worker_photo" name="worker_photo" accept="image/*" style="display: none;" onchange="previewImage(event)">
                    <table class="table-section">
                        <tr>
                            <td>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : ''; ?>" required>
                                <?php if (isset($errors['first_name'])): ?>
                                    <div class="error-message"><?php echo $errors['first_name']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label for="middle_name">Middle Name:</label>
                                <input type="text" id="middle_name" name="middle_name" value="<?php echo isset($_POST['middle_name']) ? $_POST['middle_name'] : ''; ?>">
                                <?php if (isset($errors['middle_name'])): ?>
                                    <div class="error-message"><?php echo $errors['middle_name']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : ''; ?>" required>
                                <?php if (isset($errors['last_name'])): ?>
                                    <div class="error-message"><?php echo $errors['last_name']; ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="dob">Date of Birth:</label>
                                <input type="date" id="dob" name="dob" value="<?php echo isset($_POST['dob']) ? $_POST['dob'] : ''; ?>" required>
                                <?php if (isset($errors['dob'])): ?>
                                    <div class="error-message"><?php echo $errors['dob']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td colspan="2">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : $_SESSION['email']; ?>" readonly required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="error-message"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label for="contact_no">Contact No:</label>
                                <input type="tel" id="contact_no" name="contact_no" value="<?php echo isset($_POST['contact_no']) ? $_POST['contact_no'] : ''; ?>" required>
                                <?php if (isset($errors['contact_no'])): ?>
                                    <div class="error-message"><?php echo $errors['contact_no']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label for="work_type">Work Type:</label>
                                <select id="work_type" name="work_type" required>
                                    <option value="">Select Work Type</option>
                                    <option value="Painting" <?php echo isset($_POST['work_type']) && $_POST['work_type'] == 'Painting' ? 'selected' : ''; ?>>Painting</option>
                                    <option value="Plumbing" <?php echo isset($_POST['work_type']) && $_POST['work_type'] == 'Plumbing' ? 'selected' : ''; ?>>Plumbing</option>
                                    <option value="Electrical" <?php echo isset($_POST['work_type']) && $_POST['work_type'] == 'Electrical' ? 'selected' : ''; ?>>Electrical</option>
                                    <option value="Gardening" <?php echo isset($_POST['work_type']) && $_POST['work_type'] == 'Gardening' ? 'selected' : ''; ?>>Gardening</option>
                                    <option value="Carpentry" <?php echo isset($_POST['work_type']) && $_POST['work_type'] == 'Carpentry' ? 'selected' : ''; ?>>Carpentry</option>
                                    <option value="Cleaning" <?php echo isset($_POST['work_type']) && $_POST['work_type'] == 'Cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                                    <option value="Mason" <?php echo isset($_POST['work_type']) && $_POST['work_type'] == 'Mason' ? 'selected' : ''; ?>>Mason</option>
                                </select>
                                <?php if (isset($errors['work_type'])): ?>
                                    <div class="error-message"><?php echo $errors['work_type']; ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                               <label for="address">Address:</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="state">State:</label>
                                <input type="text" id="state" name="state" value="<?php echo isset($_POST['state']) ? $_POST['state'] : ''; ?>" required>
                                <?php if (isset($errors['state'])): ?>
                                    <div class="error-message"><?php echo $errors['state']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label for="area">Area:</label>
                                <input type="text" id="area" name="area" value="<?php echo isset($_POST['area']) ? $_POST['area'] : ''; ?>" required>
                                <?php if (isset($errors['area'])): ?>
                                    <div class="error-message"><?php echo $errors['area']; ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="pin_code">Pin Code:</label>
                                <input type="number" id="pin_code" name="pin_code" value="<?php echo isset($_POST['pin_code']) ? $_POST['pin_code'] : ''; ?>" required>
                                <?php if (isset($errors['pin_code'])): ?>
                                    <div class="error-message"><?php echo $errors['pin_code']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label for="city">City:</label>
                                <input type="text" id="city" name="city" value="<?php echo isset($_POST['city']) ? $_POST['city'] : ''; ?>" required>
                                <?php if (isset($errors['city'])): ?>
                                    <div class="error-message"><?php echo $errors['city']; ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="skills">Skills:</label>
                                <textarea id="skills" name="skills" required><?php echo isset($_POST['skills']) ? $_POST['skills'] : ''; ?></textarea>
                                <?php if (isset($errors['skills'])): ?>
                                    <div class="error-message"><?php echo $errors['skills']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label for="experience">Experience:</label>
                                <input type="text" id="experience" name="experience" value="<?php echo isset($_POST['experience']) ? $_POST['experience'] : ''; ?>" required>
                                <?php if (isset($errors['experience'])): ?>
                                    <div class="error-message"><?php echo $errors['experience']; ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label for="work_photos">Work Photos:</label>
                                <input type="file" id="work_photos" name="work_photos[]" multiple accept="image/*">
                                <?php if (isset($errors['work_photos'])): ?>
                                    <div class="error-message"><?php echo $errors['work_photos']; ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <button type="submit" class="register-button">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <style>
    .error-message { color: red; font-size: 12px; display: block; }
</style>


    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('preview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        document.querySelector('.add-photo-btn').addEventListener('click', function() {
            document.getElementById('worker_photo').click();
        });

        function validateForm() {
            if (document.getElementById('worker_photo').files.length === 0) {
                alert("Please upload a profile photo.");
                return false;
            }
            if (document.getElementById('work_photos').files.length === 0) {
                alert("Please upload a work photos.");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>