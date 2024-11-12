<?php
require_once 'init.php';

header('Content-Type: application/json');

if (!isset($_GET['auction_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$auction_id = (int)$_GET['auction_id'];

try {
    $stmt = $pdo->prepare("
        SELECT current_price, 
               (SELECT COUNT(*) FROM bid WHERE auction_id = auction.auction_id) as num_bids,
               (SELECT COUNT(*) FROM watchlist WHERE auction_id = auction.auction_id) as watch_count
        FROM auction 
        WHERE auction_id = ?
    ");
    $stmt->execute([$auction_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'current_price' => $result['current_price'],
            'num_bids' => $result['num_bids'],
            'watch_count' => $result['watch_count']
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>