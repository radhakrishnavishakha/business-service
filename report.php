<?php 
session_start();

include 'db_connect.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    exit("Unauthorized access! Please log in.");
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type1'];
$response = [];

if ($user_type === 'client') {
    $query = "SELECT id FROM client WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $client_row = $result->fetch_assoc();
        $client_id = $client_row['id'];
    } 

    $query = "SELECT h.id, w.first_name AS worker_first_name, w.middle_name AS worker_middle_name, w.last_name AS worker_last_name, 
                     c.first_name AS client_first_name, c.middle_name AS client_middle_name, c.last_name AS client_last_name,
              c.area, c.city, c.state, c.pin_code, h.status, h.worker_time,  h.client_date, h.client_time
              FROM hire_data h
              JOIN worker w ON h.worker_id = w.id
              JOIN client c ON h.client_id = c.id
              WHERE h.client_id = ? AND h.status != 'Completed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'worker_name' => $row['worker_first_name'] . ' ' . $row['worker_middle_name'] . ' ' . $row['worker_last_name'],
            'client_name' => $row['client_first_name'] . ' ' . $row['client_middle_name'] . ' ' . $row['client_last_name'],
            'client_location' => $row['area'] . ', ' . $row['city'] . ', ' . $row['state'] . ' - ' . $row['pin_code'],
            'status' => $row['status'],
            'work_time' => $row['worker_time'],
            'hire_id' => $row['id'],
            'client_date' => $row['client_date'],
            'client_time' => $row['client_time']
        ];
    }
} elseif ($user_type === 'worker') {
    $query = "SELECT id FROM worker WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $worker_row = $result->fetch_assoc();
        $worker_id = $worker_row['id'];
    } 

    $query = "SELECT h.id, w.first_name AS worker_first_name, w.middle_name AS worker_middle_name, w.last_name AS worker_last_name, 
                     c.first_name AS client_first_name, c.middle_name AS client_middle_name, c.last_name AS client_last_name, 
              c.area, c.city, c.state, c.pin_code, h.status, h.worker_time,  h.client_date, h.client_time
              FROM hire_data h
              JOIN worker w ON h.worker_id = w.id
              JOIN client c ON h.client_id = c.id
              WHERE h.worker_id = ? AND h.status != 'Completed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $worker_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'client_name' => $row['client_first_name'] . ' ' . $row['client_middle_name'] . ' ' . $row['client_last_name'],
            'worker_name' => $row['worker_first_name'] . ' ' . $row['worker_middle_name'] . ' ' . $row['worker_last_name'],
            'client_location' => $row['area'] . ', ' . $row['city'] . ', ' . $row['state'] . ' - ' . $row['pin_code'],
            'status' => $row['status'],
            'work_time' => $row['worker_time'],
            'hire_id' => $row['id'],
            'client_date' => $row['client_date'],
            'client_time' => $row['client_time']
        ];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report | Business Service</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
            
        }
        .hero {
  display: flex; 
  flex-direction: column; 
  justify-content: center; /* Vertically center content */
  align-items: center; /* Horizontally center content */
 background-image: url(search2.jpg);
  background-size:cover;

  height: 100%; /* Set the height of the section to full viewport height */
  background-color: lightgray; /* Example background color for the hero section */
}
#hire-messages {
    width: 50%;  /* Container width 50% */
    display: flex;
    flex-direction: column;
    align-items: center; /* Center all boxes inside */
    
}

