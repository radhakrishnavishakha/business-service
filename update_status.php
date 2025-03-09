<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hire_id = $_POST['hire_id'];
    $status = $_POST['status'];  // 'Accepted' or 'Rejected'
    $worker_time = $_POST['worker_time'] ?? '';  // Optional work time

    if ($status == 'Accepted' && $worker_time == '') {
        echo json_encode(["success" => false, "error" => "Work time is required when accepting"]);
        exit();
    }

    // Update hire data status and worker time
    $query = "UPDATE hire_data SET status = ?, worker_time = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $status, $worker_time, $hire_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Error updating the hire data"]);
    }
}
?>

