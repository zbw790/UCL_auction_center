<?php include_once("header.php");
require_once('db_connect.php'); ?>

<div class="container my-5">

<?php
require './db_connect.php';

if (isset($_POST['submit_auction'])){
    $auction_title = trim($_POST['auction_title']);
    $category_id = (int)$_POST['category'];
    $details = trim($_POST['details']);
    $start_price = (int)$_POST['start_price'];
    $reserve_price = (int)$_POST['reserve_price'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $img_url = rand() . $_FILES["image"]["name"];
	move_uploaded_file($_FILES["image"]["tmp_name"],"./image".$img_url);
    
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        INSERT INTO auction (seller_id, item_name, description, category_id, start_date, end_date, starting_price, reserve_price, image_url)
        VALUES (:seller_id, :item_name, :description, :category_id, :start_date, :end_date, :starting_price, :reserve_price, :image_url)
     ");
    
    $stmt->execute([
         ':seller_id' => $user_id,
         ':item_name' => $auction_title,
         ':description' => $details,
         ':category_id' => $category_id,
         ':start_date' => $start_date,
         ':end_date' => $end_date,
         ':starting_price' => $start_price,
         ':reserve_price' => $reserve_price,
         ':image_url' => $img_url
     ]);
     $auction_id = $pdo->lastInsertId();
                
}






// This function takes the form data and adds the new auction to the database.

/* TODO #1: Connect to MySQL database (perhaps by requiring a file that
            already does this). */


/* TODO #2: Extract form data into variables. Because the form was a 'post'
            form, its data can be accessed via $POST['auctionTitle'], 
            $POST['auctionDetails'], etc. Perform checking on the data to
            make sure it can be inserted into the database. If there is an
            issue, give some semi-helpful feedback to user. */


/* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */
            

// If all is successful, let user know.
echo('<div class="text-center">Auction successfully created! <a href="FIXME">View your new listing.</a></div>');


?>

</div>

<?php include_once("footer.php"); ?>