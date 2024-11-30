<?php
  // This page is for showing a buyer recommended items based on their bid 
  // history. It will be pretty similar to browse.php, except there is no 
  // search bar. This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.

  // TODO: Check user's credentials (cookie/session).
  // TODO: Perform a query to pull up auctions they might be interested in.
  // TODO: Loop through results and print them out as list items.
?>

<?php include_once("header.php") ?>
<link rel="stylesheet" href="css/custom_3.css">
<div class="jumbotron bg-image">
<div class="container">

<?php
require 'db_connect.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
}
?>

<body>

<?php

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = "Please log in to view recommended items.";
    header("Location: login.php");
    exit;
}


$user_id = $_SESSION['user_id']; 

$query = "
SELECT a.auction_id, a.item_name, a.category_id, a.image_url, a.current_price, a.end_date, a.description
FROM auction a
WHERE a.category_id IN (
  SELECT DISTINCT category_id
  FROM (
    SELECT a.category_id
    FROM watchlist w
    JOIN auction a ON w.auction_id = a.auction_id
    WHERE w.user_id = :user_id
    UNION
    SELECT a.category_id
    FROM bid b
    JOIN auction a ON b.auction_id = a.auction_id
    WHERE b.user_id = :user_id
  ) interested_categories
)
AND a.auction_id NOT IN (
  SELECT auction_id
  FROM watchlist
  WHERE user_id = :user_id
  UNION
  SELECT auction_id
  FROM bid
  WHERE user_id = :user_id
)
AND a.status = 'active'
AND a.end_date > NOW()
ORDER BY a.end_date ASC, a.auction_id ASC
LIMIT 10;

";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$recommended_auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="container main-content my-4">
  <h2 class="mb-4">You might also like: </h2>

  <div class="row g-4">
    <?php if (empty($recommended_auctions)): ?>
        <div class="col-12 text-center">
            <p class="lead">No recommendations available at the moment. Start watching and bidding to get personalized recommendations!</p>
        </div>
    <?php else: ?>
        <?php foreach ($recommended_auctions as $auction): ?>
            <div class="col-12">
                <div class="card h-100 shadow-sm">
                    <a href="listing.php?auction_id=<?php echo $auction['auction_id']; ?>" class="text-decoration-none d-flex align-items-center">
                        <?php if (!empty($auction['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($auction['image_url']); ?>" class="img-thumbnail me-3" alt="<?php echo htmlspecialchars($auction['item_name']); ?>" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title text-primary"><?php echo htmlspecialchars($auction['item_name']); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($auction['description']), 0, 100); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-success">$<?php echo number_format($auction['current_price'], 2); ?></span>
                                <span class="small">Ends: <?php echo (new DateTime($auction['end_date']))->format('M d, Y H:i'); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<footer class="bg-light text-center text-lg-start mt-4">
  <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
    © 2023 My Auction Site. All rights reserved.
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
