<?php
session_start();

require_once 'db_connect.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if(empty($email) || empty($password)) {
    $_SESSION['error'] = "Please fill in all fields";
    header("Location: browse.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        
        header("Location: browse.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: browse.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Login failed: " . $e->getMessage();
    header("Location: browse.php");
    exit();
}
?>