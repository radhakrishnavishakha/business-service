<?php
session_start();
include 'db_connect.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$errors = [];

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
    if (!preg_match("/^[A-Za-z]+$/", $first_name)) {
        $errors['first_name'] = "Only letters allowed.";
    }
    if (!empty($last_name) && !preg_match("/^[A-Za-z]+$/", $last_name)) {
        $errors['last_name'] = "Only letters allowed.";
    }
    if (!preg_match("/^[0-9]{10}$/", $contact_no)) {
        $errors['contact_no'] = "Must be 10 digits.";
    }
    if (!preg_match("/^[0-9]{6}$/", $pin_code)) {
        $errors['pin_code'] = "Must be 6 digits.";
    }

    // Check if email already exists
    $stmt_check = $conn->prepare("SELECT * FROM client WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $errors['email'] = "Email already exists.";
    }
    $stmt_check->close();

    // File Upload Handling
    if ($_FILES['client_photo']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($_FILES['client_photo']['type'], $allowed_types)) {
            $errors['client_photo'] = "Only JPG, JPEG, PNG allowed.";
        }
    } else {
        $errors['client_photo'] = "Profile photo required.";
    }

    if (empty($errors)) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_ext = pathinfo($_FILES["client_photo"]["name"], PATHINFO_EXTENSION);
        $file_name = "profile_" . time() . "." . $file_ext;
        $client_photo = $upload_dir . $file_name;

        move_uploaded_file($_FILES["client_photo"]["tmp_name"], $client_photo);

        $stmt = $conn->prepare("INSERT INTO client (user_id, first_name, middle_name, last_name, contact_no, email, state, area, pin_code, city, client_photo) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssssss", $user_id, $first_name, $middle_name, $last_name, $contact_no, $email, $state, $area, $pin_code, $city, $client_photo);

        if ($stmt->execute()) {
            $_SESSION['profile_created'] = true;
            header("Location: index.php");
            exit;
        } else {
            $errors['database'] = "Error saving profile.";
        }
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
            <h1>Client Profile Creation</h1>

            <div class="profile-section">
                <div class="profile-picture">
                    <label for="client_photo">
                        <img id="preview" src="th.jpg" alt="Profile Picture">
                    </label>
                </div>
                <button class="add-photo-btn">Add Photo</button>
            </div>

            <div class="form-section">
                <form id="clientForm" method="post" enctype="multipart/form-data">
                    <input type="file" id="client_photo" name="client_photo" accept="image/*" style="display: none;" onchange="previewImage(event)">
                    <table class="table-section">
                        <tr>
                            <td>
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" required>
                                <span class="error"><?php echo $errors['first_name'] ?? ''; ?></span>
                            </td>
                            <td>
                                <label for="middle_name">Middle Name:</label>
                                <input type="text" id="middle_name" name="middle_name">
                            </td>
                            <td>
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" required>
                                <span class="error"><?php echo $errors['last_name'] ?? ''; ?></span>
                            </td>
                        </tr>
                        <tr>
                        <td>
                            <label for="contact_no">Contact No:</label>
                                <input type="tel" id="contact_no" name="contact_no" required>
                                <span class="error"><?php echo $errors['contact_no'] ?? ''; ?></span>
                            </td>
                            
                            <td colspan="2">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" value="<?php echo $_SESSION['email']; ?>" readonly required>
                                <span class="error"><?php echo $errors['email'] ?? ''; ?></span>
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
                                <input type="number" id="pin_code" name="pin_code" required>
                                <span class="error"><?php echo $errors['pin_code'] ?? ''; ?></span>
                            </td>
                            <td>
                                <label for="city">City:</label>
                                <input type="text" id="city" name="city" required>
                            </td>
                        </tr>
                        
                        
                    </table>
                    <button type="submit" class="register-button">Submit</button>
                </form>
            </div>
        </div>
    </div>

<style>
    .error { color: red; font-size: 12px; display: block; }
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
            document.getElementById('client_photo').click();
        });

        function validateForm() {
            if (document.getElementById('client_photo').files.length === 0) {
                alert("Please upload a profile photo.");
                return false;
            }
            return true;
        }
    </script>

</body>
</html>