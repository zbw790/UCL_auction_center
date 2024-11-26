<?php include_once("header.php");
require 'db_connect.php';

//All users are allowed to create auctions
//Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: browse.php');
    exit();
}

// Modify the auction
// Get 'edit_id' if auction exists
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;
$auction_id = null;

//Get all information of auction item created by the user
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM auction WHERE auction_id = :auction_id");
    $stmt->execute([':auction_id' => $edit_id]);
    $auction = $stmt->fetch(PDO::FETCH_ASSOC);
    //Back to browse if the user do not create auction
    if (!$auction) {
        header('Location: browse.php');
        exit();
    }
}
// Create new auction
// Get category list which is used to show all (value) category_name (key is category_id) in the form
$getcategorysql = "SELECT `category_id`,`category_name` FROM `category`";
$stmt = $pdo->prepare($getcategorysql);
$stmt->execute();
$category = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<link rel="stylesheet" href="css/custom_2.css">
<div class="jumbotron bg-image">
<div class="container mt-4">
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Auction form (either create or modify)-->
    <div style="max-width: 800px; margin: 10px auto">
        <h2 class="my-3"><?php echo $edit_id ? 'Modify Auction' : 'Create New Auction'; ?></h2>
        <div class="card">
            <div class="card-body">
                <form method="post" action="create_auction_result.php" enctype="multipart/form-data">
                    <div class="form-group row mb-3">
                        <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Auction title</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="auctionTitle" name="auction_title" 
                                   placeholder="e.g. Vintage Watch" required value="<?php echo $auction['item_name'] ?? ''; ?>"
                                   <?php echo $edit_id ? 'readonly' : ''; ?>>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Category</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="auctionCategory" name="category" required
                                   <?php echo $edit_id ? 'disabled' : ''; ?>>
                                <option value="">Choose...</option>
                                <?php foreach ($category as $row): ?>
                                    <option value="<?php echo htmlspecialchars($row['category_id']); ?>"
                                    <?php echo isset($auction['category_id']) && $auction['category_id'] == $row['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="auctionImage" class="col-sm-2 col-form-label text-right">Upload image</label>
                        <div class="col-sm-10">
                        <input type="file" class="form-control" id="auctionImage" name="image" accept="image/*">
                        <?php if (isset($auction) && $auction['image_url']): ?>
                                    <img src="<?php echo $auction['image_url']; ?>" alt="Current Image" class="mt-2" style="max-width: 200px;">
                        <?php endif; ?>  
                      </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="auctionDetails" name="details" 
                                      rows="4" required></textarea>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting price</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" min="0.01" step="0.01" class="form-control" 
                                       id="auctionStartPrice" name="start_price" required
                                       value="<?php echo $auction['starting_price'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" min="0.01" step="0.01" class="form-control" 
                                       id="auctionReservePrice" name="reserve_price" required
                                       value="<?php echo $auction['reserve_price'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="auctionStartDate" class="col-sm-2 col-form-label text-right">Start date</label>
                        <div class="col-sm-10">
                            <input type="datetime-local" class="form-control" id="auctionStartDate" 
                                   name="start_date" required value="<?php echo isset($auction) ? date('Y-m-d\TH:i', strtotime($auction['start_date'])) : ''; ?>"
                                   <?php echo $edit_id ? 'readonly' : ''; ?>>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
                        <div class="col-sm-10">
                            <input type="datetime-local" class="form-control" id="auctionEndDate" 
                                   name="end_date" required value="<?php echo isset($auction) ? date('Y-m-d\TH:i', strtotime($auction['end_date'])) : ''; ?>"
                                   <?php echo $edit_id ? 'readonly' : ''; ?>>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary form-control" name="submit">
                    <?php echo $edit_id ? 'Modify Auction' : 'Create Auction'; ?>
                    </button>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_id ?? ''; ?>" />
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<?php include_once("footer.php") ?>