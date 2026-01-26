<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$trade_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT t.*, 
           u_sender.username as sender_name, u_sender.email as sender_email, u_sender.id as sender_uid,
           u_receiver.username as receiver_name, u_receiver.email as receiver_email, u_receiver.id as receiver_uid
    FROM trades t
    JOIN users u_sender ON t.sender_id = u_sender.id
    JOIN users u_receiver ON t.receiver_id = u_receiver.id
    WHERE t.id = ? AND (t.sender_id = ? OR t.receiver_id = ?)
");
$stmt->execute([$trade_id, $user_id, $user_id]);
$trade = $stmt->fetch();

if (!$trade) die("Trade not found.");

$am_i_sender = ($trade['sender_id'] == $user_id);
$partner_name = $am_i_sender ? $trade['receiver_name'] : $trade['sender_name'];
$partner_email = $am_i_sender ? $trade['receiver_email'] : $trade['sender_email'];

$stmtItems = $pdo->prepare("
    SELECT ti.*, 
           cc.title, cc.denomination, cc.year, uc.price,
           uc.own_image_front, cc.catalog_image_front
    FROM trade_items ti
    JOIN user_coins uc ON ti.user_coin_id = uc.id
    JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
    WHERE ti.trade_id = ?
");
$stmtItems->execute([$trade_id]);
$items = $stmtItems->fetchAll();

$offered_items = []; // swap
$requested_items = []; // swap and sell
$total_price = 0;

foreach ($items as $item) {
    if ($item['type'] == 'offered') {
        $offered_items[] = $item;
    } else {
        $requested_items[] = $item;
        $total_price += $item['price']; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trade #<?php echo $trade_id; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <a href="my_trades.php" style="color: #666; text-decoration: none;">&larr; Back to Trades</a>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
            <h1>
                <?php echo ($trade['type'] == 'sell') ? 'Purchase Request' : 'Swap Trade'; ?> 
                <small style="font-size: 1rem; color: #777;">#<?php echo $trade_id; ?> with <?php echo htmlspecialchars($partner_name); ?></small>
            </h1>
            <div class="status-box st-<?php echo $trade['status']; ?>" style="margin-bottom: 0;">
                <?php echo strtoupper($trade['status']); ?>
            </div>
        </div>

        <?php if ($trade['status'] == 'accepted'): ?>
            <div style="background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #c8e6c9;">
                <h3 style="margin-top: 0; color: #2e7d32;">Offer Accepted!</h3>
                <p>Contact <strong><?php echo htmlspecialchars($partner_name); ?></strong> to arrange <?php echo ($trade['type'] == 'sell') ? 'payment & ' : ''; ?>delivery.</p>
                <p><strong>Email:</strong> <a href="mailto:<?php echo $partner_email; ?>"><?php echo $partner_email; ?></a></p>
                
                <hr style="border: 0; border-top: 1px solid #c8e6c9; margin: 15px 0;">
                
                <p style="font-size: 0.9rem; color: #555; margin-bottom: 10px;"><strong>Final Step:</strong> Confirm when the transaction is physically complete.</p>
                
                <div style="display: flex; gap: 20px; align-items: center;">
                    <?php 
                        $i_confirmed = ($am_i_sender && $trade['sender_confirmed']) || (!$am_i_sender && $trade['receiver_confirmed']);
                        $they_confirmed = ($am_i_sender && $trade['receiver_confirmed']) || (!$am_i_sender && $trade['sender_confirmed']);
                        
                        if ($trade['type'] == 'sell') {
                            $btnText = $am_i_sender ? "I Received the Coin" : "I Received Payment";
                        } else {
                            $btnText = "I Received the Items";
                        }
                    ?>

                    <?php if ($i_confirmed): ?>
                        <button class="btn" style="background: #ccc; cursor: default;" disabled>✔ You Confirmed</button>
                    <?php else: ?>
                        <form action="actions/process_trade.php" method="POST">
                            <input type="hidden" name="trade_id" value="<?php echo $trade_id; ?>">
                            <button type="submit" name="action" value="confirm_receipt" class="btn" style="background: #28a745;">
                                <?php echo $btnText; ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($they_confirmed): ?>
                        <span style="color: #28a745; font-weight: bold;">(Partner confirmed)</span>
                    <?php else: ?>
                        <span style="color: #666; font-style: italic;">(Waiting for partner...)</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="details-split">
            
            <div class="details-col">
                <?php if ($trade['type'] == 'sell'): ?>
                    <h3 style="border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; margin-top: 0;">Payment</h3>
                    <div style="text-align: center; padding: 30px 0;">
                        <span style="font-size: 3rem; color: #28a745; font-weight: bold;">
                            <?php echo number_format($total_price, 2); ?> <small>lv.</small>
                        </span>
                        <p style="color: #666;">Total Price for items</p>
                        <p style="font-size: 0.9rem; font-style: italic;">(Shipping not included)</p>
                    </div>
                <?php else: ?>
                    <h3 style="border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; margin-top: 0;">
                        <?php echo ($am_i_sender) ? 'You Offer' : $partner_name . ' Offers'; ?>
                    </h3>
                    <?php foreach ($offered_items as $item): ?>
                        <div class="coin-mini-card">
                            <?php $img = $item['own_image_front'] ?: $item['catalog_image_front'] ?: 'assets/images/no-coin.png'; ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" class="coin-mini-img">
                            <div>
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                <small><?php echo $item['year']; ?> • <?php echo htmlspecialchars($item['denomination']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #ccc;">
                <?php echo ($trade['type'] == 'sell') ? '&#10140;' : '&#8644;'; ?>
            </div>

            <div class="details-col">
                <h3 style="border-bottom: 2px solid var(--accent-color); padding-bottom: 10px; margin-top: 0;">
                    <?php 
                        if ($trade['type'] == 'sell') {
                            echo 'Items being Sold';
                        } else {
                            echo ($am_i_sender) ? 'You Get' : $partner_name . ' Gets';
                        }
                    ?>
                </h3>
                <?php foreach ($requested_items as $item): ?>
                    <div class="coin-mini-card">
                        <?php $img = $item['own_image_front'] ?: $item['catalog_image_front'] ?: 'assets/images/no-coin.png'; ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" class="coin-mini-img">
                        <div>
                            <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                            <small><?php echo $item['year']; ?> • <?php echo htmlspecialchars($item['denomination']); ?></small>
                            <?php if($trade['type'] == 'sell'): ?>
                                <br><span style="color: #28a745; font-weight: bold; font-size: 0.85rem;"><?php echo number_format($item['price'], 2); ?> €</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($trade['status'] == 'pending'): ?>
            <div style="margin-top: 30px; text-align: center; padding: 20px; background: #fff; border-radius: 8px;">
                
                <?php if (!$am_i_sender): ?>
                    <p><strong><?php echo htmlspecialchars($partner_name); ?></strong> wants to buy these items.</p>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <form action="actions/process_trade.php" method="POST">
                            <input type="hidden" name="trade_id" value="<?php echo $trade_id; ?>">
                            <button type="submit" name="action" value="accept" class="btn" style="background: #28a745;">Accept Sale</button>
                        </form>
                        
                        <form action="actions/process_trade.php" method="POST">
                            <input type="hidden" name="trade_id" value="<?php echo $trade_id; ?>">
                            <button type="submit" name="action" value="decline" class="btn" style="background: #dc3545;">Decline</button>
                        </form>
                    </div>

                <?php else: ?>
                    <p>Waiting for seller confirmation.</p>
                    <form action="actions/process_trade.php" method="POST">
                        <input type="hidden" name="trade_id" value="<?php echo $trade_id; ?>">
                        <button type="submit" name="action" value="cancel" class="btn" style="background: #6c757d;">Cancel Request</button>
                    </form>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>
</body>
</html>