<?php
session_start(); // Start the session

// Destroy all session variables
session_unset();  // Clears all session variables
session_destroy(); // Destroys the session

// Redirect to signup page after logging out
header("Location: index.php");
exit();
?>