.hire-box {
    width: 100%; /* Each box takes full width of the container */
    padding: 15px;
    border: 1px solid #ccc;
    background-color: #f9f9f9;
    text-align: left;
    margin-bottom: 10px; /* Space between multiple boxes */
    box-shadow: 0 2px 10px #8a2ce8; /* Add a slight shadow */
    border-radius: 8px; /* Optional: Rounded corners */
}
.hero h1 { font-size: 2rem; margin-bottom: 1rem; color: #a5a5a5; }
/*star csss */
.star {
    font-size: 30px;
    cursor: pointer;
    color: gray;
}

.modal-content h3 {
    color: rgba(82, 85, 85, 0.5);
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    text-align: center;
    box-shadow: 0 2px 10px #8a2ce8;
}

.stars {
    margin-bottom: 10px;
    font-size: 30px;
}

.star {
    margin: 0 5px;
}

.star.selected,
.star:hover {
    color: gold; /* Color for selected stars */
}

.star-btn {
    background-color: #663399;
    color: white;
    border-radius: 2px;
    border: none;
    box-shadow: 0 2px 10px #8a2ce8;
}

</style>

             
    </style>
</head>
<body>
<header>
        <div class="logo">Business Service</div>
        <nav>
            <ul>
                <li ><a href="index.php">Home</a></li>
                <li ><a href="find-worker.php">Find Worker</a></li>
                <?php
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
        if ($_SESSION['user_type1'] === 'client') {
            // Check if the client has already created a profile
            if (isset($_SESSION['profile_created']) && $_SESSION['profile_created'] === true) {
                echo "<li><a href='client-update-profile.php'>Update Client Profile</a></li>";
            } else {
                echo "<li><a href='client-create-profile.php'>Create Client Profile</a></li>";
            }
        } elseif ($_SESSION['user_type1'] === 'worker') {
            // Check if the worker has already created a profile
            if (isset($_SESSION['profile_created']) && $_SESSION['profile_created'] === true) {
                echo "<li><a href='worker-update-profile.php'>Update Worker Profile</a></li>";
            } else {
                echo "<li><a href='worker-create-profile.php'>Create Worker Profile</a></li>";
            }
        }
    } else {
        echo "<li><a href='asking.php'>Create Profile</a></li>";
    }
    ?>
                <li class="active"><a href="report.php">Report</a></li>
            </ul>
        </nav>
       <div class="account">
            <?php
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
                
                echo "<a href='logout.php'>Logout</a>";
                
            } else {
                
                echo "<a href='login.php'>Login</a> | <a href='asking.php'>Signup</a>";
            }
            ?>
        </div>
    </header>

    <section class="hero">
        <h1>Hire Updates</h1>
        <div id="hire-messages">Loading hire data...</div>
    </section>
    <section class="services">
    <h2>Our Services</h2>
    <ul class="service-list">
        <li class="service-item">
            <a href="#" class="service-link" data-category="Plumbing">
                <img src="plumbing.jpg" alt="Plumbing">
                <h3>Plumbing</h3>
            </a>
        </li>
        <li class="service-item">
            <a href="#" class="service-link" data-category="Painting">
                <img src="painting.jpg" alt="Painting">
                <h3>Painting</h3>
            </a>
        </li>
        <li class="service-item">
            <a href="#" class="service-link" data-category="Electrical">
                <img src="electrical.jpg" alt="Electrical">
                <h3>Electrical</h3>
            </a>
        </li>
        <li class="service-item">
            <a href="#" class="service-link" data-category="Carpentry">
                <img src="carpentry.jpg" alt="Carpentry">
                <h3>Carpentry</h3>
            </a>
        </li>
        <li class="service-item">
            <a href="#" class="service-link" data-category="Cleaning">
                <img src="cleaning.jpg" alt="Cleaning">
                <h3>Cleaning</h3>
            </a>
        </li>
        <li class="service-item">
            <a href="#" class="service-link" data-category="Gardening">
                <img src="gardening.jpg" alt="Gardening">
                <h3>Gardening</h3>
            </a>
        </li>
    </ul>
</section>
    <footer>
    <div class="footer-content">
        <div class="footer-about">
            <h3>About Us</h3>
            <p>Connecting you with skilled professionals for all your home needs. From plumbers to carpenters, find reliable workers for any task.</p>
        </div>
        <div class="footer-links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="find-worker.php">Find worker</a></li>
                <li><a href="asking.php">Register</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="/contact">Contact</a></li>
                
        </div>
        <div class="footer-contact">
            <h3>Contact Us</h3>
            <p>Email: vishakhajangir2006@gmail.com</p>
            <p>Phone: +91-8830341004</p>
        </div>
        <div class="footer-social">
            <h3>Follow Us</h3>
            <ul>
                <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                <li><a href="#"><i class="fab fa-google"></i></a></li>
                <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>2025 Business Service. All rights reserved.</p>
    </div>
</footer>
<script src="imageclick.js"></script>
<script>
let hireData = <?php echo json_encode($response); ?>;
let userType = "<?php echo $user_type; ?>";

$(document).ready(function() {
    let htmlContent = '';

    hireData.forEach(function(item) {
        htmlContent += `
            <div class="hire-box" id="hire-box-${item.hire_id}">
                <p><strong>Worker:</strong> ${item.worker_name}</p>
                <p><strong>Client:</strong> ${item.client_name}</p>
                <p><strong>Client Location:</strong> ${item.client_location}</p>
                <p><strong>Status:</strong> ${item.status}</p>
                <p><strong>Client Available Date:</strong> ${item.client_date}</p>
                <p><strong>Client Available Time:</strong> ${item.client_time}</p>
        `;

        // If worker is logged in and job status is 'Pending'
        if (userType === 'worker' && item.status === 'Pending') {
            htmlContent += `
                <input type="text" id="worker-time-${item.hire_id}" placeholder="Enter work time">
                <button onclick="updateStatus(${item.hire_id}, 'Accepted')">Accept</button>
                <button onclick="updateStatus(${item.hire_id}, 'Rejected')">Not Accept</button>
            `;
        }

        // If client is logged in and status is 'Accepted' or 'Rejected'
        if (userType === 'client' && (item.status === 'Accepted' || item.status === 'Rejected')) {
            htmlContent += `
                <p><strong>Work Time:</strong> ${item.work_time ? item.work_time : 'Not Set'}</p>
                <button onclick="finishJob(${item.hire_id}, ${item.worker_id}, '${item.status}')">Finish</button>
            `;
        }

        htmlContent += `</div>`;
    });

    $('#hire-messages').html(htmlContent);
});

