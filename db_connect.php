<?php
// db_connect.php
$host = 'localhost';
$db = 'UCL_auction_center';
$user = 'auction_user'; 
$pass = 'password'; 

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed. Please try again later.");
}
?>