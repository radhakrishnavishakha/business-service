<?php
session_start();
include 'db_connect.php'; // Include database connection

// Check if the user is logged in and is a client
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type1'] !== 'client') {
    exit("Unauthorized access! Please log in as a client.");
}

// Get the user_id from session
$user_id = intval($_SESSION['user_id']); // This is the foreign key in client table

// Fetch client ID using user_id
$query = "SELECT id FROM client WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exit("Error: Client not found in the database.");
}

$row = $result->fetch_assoc();
$client_id = $row['id']; // This is the actual client ID

// Check if worker_id, client_date, client_time_start, and client_time_end are received via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['worker_id'], $_POST['client_date'], $_POST['client_time_start'], $_POST['client_time_end'])) {
    $worker_id = intval($_POST['worker_id']); // Ensure it's a valid integer
    $client_date = $_POST['client_date'];
    $client_time_start = $_POST['client_time_start'];
    $client_time_end = $_POST['client_time_end'];
    
    $client_time = $client_time_start . " - " . $client_time_end;

    // Insert hire record into hire_data table
    $query = "INSERT INTO hire_data (client_id, worker_id, client_date, client_time, status) VALUES (?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $client_id, $worker_id, $client_date, $client_time);

    if ($stmt->execute()) {
        echo "<script>alert('You successfully hired the worker!'); window.location.href='report.php';</script>";
    } else {
        echo "<script>alert('Error hiring worker. Please try again!'); window.location.href='find-worker.php';</script>";
    }
} else {
    // If any necessary POST data is missing, show an error
    echo "<script>alert('Invalid request! Please ensure all fields are filled out.'); window.location.href='find-worker.php';</script>";
}
?>
