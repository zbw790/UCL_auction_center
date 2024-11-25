<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("header.php");
require './db_connect.php';

$auction = null;
if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
    // Get original auction information for editing
    $stmt = $pdo->prepare("SELECT * FROM auction WHERE auction_id = :auction_id AND seller_id = :seller_id");
    $stmt->execute([':auction_id' => $_POST['edit_id'], ':seller_id' => $_SESSION['user_id']]);
    $auction = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
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
                throw new Exception("Please fill in all required fields");
                }
        }

        // Validate atart and end date of auction
        $start_date = new DateTime($_POST['start_date']);
        $end_date = new DateTime($_POST['end_date']);
        $now = new DateTime();

        if ($end_date <= $start_date) {
            throw new Exception("End date must be after start date");
        }

        // Process form data（for editing, auction_title, category_id should not be modified）
        $auction_title = !empty($_POST['auction_title']) ? trim($_POST['auction_title']) : (isset($auction['item_name']) ? $auction['item_name'] : null);
        $category_id = !empty($_POST['category']) ? (int)$_POST['category'] : (isset($auction['category_id']) ? $auction['category_id'] : null);
        $details = trim($_POST['details']);
        $start_price = (float)$_POST['start_price'];
        $reserve_price = !empty($_POST['reserve_price']) ? (float)$_POST['reserve_price'] : null;
 
        if ($start_price <= 0) {
            throw new Exception("Starting price must be greater than 0");
        }

        if ($reserve_price !== null && $reserve_price < $start_price) {
            throw new Exception("Reserve price cannot be less than starting price");
        }
        
       // Process uploaded image(if there are new images)
       if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "./images/";
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                throw new Exception("Failed to create images directory");
            }
        }

        $img_url = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $img_url;

        // Check if the uploaded file is image format
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            throw new Exception("File is not an image");
        }

        // Moving image to 'images/'
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            throw new Exception("Failed to upload image");
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
            ':start_date' => $_POST['start_date'],
            ':end_date' => $_POST['end_date'],
            ':starting_price' => $start_price,
            ':reserve_price' => $reserve_price,
            ':image_url' => $img_url
        ]);
        
        //Insert auction_id in final step
        $auction_id = $pdo->lastInsertId();
        
        echo '<div class="alert alert-success text-center">
                Auction successfully created! 
                <a href="listing.php?auction_id=' . $auction_id . '">View your new listing</a>
              </div>';
    }

    } catch (Exception $e) {
        // Get error message if there is an error
        echo '<div class="alert alert-danger text-center">
                Error: ' . htmlspecialchars($e->getMessage()) . '
                <br><a href="create_auction.php">Go back to create auction</a>
              </div>';
        // Record errors
        error_log("Auction creation error: " . $e->getMessage());
    }
} else {
    header('Location: create_auction.php');
    exit();
}
?>
</div>

<?php include_once("footer.php") ?>