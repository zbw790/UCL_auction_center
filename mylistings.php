<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php require("db_connect.php")?>
<?php 
// Creator can delete the auction if the auction exists
if (isset($_GET['delete_id'])) {
  try {
      $stmtdelete = $pdo->prepare("DELETE FROM auction WHERE auction_id = :auction_id");
      $stmtdelete->execute([':auction_id' => $_GET['delete_id']]);
      
      if ($stmtdelete->rowCount() > 0) {
          echo "<script>alert('The auction item is successfully deleted from your listings...');</script>";
      } else {
          echo "<script>alert('No auction items were deleted.');</script>";
      }
  } catch (PDOException $e) {
      echo "<script>alert('Error deleting record: " . $e->getMessage() . "');</script>";
  }
}
?>

<link rel="stylesheet" href="css/custom_3.css">
<div class="jumbotron bg-image">
<div class="container">

  <h2 class="my-3">My listings</h2>
  
  <div class="checkout-left">  

    <div class="col-md-12 ">
      <table id="datatable" class="table table-striped table-bordered dataTable" role="grid" aria-describedby="example_info">            
        <thead>
          <tr>  
            <th style="width:175px;">Item Name</th>
            <th>Category</th>        
            <th>Bids</th>
            <th>Last Bid</th>
            <th>Auction Period</th>
            <th>Reserve Price</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>

        <?php
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: browse.php');
            exit();
        }
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("
            SELECT * FROM auction a
            LEFT JOIN category c ON a.category_id = c.category_id
            WHERE a.seller_id = :seller_id
            ORDER BY a.auction_id ASC
            ");
        $stmt->execute([
            'seller_id' => $user_id
        ]);
        while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
            $stmtbidding = $pdo->prepare("SELECT * FROM bid WHERE auction_id = :auction_id ");
            $stmtbidding->execute(['auction_id' => $rows['auction_id']]);
            $bidCount = $stmtbidding->rowCount();

            echo "<tr>
                  <td>".htmlspecialchars($rows['item_name'])."</td>";
            echo "<td>".htmlspecialchars($rows['category_name'])."</td>";
            echo "<td>".$bidCount."</td>";
            
            // Display the last bid or starting price
            if ($bidCount >= 1) {
               echo "<td>".htmlspecialchars($rows['highest_bid_price'])."</td>";
            } else {
               echo "<td>".htmlspecialchars($rows['starting_price'])."</td>";
            }

            // Display auction period (start date and end date)
            echo "<td>". date("d/m/Y h:i A",strtotime($rows['start_date'])) . " -".  date("d/m/Y h:i A",strtotime($rows['end_date'])) . "</td>";
            echo "<td>".htmlspecialchars($rows['reserve_price'])."</td>";
            echo "<td>".htmlspecialchars($rows['status'])."</td>";

            // Determine actions based on auction status and bid count
            $status = $rows['status'];
            $auction_id = $rows['auction_id'];
            
            if ($status == 'active' && $bidCount == 0) {
                // If status is 'active' and no bids, allow edit and delete
                echo "<td>
                    <a href='create_auction.php?edit_id=$auction_id' class='btn btn-warning custom-btn'>Edit</a> <br>
                    <a href='mylistings.php?delete_id=$auction_id' onclick='return deleteconfirm()' class='btn btn-danger custom-btn'>Delete</a> <br>
                    <a href='listing.php?auction_id=$auction_id' target='_blank' class='btn btn-info custom-btn'>View</a>
                </td>";
            } elseif ($status == 'active' && $bidCount > 0) {
                // If status is 'active' and there are bids, only allow view
                echo "<td>
                    <a href='listing.php?auction_id=$auction_id' target='_blank' class='btn btn-info custom-btn'>View</a>
                </td>";
            } elseif ($status == 'ended') {
                // If auction has ended, only allow view
                echo "<td>
                    <a href='listing.php?auction_id=$auction_id' target='_blank' class='btn btn-info custom-btn'>View</a>
                </td>";
            } elseif ($status == 'cancelled') {
                // If auction is cancelled
                if ($bidCount == 0) {
                    // If no bids, allow delete and view
                    echo "<td>
                        <a href='mylistings.php?delete_id=$auction_id' onclick='return deleteconfirm()' class='btn btn-danger custom-btn'>Delete</a> <br>
                        <a href='listing.php?auction_id=$auction_id' target='_blank' class='btn btn-info custom-btn'>View</a>
                    </td>";
                } else {
                    // If there are bids, only allow view
                    echo "<td>
                        <a href='listing.php?auction_id=$auction_id' target='_blank' class='btn btn-info custom-btn'>View</a>
                    </td>";
                }
            }
        }
        ?>

        </tbody>
      </table>
    </div>

  </div> 
  
</div>
</div>

<script>
    function deleteconfirm() {
        if (confirm("Are you sure you want to remove this item from your listings? This action is irreversible.") == true) {
            return true;
        } else {
            return false;
        }
    }
</script>

<?php include_once("footer.php")?>
