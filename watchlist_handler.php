<?php
require_once 'init.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!isset($_POST['auction_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$auction_id = (int)$_POST['auction_id'];
$user_id = $_SESSION['user_id'];
$action = $_POST['action'];

try {
    if ($action === 'add') {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO watchlist (user_id, auction_id)
            VALUES (?, ?)
        ");
    } else {
        $stmt = $pdo->prepare("
            DELETE FROM watchlist
            WHERE user_id = ? AND auction_id = ?
        ");
    }
    
    $stmt->execute([$user_id, $auction_id]);
    
    // Get updated watch count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as watch_count 
        FROM watchlist 
        WHERE auction_id = ?
    ");
    $stmt->execute([$auction_id]);
    $watch_count = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'watch_count' => $watch_count
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>