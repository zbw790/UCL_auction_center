<?php
require_once 'init.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    
    //send a welcoming email
    try {
        $mail = new PHPMailer(true);

        // SMTP setting
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'lucy.yang.test@gmail.com'; 
        $mail->Password = 'dkkswnibnpdvwslv';         
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587; 

        
        $mail->setFrom('lucy.yang.test@gmail.com', 'Auction Site');
        $mail->addAddress($email, $username); 

        
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Auction Site!';
        $mail->Body = "
            <h1>Hi $username,</h1>
            <p>Thank you for registering at Auction Site.</p>
            <p>We're thrilled to have you on board. Start exploring our platform today and make the most of our auctions!</p>
            <p><a href=\"http://localhost/UCL_auction_center/browse.php\">Visit Auction Site</a></p>
            <br>
            <p>Best regards,<br>The Auction Site Team</p>
        ";

        
        $mail->send();
        $_SESSION['message'] .= " Welcome to our auction site!";
    } catch (Exception $e) {
        $_SESSION['message'] .= " However, we couldn't send a welcome email. Error: " . $mail->ErrorInfo;
    }

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
