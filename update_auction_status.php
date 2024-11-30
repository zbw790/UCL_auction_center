<?php
require_once('init.php');
require_once('utilities.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Retrieve the auction ID
$auction_id = $_POST['auction_id'] ?? null;

if (!$auction_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid auction ID']);
    exit();
}

try {
    // Retrieve the auction details
    $stmt = $pdo->prepare("SELECT * FROM auction WHERE auction_id = ?");
    $stmt->execute([$auction_id]);
    $auction = $stmt->fetch();

    if (!$auction) {
        echo json_encode(['success' => false, 'message' => 'Auction not found']);
        exit();
    }

    // Retrieve the current time and the auction time
    $now = new DateTime();
    $start_date = new DateTime($auction['start_date']);
    $end_date = new DateTime($auction['end_date']);

    
    if ($now >= $end_date && $auction['status'] == 'active') {
        // First, update the auction status to 'ended'
        $stmt = $pdo->prepare("UPDATE auction SET status = 'ended' WHERE auction_id = ?");
        $stmt->execute([$auction_id]);

        // Retrieve the current bid information
        $stmt = $pdo->prepare("SELECT COUNT(*) AS bid_count, COALESCE(MAX(bid_amount), 0) AS highest_bid FROM bid WHERE auction_id = ?");
        $stmt->execute([$auction_id]);
        $bid_info = $stmt->fetch();

        // If there are no bids, update the status to 'cancelled'
        if ($bid_info['bid_count'] == 0) {
            $stmt = $pdo->prepare("UPDATE auction SET status = 'cancelled' WHERE auction_id = ?");
            $stmt->execute([$auction_id]);
            echo json_encode(['success' => true, 'message' => 'Auction ended with no bids. Status set to cancelled.']);
        } else {
            // If there are bids, generate a transaction
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
                // If the highest bid is lower than the reserve price, update the status to 'cancelled'
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
