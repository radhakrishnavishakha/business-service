<?php
session_start();
include 'db_connect.php'; // Include the database connection

// Check if the worker ID is provided in the URL
if (isset($_GET['worker_id'])) {
    $worker_id = $_GET['worker_id'];
} else {
    echo "<script>alert('Error: No worker ID provided.'); window.location.href='index.php';</script>";
    exit;
}

// Fetch worker profile details from the database
$stmt = $conn->prepare("SELECT * FROM worker WHERE id = ?");
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $worker = $result->fetch_assoc();
} else {
    echo "<script>alert('Error: Worker profile not found.'); window.location.href='index.php';</script>";
    exit;
}
// Fetch ratings and client details for this worker
// Fetch ratings and client details for this worker
$rating_stmt = $conn->prepare("
    SELECT r.rating, c.first_name, c.middle_name, c.last_name
    FROM ratings r
    JOIN client c ON r.client_id_b = c.id  -- Join client table to get client name
    WHERE r.worker_id_b = ?  -- Match worker_id_b with worker's ID
    ORDER BY r.id DESC  -- Show latest ratings first
");

$rating_stmt->bind_param("i", $worker_id);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();


$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Worker Profile</title>
    <link rel="stylesheet" href="view-profile.css">
</head>
<style>/* General Styles */
/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
}

.container {
    width: 80%;
    margin: 0 auto;
    padding: 20px;
    box-shadow: 0 2px 10px #8a2ce8;
}

/* Profile Section */
.profile-section {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px #8a2ce8;
    text-align: center;
}

h1 {
    font-size: 28px;
    color: #663399;
    margin-bottom: 20px;
}

/* Profile Photo */
.profile-photo {
    width: 150px;
    height: 150px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: 50%;
    box-shadow: 0 2px 10px #8a2ce8;
}

.profile-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Profile Details Table */
.profile-info-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    text-align: left;
}

.profile-info-table td {
    padding: 8px;
    font-size: 16px;
    color: #333;
}

.profile-info-table td strong {
    color: #663399;
    font-weight: normal;
}

/* Work Photos Section */
.work-photos {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.work-photos img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 10px #8a2ce8;
}

/* Responsive Design for Smaller Screens */
@media (max-width: 768px) {
    .container {
        width: 95%;
    }

    .profile-photo {
        width: 120px;
        height: 120px;
    }

    .profile-info-table td {
        font-size: 14px;
    }

    .work-photos img {
        width: 120px;
        height: 120px;
    }
}
/*rating*/
.ratings-container {
    margin-top: 20px;
    padding: 10px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px #8a2ce8;
}

/* Individual Rating Box */
.rating-box {
    border: 1px solid #ddd;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
    background: #f9f9ff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* No Rating Message */
.no-rating {
    font-size: 16px;
    color: #888;
    text-align: center;
}

/* Star Ratings */
.rating-box p strong {
    color: #663399;
}



    
    </style>

<body>

    <div class="container">
        <div class="profile-section">
            <h1>Worker Profile</h1>

            <div class="profile-photo">
                <img src="<?php echo $worker['worker_photo']; ?>" alt="Profile Photo">
            </div>

            <div class="profile-details">
    <h2><?php echo $worker['first_name'] . ' ' . $worker['last_name']; ?></h2>

    <table class="profile-info-table">
        <tr>
            <td><strong>Work Type:</strong></td>
            <td><?php echo $worker['work_type']; ?></td>
        </tr>
        <tr>
            <td><strong>Skills:</strong></td>
            <td><?php echo $worker['skills']; ?></td>
        </tr>
        <tr>
            <td><strong>Experience:</strong></td>
            <td><?php echo $worker['experience']; ?> years</td>
        </tr>
        <tr>
            <td><strong>Contact No:</strong></td>
            <td><?php echo $worker['contact_no']; ?></td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td><?php echo $worker['email']; ?></td>
        </tr>
        <tr>
            <td><strong>Address:</strong></td>
            <td><?php echo $worker['area'] . ', ' . $worker['city'] . ', ' . $worker['state'] . ', ' . $worker['pin_code']; ?></td>
        </tr>
    </table>

    <h3>Work Photos</h3>
    <div class="work-photos">
        <?php for ($i = 1; $i <= 5; $i++) {
            $work_photo = $worker["work_photo$i"];
            if (!empty($work_photo)) {
                echo "<img src='$work_photo' alt='Work Photo $i'>";
            }
        } ?>
    </div>
    <!-- Rating Section -->
<h3>Ratings & Reviews</h3>
<div class="ratings-container">
    <?php if ($rating_result->num_rows > 0) { ?>
        <?php while ($rating = $rating_result->fetch_assoc()) { ?>
            <div class="rating-box">
                <p><strong>Client:</strong> <?php echo $rating['first_name'] . ' ' . $rating['middle_name'] . ' ' . $rating['last_name']; ?></p>
                <p><strong>Rating:</strong> 
                    <?php 
                    $stars = intval($rating['rating']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $stars ? "★" : "☆";
                    }
                    ?>
                </p>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p class="no-rating">No ratings yet.</p>
    <?php } ?>
</div>

</div>

        </div>
    </div>


</body>

</html>
