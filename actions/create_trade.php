<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = (int)$_POST['receiver_id'];
$type = $_POST['type'] ?? 'swap';
$message = isset($_POST['message']) ? trim($_POST['message']) : null;

$offered_ids = isset($_POST['offered']) ? $_POST['offered'] : [];
$requested_ids = isset($_POST['requested']) ? $_POST['requested'] : [];

if ($type === 'swap') {
    if (empty($offered_ids) && empty($requested_ids)) {
        $_SESSION['error'] = "Invalid swap selection.";
        header("Location: ../index.php");
        exit;
    }
} elseif ($type === 'sell') {
    if (empty($requested_ids)) {
        $_SESSION['error'] = "No coin selected for purchase.";
        header("Location: ../index.php");
        exit;
    }
}

try {
    $pdo->beginTransaction();

    $stmtTrade = $pdo->prepare("INSERT INTO trades (sender_id, receiver_id, type, status, message, created_at) VALUES (?, ?, ?, 'pending', ?, NOW())");
    $stmtTrade->execute([$sender_id, $receiver_id, $type, $message]);
    $trade_id = $pdo->lastInsertId();

    if (!empty($offered_ids)) {
        $sqlItem = "INSERT INTO trade_items (trade_id, user_coin_id, type) VALUES (?, ?, 'offered')";
        $stmtItem = $pdo->prepare($sqlItem);

        $stmtLock = $pdo->prepare("UPDATE user_coins SET is_locked = 1 WHERE id = ? AND user_id = ?");

        foreach ($offered_ids as $coin_id) {

            $stmtItem->execute([$trade_id, $coin_id]);

            $stmtLock->execute([$coin_id, $sender_id]);

            if ($stmtLock->rowCount() == 0) {
                throw new Exception("One of your offered coins is not available.");
            }
        }
    }

    if (!empty($requested_ids)) {
        $sqlItem = "INSERT INTO trade_items (trade_id, user_coin_id, type) VALUES (?, ?, 'requested')";
        $stmtItem = $pdo->prepare($sqlItem);

        $stmtLock = $pdo->prepare("UPDATE user_coins SET is_locked = 1 WHERE id = ? AND user_id = ?");

        foreach ($requested_ids as $coin_id) {
            $stmtItem->execute([$trade_id, $coin_id]);

            $stmtLock->execute([$coin_id, $receiver_id]);

            if ($stmtLock->rowCount() == 0) {
                throw new Exception("One of the requested coins is no longer available.");
            }
        }
    }

    $pdo->commit();

    $_SESSION['success'] = "Trade offer sent successfully! Coins are now locked pending approval.";
    header("Location: ../index.php"); // can redirect to my trades in the future

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Trade failed: " . $e->getMessage();
    header("Location: ../swap_request.php?receiver_id=$receiver_id");
    exit;
}
