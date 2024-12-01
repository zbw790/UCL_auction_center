<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php require("db_connect.php")?>

<link rel="stylesheet" href="css/custom_3.css">
<div class="jumbotron bg-image">
<div class="container">

  <h2 class="my-3">My Transactions</h2>
  
  <div class="checkout-left">  
     <div class="col-md-12">
        <!-- Transaction Summary Cards -->
        <?php
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: browse.php');
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Get transaction statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_purchases,
                COALESCE(SUM(transaction_amount), 0) as total_spent
            FROM auction_transaction 
            WHERE buyer_id = :user_id
        ");
        $stmt->execute(['user_id' => $user_id]);
        $stats = $stmt->fetch();
        ?>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Purchase Summary</h5>
                        <p class="card-text">Total Purchases: <?php echo $stats['total_purchases']; ?></p>
                        <p class="card-text">Total Spent: £<?php echo number_format($stats['total_spent'], 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <table id="datatable" class="table table-striped table-bordered dataTable" role="grid" aria-describedby="example_info">            
            <thead>
                <tr>  
                    <th>Item Name</th>
                    <th>Transaction Type</th>
                    <th>Other Party</th>
                    <th>Amount</th>
                    <th>Transaction Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Get all transactions (both as buyer and seller)
            $stmt = $pdo->prepare("
                SELECT 
                    t.*,
                    a.item_name,
                    a.seller_id,
                    CASE 
                        WHEN t.buyer_id = :user_id THEN 'Purchase'
                        ELSE 'Sale'
                    END as transaction_type,
                    CASE 
                        WHEN t.buyer_id = :user_id THEN seller.username
                        ELSE buyer.username
                    END as other_party
                FROM auction_transaction t
                JOIN auction a ON t.auction_id = a.auction_id
                JOIN user seller ON a.seller_id = seller.user_id
                JOIN user buyer ON t.buyer_id = buyer.user_id
                WHERE t.buyer_id = :user_id OR a.seller_id = :user_id
                ORDER BY t.transaction_date DESC
            ");
            $stmt->execute(['user_id' => $user_id]);

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['transaction_type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['other_party']) . "</td>";
                echo "<td>£" . number_format($row['transaction_amount'], 2) . "</td>";
                echo "<td>" . date("d/m/Y h:i A", strtotime($row['transaction_date'])) . "</td>";
                
                // Status column (you might want to add more status types)
                echo "<td><span class='badge bg-success'>Completed</span></td>";
                
                // Action column
                echo "<td>
                    <a href='listing.php?auction_id=" . $row['auction_id'] . "' target='_blank' class='btn btn-info custom-btn'>View Auction</a>
                </td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
     </div>
  </div> 
  
</div>
</div>

<!-- Optional: Add any JavaScript you need -->
<script>
$(document).ready(function() {
    $('#datatable').DataTable();
});
</script>

<?php include_once("footer.php")?>