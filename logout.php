<?php

session_start();

// Remove all session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Send the user back to the login page
header("Location: login.php");
exit();

?>