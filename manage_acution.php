<?php
include_once("header.php");
require_once('init.php');
require_once('utilities.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}


$auction_id = $_GET['auction_id'];

try {
    // get auction information
    $stmt = $pdo->prepare("SELECT * FROM auction WHERE auction_id = ?");
    $stmt->execute([$auction_id]);
    $auction = $stmt->fetch();

    if (!$auction) {
        echo ('<div class="alert alert-danger">Auction not found!</div>');
        exit();
    }

    // validate identification
    if ($_SESSION['user_id'] !== $auction['seller_id']) {
        echo "<div class='alert alert-danger'>You do not have permission to manage this auction.</div>";
        exit();
    }

    var_dump($auction);
    var_dump($_SESSION['user_id']);
    var_dump($auction['seller_id']);

    $start_date = new DateTime($auction['start_date']);
    $end_date = new DateTime($auction['end_date']);

    
    $is_seller = ($_SESSION['user_id'] == $auction['seller_id']);
    if ($is_seller && $auction['status'] == 'active' && $start_date < $end_date) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_auction'])) {
            $stmt = $pdo->prepare("UPDATE auction SET status = 'cancelled' WHERE auction_id = ?");
            $stmt->execute([$auction_id]);
            echo ('<div class="alert alert-success">Auction successfully cancelled.</div>');
            exit();
        }
    }
} catch (PDOException $e) {
    echo ('<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>');
}
?>

<div class="container my-5">
    <?php if ($is_seller && $auction['status'] == 'active' && $start_date < $end_date): ?>
        <form method="post">
            <button type="submit" name="delete_auction" class="btn btn-danger">Cancel Auction</button>
        </form>
    <?php endif; ?>
</div>

<?php include_once("footer.php"); ?>