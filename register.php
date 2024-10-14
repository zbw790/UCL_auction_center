<?php include_once("header.php")?>

<div class="container-fluid p-0">
  <!-- 添加吸引人的头部横幅 -->
  <div class="jumbotron bg-primary text-white text-center py-5 mb-4">
    <h1 class="display-4">Join Our Auction Community</h1>
    <p class="lead">Register now to start bidding and selling!</p>
  </div>

  <div class="container">
    <h2 class="my-4">Register New Account</h2>

    <!-- 注册表单 -->
    <div class="bg-light p-4 rounded shadow-sm mb-4">
      <form method="POST" action="process_registration.php">
        <div class="mb-3 row">
          <label for="username" class="col-sm-2 col-form-label">Username</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" required>
            <small class="form-text text-muted">This will be your public name on the site.</small>
          </div>
        </div>
        <div class="mb-3 row">
          <label for="email" class="col-sm-2 col-form-label">Email</label>
          <div class="col-sm-10">
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            <small class="form-text text-muted">We'll never share your email with anyone else.</small>
          </div>
        </div>
        <div class="mb-3 row">
          <label for="password" class="col-sm-2 col-form-label">Password</label>
          <div class="col-sm-10">
            <input type="password" class="form-control" id="password" name="password" placeholder="Choose a password" required>
            <small class="form-text text-muted">Use at least 8 characters with a mix of letters, numbers & symbols.</small>
          </div>
        </div>
        <div class="mb-3 row">
          <label for="passwordConfirmation" class="col-sm-2 col-form-label">Confirm Password</label>
          <div class="col-sm-10">
            <input type="password" class="form-control" id="passwordConfirmation" name="passwordConfirmation" placeholder="Repeat your password" required>
          </div>
        </div>
        <div class="mb-3 row">
          <div class="col-sm-10 offset-sm-2">
            <button type="submit" class="btn btn-primary">Register</button>
          </div>
        </div>
      </form>
    </div>

    <div class="text-center mb-4">
      Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
    </div>
  </div>
</div>

<?php include_once("footer.php")?>