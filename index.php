<?php
session_start(); // Start the session 

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Business Service</title>
    <link rel="stylesheet" href="style.css">
    <style>
        
        
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            
            background-color: #f8f8f8;
            
        }
        
        .hero {
            text-align: center;
            padding: 2rem 0;
            background-color: #fff;
            background-image: url(search2.jpg);
            background-size: cover;
        }
        
        .hero h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #a5a5a5;
           
        }
        
        .hero p {
            margin-bottom: 2rem;
            color: #a5a5a5;
            
        }
        
        .hero button {
            background-color: #663399;
           
            color: #fff;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 5px 10px #851ded;
        }
        
        .hero button a {
            text-decoration: none;
            color: white;
        }

        .account a {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #663399;
            color: white;
            border-radius: 5px;
        }
        

    </style>
</head>

<body>

    <header>
        <div class="logo">Business Service</div>
        <nav>
            <ul>
                <li class="active"><a href="index.php">Home</a></li>
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

    <section class="hero">
        <h1>Find Skilled Workers</h1>
        <p>Easily find reliable and skilled workers for your home improvement projects.</p>
        <button><a href="find-worker.php">Find Workers Now</a></button>
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

</body>

</html>