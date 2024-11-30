<?php
require_once 'init.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!isset($_POST['auction_id']) || !isset($_POST['bid_amount'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$auction_id = (int)$_POST['auction_id'];
$bid_amount = (float)$_POST['bid_amount'];
$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(MAX(b.bid_amount), a.starting_price) AS current_price,
            a.end_date,
            a.seller_id,
            a.highest_bidder_id,
            a.item_name
        FROM auction a
        LEFT JOIN bid b ON a.auction_id = b.auction_id
        WHERE a.auction_id = ?
        GROUP BY a.auction_id
        FOR UPDATE
    ");

    $stmt->execute([$auction_id]);
    $auction = $stmt->fetch();

    // Validate bid
    if (!$auction) {
        throw new Exception('Auction not found');
    }

    if (new DateTime() > new DateTime($auction['end_date'])) {
        throw new Exception('Auction has ended');
    }

    if ($auction['seller_id'] == $user_id) {
        throw new Exception('You cannot bid on your own auction');
    }

    if ($bid_amount <= $auction['current_price']) {
        throw new Exception('Bid must be higher than current price');
    }

    if ($auction['highest_bidder_id'] && $auction['highest_bidder_id'] != $user_id) {
        $stmt = $pdo->prepare("
            SELECT email, username 
            FROM user 
            WHERE user_id = ?
        ");
        $stmt->execute([$auction['highest_bidder_id']]);
        $previous_bidder = $stmt->fetch();

        if ($previous_bidder) {
            $email = $previous_bidder['email'];
            $username = $previous_bidder['username'];
            $item_name = $auction['item_name'] ?? 'the auction item';
            $new_price = number_format($bid_amount, 2);

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'lucy.yang.test@gmail.com';
                $mail->Password = 'dkkswnibnpdvwslv';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('lucy.yang.test@gmail.com', 'Auction Site');
                $mail->addAddress($email, $username);
                $mail->isHTML(true);
                $mail->Subject = 'You have been outbid!';
                $mail->Body = "
                    <h1>Hi $username,</h1>
                    <p>Your bid on <strong>$item_name</strong> has been outbid.</p>
                    <p>The new highest bid is: <strong>\$$new_price</strong></p>
                    <p>You can place a new bid to stay in the race. <a href='https://example.com/listing.php?auction_id=$auction_id'>View Auction</a></p>
                    <br>
                    <p>Best regards,<br>The Auction Site Team</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                error_log("Email to $email failed: " . $mail->ErrorInfo);
            }
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO bid (auction_id, user_id, bid_amount)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$auction_id, $user_id, $bid_amount]);

    $stmt = $pdo->prepare("
        UPDATE auction
        SET current_price = ?, highest_bidder_id = ?
        WHERE auction_id = ?
    ");
    $stmt->execute([$bid_amount, $user_id, $auction_id]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
