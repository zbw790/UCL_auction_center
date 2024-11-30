<?
require_once 'db_connect.php';
function checkAuctionStatus($auction_id, $pdo)
{
    // Retrieve the current status information of the auction
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS bid_count, 
            COALESCE(MAX(b.bid_amount), 0) AS highest_bid, 
            a.reserve_price
        FROM auction a
        LEFT JOIN bid b ON a.auction_id = b.auction_id
        WHERE a.auction_id = ?
        GROUP BY a.auction_id
    ");
    $stmt->execute([$auction_id]);
    $result = $stmt->fetch();

    if ($result) {
        // Retrieve the number of bids and the highest bid for the auction
        $bid_count = $result['bid_count'];
        $highest_bid = $result['highest_bid'];
        $reserve_price = $result['reserve_price'];

        if ($bid_count == 0) {
            // If there are no bids, set the auction status to 'cancelled'
            $stmt = $pdo->prepare("UPDATE auction SET status = 'cancelled' WHERE auction_id = ?");
            $stmt->execute([$auction_id]);
        } elseif ($highest_bid < $reserve_price) {
            // If the highest bid is lower than the reserve price, set the auction status to 'cancelled'
            $stmt = $pdo->prepare("UPDATE auction SET status = 'cancelled' WHERE auction_id = ?");
            $stmt->execute([$auction_id]);
        }
    }
}
?>