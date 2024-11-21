<?php include_once("header.php");
require_once('db_connect.php'); ?>

<div class="container my-5">

    <?php
    // 提取表单数据
    $item_name = $_POST['auctionTitle'];
    $description = $_POST['auctionDetails'];
    $category_id = $_POST['auctionCategory'];
    $starting_price = $_POST['auctionStartPrice'];
    $reserve_price = isset($_POST['auctionReservePrice']) ? $_POST['auctionReservePrice'] : null;
    $end_date = $_POST['auctionEndDate'];

    // 设置拍卖开始时间
    $start_date = new DateTime();

    // 插入拍卖数据到 auction 表
    try {
        $stmt = $pdo->prepare("
        INSERT INTO auction (seller_id, item_name, description, category_id, start_date, end_date, starting_price, reserve_price, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
        $stmt->execute([$_SESSION['user_id'], $item_name, $description, $category_id, $start_date->format('Y-m-d H:i:s'), $end_date, $starting_price, $reserve_price]);

        // 显示成功消息
        echo ('<div class="text-center">Auction successfully created! <a href="listing.php?auction_id=' . $pdo->lastInsertId() . '">View your new listing.</a></div>');
    } catch (PDOException $e) {
        echo ('<div class="alert alert-danger">Failed to create auction: ' . $e->getMessage() . '</div>');
    }
    ?>

</div>

<?php include_once("footer.php"); ?>