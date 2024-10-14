<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false;
    $_SESSION['account_type'] = 'guest'; 
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <!-- Bootstrap 5 和 FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <!-- AOS CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />

  <!-- 自定义CSS -->
  <link rel="stylesheet" href="css/custom.css">

  <title>Browse Auctions - My Auction Site</title>
</head>

<body>

<!-- Navigation bar -->
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

<div class="container-fluid p-0">
  <div class="jumbotron bg-primary text-white text-center py-5 mb-4">
    <h1 class="display-4">Discover Amazing Auctions</h1>
    <p class="lead">Find unique items and great deals!</p>
  </div>

  <div class="container">
    <h2 class="my-4">Browse Listings</h2>

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

    <div id="searchSpecs" class="bg-light p-4 rounded shadow-sm mb-4">
      <form method="get" action="browse.php">
        <div class="row g-3 align-items-center">
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text bg-white">
                <i class="fa fa-search"></i>
              </span>
              <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Search for anything">
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" id="cat" name="cat">
              <option selected value="all">All categories</option>
              <option value="electronics">Electronics</option>
              <option value="fashion">Fashion</option>
              <option value="home">Home & Garden</option>
              <option value="sports">Sports</option>
              <option value="toys">Toys & Hobbies</option>
            </select>
          </div>
          <div class="col-md-3">
            <div class="input-group">
              <label class="input-group-text" for="order_by">Sort by:</label>
              <select class="form-select" id="order_by" name="order_by">
                <option selected value="pricelow">Price (low to high)</option>
                <option value="pricehigh">Price (high to low)</option>
                <option value="date">Soonest expiry</option>
              </select>
            </div>
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Search</button>
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
      $ordering = "pricelow";
    } else {
      $ordering = $_GET['order_by'];
    }
    
    if (!isset($_GET['page'])) {
      $curr_page = 1;
    } else {
      $curr_page = $_GET['page'];
    }

    /* TODO: Use above values to construct a query. Use this query to 
       retrieve data from the database. (If there is no form data entered,
       decide on appropriate default value/default query to make. */
    
    /* For the purposes of pagination, it would also be helpful to know the
       total number of results that satisfy the above query */
    $num_results = 96; // TODO: Calculate me for real
    $results_per_page = 10;
    $max_page = ceil($num_results / $results_per_page);
    ?>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
      <?php
      // Replace this with your actual data retrieval logic
      for ($i = 0; $i < 6; $i++) {
        $item_id = "item_" . $i;
        $title = "Auction Item " . ($i + 1);
        $description = "This is a sample description for auction item " . ($i + 1) . ". It's a great product!";
        $current_price = rand(10, 1000);
        $num_bids = rand(0, 20);
        $end_date = new DateTime(date('Y-m-d H:i:s', strtotime('+' . rand(1, 30) . ' days')));
        
        echo '<div class="col" data-aos="fade-up">';
        echo '<div class="card h-100 shadow-sm">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">' . $title . '</h5>';
        echo '<p class="card-text">' . substr($description, 0, 100) . '...</p>';
        echo '<p class="card-text"><strong>Current Price:</strong> $' . number_format($current_price, 2) . '</p>';
        echo '<p class="card-text"><strong>Bids:</strong> ' . $num_bids . '</p>';
        echo '<p class="card-text"><strong>Ends:</strong> ' . $end_date->format('Y-m-d H:i:s') . '</p>';
        echo '</div>';
        echo '<div class="card-footer">';
        echo '<a href="listing.php?item_id=' . $item_id . '" class="btn btn-primary">View Auction</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
      }
      ?>
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
          UCL is the best!!!!.
        </p>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
        <h5 class="text-uppercase">Links</h5>
        <ul class="list-unstyled mb-0">
          <li><a href="#!" class="text-dark">FAQ</a></li>
          <li><a href="#!" class="text-dark">Contact Us</a></li>
          <li><a href="#!" class="text-dark">Terms of Service</a></li>
          <li><a href="#!" class="text-dark">Privacy Policy</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
        <h5 class="text-uppercase">Follow Us</h5>
        <ul class="list-unstyled">
          <li><a href="#!" class="text-dark"><i class="fab fa-facebook-f"></i> Facebook</a></li>
          <li><a href="#!" class="text-dark"><i class="fab fa-twitter"></i> Twitter</a></li>
          <li><a href="#!" class="text-dark"><i class="fab fa-instagram"></i> Instagram</a></li>
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