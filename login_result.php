<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to login.
// Notify user of success/failure and redirect/give navigation options.

// For now, I will just set session variables and redirect.

session_start();
$_SESSION['logged_in'] = true;
$_SESSION['username'] = "test";
$_SESSION['account_type'] = "buyer";
header("Location: browse.php");

?>