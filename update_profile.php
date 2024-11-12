<?php
require_once 'init.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone_number = trim($_POST['phone_number']);
    
    try {
        $stmt = $pdo->prepare("
            UPDATE user 
            SET first_name = :first_name,
                last_name = :last_name,
                phone_number = :phone_number
            WHERE user_id = :user_id
        ");
        
        $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':phone_number' => $phone_number,
            ':user_id' => $_SESSION['user_id']
        ]);
        
        $_SESSION['message'] = "Profile updated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error updating profile: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    header('Location: profile.php');
    exit();
}
?>