<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php require("db_connect.php") ?>

<div class="container">



<h2 class="my-3">My bids</h2>

  <div class="container main-content">
    <!-- <h2 class="my-4">Browse Listings</h2> -->
     

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

    $base_query = "FROM auction a
                LEFT JOIN bid b 
                  ON a.auction_id = b.auction_id
                WHERE a.status = 'active' 
                AND a.end_date > NOW()";

    if(!empty($keyword)) {
      $base_query .= " AND (a.item_name LIKE :keyword OR a.description LIKE :keyword)";
      $params[":keyword"] = '%' . $keyword . '%';
    }
    if($category != "all") {
      $base_query .= " AND a.category_id = :category_id";
      $params[":category_id"] = $category;
    }





    $full_query = "SELECT a.auction_id, 
                          a.item_name, 
                          a.description, 
                          COALESCE(MAX(b.bid_amount), 
                          a.starting_price) AS current_price, 
                          COUNT(b.bid_id) AS num_bids, 
                          a.end_date
                    FROM auction a
                    LEFT JOIN bid b ON a.auction_id = b.auction_id
                    WHERE a.status = 'active' AND a.end_date > NOW()
                    GROUP BY a.auction_id
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

    $count_query = "SELECT COUNT(DISTINCT a.auction_id) AS total
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
                      <a href="listing.php?auction_id=<?php echo $auction['auction_id']; ?>" class="text-decoration-none">
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
        



        ?>
      </ul>
    </nav>
  </div>
</div>



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

<?php
  // This page is for showing a user the auctions they've bid on.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
  
  
  // TODO: Check user's credentials (cookie/session).
  
  // TODO: Perform a query to pull up the auctions they've bidded on.
  
  // TODO: Loop through results and print them out as list items.
  
?>
<?php include_once("footer.php") ?>
