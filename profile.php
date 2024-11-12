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

  <title>Profile - My Auction Site</title>
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

<div class="jumbotron bg-image text-white text-center py-5 mb-4">
  <div class="container">
    <h1 class="display-4">My Profile</h1>
    <p class="lead">Update your personal information</p>
  </div>
</div>

<div class="container main-content">
  <?php
  // Check if user is logged in
  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
  }

  // Get user information
  $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();

  if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
      <?php 
      echo $_SESSION['message'];
      unset($_SESSION['message']);
      unset($_SESSION['message_type']);
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h3 class="card-title mb-4">Personal Information</h3>
          <form action="update_profile.php" method="POST">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>

            <div class="mb-3">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" id="first_name" name="first_name" 
                     value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
            </div>

            <div class="mb-3">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="last_name" name="last_name" 
                     value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
            </div>

            <div class="mb-3">
              <label for="phone_number" class="form-label">Phone Number</label>
              <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                     value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
          </form>
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

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    once: true
  });
</script>

</body>
</html>