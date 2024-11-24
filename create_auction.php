<?php include_once("header.php") ?>

<?php
require 'db_connect.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: login.php');
}
$getcategorysql = "SELECT `category_id`,`category_name` FROM `category`";
$stmt = $pdo->prepare($getcategorysql);
$stmt->execute();
$category = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="css/custom_2.css">
<div class="jumbotron bg-image">
  <div class="container">

    <!-- Create auction form -->
    <div style="max-width: 800px; margin: 10px auto">
      <h2 class="my-3">Create new auction</h2>
      <div class="card">
        <div class="card-body">
          <form method="post" action="create_auction_result.php" enctype="multipart/form-data">
            <div class="form-group row mb-3">
              <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Auction title</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="auctionTitle" name="auction_title" placeholder="e.g. Vintage Watch">
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Category</label>
              <div class="col-sm-10">
                <select class="form-control" id="auctionCategory" name="category">
                  <option selected>Choose...</option>
                  <?php
                  foreach ($category as $row) {
                    echo "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="auctionImage" class="col-sm-2 col-form-label text-right">Upload image</label>
              <div class="col-sm-10">
                <input type="file" class="form-control" id="auctionImage" name="image" placeholder="Upload images" accept="image/*"></input>
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
              <div class="col-sm-10">
                <textarea class="form-control" id="auctionDetails" name="details" rows="4"></textarea>
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting price</label>
              <div class="col-sm-10">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">£</span>
                  </div>
                  <input type="number" min="1" class="form-control" step="any" id="auctionStartPrice" name="start_price" placeholder="Enter number">
                </div>
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
              <div class="col-sm-10">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">£</span>
                  </div>
                  <input type="number" min="1" class="form-control" step="any" id="auctionReservePrice" name="reserve_price" placeholder="Enter number">
                </div>
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="auctionStartDate" class="col-sm-2 col-form-label text-right">Start date</label>
              <div class="col-sm-10">
                <input type="datetime-local" class="form-control" id="auctionStartDate" name="start_date" min="<?php echo date('Y-m-d\TH:i'); ?>">
              </div>
            </div>
            <div class="form-group row mb-3">
              <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
              <div class="col-sm-10">
                <input type="datetime-local" class="form-control" id="auctionEndDate" name="end_date">
              </div>
            </div>

            <button type="submit" class="btn btn-primary form-control" name="create_auction">Create Auction</button>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
  // JavaScript to add further validation for end date
  document.getElementById('auctionStartDate').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDateInput = document.getElementById('auctionEndDate');
    endDateInput.min = this.value; // Ensure end date cannot be earlier than start date
  });

  document.getElementById('auctionEndDate').addEventListener('change', function() {
    const startDate = new Date(document.getElementById('auctionStartDate').value);
    const endDate = new Date(this.value);

    if (endDate <= startDate) {
      alert('End date must be after the start date.');
      this.value = '';
    }
  });
</script>

<?php include_once("footer.php") ?>