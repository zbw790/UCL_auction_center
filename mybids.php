<?php include_once("header.php"); ?>
<?php require("utilities.php"); ?>
<?php require("db_connect.php"); ?>

<div class="container">
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Query to fetch the auctions the user has bid on
$query = "SELECT a.auction_id, 
                 a.item_name, 
                 a.description, 
                 COALESCE(MAX(b.bid_amount), a.starting_price) AS current_price, 
                 COUNT(b.bid_id) AS num_bids, 
                 a.end_date 
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
        <p>You have not bidded on any auctions yet.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($auctions as $auction): ?>
                <li class="list-group-item">
                    <h5><?php echo htmlspecialchars($auction['item_name']); ?></h5>
                    Current Price: $<?php echo number_format($auction['current_price'], 2); ?><br>
                    Bids: <?php echo $auction['num_bids']; ?><br>
                    Ends: <?php echo (new DateTime($auction['end_date']))->format('M d, Y H:i'); ?><br>
                    <a href="listing.php?auction_id=<?php echo $auction['auction_id']; ?>">View Details</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php include_once("footer.php"); ?>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    once: true
  });
</script>
</body>
</html>