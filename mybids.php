<?php include_once("header.php"); ?>
<?php require("utilities.php"); ?>
<?php require("db_connect.php"); ?>
<link rel="stylesheet" href="css/custom_3.css">
<div class="jumbotron bg-image">

<div class="container">
    <?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Query to fetch auctions the user has bid on
    $query = "
        SELECT a.auction_id, 
               a.item_name, 
               a.description, 
               COALESCE(MAX(b.bid_amount), a.starting_price) AS current_price, 
               COUNT(b.bid_id) AS num_bids, 
               a.end_date, 
               (SELECT MAX(b2.bid_amount) 
                FROM bid b2 
                WHERE b2.auction_id = a.auction_id) AS highest_bid, 
               (SELECT b3.bid_amount 
                FROM bid b3 
                WHERE b3.auction_id = a.auction_id AND b3.user_id = :user_id
                ORDER BY b3.bid_amount DESC LIMIT 1) AS user_bid
        FROM auction a
        LEFT JOIN bid b 
          ON a.auction_id = b.auction_id
        WHERE b.user_id = :user_id
        GROUP BY a.auction_id
        ORDER BY a.end_date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <h2 class="my-3">My Bids</h2>
    <div class="container">
        <?php if (empty($auctions)): ?>
            <p>You have not placed any bids yet.</p>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($auctions as $auction): ?>
                    <?php
                    // Current time
                    $current_time = new DateTime();
                    $end_time = new DateTime($auction['end_date']);

                    // Bid status
                    $status = "";
                    if ($current_time < $end_time) {
                        // Auction not yet ended
                        if ($auction['user_bid'] == $auction['highest_bid']) {
                            $status = '<span class="badge bg-success">Highest now</span>';
                        } else {
                            $status = '<span class="badge bg-warning">Outbid by others</span>';
                        }
                    } else {
                        // Auction ended
                        if ($auction['user_bid'] == $auction['highest_bid']) {
                            $status = '<span class="badge bg-primary">Auction won, payment required</span>';
                        } else {
                            $status = '<span class="badge bg-danger">Auction failed</span>';
                        }
                    }
                    ?>
                    <li class="list-group-item">
                        <h5><?php echo htmlspecialchars($auction['item_name']); ?></h5>
                        Current Price: $<?php echo number_format($auction['current_price'], 2); ?><br>
                        My Bidding History: <?php echo $auction['num_bids']; ?><br>
                        Ends: <?php echo $end_time->format('M d, Y H:i'); ?><br>
                        My Status: <?php echo $status; ?><br>
                        <a href="listing.php?auction_id=<?php echo $auction['auction_id']; ?>">View Details</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php include_once("footer.php"); ?>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS JS -->
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>
</body>
</html>
