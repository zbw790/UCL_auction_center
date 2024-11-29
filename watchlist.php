<?php require_once 'init.php'; ?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Watchlist - My Auction Site</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/custom_1.css">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="#">My Auction Site</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="browse.php">Browse</a>
        </li>
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true): ?>
          <li class="nav-item">
            <a class="nav-link" href="create_auction.php">Create Auction</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="mybids.php">My Bids</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="mylistings.php">My Listings</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="watchlist.php">WatchList</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="recommendations.php">Recommendation</a>
          </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle"></i> 
            <?php 
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
              echo 'Hello ' . htmlspecialchars($_SESSION['username']) . '!';
            } else {
              echo 'Please login';
            }
            ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true): ?>
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            <?php else: ?>
              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
              <li><a class="dropdown-item" href="register.php">Register</a></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="jumbotron bg-image text-white text-center py-5 mb-4">
  <div class="container">
    <h1 class="display-4">My Watchlist</h1>
    <p class="lead">Track your favorite auctions</p>
  </div>
</div>

<div class="container">
    <?php
    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        echo '<div class="alert alert-warning">Please <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">login</a> to view your watchlist.</div>';
        exit;
    }

    // Get watchlist items
    $stmt = $pdo->prepare("
        SELECT a.*, 
               u.username as seller_username,
               c.category_name,
               COUNT(DISTINCT b.bid_id) as num_bids,
               COALESCE(MAX(b.bid_amount), a.starting_price) as current_price
        FROM watchlist w
        JOIN auction a ON w.auction_id = a.auction_id
        LEFT JOIN user u ON a.seller_id = u.user_id
        LEFT JOIN category c ON a.category_id = c.category_id
        LEFT JOIN bid b ON a.auction_id = b.auction_id
        WHERE w.user_id = ?
        AND a.end_date > NOW()
        AND a.status = 'active'
        GROUP BY a.auction_id
        ORDER BY a.end_date ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $watchlist_items = $stmt->fetchAll();
    ?>

    <?php if (empty($watchlist_items)): ?>
        <div class="alert alert-info">
            Your watchlist is empty. Browse our <a href="browse.php">auctions</a> to add items to your watchlist.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($watchlist_items as $item): ?>
                <?php
                $now = new DateTime();
                $end_date = new DateTime($item['end_date']);
                $time_remaining = $now->diff($end_date);
                ?>
                <div class="col" data-aos="fade-up">
                    <div class="card h-100 shadow-sm hover-effect">
                        <div class="position-relative">
                            <!-- Remove button stays on top -->
                            <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2"
                                    onclick="removeFromWatchlist(<?php echo $item['auction_id']; ?>, this); event.stopPropagation();">
                                <i class="fas fa-times"></i> Remove
                            </button>
                            
                            <!-- Make the entire card clickable -->
                            <a href="listing.php?auction_id=<?php echo $item['auction_id']; ?>" 
                               class="text-decoration-none text-dark">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($item['item_name']); ?>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <?php echo substr(htmlspecialchars($item['description']), 0, 100) . '...'; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-success">$<?php echo number_format($item['current_price'], 2); ?></span>
                                        <span class="badge bg-primary rounded-pill"><?php echo $item['num_bids']; ?> bids</span>
                                    </div>
                                    <p class="card-text small mb-0">
                                        <i class="fas fa-clock me-1"></i> 
                                        <?php
                                        echo $time_remaining->days . ' days, ' . 
                                             $time_remaining->h . ' hours, ' . 
                                             $time_remaining->i . ' minutes';
                                        ?>
                                    </p>
                                    <p class="card-text small">
                                        <i class="fas fa-user me-1"></i> 
                                        Seller: <?php echo htmlspecialchars($item['seller_username']); ?>
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="bg-light text-center text-lg-start mt-4">
  <div class="container p-4">
    <div class="row">
      <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
        <h5 class="text-uppercase">About Us</h5>
        <p>
          We are group 40 and UCL is the best!!!!.
        </p>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
        <h5 class="text-uppercase">Links</h5>
        <ul class="list-unstyled mb-0">
          <li><a href="#!" class="text-dark">Contact Us</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
    © 2023 My Auction Site. All rights reserved.
  </div>
</footer>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="login_result.php">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary">Sign in</button>
        </form>
        <div class="mt-3">
          <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
AOS.init({
    duration: 800,
    once: true
});

function removeFromWatchlist(auctionId, button) {
    if (confirm('Are you sure you want to remove this item from your watchlist?')) {
        $.ajax({
            url: 'watchlist_handler.php',
            type: 'POST',
            data: {
                auction_id: auctionId,
                action: 'remove'
            },
            success: function(response) {
                if (response.success) {
                    $(button).closest('.col').fadeOut(400, function() {
                        $(this).remove();
                        // Check if watchlist is empty after removal
                        if ($('.col').length === 0) {
                            location.reload(); // Reload to show empty message
                        }
                    });
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error processing request');
            }
        });
    }
}
</script>

</body>
</html>