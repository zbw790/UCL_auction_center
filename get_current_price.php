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
        SELECT 
            COALESCE(MAX(b.bid_amount), a.starting_price) as current_price,
            COUNT(DISTINCT b.bid_id) as num_bids,
            (SELECT COUNT(*) FROM watchlist w WHERE w.auction_id = a.auction_id) as watch_count
        FROM auction a
        LEFT JOIN bid b ON a.auction_id = b.auction_id
        WHERE a.auction_id = ?
        GROUP BY a.auction_id
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