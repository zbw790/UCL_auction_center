<?php
require_once('init.php');
require_once('utilities.php');

// 验证用户是否登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// 获取拍卖ID
$auction_id = $_POST['auction_id'] ?? null;

if (!$auction_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid auction ID']);
    exit();
}

try {
    // 获取拍卖详情
    $stmt = $pdo->prepare("SELECT * FROM auction WHERE auction_id = ?");
    $stmt->execute([$auction_id]);
    $auction = $stmt->fetch();

    if (!$auction) {
        echo json_encode(['success' => false, 'message' => 'Auction not found']);
        exit();
    }

    // 获取当前时间和拍卖时间
    $now = new DateTime();
    $start_date = new DateTime($auction['start_date']);
    $end_date = new DateTime($auction['end_date']);

    // 如果当前时间超过了拍卖结束时间
    if ($now >= $end_date && $auction['status'] == 'active') {
        // 先将拍卖状态更新为 ended
        $stmt = $pdo->prepare("UPDATE auction SET status = 'ended' WHERE auction_id = ?");
        $stmt->execute([$auction_id]);

        // 获取当前出价信息
        $stmt = $pdo->prepare("SELECT COUNT(*) AS bid_count, COALESCE(MAX(bid_amount), 0) AS highest_bid FROM bid WHERE auction_id = ?");
        $stmt->execute([$auction_id]);
        $bid_info = $stmt->fetch();

        // 如果没有出价，更新状态为 cancelled
        if ($bid_info['bid_count'] == 0) {
            $stmt = $pdo->prepare("UPDATE auction SET status = 'cancelled' WHERE auction_id = ?");
            $stmt->execute([$auction_id]);
            echo json_encode(['success' => true, 'message' => 'Auction ended with no bids. Status set to cancelled.']);
        } else {
            // 如果有出价，生成交易
            $highest_bid = $bid_info['highest_bid'];
            if ($highest_bid >= $auction['reserve_price']) {
                $stmt = $pdo->prepare("SELECT user_id FROM bid WHERE auction_id = ? AND bid_amount = ? LIMIT 1");
                $stmt->execute([$auction_id, $highest_bid]);
                $highest_bidder = $stmt->fetch();

                if ($highest_bidder) {
                    $stmt = $pdo->prepare("INSERT INTO auction_transaction (auction_id, buyer_id, transaction_amount, transaction_date) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$auction_id, $highest_bidder['user_id'], $highest_bid, $now->format('Y-m-d H:i:s')]);
                    echo json_encode(['success' => true, 'message' => 'Auction ended successfully. Transaction created for highest bid.']);
                }
            } else {
                // 如果最高出价低于保留价，更新状态为 cancelled
                $stmt = $pdo->prepare("UPDATE auction SET status = 'cancelled' WHERE auction_id = ?");
                $stmt->execute([$auction_id]);
                echo json_encode(['success' => true, 'message' => 'Highest bid did not meet reserve price. Auction cancelled.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Auction is not yet ended or already updated']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
