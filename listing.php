<?php require_once 'init.php'; ?>
<?php require_once 'utilities.php'; ?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Auction Details - My Auction Site</title>

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
              <a class="nav-link" href="watchlist.php">Watchlist</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="mylistings.php">My Listings</a>
            </li>
            <li class="nav-item mx-1">
            <a class="nav-link" href="my_transactions.php">My Transactions</a>
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
                <li>
                  <hr class="dropdown-divider">
                </li>
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

  <?php
  // Get auction ID from URL
  $auction_id = isset($_GET['auction_id']) ? (int)$_GET['auction_id'] : 0;

  // Fetch auction details with watch count
  $stmt = $pdo->prepare("
    SELECT a.*, 
           u.username as seller_username, 
           c.category_name,
           COUNT(DISTINCT b.bid_id) as num_bids,
           COALESCE(MAX(b.bid_amount), a.starting_price) as current_price,
           (SELECT COUNT(*) FROM watchlist w WHERE w.auction_id = a.auction_id) as watch_count
    FROM auction a
    LEFT JOIN user u ON a.seller_id = u.user_id
    LEFT JOIN category c ON a.category_id = c.category_id
    LEFT JOIN bid b ON a.auction_id = b.auction_id
    WHERE a.auction_id = ?
    GROUP BY a.auction_id
");
  $stmt->execute([$auction_id]);
  $auction = $stmt->fetch();

  if (!$auction) {
    echo '<div class="container mt-4"><div class="alert alert-danger">Auction not found!</div></div>';
    exit();
  }

  // Check if user is watching this auction
  $watching = false;
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $stmt = $pdo->prepare("
        SELECT 1 FROM watchlist 
        WHERE user_id = ? AND auction_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $auction_id]);
    $watching = $stmt->fetchColumn();
  }

  // Calculate time remaining
  $now = new DateTime();
  $start_date = new DateTime($auction['start_date']);
  $end_date = new DateTime($auction['end_date']);
  $time_remaining = $now < $end_date ? $end_date->diff($now) : null; //按钮？

  // 只有当拍卖状态为 active 且当前时间小于结束时间时，允许竞标
  $bid_disabled = $auction['status'] != 'active' || $now >= $end_date;
  ?>

  <div class="container mt-4">
    <div class="row">
      <!-- Left Column -->
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h2 class="card-title mb-0"><?php echo htmlspecialchars($auction['item_name']); ?></h2>
              <?php if (isset($_SESSION['logged_in']) && $now < $end_date): ?>
                <div id="watchlist-buttons" class="text-end">
                  <button id="watch-btn" class="btn btn-outline-primary <?php echo $watching ? 'd-none' : ''; ?>"
                          onclick="toggleWatchlist(<?php echo $auction_id; ?>, true)">
                      <i class="far fa-star"></i> Add to Watchlist
                  </button>
                  <button id="unwatch-btn" class="btn btn-primary <?php echo !$watching ? 'd-none' : ''; ?>"
                          onclick="toggleWatchlist(<?php echo $auction_id; ?>, false)">
                      <i class="fas fa-star"></i> Watching
                  </button>
                  <div class="text-muted mt-2">
                      <small><span id="watch-count"><?php echo $auction['watch_count']; ?></span> people watching</small>
                  </div>
