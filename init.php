<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false;
    $_SESSION['account_type'] = 'guest'; 
}
?>