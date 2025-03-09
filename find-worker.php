<?php
session_start();
include 'db_connect.php'; // Include database connection
$category = isset($_GET['category']) ? $_GET['category'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
   
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || (isset($_SESSION['user_type1']) && $_SESSION['user_type1'] === 'worker')) {
        // Display the message if the user is not logged in or is a worker
        echo "<p style='background-color: #8a2ce8;; color:rgb(214, 210, 219);box-shadow: 0 5px 10px #851ded; padding: 15px; margin: 20px 0; border-radius: 5px; font-size: 16px; text-align: center; font-weight: bold;'>Please create an account as a client to search for workers.</p>";
        exit; // Stop further execution, so no worker data is shown
    }

    $searchTerm = "%" . $_POST['search'] . "%";
    $stmt = $conn->prepare("SELECT * FROM worker WHERE work_type LIKE ?");
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($worker = $result->fetch_assoc()) {
            echo "<div class='worker-box'>";
            $_SESSION['worker_id'] = $worker['id'];

            // Worker Table
            echo "<table class='worker-table'>";
            echo "<tr>";

            // Worker Photo
            echo "<td class='worker-photo'>";
            echo "<img src=". htmlspecialchars($worker['worker_photo'])." alt='Profile Photo'>";
            echo "</td>";

            // Worker Information
            echo "<td class='worker-info'>";
            echo "<h3>" . htmlspecialchars($worker['first_name']) . " " . htmlspecialchars($worker['middle_name']) . " " . htmlspecialchars($worker['last_name']) . "</h3>";
            echo "<table class='details'>";
            echo "<tr><td><strong>Email:</strong></td><td>" . htmlspecialchars($worker['email']) . "</td></tr>";
            echo "<tr><td><strong>Contact No.:</strong></td><td>" . htmlspecialchars($worker['contact_no']) . "</td></tr>";
            echo "<tr><td><strong>Experience:</strong></td><td>" . htmlspecialchars($worker['experience']) . " years</td></tr>";
            echo "<tr><td><strong>Skills:</strong></td><td>" . htmlspecialchars($worker['skills']) . "</td></tr>";
            echo "</table>";
            echo "</td>";

            echo "</tr>";
            echo "</table>";

            // Fetch Rating and Client Name
            $worker_id = $worker['id'];
            $stmt_rating = $conn->prepare("SELECT AVG(rating) AS avg_rating, client.first_name, client.middle_name, client.last_name
                                          FROM ratings
                                          LEFT JOIN client ON ratings.client_id_b = client.id
                                          WHERE worker_id_b = ?");
            $stmt_rating->bind_param("i", $worker_id);
            $stmt_rating->execute();
            $rating_result = $stmt_rating->get_result();
            $rating_data = $rating_result->fetch_assoc();

            // Handle case when there are no ratings
            $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
            $client_name = $rating_data['first_name'] . " " . $rating_data['middle_name'] . " " . $rating_data['last_name'];

            // Display Rating as Stars
            echo "<div class='worker-details-container'>";
            echo "<div class='worker-info-container'>"; // Left side: Worker info & buttons
            // Hire Button with POST Method
            echo "<a class='hire-btn'><button onclick='openModal(" . $worker['id'] . ")'>Hire</button></a>";

    // Generate modal for each worker
    echo "
    <div id='hireModal-" . $worker['id'] . "' class='hireModal' style='display:none;'>
        <div class='modal-content'>
            <h2>Enter Your Available Time</h2>
            <form action='hire.php' method='POST'>
                <input type='hidden' name='worker_id' value='" . $worker['id'] . "'>
                <table>
                <tr>
                <td style='text-align: left;>
                <label for='client_date'>Select Date:</label>
                </td>
                <td>
                <input type='date' name='client_date'style='width: 70%;' required><br>
                </td>
                </tr>
                <tr>
                <td style='text-align: left;>

                <label for='client_time_start'>Available From:</label>
                </td>
                <td>
                <input type='time' name='client_time_start'style='width: 70%;' required><br>
                </td>
                </tr>
                <tr>
                <td style='text-align: left;>

                <label for='client_time_end'>Available  Until:</label>
                </td>
                <td >
                <input type='time' name='client_time_end'style='width: 70%;' required>
                </td>
                <br></tr>
                </table>

                <button type='submit'>Confirm Hire</button>
                <button type='button' onclick='closeModal(" . $worker['id'] . ")'>Cancel</button>
            </form>
        </div>
    </div>
    ";


            // View Profile Button
            echo "<a href='view-worker-profile.php?worker_id=" . urlencode($worker['id']) . "' class='view-btn'><button>View</button></a>";

            echo "</div>"; // End of worker-info-container

            // Right side: Rating info
            echo "<div class='rating-info-container'>";
            // Display Rating as Stars
            echo "<div class='rating'>";
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $avg_rating) {
                    echo "<span class='star filled'>★</span>";
                } else {
                    echo "<span class='star'>★</span>";
                }
            }
            echo "</div>";

            // Display Client's Name with Rating
            if ($avg_rating > 0) {
                echo "<div class='rating-client'>";
                echo "<p><strong>Client: </strong>" . htmlspecialchars($client_name) . "</p>";
                echo "<p><strong>Rating: </strong>" . $avg_rating . " / 5</p>";
                echo "</div>";
            } else {
                echo "<div class='no-rating'><p>No ratings yet.</p></div>";
            }
            echo "</div>"; // End of rating-info-container

            echo "</div>"; // End of worker-details-container
            echo "</div>"; // End of worker-box
        }
    } else {
        echo "<p>No workers found for this category.</p>";
    }
    exit; // Stop further execution to return only search results
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Worker | Business Service</title>
    <link rel="stylesheet" href="style.css">
    <style>
        
        
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
            
        }
        
        .find-worker {
           background-image: url(search2.jpg);
            background-size:cover;
            height: 100%;
            text-align: center;
            padding: 2rem 0;
        }
        
        .find-worker h2 {
            margin-bottom: 60px;
            color: #c0bcbc;
            margin-top: 80px;
            font-family: fantasy;
        }
        .find-worker h3{
            
            color: #bdbac0;;
            margin-top: 80px;
            font-family: fangsong;
        }
        .find-worker form{
            margin-bottom: 60px;
        }
        .find-worker form input{
            width: 35%;
            background-color:#f3f1f6 ;
            padding: 8px;
            border:none;
            border-radius: 5px;
            box-shadow: 0 2px 10px #8a2ce8;
            
        }
        input :placeholder{
            color: white;
            align-items: center;
        }
        .find-worker button{
            width: auto; /* Adjusts width to fit content */
            min-width: 80px; /* Ensures it does not shrink too much */
            font-family: fantasy;
        
            padding:10px ;
            color: rgb(92, 69, 154);
            border: none;
            border-radius:5px ;
            box-shadow: 0 2px 10px #8a2ce8;
        }
        

