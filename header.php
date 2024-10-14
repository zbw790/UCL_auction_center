<?php
// 启用 session
session_start();
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false;
    $_SESSION['account_type'] = 'guest';  // 默认账户类型为 guest
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <!-- Bootstrap 5 和 FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <!-- 自定义CSS -->
  <link rel="stylesheet" href="css/custom.css">

  <title>My Auction Site</title>
</head>

<body>

<!-- 导航栏 -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
  <a class="navbar-brand" href="#">My Auction Site</a>
  <ul class="navbar-nav ms-auto">
    <li class="nav-item">
      <?php
      // 根据 session 状态显示登录或注销按钮
      if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
        echo '<a class="nav-link" href="logout.php">Logout</a>';
      } else {
        echo '<button type="button" class="btn nav-link" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>';
      }
      ?>
    </li>
  </ul>
</nav>

<!-- 二级导航栏 -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <ul class="navbar-nav align-middle">
    <li class="nav-item mx-1">
      <a class="nav-link" href="browse.php">Browse</a>
    </li>
    <?php
    // 根据账户类型显示不同的导航项
    if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'buyer') {
      echo('
      <li class="nav-item mx-1">
        <a class="nav-link" href="mybids.php">My Bids</a>
      </li>
      <li class="nav-item mx-1">
        <a class="nav-link" href="recommendations.php">Recommended</a>
      </li>');
    }
    if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'seller') {
      echo('
      <li class="nav-item mx-1">
        <a class="nav-link" href="mylistings.php">My Listings</a>
      </li>
      <li class="nav-item ml-3">
        <a class="nav-link btn border-light" href="create_auction.php">+ Create auction</a>
      </li>');
    }
    ?>
  </ul>
</nav>

<!-- 登录模态框 -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- 模态框头部 -->
      <div class="modal-header">
        <h4 class="modal-title" id="loginModalLabel">Login</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- 模态框主体 -->
      <div class="modal-body">
        <form method="POST" action="login_result.php">
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
          </div>
          <button type="submit" class="btn btn-primary form-control">Sign in</button>
        </form>
        <div class="text-center mt-3">or <a href="register.php">create an account</a></div>
      </div>

    </div>
  </div>
</div>

<!-- 引入 Bootstrap 5 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