// Function to update status (Accept/Reject) for worker
function updateStatus(hireId, newStatus) {
    let workTime = $(`#worker-time-${hireId}`).val() || '';  // Get work time if entered

    $.ajax({
        url: 'update_status.php',
        type: 'POST',
        data: { hire_id: hireId, status: newStatus, worker_time: workTime },
        success: function(response) {
            console.log(response);
            try {
                let jsonResponse = JSON.parse(response);
                if (jsonResponse.success) {
                    alert('Status updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating status: ' + (jsonResponse.error || 'Unknown error'));
                }
            } catch (e) {
                alert('Error: Invalid response from server');
                console.log('Parsing error:', e);
            }
        },
        error: function(xhr, status, error) {
            alert('AJAX Error: ' + error);
            console.log('AJAX Error Details:', xhr.responseText);
        }
    });
}

function finishJob(hireId, workerId, status) {
    // If the worker rejected, just delete the hire row and do not show rating modal
    if (status === 'Rejected') {
        // Send AJAX request to delete the hire row from the database
        $.post('report.php', { hire_id: hireId, action: 'delete' }, function(response) {
            alert('The job was rejected. Hire data deleted.');
            $(`#hire-box-${hireId}`).remove(); // Remove the hire box from the page
        });
        return; // Stop further execution if job was rejected
    }

    
// If worker accepted, show rating modal
// Dynamically create the rating modal
let ratingHtml = `
    <div id="rating-modal" class="modal">
        <div class="modal-content">
            <h3>Rate the Worker</h3>
            <div class="stars">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>
            <button class="star-btn" id="submit-rating-btn">Submit Rating</button>
        </div>
    </div>
`;
$('body').append(ratingHtml);

// Handle star clicks to change their color
$('.star').click(function() {
    let ratingValue = $(this).data('value');
    // Set the color of the clicked star and all stars before it to gold
    $('.star').each(function() {
        if ($(this).data('value') <= ratingValue) {
            $(this).addClass('selected');
        } else {
            $(this).removeClass('selected');
        }
    });
});

// Submit the rating when the button is clicked
$('#submit-rating-btn').click(function() {
    let rating = $('.star.selected').last().data('value') || 0;

    if (rating === 0) {
        alert('Please select a rating before submitting.');
        return;
    }

    // Call the function to submit the rating and perform other actions
    submitRating(hireId, workerId, rating);
});

// Function to handle rating submission
function submitRating(hireId, workerId, rating) {
    // Proceed with the rating submission logic, e.g., saving the rating to the database
    console.log('Submitted rating:', rating);

    // Send AJAX request to store the rating
    $.post('report.php', { hire_id: hireId, rating: rating, action: 'rate' }, function(response) {
        alert('Job completed and rating submitted successfully!');
        $('#rating-modal').remove(); // Remove the rating modal
        $(`#hire-box-${hireId}`).remove(); // Remove the hire box from the page
    });
}
}
</script>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hire_id = $_POST['hire_id'];
    $action = $_POST['action'];  // Action can be 'delete' or 'rate'

    // Get client_id and worker_id before taking action
    $query = "SELECT client_id, worker_id FROM hire_data WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $hire_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo json_encode(["error" => "Invalid hire_id"]);
        exit;
    }

    $client_id_b = $row['client_id'];
    $worker_id_b = $row['worker_id'];

    // Action is 'delete' (if worker rejected the job)
    if ($action === 'delete') {
        $query = "DELETE FROM hire_data WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $hire_id);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Hire data deleted."]);
    }
    // Action is 'rate' (if worker accepted the job)
    elseif ($action === 'rate') {
        $rating = $_POST['rating'];

        // Insert rating into the ratings table
        $query = "INSERT INTO ratings (client_id_b, worker_id_b, rating) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $client_id_b, $worker_id_b, $rating);
        $stmt->execute();

        // Delete the hire record after rating
        $query = "DELETE FROM hire_data WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $hire_id);
        $stmt->execute();
        
        echo json_encode(["success" => true, "message" => "Rating submitted and hire data deleted."]);
    }
}
?>

</body>
</html>