/* Style for the worker box */
.worker-box {
    display: flex;
    flex-direction: column; /* Stack photo, info, and buttons vertically */
    align-items: center; /* Center align the box */
    background: #fff;
    border: 1px solid #ddd;
    padding: 20px;
    margin: 15px auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    max-width: 900px;
    width: 90%;
    height: 100%; /* Allow box to take full height */
    justify-content: space-between; /* Ensure content is spaced out */
    box-shadow: 0 2px 10px #8a2ce8;
}

/* On hover, make the box pop up */
.worker-box:hover {
    transform: translateY(-5px);
}

/* Worker Photo Styling */
.worker-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
}

.worker-photo img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ddd;
    box-shadow: 0 2px 10px #8a2ce8;
}

/* Worker Information Section */
.worker-info {
    flex: 1; /* Take remaining space */
    padding: 0 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Name Section */
.worker-info h3 {
    color: #663399;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 15px;
}

/* Worker Details Table */
.details {
    width: 100%;
    border-collapse: collapse;
}

/* Table Cells */
.details td {
    padding: 8px;
    font-size: 16px;
    color: #333;
    text-align: left; /* Align text to the left */
}

/* Label (Strong) Styling */
.details td strong {
    color: #663399;
    font-weight: normal;
    text-align: left; /* Align labels to the left */
    display: inline-block;
    width: 150px; /* Fixed width for labels */
}

/* Buttons Section */
.worker-box .buttons-container {
    display: flex;
    justify-content: space-between;
    width: 100%; /* Ensure the buttons fill the width of the box */
    /*margin-top: 5px; /* Add space above the buttons */
   /* padding: 0 10px; /* Add some padding */
}

/* Button Styling */
.worker-box button {
    background-color: #663399; /* Purple background */
    color: white;
    /*margin-bottom: 5px;*/
    /*padding: 10px 20px;*/
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    width: 80%; /* Make buttons take up 45% of the container width */
    box-shadow: 0 2px 10px #8a2ce8;
}

.worker-box button:hover {
    background-color: #5a3bc0; /* Darker purple on hover */
}

/* Space between each worker box */
.worker-box + .worker-box {
    margin-top: 30px;
}
/* Rating Stars */
.rating {
    font-size: 20px;
    color: gold;
}

.star {
    color: #ccc;
}

.star.filled {
    color: gold;
}

.rating-client {
    font-size: 14px;
    margin-top: 10px;
    color: #333;
}

.no-rating {
    font-size: 14px;
    color: #888;
}


/* Responsive Design for Smaller Screens */
@media (max-width: 768px) {
    .worker-box {
        flex-direction: column; /* Stack photo and content vertically */
        align-items: center; /* Center align the box */
        text-align: center; /* Center align text */
    }

    .worker-box .buttons-container {
        flex-direction: column; /* Stack buttons vertically */
        align-items: center; /* Center align buttons */
    }

    .worker-box button {
        width: 80%; /* Make buttons take up more space on small screens */
        margin-bottom: 10px; /* Add space between buttons */
    }
}


    </style>
</head>

<body>

<header>
        <div class="logo">Business Service</div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li class="active"><a href="find-worker.php">Find Worker</a></li>
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
                <li><a href="report.php">Report</a></li>
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

    <section class="find-worker">
        <h3>The platform empowers individuals to take control of their home improvement projects by connecting them with reliable and skilled professionals.</h3>
        <h2>Find Skilled Workers</h2>
        <form id="worker-search-form" action="find-worker.php" method="GET">
            <input type="text" id="worker-search" name="category" value="<?php echo htmlspecialchars($category); ?>" placeholder="Search for workers...">
            <button type="submit">Search</button>
        </form>
<div id="worker-results"></div>
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
    // Function to open the modal
    function openModal(workerId) {
        document.getElementById('hireModal-' + workerId).style.display = 'block';
    }

    // Function to close the modal
    function closeModal(workerId) {
        document.getElementById('hireModal-' + workerId).style.display = 'none';
    }
</script>

<style>
    /* Simple modal styles */
    .hireModal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
    }

    .modal-content {
        background-color: white;
        margin: 15% auto;
        padding: 20px;
        width: 300px;
        text-align: center;
    }
</style>

</body>

</html>