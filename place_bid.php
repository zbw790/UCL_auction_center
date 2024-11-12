<?php
require_once 'init.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!isset($_POST['auction_id']) || !isset($_POST['bid_amount'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$auction_id = (int)$_POST['auction_id'];
$bid_amount = (float)$_POST['bid_amount'];
$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get current auction info with the latest bid amount or starting price
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(MAX(b.bid_amount), a.starting_price) as current_price,
            a.end_date,
            a.seller_id
        FROM auction a
        LEFT JOIN bid b ON a.auction_id = b.auction_id
        WHERE a.auction_id = ?
        GROUP BY a.auction_id
        FOR UPDATE
    ");
    $stmt->execute([$auction_id]);
    $auction = $stmt->fetch();
    
    // Validate bid
    if (!$auction) {
        throw new Exception('Auction not found');
    }
    
    if (new DateTime() > new DateTime($auction['end_date'])) {
        throw new Exception('Auction has ended');
    }
    
    if ($auction['seller_id'] == $user_id) {
        throw new Exception('You cannot bid on your own auction');
    }
    
    if ($bid_amount <= $auction['current_price']) {
        throw new Exception('Bid must be higher than current price');
    }
    
    // Place bid
    $stmt = $pdo->prepare("
        INSERT INTO bid (auction_id, user_id, bid_amount)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$auction_id, $user_id, $bid_amount]);
    
    // Update auction current price
    $stmt = $pdo->prepare("
        UPDATE auction
        SET current_price = ?, highest_bidder_id = ?
        WHERE auction_id = ?
    ");
    $stmt->execute([$bid_amount, $user_id, $auction_id]);
    
    $pdo->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
