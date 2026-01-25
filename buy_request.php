<?php
// buy_request.php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];
$coin_id = isset($_GET['coin_id']) ? (int)$_GET['coin_id'] : 0;

// Взимаме инфо за монетата и продавача
$stmt = $pdo->prepare("
    SELECT uc.*, 
           u.username as seller_name, u.id as seller_id,
           cc.title, cc.denomination, cc.year, 
           uc.own_image_front, cc.catalog_image_front,
           c.name as country_name
    FROM user_coins uc
    JOIN users u ON uc.user_id = u.id
    JOIN catalog_coins cc ON uc.catalog_coin_id = cc.id
    JOIN countries c ON cc.country_id = c.id
    WHERE uc.id = ? AND uc.status = 'sell' AND uc.is_locked = 0
");
$stmt->execute([$coin_id]);
$coin = $stmt->fetch();

if (!$coin) {
    die("Coin not found, already sold, or locked in another trade.");
}

if ($coin['seller_id'] == $buyer_id) {
    die("You cannot buy your own coin.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buy Coin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="max-width: 600px; margin-top: 40px;">
        <div class="card" style="text-align: center;">
            <h1 style="margin-top: 0;">Confirm Purchase Request</h1>
            <p>You are about to send a purchase request to <strong><?php echo htmlspecialchars($coin['seller_name']); ?></strong>.</p>

            <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <?php 
                    $img = $coin['own_image_front'] ?: $coin['catalog_image_front'] ?: 'assets/images/no-coin.png';
                ?>
                <img src="<?php echo htmlspecialchars($img); ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                
                <h2 style="margin: 10px 0 5px 0;"><?php echo htmlspecialchars($coin['title']); ?></h2>
                <div style="color: #666;"><?php echo $coin['year']; ?> • <?php echo htmlspecialchars($coin['country_name']); ?></div>
                
                <div style="font-size: 1.5rem; color: #28a745; font-weight: bold; margin-top: 15px;">
                    Price: <?php echo number_format($coin['price'], 2); ?> lv.
                </div>
            </div>

            <p style="font-size: 0.9rem; color: #666; margin-bottom: 20px;">
                Once the seller accepts, you will see their email to arrange payment and delivery.<br>
                The coin will be reserved (locked) for you until the deal is complete.
            </p>

            <form action="actions/create_trade.php" method="POST">
                <input type="hidden" name="type" value="sell">
                <input type="hidden" name="receiver_id" value="<?php echo $coin['seller_id']; ?>">
                <input type="hidden" name="requested[]" value="<?php echo $coin['id']; ?>">
                
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <a href="catalog_coin.php?id=<?php echo $coin['catalog_coin_id']; ?>" class="btn" style="background: #ccc; color: #333;">Cancel</a>
                    <button type="submit" class="btn btn-accent">Confirm Buy Request</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>