<?php include_once("header.php");
require_once('init.php');
require_once('utilities.php'); ?>
<div class="container my-5">
    <?php
   
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }

    
    $item_name = $_POST['auction_title'];
    $description = $_POST['details'];
    $category_id = $_POST['category'];
    $starting_price = $_POST['start_price'];
    $reserve_price = isset($_POST['reserve_price']) ? $_POST['reserve_price'] : null;
    $end_date = $_POST['end_date'];// the end time is customized by the user

    // time countdown start automatically when auction is created
    $start_date = new DateTime();

    // 检查是否有上传图片
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image["name"]);

        
        if (move_uploaded_file($image["tmp_name"], $target_file)) {
            $image_url = $target_file;
        } else {
            $image_url = null;
        }
    } else {
        $image_url = null;
    }

    
    try {
        $stmt = $pdo->prepare("INSERT INTO auction (seller_id, item_name, description, category_id, start_date, end_date, starting_price, reserve_price, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$_SESSION['user_id'], $item_name, $description, $category_id, $start_date->format('Y-m-d H:i:s'), $end_date, $starting_price, $reserve_price, $image_url]);

        
        $auction_id = $pdo->lastInsertId();
        echo ('<div class="text-center">Auction successfully created! Time remaining: ' . display_time_remaining((new DateTime($end_date))->diff($start_date)) . ' <a href="listing.php?auction_id=' . $auction_id . '">View your new listing.</a></div>');
    } catch (PDOException $e) {
        echo ('<div class="alert alert-danger">Failed to create auction: ' . $e->getMessage() . '</div>');
    }
    ?>
    <?php include_once("footer.php"); ?>