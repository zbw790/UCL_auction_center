<?
require_once 'db_connect.php';
function checkAuctionStatus($auction_id, $pdo)
{
    // 获取拍卖的当前状态信息
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
        // 获取拍卖的出价数量和最高出价
        $bid_count = $result['bid_count'];
        $highest_bid = $result['highest_bid'];
        $reserve_price = $result['reserve_price'];

        if ($bid_count == 0) {
            // 如果没有出价，将拍卖状态设置为 'cancelled'
            $stmt = $pdo->prepare("UPDATE auction SET status = 'cancelled' WHERE auction_id = ?");
            $stmt->execute([$auction_id]);
        } elseif ($highest_bid < $reserve_price) {
            // 如果最高出价小于保留价，将拍卖状态设置为 'cancelled'
            $stmt = $pdo->prepare("UPDATE auction SET status = 'cancelled' WHERE auction_id = ?");
            $stmt->execute([$auction_id]);
        }
    }
}

?>