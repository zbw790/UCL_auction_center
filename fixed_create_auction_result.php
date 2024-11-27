<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("header.php");
require_once('init.php');
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
                throw new Exception("Please fill in all required fields");
                }
        }

        if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
            // 编辑模式：不修改 start_date 和 end_date，只验证其正确性
            // 从数据库获取原始的 start_date 和 end_date
            $start_date = new DateTime($auction['start_date']);  
            $end_date = new DateTime($auction['end_date']);  
            // 提交时，用户不能修改这两个时间，因此直接跳过 start_date 和 end_date 的验证
            // 但是仍然需要验证 end_date 是否大于 start_date
            if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
                $new_end_date = new DateTime($_POST['end_date']);
                if ($new_end_date <= $start_date) {
                    throw new Exception("End date must be after start date");
                }
            }

        } else {
            // 创建模式：验证 start_date 和 end_date
            $start_date = new DateTime($_POST['start_date']);
            $end_date = new DateTime($_POST['end_date']);
            $now = new DateTime();
        
            if ($start_date < $now) {
                throw new Exception("Start date must be in the future");
            }
        
            if ($end_date <= $start_date) {
                throw new Exception("End date must be after start date");
            }
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