<?php 
include_once("header.php");
require_once('db_connect.php');
require_once('utilities.php');

$auction = null;
if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
    // Get original auction information for editing
    $stmt = $pdo->prepare("SELECT * FROM auction WHERE auction_id = :auction_id AND seller_id = :seller_id");
    $stmt->execute([':auction_id' => $_POST['edit_id'], ':seller_id' => $_SESSION['user_id']]);
    $auction = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container my-5">
<?php
if (isset($_POST['submit'])) {
    try {
        //Validate required field for creating auction (not necessary for modifying auction)
        if (empty($_POST['auction_title']) || empty($_POST['category']) || 
            empty($_POST['start_price']) || empty($_POST['start_date']) || 
            empty($_POST['end_date']) || empty($_POST['details'])) {
                if (empty($_POST['edit_id'])) {
                    echo ('<div class="alert alert-danger">Failed to create auction: Please fill in all required fields.</div>');
                    exit();
                }
        }

        if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
            // Edit mode: Do not modify start_date and end_date, only validate their correctness
            // Retrieve the original start_date and end_date from the database

            $start_date = new DateTime($auction['start_date']);  
            $end_date = new DateTime($auction['end_date']);  

            // On submission, the user cannot modify these two dates, so skip the validation for start_date and end_date
            // However, it is still necessary to validate that end_date is greater than start_date

            if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
                $new_end_date = new DateTime($_POST['end_date']);
                if ($new_end_date <= $start_date) {
                    echo ('<div class="alert alert-danger">Failed to modify auction: End date must be after start date.</div>');
                    exit();
                }
            }

        } else {
            // Create mode: Validate start_date and end_date
            $start_date = new DateTime($_POST['start_date']);
            $end_date = new DateTime($_POST['end_date']);
            $now = new DateTime();
        
            if ($start_date < $now) {
               echo ('<div class="alert alert-danger">Failed to create auction: Start date must be in the future.</div>');
               exit();
            }
        
            if ($end_date <= $start_date) {
               echo ('<div class="alert alert-danger">Failed to create auction: End date must be after start date.</div>');
               exit();
            }
        }

        // Process form data（for editing, auction_title, category_id should not be modified）
        $auction_title = !empty($_POST['auction_title']) ? trim($_POST['auction_title']) : (isset($auction['item_name']) ? $auction['item_name'] : null);
        $category_id = !empty($_POST['category']) ? (int)$_POST['category'] : (isset($auction['category_id']) ? $auction['category_id'] : null);
        $details = trim($_POST['details']);
        $start_price = (float)$_POST['start_price'];
        $reserve_price = !empty($_POST['reserve_price']) ? (float)$_POST['reserve_price'] : null;
        
        if ($start_price <= 0) {
            echo ('<div class="alert alert-danger">Starting price must be greater than 0.</div>');
            exit();
        }

        if ($reserve_price !== null && $reserve_price < $start_price) {
            echo ('<div class="alert alert-danger">Reserve price cannot be less than starting price.</div>');
            exit();
        }
        
        // Process uploaded image(if there are new images)
       if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "./images/";
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                echo ('<div class="alert alert-danger">Failed to create images directory.</div>');
                exit();
            }
        }

        $img_url = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $img_url;

        // Check if the uploaded file is image format
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            echo ('<div class="alert alert-danger">File is not an image.</div>');
            exit();
        }

        // Moving image to 'images/'
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            echo ('<div class="alert alert-danger">Failed to upload image.</div>');
            exit();
        }

        $img_url = 'images/' . $img_url;
    } else {
        // Editor can choose upload new image or keep the original image
        // Image must be upload if user create new auction
        if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])&& isset($auction)) {
            $img_url = $auction['image_url'] ?? null;
        } else {
            $img_url = null;
        }
    }
        $user_id = $_SESSION['user_id'];
        //Modify created auction's information and update them in database
        if (isset($_POST['edit_id']) && $_POST['edit_id'] != '') {
            $edit_id = (int)$_POST['edit_id'];
            $stmt = $pdo->prepare("
            UPDATE auction
            SET 
                description = :description,
                category_id = :category_id,
                starting_price = :starting_price,
                reserve_price = :reserve_price,
                image_url = :image_url
            WHERE auction_id = :auction_id AND seller_id = :seller_id
        ");
        
            $stmt->execute([
                ':description' => $details,
                ':category_id' => $category_id,
                ':starting_price' => $start_price,
                ':reserve_price' => $reserve_price,
                ':image_url' => $img_url,
                ':auction_id' => $_POST['edit_id'],
                ':seller_id' => $user_id
            ]);
            echo '<div class="alert alert-success text-center">Auction successfully updated!</div>';
        }else {
            // Insert a new auction item's information into the database
            $stmt = $pdo->prepare("
                INSERT INTO auction (
                    seller_id, 
                    item_name, 
                    description, 
                    category_id, 
                    start_date, 
                    end_date, 
                    starting_price, 
                    reserve_price, 
                    image_url,
                    status,
                    current_price
                ) VALUES (
                    :seller_id, 
                    :item_name, 
                    :description, 
                    :category_id, 
                    :start_date, 
                    :end_date, 
                    :starting_price, 
                    :reserve_price, 
                    :image_url,
                    'active',
                    :starting_price
                )
            ");
            
            $stmt->execute([
                ':seller_id' => $user_id,
                ':item_name' => $auction_title,
                ':description' => $details,
                ':category_id' => $category_id,
                ':start_date' => $start_date->format('Y-m-d H:i:s'),
                ':end_date' => $end_date->format('Y-m-d H:i:s'),
                ':starting_price' => $start_price,
                ':reserve_price' => $reserve_price,
                ':image_url' => $img_url
            ]);
            
            //Insert auction_id in final step
            $auction_id = $pdo->lastInsertId();

            echo ('<div class="text-center">Auction successfully created! Time remaining: ' . display_time_remaining($end_date->diff($start_date)) . ' 
                   <a href="listing.php?auction_id=' . $auction_id . '">View your new listing.</a></div>');
        }       
    } catch (PDOException $e) {
            echo ('<div class="alert alert-danger">Failed to create auction: ' . $e->getMessage() . '</div>');
    }
} else {
    header('Location: create_auction.php');
    exit();
}   
    ?>
    <?php include_once("footer.php"); ?>