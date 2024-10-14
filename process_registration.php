<?php
require_once 'init.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['passwordConfirmation'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['message_type'] = "danger";
        header("Location: register.php");
        exit();
    }

    if ($password != $confirm_password) {
        $_SESSION['message'] = "Passwords do not match!";
        $_SESSION['message_type'] = "danger";
        header("Location: register.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = :username OR email = :email");
    $stmt->execute([':username' => $username, ':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['message'] = "Username or email already taken.";
        $_SESSION['message_type'] = "danger";
        header("Location: register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO user (username, email, password) VALUES (:username, :email, :password)");
    $stmt->execute([':username'=>$username, ':email'=>$email, ':password'=>$hashed_password]);

    // Get the newly created user's ID
    $userId = $pdo->lastInsertId();

    // Set session variables to log the user in
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    
    $_SESSION['message'] = "Registration successful! You are now logged in.";
    $_SESSION['message_type'] = "success";
    
    header("Location: browse.php");
    exit();
} else {
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = "danger";
    header("Location: register.php");
    exit();
}
?>