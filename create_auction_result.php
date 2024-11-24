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
    $start_date = new DateTime($_POST['start_date']);  // 用户输入的开始时间
    $end_date = new DateTime($_POST['end_date']);      // 用户输入的结束时间
    $now = new DateTime();

    // 后端验证开始时间和结束时间的合理性
    if ($start_date < $now) {
        echo ('<div class="alert alert-danger">Failed to create auction: Start date must be in the future.</div>');
        exit();
    }

    if ($end_date <= $start_date) {
        echo ('<div class="alert alert-danger">Failed to create auction: End date must be after the start date.</div>');
        exit();
    }

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
        // 插入数据库时使用用户提供的开始和结束时间
        $stmt = $pdo->prepare("INSERT INTO auction (seller_id, item_name, description, category_id, start_date, end_date, starting_price, reserve_price, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$_SESSION['user_id'], $item_name, $description, $category_id, $start_date->format('Y-m-d H:i:s'), $end_date->format('Y-m-d H:i:s'), $starting_price, $reserve_price, $image_url]);

        // 获取插入的拍卖 ID
        $auction_id = $pdo->lastInsertId();
        echo ('<div class="text-center">Auction successfully created! Time remaining: ' . display_time_remaining($end_date->diff($start_date)) . ' <a href="listing.php?auction_id=' . $auction_id . '">View your new listing.</a></div>');
    } catch (PDOException $e) {
        echo ('<div class="alert alert-danger">Failed to create auction: ' . $e->getMessage() . '</div>');
    }
    ?>
    <?php include_once("footer.php"); ?>