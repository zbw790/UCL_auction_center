<?php require_once 'init.php'; ?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />

  <link rel="stylesheet" href="css/custom_1.css">

  <title>Browse Auctions - My Auction Site</title>
</head>

<body>


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
        <?php if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'buyer'): ?>
          <li class="nav-item">
            <a class="nav-link" href="mybids.php">My Bids</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="recommendations.php">Recommended</a>
          </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'seller'): ?>
          <li class="nav-item">
            <a class="nav-link" href="mylistings.php">My Listings</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="create_auction.php">Create Auction</a>
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

<div class="jumbotron bg-image text-white text-center py-5 mb-4">
  <div class="container">
    <h1 class="display-4">Discover Amazing Auctions</h1>
    <p class="lead">Find unique items and great deals!</p>
  </div>
</div>

  <div class="container main-content">
    <!-- <h2 class="my-4">Browse Listings</h2> -->

    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php
      unset($_SESSION['message']);
      unset($_SESSION['message_type']);
      ?>
    <?php endif; ?>

    <div id="searchSpecs" class="search-container bg-white p-4 rounded-pill shadow mb-4">
      <form method="get" action="browse.php">
        <div class="row g-3 align-items-center">
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text bg-transparent border-0">
                <i class="fa fa-search"></i>
              </span>
              <input type="text" class="form-control border-0" id="keyword" name="keyword" placeholder="Search for anything">
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select border-0" id="cat" name="cat">
              <option selected value="all">All categories</option>
              <?php
              $stmt = $pdo->prepare("SELECT category_id, category_name FROM category ORDER BY category_name");
              $stmt->execute();
              $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

              foreach ($categories as $category) {
                echo '<option value="' . htmlspecialchars($category['category_id']) . '">' .
                htmlspecialchars($category['category_name']) . '</option>';
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <div class="input-group">
              <label class="input-group-text bg-transparent border-0" for="order_by">Sort by:</label>
              <select class="form-select border-0" id="order_by" name="order_by">
                <option selected value="price_low">Price (low to high)</option>
                <option value="price_high">Price (high to low)</option>
                <option value="date_asc">Expiry (soonest first)</option>
                <option value="date_desc">Expiry (latest first)</option>
              </select>
            </div>
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-primary rounded-pill w-100">Search</button>
          </div>
        </div>
      </form>
    </div>

    <?php
    // Retrieve these from the URL
    if (!isset($_GET['keyword'])) {
      $keyword = "";
    } else {
      $keyword = $_GET['keyword'];
    }

    if (!isset($_GET['cat'])) {
      $category = "all";
    } else {
      $category = $_GET['cat'];
    }
    
    if (!isset($_GET['order_by'])) {
      $ordering = "price_low";
    } else {
      $ordering = $_GET['order_by'];
    }
    
    if (!isset($_GET['page'])) {
      $curr_page = 1;
    } else {
      $curr_page = $_GET['page'];
    }


    $results_per_page = 9;
    $offset = ($curr_page - 1) * $results_per_page;

    $params = array();

    $base_query = "FROM item i
                    LEFT JOIN bid b ON i.item_id = b.item_id
                    WHERE i.status = 'active'";

    if(!empty($keyword)) {
      $base_query .= " AND (i.item_name LIKE :keyword OR i.description LIKE :keyword)";
      $params[":keyword"] = '%' . $keyword . '%';
    }
    if($category != "all") {
      $base_query .= "AND i.category_id = :category_id";
      $params[":category_id"] = $category;
    }

    $group_query = " GROUP BY i.item_id";

    switch ($ordering) {
      case 'price_low':
        $order = "ORDER BY COALESCE(MAX(b.bid_amount), i.starting_price) ASC";
        break;
      case 'price_high':
        $order = "ORDER BY COALESCE(MAX(b.bid_amount), i.starting_price) DESC";
        break;
      case 'date_asc':
        $order = "ORDER BY i.end_date ASC";
        break;
      case 'date_desc':
        $order = "ORDER BY i.end_date DESC";
        break;
      default:
        $order = "ORDER BY i.item_id ASC";
    }

    $full_query = "SELECT i.item_id, i.item_name, i.description, COALESCE(MAX(b.bid_amount), i.starting_price) AS current_price, COUNT(b.bid_id) AS num_bids, i.end_date, i.image_url
                    $base_query
                    $group_query
                    $order
                    LIMIT :offset, :limit";
    
    $stmt = $pdo->prepare($full_query);

    if (!empty($params)) {
      foreach ($params as $key => $value) {
          $stmt->bindValue($key, $value);
      }
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);

    $stmt->execute();
    $auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count_query = "SELECT COUNT(DISTINCT i.item_id) AS total
                    $base_query";
    $stmt = $pdo->prepare($count_query);

    if (!empty($params)) {
      foreach ($params as $key => $value) {
          $stmt->bindValue($key, $value);
      }
    }
    
    $stmt->execute();
    $num_results = $stmt->fetchColumn();
    $max_page = ceil($num_results / $results_per_page);
    ?>

    <?php
    function getRemainingTime($endDate) {
      $now = new DateTime();
      $end = new DateTime($endDate);
      $interval = $now->diff($end);
      
      if ($interval->invert) {
          return "Ended";
      }
  
      $parts = [];
  
      if ($interval->y > 0) {
          $parts[] = $interval->y . " year" . ($interval->y > 1 ? "s1" : "");
      }
  
      if ($interval->m > 0) {
          $parts[] = $interval->m . " month" . ($interval->m > 1 ? "s" : "");
      }
  
      if ($interval->d > 0) {
          $parts[] = $interval->d . " day" . ($interval->d > 1 ? "s" : "");
      }
  
      if ($interval->h > 0) {
          $parts[] = $interval->h . " hour" . ($interval->h > 1 ? "s" : "");
      }
  
      if (empty($parts)) {
          return "Less than an hour";
      }
  
      return implode(" ", array_slice($parts, 0, 2)); // 只返回最大的两个时间单位
  }
    ?>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
    <?php if (empty($auctions)): ?>
        <div class="col-12 text-center">
            <p class="lead">No active auctions match your search. Try different criteria!</p>
        </div>
    <?php else: ?>
        <?php foreach ($auctions as $auction): ?>
            <div class="col" data-aos="fade-up">
                <div class="card auction-card h-100 shadow-sm hover-effect">
                    <a href="listing.php?item_id=<?php echo $auction['item_id']; ?>" class="text-decoration-none">
                        <?php if (!empty($auction['image_url'])): ?>
                            <div class="card-img-top-wrapper">
                                <img src="<?php echo htmlspecialchars($auction['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($auction['item_name']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3"><?php echo htmlspecialchars($auction['item_name']); ?></h5>
                            <p class="card-text text-muted mb-3"><?php echo substr(htmlspecialchars($auction['description']), 0, 100); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-success">$<?php echo number_format($auction['current_price'], 2); ?></span>
                                <span class="badge bg-primary rounded-pill"><?php echo $auction['num_bids']; ?> bids</span>
                            </div>
                            <p class="card-text small mb-1"><i class="fas fa-clock me-2"></i><?php echo getRemainingTime($auction['end_date']); ?> left</p>
                            <p class="card-text small"><i class="fas fa-calendar-alt me-2"></i>Ends: <?php echo (new DateTime($auction['end_date']))->format('M d, Y H:i'); ?></p>
                        </div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
  

    <nav aria-label="Search results pages" class="my-4">
      <ul class="pagination justify-content-center">
        <?php
        // Copy any currently-set GET variables to the URL.
        $querystring = "";
        foreach ($_GET as $key => $value) {
          if ($key != "page") {
            $querystring .= "$key=$value&amp;";
          }
        }
        
        $high_page_boost = max(3 - $curr_page, 0);
        $low_page_boost = max(2 - ($max_page - $curr_page), 0);
        $low_page = max(1, $curr_page - 2 - $low_page_boost);
        $high_page = min($max_page, $curr_page + 2 + $high_page_boost);
        
        if ($curr_page != 1) {
          echo('
          <li class="page-item">
            <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
              <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
              <span class="sr-only">Previous</span>
            </a>
          </li>');
        }
          
        for ($i = $low_page; $i <= $high_page; $i++) {
          if ($i == $curr_page) {
            // Highlight the link
            echo('
          <li class="page-item active">');
          }
          else {
            // Non-highlighted link
            echo('
          <li class="page-item">');
          }
          
          // Do this in any case
          echo('
            <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
          </li>');
        }
        
        if ($curr_page != $max_page) {
          echo('
          <li class="page-item">
            <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
              <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
              <span class="sr-only">Next</span>
            </a>
          </li>');
        }
        ?>
      </ul>
    </nav>
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