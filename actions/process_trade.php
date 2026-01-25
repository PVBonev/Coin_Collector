<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$trade_id = (int)$_POST['trade_id'];
$action = $_POST['action']; // accept, decline, cancel, confirm_receipt, counter

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM trades WHERE id = ?");
    $stmt->execute([$trade_id]);
    $trade = $stmt->fetch();

    if (!$trade) throw new Exception("Trade not found.");
    
    if ($action === 'confirm_receipt') {
        if ($trade['status'] !== 'accepted') throw new Exception("Trade needs to be accepted first.");

        if ($trade['sender_id'] == $user_id) {
            $stmtUp = $pdo->prepare("UPDATE trades SET sender_confirmed = 1 WHERE id = ?");
            $stmtUp->execute([$trade_id]);
            $trade['sender_confirmed'] = 1; 
        } elseif ($trade['receiver_id'] == $user_id) {
            $stmtUp = $pdo->prepare("UPDATE trades SET receiver_confirmed = 1 WHERE id = ?");
            $stmtUp->execute([$trade_id]);
            $trade['receiver_confirmed'] = 1;
        } else {
            throw new Exception("Access denied.");
        }

        if ($trade['sender_confirmed'] && $trade['receiver_confirmed']) {
            $stmtItems = $pdo->prepare("SELECT * FROM trade_items WHERE trade_id = ?");
            $stmtItems->execute([$trade_id]);
            $items = $stmtItems->fetchAll();

            $updateOwner = $pdo->prepare("UPDATE user_coins SET user_id = ?, status = 'collection', is_locked = 0, added_at = NOW() WHERE id = ?");

            foreach ($items as $item) {
                if ($item['type'] == 'offered') {
                    $updateOwner->execute([$trade['receiver_id'], $item['user_coin_id']]);
                } else {
                    $updateOwner->execute([$trade['sender_id'], $item['user_coin_id']]);
                }
            }

            $pdo->prepare("UPDATE trades SET status = 'completed', updated_at = NOW() WHERE id = ?")->execute([$trade_id]);
            $_SESSION['success'] = "Transaction completed! Coins have been swapped.";
        } else {
            $_SESSION['success'] = "Receipt confirmed. Waiting for the other party.";
        }
        
        $pdo->commit();
        header("Location: ../trade_details.php?id=" . $trade_id);
        exit;
    }

    if ($trade['sender_id'] != $user_id && $trade['receiver_id'] != $user_id) throw new Exception("Access denied.");
    if ($trade['status'] !== 'pending') throw new Exception("Trade already processed.");

    if ($action === 'accept') {
        if ($trade['receiver_id'] != $user_id) throw new Exception("Only receiver can accept.");
        $pdo->prepare("UPDATE trades SET status = 'accepted', updated_at = NOW() WHERE id = ?")->execute([$trade_id]);
        $_SESSION['success'] = "Trade accepted! Contact info revealed.";
        $redirectUrl = "../trade_details.php?id=" . $trade_id;

    } elseif ($action === 'counter') {
        if ($trade['receiver_id'] != $user_id) throw new Exception("Only receiver can counter.");

        $pdo->prepare("UPDATE trades SET status = 'declined', updated_at = NOW() WHERE id = ?")->execute([$trade_id]);

        $pdo->prepare("UPDATE user_coins SET is_locked = 0 WHERE id IN (SELECT user_coin_id FROM trade_items WHERE trade_id = ?)")->execute([$trade_id]);

        $pdo->commit(); 

        header("Location: ../swap_request.php?receiver_id=" . $trade['sender_id']);
        exit;

    } elseif ($action === 'decline' || $action === 'cancel') {
        $newStatus = ($action === 'cancel') ? 'cancelled' : 'declined';
        
        if ($action === 'cancel' && $trade['sender_id'] != $user_id) throw new Exception("Only sender can cancel.");
        if ($action === 'decline' && $trade['receiver_id'] != $user_id) throw new Exception("Only receiver can decline.");

        $pdo->prepare("UPDATE trades SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$newStatus, $trade_id]);
        
        $pdo->prepare("UPDATE user_coins SET is_locked = 0 WHERE id IN (SELECT user_coin_id FROM trade_items WHERE trade_id = ?)")->execute([$trade_id]);

        $_SESSION['success'] = "Trade " . $newStatus . ". Coins unlocked.";
        $redirectUrl = "../my_trades.php";
    }

    $pdo->commit();
    header("Location: " . $redirectUrl);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: ../my_trades.php");
    exit;
}
?>