</div>
              <?php endif; ?>
            </div>

            <?php if ($auction['image_url']): ?>
              <img src="<?php echo htmlspecialchars($auction['image_url']); ?>"
                class="img-fluid mb-3" alt="<?php echo htmlspecialchars($auction['item_name']); ?>">
            <?php endif; ?>

            <div class="mb-3">
              <h5>Description</h5>
              <p><?php echo nl2br(htmlspecialchars($auction['description'])); ?></p>
            </div>

            <div class="auction-details">
              <p><strong>Category:</strong> <?php echo htmlspecialchars($auction['category_name']); ?></p>
              <p><strong>Seller:</strong> <?php echo htmlspecialchars($auction['seller_username']); ?></p>
              <p><strong>Start Date:</strong> <?php echo (new DateTime($auction['start_date']))->format('M d, Y H:i'); ?></p>
              <p><strong>End Date:</strong> <?php echo $end_date->format('M d, Y H:i'); ?></p>
            </div>
          </div>
        </div>
      </div>

    <!-- Right Column -->
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-center mb-3">
            <?php if ($now < $end_date): ?>
              <div class="countdown-timer mb-3">
                <h5>Time remaining:</h5>
                <strong id="time-remaining">
                  <?php
                  if ($time_remaining) {
                    echo display_time_remaining($time_remaining);
                  }
                  ?>
                </strong>
              </div>
            <?php else: ?>
              <div class="alert alert-secondary">
                This auction has ended
              </div>
            <?php endif; ?>
          </div>

          <div class="price-info text-center mb-4">
            <h3>Current Price</h3>
            <h2 class="text-primary">$<span id="current-price"><?php echo number_format($auction['current_price'], 2); ?></span></h2>
            <p class="text-muted"><span id="num-bids"><?php echo $auction['num_bids']; ?></span> bids</p>
          </div>

          <?php if (!$bid_disabled && isset($_SESSION['logged_in'])): ?>
            <form id="bid-form" class="mb-3">
              <div class="input-group mb-3">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control" id="bid-amount"
                  min="<?php echo $auction['current_price'] + 0.01; ?>"
                  step="0.01" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Place Bid</button>
            </form>
          <?php elseif (!isset($_SESSION['logged_in'])): ?>
            <div class="alert alert-info text-center">
              Please <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">login</a> to place a bid
            </div>
          <?php else: ?>
            <div class="alert alert-secondary text-center">
              Bidding has ended for this auction
            </div>
          <?php endif; ?>
        </div>
      </div>


      <!-- Recent Bids Section -->
      <div class="card shadow-sm mt-3">
        <div class="card-body">
          <h5 class="card-title">Recent Bids</h5>
          <div id="recent-bids">
            <?php
            $stmt = $pdo->prepare("
                            SELECT b.bid_amount, b.bid_date, u.username
                            FROM bid b
                            JOIN user u ON b.user_id = u.user_id
                            WHERE b.auction_id = ?
                            ORDER BY b.bid_date DESC
                            LIMIT 5
                        ");
            $stmt->execute([$auction_id]);
            $recent_bids = $stmt->fetchAll();
            ?>

            <?php if ($recent_bids): ?>
              <ul class="list-group list-group-flush">
                <?php foreach ($recent_bids as $bid): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge bg-primary rounded-pill">$<?php echo number_format($bid['bid_amount'], 2); ?></span>
                      by <?php echo htmlspecialchars($bid['username']); ?>
                    </div>
                    <small class="text-muted">
                      <?php echo (new DateTime($bid['bid_date']))->format('M d, H:i'); ?>
                    </small>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="text-muted text-center">No bids yet</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
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

  <script>
    // Function to update price and bid count
    function updatePriceAndBids() {
      $.ajax({
        url: 'get_current_price.php',
        type: 'GET',
        data: {
          auction_id: <?php echo $auction_id; ?>
        },
        success: function(response) {
          if (response.success) {
            $('#current-price').text(parseFloat(response.current_price).toFixed(2));
            $('#num-bids').text(response.num_bids);
            $('#bid-amount').attr('min', parseFloat(response.current_price) + 0.01);
            $('#watch-count').text(response.watch_count);
          }
        }
      });
    }

    // Update price every 5 seconds
    setInterval(updatePriceAndBids, 1000); //every 1 second

    // 更新服务器时间的函数
    function fetchServerTime() {
      return $.ajax({
        url: 'server_time.php', // 这个 PHP 文件返回当前服务器时间
        type: 'GET',
        dataType: 'json'
      });
    }

    // Update time remaining every second
    // 更新剩余时间的函数
    function updateTimeRemaining() {
      // 通过 AJAX 获取服务器时间
      fetchServerTime().done(function(response) {
        if (response.success) {
          // 使用服务器时间计算剩余时间
          const serverTime = new Date(response.server_time);
          const startDate = new Date('<?php echo $auction['start_date']; ?>');
          const endDate = new Date('<?php echo $auction['end_date']; ?>');

          // 如果拍卖还未开始，显示拍卖未开始的信息
          if (serverTime < startDate) {
            $('#time-remaining').closest('.countdown-timer').parent().html(
              '<div class="alert alert-secondary">This auction has not started yet</div>'
            );
            $('#bid-form').remove(); // 禁用竞标表单
            return;
          }

          const diff = endDate - serverTime;

          // 如果倒计时结束，通知服务器更新拍卖状态
          if (diff <= 0) {
            $.ajax({
              url: 'update_auction_status.php', // 新增的后端接口，用于处理拍卖结束状态
              type: 'POST',
              data: {
                auction_id: <?php echo $auction_id; ?>
              },
              success: function(response) {
                if (response.success) {
                  $('#time-remaining').closest('.countdown-timer').parent().html(
                    '<div class="alert alert-secondary">This auction has ended</div>'
                  );
                  $('#bid-form').remove(); // 禁用竞标表单
                }
              }
            });
            return;
          }

          // 更精确地计算剩余时间
          const totalSeconds = Math.floor(diff / 1000);
          const days = Math.floor(totalSeconds / (60 * 60 * 24));
          const hours = Math.floor((totalSeconds % (60 * 60 * 24)) / (60 * 60));
          const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
          const seconds = totalSeconds % 60;

          // 更新倒计时显示
          $('#time-remaining').text(
            `${days} days, ${hours} hours, ${minutes} minutes, ${seconds} seconds`
          );

          // 如果倒计时低于10秒，增加刷新频率
          if (totalSeconds <= 10) {
            clearInterval(updateInterval);
            updateInterval = setInterval(updateTimeRemaining, 200); // 每 200 毫秒更新一次
          }

          // 如果倒计时结束，禁用竞标按钮（双重保险）
          if (serverTime >= endDate) {
            $('#bid-form').remove(); // 移除竞标表单
            $('#time-remaining').closest('.countdown-timer').parent().html(
              '<div class="alert alert-secondary">This auction has ended</div>'
            );
          }
        }
      });
    }



    // Update time remaining every minute
    setInterval(updateTimeRemaining, 1000); //update every second

    function toggleWatchlist(auctionId, adding) {
      $.ajax({
        url: 'watchlist_handler.php',
        type: 'POST',
        data: {
          auction_id: auctionId,
          action: adding ? 'add' : 'remove'
        },
        success: function(response) {
          if (response.success) {
            $('#watch-btn').toggleClass('d-none');
            $('#unwatch-btn').toggleClass('d-none');
            $('#watch-count').text(response.watch_count);
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: function() {
          alert('Error processing request');
        }
      });
    }

    // Bid form submission
    $('#bid-form').on('submit', function(e) {
      e.preventDefault();

      $.ajax({
        url: 'place_bid.php',
        type: 'POST',
        data: {
          auction_id: <?php echo $auction_id; ?>,
          bid_amount: $('#bid-amount').val()
        },
        success: function(response) {
          if (response.success) {
            location.reload();
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: function() {
          alert('Error processing bid');
        }
      });
    });

    // Initial updates
    updatePriceAndBids();
    updateTimeRemaining();
  </script>

</body>

</html>